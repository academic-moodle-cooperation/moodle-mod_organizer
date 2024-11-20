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
 * appointment_delete.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_organizer\event\appointment_deleted;

define("ORGANIZER_TAB_STUDENT_VIEW", 2);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/appointment_delete_form.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

$appid = optional_param('appid', null, PARAM_INT);
$id = optional_param('id', null, PARAM_INT);

[$cm, $course, $organizer, $context] = organizer_get_course_module_data();

require_login($course, false, $cm);

require_capability('mod/organizer:deleteslots', $context);

$redirecturl = new moodle_url('/mod/organizer/view.php');
$redirecturl->param('id', $cm->id);
$redirecturl->param('mode', '3');

$url = new moodle_url('/mod/organizer/appointment_delete.php', ['id' => $id, 'appid' => $appid]);
$PAGE->set_url($url);

$params['limitedwidth'] = organizer_get_limitedwidth();

$mform = new organizer_delete_appointment_form(null, ['id' => $cm->id, 'appid' => $appid]);

if ($data = $mform->get_data()) {
    $infoboxmessage = "";
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $app = $DB->get_record('organizer_slot_appointments', ['id' => $data->appid], 'slotid, groupid');
        if (organizer_delete_appointment_group($app->slotid, $app->groupid)) {
            $event = appointment_deleted::create(
                [
                    'objectid' => $cm->id,
                    'context' => $context,
                ]
            );
            $groupname = organizer_fetch_groupname($app->groupid);
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_appointment_deleted_group', 'organizer'),
                'success');
        } else {
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_appointment_not_deleted', 'organizer'),
                'error');
        }
    } else {
        if (organizer_delete_appointment($data->appid)) {
            $event = appointment_deleted::create(
                [
                    'objectid' => $cm->id,
                    'context' => $context,
                ]
            );
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_appointment_deleted', 'organizer'),
                'success');
            $redirecturl->param('messages[]', 'message_info_appointment_deleted');
        } else {
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_appointment_not_deleted', 'organizer'),
                'error');
        }
    }
    $event->trigger();
    $_SESSION["infoboxmessage"] = $infoboxmessage;
    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('title_delete_appointment', 'organizer'));
}
