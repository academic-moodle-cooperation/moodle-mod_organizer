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
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_remind_all.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$recipient = optional_param('user', null, PARAM_INT);
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

$jsmodule = array(
        'name' => 'mod_organizer',
        'fullpath' => '/mod/organizer/module.js',
        'requires' => array('node', 'event', 'node-screen', 'panel', 'node-event-delegate'),
);
$PAGE->requires->js_module($jsmodule);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

// Get recipients.
if ($recipient != null) {
    $recipients = array();

    if ($cm->groupmode == 0) {
        $recipients = $DB->get_records_list('user', 'id', array($recipient));
    } else {
        $recipients = groups_get_members($recipient, $fields = 'u.id, u.idnumber', $sort = 'lastname ASC');
    }

    $counter = count($recipients);

} else {
    // Send reminders to all students without an appointment.
    $counter = 0;
    $recipients = array();

    $entries = organizer_organizer_get_status_table_entries(array('sort' => ''));

    // Filter all not registered and not attended.
    foreach ($entries as $entry) {
        if ($entry->status == ORGANIZER_APP_STATUS_NOT_REGISTERED || $entry->status == ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP) {
            $counter++;
            $recipients[] = $entry;
        }
    }
}




$mform = new organizer_remind_all_form(null, array('id' => $cm->id, 'mode' => $mode,
        'slots' => $slots, 'recipients' => $recipients, 'recipient' => $recipient));

if ($data = $mform->get_data()) {

	$recipient = $data->recipient;

    $count = organizer_remind_all($recipient, $data->message_custommessage['text']);

    $redirecturl->param('data[count]', $count);
    if ($count == 1) {
        $redirecturl->param('messages[]', 'message_info_reminders_sent_sg');
    } else {
        $redirecturl->param('messages[]', 'message_info_reminders_sent_pl');
    }

    $event = \mod_organizer\event\appointment_reminder_sent::create(array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
    ));
    $event->trigger();

    $redirecturl = $redirecturl->out();
    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('organizer_remind_all_title', 'organizer'));
}
print_error('If you see this, something went wrong with delete action!');

die;

function organizer_remind_all($recipient = null, $custommessage = "") {
    global $DB;

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    if ($recipient != null) {
		if ($cm->groupingid == 0) {
        	$entries = $DB->get_records_list('user', 'id', array($recipient));
		} else {
        	$entries = organizer_fetch_groupusers($recipient);
		}
    } else if ($cm->groupingid == 0) {
        $entries = get_enrolled_users($context, 'mod/organizer:register');
    } else {
        $query = "SELECT u.* FROM {user} u
            INNER JOIN {groups_members} gm ON u.id = gm.userid
            INNER JOIN {groups} g ON gm.groupid = g.id
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :grouping";
        $par = array('grouping' => $cm->groupingid);
        $entries = $DB->get_records_sql($query, $par);
    }

    $query = "SELECT DISTINCT u.id FROM {organizer} o
        INNER JOIN {organizer_slots} s ON o.id = s.organizerid
        INNER JOIN {organizer_slot_appointments} a ON s.id = a.slotid
        INNER JOIN {user} u ON a.userid = u.id
        WHERE o.id = :id AND (a.attended = 1 OR a.attended IS NULL)";
    $par = array('id' => $organizer->id);
    $nonrecepients = $DB->get_fieldset_sql($query, $par);

    $count = 0;
    foreach ($entries as $entry) {
        if (!in_array($entry->id, $nonrecepients)) {
            organizer_prepare_and_send_message(array('user' => $entry->id, 'organizer' => $organizer,
                'custommessage' => $custommessage), 'register_reminder:student');
            $count++;
        }
    }
    return $count;
}