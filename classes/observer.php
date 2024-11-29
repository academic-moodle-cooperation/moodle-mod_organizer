<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Event observers used in organizer.
 *
 * @package    mod_organizer
 * @author Simeon Naydenov (moninaydenov@gmail.com)
 * @author Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @copyright 2022 Academic Moodle Cooperation {@link https://www.academic-moodle-cooperation.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\event\group_member_added;
use core\event\group_member_removed;
use core\event\user_enrolment_deleted;
use mod_grouptool\event\registration_created;
use mod_grouptool\event\registration_deleted;

/**
 * Event observer for mod_organizer.
 */
class mod_organizer_observer {

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted($event) {
        global $DB;
        require_once(__DIR__ . '/../locallib.php');
        require_once(__DIR__ . '/../messaging.php');

        // NOTE: this has to be as fast as possible.
        // Get user enrolment info from event.
        $cp = (object)$event->other['userenrolment'];
        if ($cp->lastenrol) {
            if (!$organizers = $DB->get_records('organizer', ['course' => $cp->courseid])) {
                return;
            }
            [$organizerselect, $params] = $DB->get_in_or_equal(array_keys($organizers), SQL_PARAMS_NAMED);

            // Handle queues.
            foreach ($organizers as $organizer) {
                organizer_delete_user_from_any_queue($organizer->id, $cp->userid);
            }

            // Handle slots.
            if ($slots = $DB->get_records_select('organizer_slots', 'organizerid ' . $organizerselect, $params)) {
                [$slotselect, $slotparams] = $DB->get_in_or_equal(array_keys($slots), SQL_PARAMS_NAMED);
                $slotparams['userid'] = $cp->userid;

                $slotappointments = $DB->get_records_select('organizer_slot_appointments',
                    'userid = :userid AND slotid ' . $slotselect, $slotparams);

                foreach ($slotappointments as $slotappointment) {
                    $slot = $slots[$slotappointment->slotid];
                    $organizer = $organizers[$slot->organizerid];
                    $success = organizer_unregister_single_appointment($slot->id, $cp->userid, $organizer);
                    if ($success) {
                        if ($organizer->queue == '1') {
                            $slotx = new organizer_slot($slot, true, $organizer);
                            if ($organizer->isgrouporganizer != ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                                if ($next = $slotx->get_next_in_queue()) {
                                    organizer_register_appointment($slot->id, 0, $next->userid, true);
                                    organizer_delete_from_queue($slot->id, $next->userid);
                                    $booked = organizer_count_bookedslots($organizer->id, $next->userid, null);
                                    if (organizer_multiplebookings_status($booked, $organizer->id)
                                        == USERSLOTS_MAX_REACHED) {
                                        organizer_delete_user_from_any_queue($organizer->id, $next->userid, null);
                                    }
                                }
                            }
                        }
                        organizer_prepare_and_send_message($slot->id, 'register_notify_teacher:unregister'); // Message.
                    }
                }
            }
        }
    }

    /**
     * group_member_added
     *
     * @param group_member_added $event Event object containing useful data
     * @return bool true if success
     * @throws coding_exception
     * @throws dml_exception
     */
    public static function group_member_added(group_member_added $event) {
        global $DB;

        $groupid = $event->objectid;
        $userid = $event->relateduserid;

        $params = ['groupid' => $groupid, 'groupmode' => ORGANIZER_GROUPMODE_EXISTINGGROUPS];
        if ($groupapps = $DB->get_records_sql(
            'SELECT DISTINCT a.id, a.slotid, a.applicantid, a.teacherapplicantid, s.organizerid
            FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            INNER JOIN {organizer} o ON o.id = s.organizerid
            WHERE a.groupid = :groupid AND o.isgrouporganizer = :groupmode
            AND o.synchronizegroupmembers = 1
            ORDER BY a.slotid ASC', $params
        )) {
            require_once(__DIR__ . '/../messaging.php');
            $slotid = 0;
            foreach ($groupapps as $groupapp) {
                if ($groupapp->slotid != $slotid) {
                    if (!$DB->get_field('organizer_slot_appointments', 'id', ['slotid' => $groupapp->slotid,
                        'userid' => $userid])) {
                        organizer_register_single_appointment($groupapp->slotid, $userid,
                            $groupapp->applicantid, $groupid, $groupapp->teacherapplicantid,
                            false, null, $groupapp->organizerid
                        );
                    }
                    $slotid = $groupapp->slotid;
                }
            }
        }

        return true;
    }

    /**
     * group_remove_member_handler
     * event:       groups_member_removed
     * schedule:    instant
     *
     * @param group_member_removed $event Event object containing useful data
     * @return bool true if success
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public static function group_member_removed(group_member_removed $event) {
        global $DB;

        $groupid = $event->objectid;
        $userid = $event->relateduserid;

        $params = ['groupid' => $groupid, 'userid' => $userid, 'groupmode' => ORGANIZER_GROUPMODE_EXISTINGGROUPS];
        if ($apps = $DB->get_records_sql(
            'SELECT DISTINCT a.id
            FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            INNER JOIN {organizer} o ON o.id = s.organizerid
            WHERE a.groupid = :groupid AND a.userid = :userid AND o.isgrouporganizer = :groupmode
            AND o.synchronizegroupmembers = 1', $params
        )) {
            require_once(__DIR__ . '/../messaging.php');
            foreach ($apps as $app) {
                organizer_delete_appointment($app->id);
            }
        }

        return true;
    }

}
