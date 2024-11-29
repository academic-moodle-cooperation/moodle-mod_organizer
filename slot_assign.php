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
 * @package   mod_organizer
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_organizer\event\appointment_assigned;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

[$cm, $course, $organizer, $context] = organizer_get_course_module_data();

require_login($course, false, $cm);

require_capability('mod/organizer:assignslots', $context);

$mode = required_param('mode', PARAM_INT);
$slotid = required_param('slot', PARAM_INT);
$assignid = required_param('assignid', PARAM_INT);

$url = new moodle_url('/mod/organizer/slot_assign.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('slot', $slotid);
$url->param('sesskey', sesskey());
$url->param('assignid', $assignid);

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$params['limitedwidth'] = organizer_get_limitedwidth();

$redirecturl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id, 'mode' => 3]);

$groupid = null;
$participantid = null;
if (organizer_is_group_mode()) {
    $groupid = $assignid;
} else {
    $participantid = $assignid;
}

$appointmentid = organizer_register_appointment($slotid, $groupid, $participantid, false, $USER->id);

$data = new stdClass();
$data->selectedslot = $slotid;

if (organizer_is_group_mode()) {
    $data->group = $groupid;
    $data->participant = false;
} else {
    $data->participant = $participantid;
    $data->group = false;
}

$sent = organizer_prepare_and_send_message($data, 'assign_notify_student'); // Message.
if ($sent) {  // If slot not in the past.
    $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('assignsuccess',
        'organizer'), 'success');
} else {
    $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('assignsuccessnotsent',
        'organizer'), 'error');
}
organizer_prepare_and_send_message($data, 'assign_notify_teacher'); // Message.

$newurl = $redirecturl->out();

$event = appointment_assigned::create(
    [
        'objectid' => $PAGE->cm->id,
        'context' => $PAGE->context,
    ]
);
$event->trigger();

redirect($newurl);

throw new coding_exception('If you see this, something went wrong!');

die;
