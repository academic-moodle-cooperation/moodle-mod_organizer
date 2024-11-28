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

use mod_organizer\event\slot_updated;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_edit.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

[$cm, $course, $organizer, $context] = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = organizer_get_param_slots();
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

$params['limitedwidth'] = organizer_get_limitedwidth();

$redirecturl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id, 'mode' => $mode]);

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode;

require_capability('mod/organizer:editslots', $context);

if (!$slots) {
    $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_warning_no_slots_selected', 'organizer'),
        'error');
    redirect($redirecturl);
}

$mform = new organizer_edit_slots_form(
    null, ['id' => $cm->id, 'mode' => $mode, 'slots' => $slots]);

if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else if ($data = $mform->get_data()) {
    $slotids = organizer_update_slot($data);

    $a = new stdClass();
    $a->count = count($slotids);
    if ($a->count == 1) {
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_slots_edited_sg',
            'organizer', $a), 'success');
    } else {
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_slots_edited_pl', 'organizer', $a),
            'success');
    }

    $data->slot_trainer = $data->slot_trainer ?? "-";
    $data->slot_location = $data->slot_location ?? "-";
    $data->slot_comments = $data->slots_comments ?? "-";
    organizer_prepare_and_send_message($data, 'edit_notify_teacher');
    organizer_prepare_and_send_message($data, 'edit_notify_student');

    $redirecturl->param('slots', implode(',', array_values($slotids)));
    $newurl = $redirecturl->out();

    $event = slot_updated::create(
        [
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context,
        ]
    );
    $event->trigger();

    redirect($newurl);
} else {
    organizer_display_form($mform, get_string('title_edit', 'organizer'));
}
throw new coding_exception('If you see this, something went wrong with edit action!');

die;
