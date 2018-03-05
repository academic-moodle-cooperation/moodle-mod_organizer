<?php
// This file is part of mod_organizer for Moodle - http://moodle.org/
//
// It is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// It is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * view_action.php
 *
 * @package   mod_organizer
 * @author    Andreas Windbichler
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_delete.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = optional_param_array('slots', array(), PARAM_INT);
$app = optional_param('app', null, PARAM_INT);
$tsort = optional_param('tsort', null, PARAM_ALPHA);

$url = new moodle_url('/mod/organizer/view_action.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('action', $action);
$url->param('sesskey', sesskey());

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;


require_capability('mod/organizer:deleteslots', $context);

if (!$slots) {
    $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
    redirect($redirecturl);
}

if (!organizer_security_check_slots($slots)) {
    print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
}

$mform = new organizer_delete_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

if ($data = $mform->get_data()) {
    if (isset($slots)) {
        $notified = 0;
        foreach ($slots as $slotid) {
            $notified += organizer_delete_appointment_slot($slotid);
        }

        $slotsdeleted = count($slots);

        $slots = $DB->get_records('organizer_slots', array('organizerid' => $organizer->id));

        $event = \mod_organizer\event\slot_deleted::create(
            array(
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context
            )
        );
        $event->trigger();

        // Count_records_sql doesnt work.
           $appointmentstotal = $DB->get_record_sql(
               'SELECT COUNT(*) as total
            FROM {organizer_slots} org
            JOIN {organizer_slot_appointments} app ON org.id = app.slotid
            WHERE org.organizerid=?', array($organizer->id)
           );
           $appointmentstotal = $appointmentstotal->total;

        if ($organizer->isgrouporganizer==ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $redirecturl->param('messages[]', 'message_info_slots_deleted_group');

            $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
            $registrantstotal = count($groups);

            $placestotal = count($slots);

        } else {
            $redirecturl->param('messages[0]', 'message_info_slots_deleted');

            $slots = $DB->get_records('organizer_slots', array('organizerid' => $organizer->id));
            $placestotal = 0;
            foreach ($slots as $slot) {
                $placestotal += $slot->maxparticipants;
            }

            $registrantstotal = count(get_enrolled_users($context, 'mod/organizer:register'));
        }

        $freetotal = $placestotal - $appointmentstotal;
        $notregistered = $registrantstotal - $appointmentstotal;

        $redirecturl->param('data[deleted]', $slotsdeleted);
        $redirecturl->param('data[notified]', $notified); // Anzahl benachrichtigter studenten.
        $redirecturl->param('data[freeslots]', $freetotal); // Freie slots.
        $redirecturl->param('data[notregistered]', $notregistered); // Anzahl noch nicht angemeldeter studenten.

        $prefix = ($notregistered > $freetotal) ? 'warning' : 'info';
        $suffix = ($organizer->isgrouporganizer==ORGANIZER_GROUPMODE_EXISTINGGROUPS) ? '_group' : '';

        $redirecturl->param('messages[1]', 'message_' . $prefix . '_available' . $suffix);

    }
    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('title_delete', 'organizer'));
}
print_error('If you see this, something went wrong with delete action!');

die;