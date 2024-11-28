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
 * send_reminder.php
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

use mod_organizer\event\appointment_reminder_sent;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_remind_all.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$recipient = optional_param('recipient', null, PARAM_INT);
$recipients = optional_param_array('recipients', [], PARAM_INT);
$bulkaction = optional_param('bulkaction', '', PARAM_TEXT);

[$cm, $course, $organizer, $context, $redirecturl] = organizer_slotpages_header();

if ($bulkaction && !$recipients) {
    $redirecturl = $redirecturl->out();
    $msg = get_string('err_norecipients', 'organizer');
    redirect($redirecturl, $msg);
}

require_login($course, false, $cm);

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

// Get recipients.
if ($recipient != null) {
    $recipients = [];
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $recipients = get_enrolled_users($context, 'mod/organizer:register', $recipient, 'u.id',
            'lastname,firstname', null, null, true);
    } else {
        $recipients = $DB->get_records_list('user', 'id', [$recipient]);
    }
} else if ($recipients) {
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $recipientsarr = [];
        foreach ($recipients as $key => $recipientgroup) {
            $members = organizer_fetch_groupusers($recipientgroup);
            foreach ($members as $member) {
                if (has_capability('mod/organizer:register', $context, $member->id, false)) {
                    $recipientsarr[$member->id] = $member->id;
                }
            }
        }
    } else {
        $recipientsarr = $DB->get_records_list('user', 'id', $recipients, 'lastname,firstname');
    }
    $recipientsstr = $recipientsarr ? implode(",", array_keys($recipientsarr)) : "";
} else {
    // Send reminders to all students without enough appointments.
    $recipients = organizer_get_reminder_recipients($organizer);
    $recipientsstr = $recipients ? implode(",", $recipients) : "";
}

$mform = new organizer_remind_all_form(
    null, [
        'id' => $cm->id,
        'mode' => $mode,
        'recipients' => isset($recipientsstr) ? $recipientsstr : '',
        'recipient' => $recipient,
    ]
);

if ($data = $mform->get_data()) {
    $infoboxmessage = "";
    $a = new stdClass();
    $recipients = $data->recipientsstr ? explode(",", $data->recipientsstr) : [];
    $count = organizer_remind_all($data->recipient, $recipients, $data->message_custommessage['text']);
    $a->count = $count;
    if ($count == 1) {
        $infoboxmessage .= $OUTPUT->notification(get_string('message_info_reminders_sent_sg', 'organizer', $a),
            'success');
    } else {
        $infoboxmessage .= $OUTPUT->notification(get_string('message_info_reminders_sent_pl', 'organizer', $a),
            'success');
    }
    $event = appointment_reminder_sent::create(
        [
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context,
        ]
    );
    $event->trigger();
    $_SESSION["infoboxmessage"] = $infoboxmessage;
    $redirecturl = $redirecturl->out();
    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('organizer_remind_all_title', 'organizer'));
}
