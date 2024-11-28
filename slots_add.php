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
 * addslot.php
 *
 * @package   mod_organizer
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core\output\notification;
use mod_organizer\event\slot_created;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/view_action_form_add.php');
require_once(dirname(__FILE__) . '/view_lib.php');

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = organizer_get_param_slots();

[$cm, $course, $organizer, $context, $redirecturl] = organizer_slotpages_header();

$params['limitedwidth'] = organizer_get_limitedwidth();

require_login($course, false, $cm);

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

require_capability('mod/organizer:addslots', $context);

$mform = new organizer_add_slots_form(null, ['id' => $cm->id, 'mode' => $mode]);

if ($data = $mform->get_data()) {  // When page is called the first time (=empty form) or form data has errors: no data.
    if (isset($data->addday)) {  // Additional slot fields are to be displayed.
        organizer_display_form($mform, get_string('title_add', 'organizer'));
    } else {  // Submit button was pressed and submitted form data has no errors.
        [$slotids, $slotsnotcreatedduetodeadline, $slotsnotcreatedduetopasttime, $messages] = organizer_add_new_slots($data);
        $finalslots = count($slotids);
        $infoboxmessage = "";
        $a = new stdClass();
        if ($finalslots == 0) {
            $infoboxmessage .= $OUTPUT->notification(get_string('message_warning_no_slots_added', 'organizer'), 'error');
        } else {
            $event = slot_created::create(
                [
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context,
                ]
            );
            $event->trigger();

            $a->count = $finalslots;
            if ($finalslots == 1) {
                $infoboxmessage .= $OUTPUT->notification(get_string('message_info_slots_added_sg', 'organizer', $a),
                    'success');
            } else {
                $infoboxmessage .= $OUTPUT->notification(get_string('message_info_slots_added_pl', 'organizer', $a),
                    'success');
            }
            $redirecturl->param('slots', implode(',', array_values($slotids)));
        }
        if ($slotsnotcreatedduetodeadline) {
            $a->slots = $slotsnotcreatedduetodeadline;
            $infoboxmessage .= $OUTPUT->notification(get_string('infobox_deadline_passed_slotphp', 'organizer', $a),
                'error');
        }
        if ($slotsnotcreatedduetopasttime) {
            $a->slots = $slotsnotcreatedduetopasttime;
            $infoboxmessage .= $OUTPUT->notification(get_string('pasttimeslotstringphp', 'organizer', $a), 'error');
        }
        $_SESSION["infoboxmessage"] = $infoboxmessage;
        if ($messages) {
            redirect($redirecturl, $messages, 10, notification::NOTIFY_WARNING);
        } else {
            redirect($redirecturl);
        }
    }
} else if ($mform->is_cancelled()) {  // Cancel button of form was pressed.
    redirect($redirecturl);
} else { // Display empty form initially or submitted form has errors.
    organizer_display_form($mform, get_string('title_add', 'organizer'));
}
throw new coding_exception('If you see this, something went wrong with add action!');

die;
