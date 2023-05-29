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
 * @copyright 2022 Academic Moodle Cooperation {@link https://www.academic-moodle-cooperation.org/}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for mod_organizer.
 */
class mod_organizer_observer {

    /**
     * Triggered via user_enrolment_deleted event.
     *
     * @param \core\event\user_enrolment_deleted $event
     */
    public static function user_enrolment_deleted(/*\core\event\user_enrolment_deleted*/ $event) {
        global $DB;
        require_once(__DIR__ . '/../locallib.php');
        require_once(__DIR__ . '/../messaging.php');

        // NOTE: this has to be as fast as possible.
        // Get user enrolment info from event.
        $cp = (object)$event->other['userenrolment'];
        if ($cp->lastenrol) {
            if (!$organizers = $DB->get_records('organizer', array('course' => $cp->courseid))) {
                return;
            }
            list($organizerselect, $params) = $DB->get_in_or_equal(array_keys($organizers), SQL_PARAMS_NAMED);

            // Handle queues.
            foreach ($organizers as $organizer) {
                organizer_delete_user_from_any_queue($organizer->id, $cp->userid);
            }

            // Handle slots.
            $slots = $DB->get_records_select('organizer_slots', 'organizerid ' . $organizerselect, $params);

            list($slotselect, $slotparams) = $DB->get_in_or_equal(array_keys($slots), SQL_PARAMS_NAMED);
            $slotparams['userid'] = $cp->userid;

            $slotappointments = $DB->get_records_select('organizer_slot_appointments', 'userid = :userid AND slotid ' . $slotselect, $slotparams);

            foreach ($slotappointments as $slotappointment) {
                $slot = $slots[$slotappointment->slotid];
                $organizer = $organizers[$slot->organizerid];
                $success = organizer_unregister_single_appointment($slot->id, $cp->userid, $organizer);
                if ($success) {
                    if ($organizer->queue == '1') {
                        if ($organizer->isgrouporganizer != ORGANIZER_GROUPMODE_EXISTINGGROUP) {
                            $slotx = new organizer_slot($slot, true, $organizer);
                            if ($next = $slotx->get_next_in_queue()) {
                                organizer_register_appointment($slot->id, 0, $next->userid, true);
                                organizer_delete_from_queue($slot->id, $next->userid);
                            }
                        }
                        // TODO: Handle group mode? Is it even possible???
                        /*
                            if ($organizer->isgrouporganizer== ORGANIZER_GROUPMODE_EXISTINGGROUP) {
                                if ($next = $slotx->get_next_in_queue_group()) {
                                    organizer_register_appointment($slot->id, $next->groupid, 0, true);
                                    organizer_delete_from_queue($slot->id, null, $next->groupid);
                                }
                            }
                        */
                    }
                    organizer_prepare_and_send_message($slot, 'register_notify_teacher:unregister'); // Message.
                }
            }
        }
    }
}
