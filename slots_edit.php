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
require_once(dirname(__FILE__) . '/view_action_form_edit.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = optional_param_array('slots', null, PARAM_INT);
$app = optional_param('app', null, PARAM_INT);
$tsort = optional_param('tsort', null, PARAM_ALPHA);

$url = new moodle_url('/mod/organizer/view_action.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('sesskey', sesskey());

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode));

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode;

require_capability('mod/organizer:editslots', $context);

if (!$slots) {
    $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
    redirect($redirecturl);
}

$mform = new organizer_edit_slots_form(
    null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

if ($data = $mform->get_data()) {
    $slotids = organizer_update_slot($data);

    organizer_prepare_and_send_message($data, 'edit_notify_teacher');
    organizer_prepare_and_send_message($data, 'edit_notify_student'); // Message.

    $newurl = $redirecturl->out();
    foreach ($slotids as $slotid) {
        $newurl .= '&slots[]=' . $slotid;
    }

    $event = \mod_organizer\event\slot_updated::create(
        array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
        )
    );
    $event->trigger();

    redirect($newurl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('title_edit', 'organizer'));
}
print_error('If you see this, something went wrong with edit action!');

die;