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
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_remind_all.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$recipient = optional_param('recipient', null, PARAM_INT);
$recipients = optional_param_array('$recipients', array(), PARAM_INT);

list($cm, $course, $organizer, $context, $redirecturl) = organizer_slotpages_header();

require_login($course, false, $cm);

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

// Get recipients.
if ($recipient != null) {
    $recipients = array();
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $recipients = get_enrolled_users($context, 'mod/organizer:register', $recipient, 'u.id',
            'lastname', null, null, true);
    } else {
        $recipients = $DB->get_records_list('user', 'id', array($recipient));
    }
    $counter = count($recipients);
} else if ($recipients) {
    $recipients = $DB->get_records_list('user', 'id', $recipients);
    $counter = count($recipients);
} else {
    // Send reminders to all students without enough appointments.
    $counter = 0;
    $recipients = array();
    $entries = organizer_organizer_get_reg_status_table_entries(array('sort' => ''));
    if ($entries->valid()) {
        // Filter all not registered and not attended.
        $entrybefore = 0;
        foreach ($entries as $entry) {
            if ($entry->id != $entrybefore) {
                $in = false;
                if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                    $group = organizer_fetch_user_group($entry->id, $organizer->id);
                    if (organizer_multiplebookings_status(
                            organizer_count_bookedslots($organizer->id, null, $group->id),
                                $organizer) == USERSLOTS_MIN_NOT_REACHED) {
                        $in = true;
                    }
                } else {
                    if (organizer_multiplebookings_status(
                            organizer_count_bookedslots($organizer->id, $entry->id, null),
                                $organizer) == USERSLOTS_MIN_NOT_REACHED) {
                        $in = true;
                    }
                }
                if ($in) {
                    $counter++;
                    $recipients[] = $entry;
                }
                $entrybefore = $entry->id;
            }
        }
    }
    $entries->close();
}

$mform = new organizer_remind_all_form(
    null, array('id' => $cm->id, 'mode' => $mode, 'recipients' => $recipients, 'recipient' => $recipient)
);

if ($data = $mform->get_data()) {
    $infoboxmessage = "";
    $a = new stdClass();
    $count = organizer_remind_all($data->recipient, $data->recipients, $data->message_custommessage['text']);
    $a->count = $count;
    if ($count == 1) {
        $infoboxmessage .= $OUTPUT->notification(get_string('message_info_reminders_sent_sg', 'organizer', $a),
            'success');
    } else {
        $infoboxmessage .= $OUTPUT->notification(get_string('message_info_reminders_sent_pl', 'organizer', $a),
            'success');
    }
    $event = \mod_organizer\event\appointment_reminder_sent::create(
        array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
        )
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

function organizer_remind_all($recipient = null, $recipients = array(), $custommessage = "") {
    global $DB;

    list($cm, , $organizer, $context) = organizer_get_course_module_data();

    if ($recipient != null) {
        if (!organizer_is_group_mode()) {
               $entries = $DB->get_records_list('user', 'id', array($recipient));
        } else {
            $entries = get_enrolled_users($context, 'mod/organizer:register',
                $recipient, 'u.id', null, null, null, true);
        }
    } else if ($recipients) {
        $entries = $DB->get_records_list('user', 'id', $recipients);
    } else if (!organizer_is_group_mode()) {
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
            organizer_prepare_and_send_message(
                array('user' => $entry->id, 'organizer' => $organizer,
                'custommessage' => $custommessage), 'register_reminder_student'
            );
            $count++;
        }
    }
    return $count;
}
