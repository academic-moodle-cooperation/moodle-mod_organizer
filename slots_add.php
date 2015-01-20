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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * addslot.php
 *
 * @package       mod_organizer
 * @author        Andreas Windbichler
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/view_action_form_add.php');
require_once(dirname(__FILE__) . '/view_lib.php');

//--------------------------------------------------------------------------------------------------

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);
// require_sesskey();

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

$jsmodule = array(
        'name' => 'mod_organizer',
        'fullpath' => '/mod/organizer/module.js',
        'requires' => array('node', 'event', 'node-screen', 'panel', 'node-event-delegate'),
);
$PAGE->requires->js_module($jsmodule);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;


require_capability('mod/organizer:addslots', $context);
    
$mform = new organizer_add_slots_form(null, array('id' => $cm->id, 'mode' => $mode));

if ($data = $mform->get_data()) {
	if (isset($data->reviewslots) || isset($data->addday) || isset($data->back)) {
		organizer_display_form($mform, get_string('title_add', 'organizer'));
	} else if (isset($data->createslots)) {
		$count = count($slotids = organizer_add_appointment_slots($data));
		if ($count == 0) {
			$redirecturl->param('messages[]', 'message_warning_no_slots_added');
		} else {
			$event = \mod_organizer\event\slot_created::create(array(
					'objectid' => $PAGE->cm->id,
					'context' => $PAGE->context
			));
			$event->trigger();
			
			$redirecturl->param('data[count]', $count);
			if ($count == 1) {
				$redirecturl->param('messages[]', 'message_info_slots_added_sg');
			} else {
				$redirecturl->param('messages[]', 'message_info_slots_added_pl');
			}
	
			$redirecturl = $redirecturl->out();
			foreach ($slotids as $slotid) {
				$redirecturl .= '&slots[]=' . $slotid;
			}
		}
		redirect($redirecturl);
	} else {
		print_error('Something went wrong with the submission of the add action!');
	}
} else if ($mform->is_cancelled()) {
	redirect($redirecturl);
} else {
	organizer_display_form($mform, get_string('title_add', 'organizer'));
}
print_error('If you see this, something went wrong with add action!');

die;