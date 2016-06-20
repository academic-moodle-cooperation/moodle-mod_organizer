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
 * slot_assign.php
 *
 * @package       mod_organizer
 * @author        Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_assign.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$participant = optional_param('participant', null, PARAM_INT);
$group = optional_param('group', null, PARAM_INT);

$url = new moodle_url('/mod/organizer/slot_assign.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('action', $action);
$url->param('sesskey', sesskey());
$url->param('participant', $participant);
$url->param('group', $group);

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

require_capability('mod/organizer:assignslots', $context);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = 'slot_assign.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

$groupname = "";
if (organizer_is_group_mode()) {
	$groupname = organizer_get_groupname($group);
}

$mform = new organizer_assign_slot_form(null, array('id' => $cm->id, 'mode' => $mode, 'participant' => $participant, 'group' => $group, 'groupname' => $groupname, 'organizerid' => $organizer->id));

if ($data = $mform->get_data()) {
    
	$slotid = $data->selectedslot;
	$participantid = $data->participant;
	$groupid = $data->group;
	
	$appointment_id = organizer_register_appointment($slotid, $groupid, $participantid, false, $USER->id);

    organizer_prepare_and_send_message($data, 'assign_notify:student'); // Message.
    organizer_prepare_and_send_message($data, 'assign_notify:teacher'); // Message.

    $newurl = $redirecturl->out();

    $event = \mod_organizer\event\appointment_assigned::create(array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
    ));
    $event->trigger();

    redirect($newurl);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('title_assign', 'organizer'));
	$mform->display();
    echo $OUTPUT->footer();
}
print_error('If you see this, something went wrong with edit action!');

die;