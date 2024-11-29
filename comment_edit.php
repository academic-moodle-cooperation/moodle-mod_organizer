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

use mod_organizer\event\appointment_commented;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_comment.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

[$cm, $course, $organizer, $context] = organizer_get_course_module_data();

require_login($course, false, $cm);

$slot = optional_param('slot', null, PARAM_INT);

$url = new moodle_url('/mod/organizer/comment_edit.php');
$url->param('id', $cm->id);

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$params['limitedwidth'] = organizer_get_limitedwidth();

$redirecturl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id]);

$logurl = new moodle_url('/mod/organizer/comment_edit.php', ['id' => $cm->id]);

require_capability('mod/organizer:comment', $context);

$mform = new organizer_comment_slot_form(null, ['id' => $cm->id, 'slot' => $slot]);

if (($data = $mform->get_data()) && confirm_sesskey()) {

    $app = $DB->get_record('organizer_slot_appointments', ['slotid' => $slot, 'userid' => $USER->id]);

    organizer_update_comments($app->id, $data->comments);

    $event = appointment_commented::create(
        [
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context,
        ]
    );
    $event->trigger();

    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('title_comment', 'organizer'));
}
