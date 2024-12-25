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
 * messaging.php
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

use core\message\message;

defined('MOODLE_INTERNAL') || die();

define('ORGANIZER_ENABLE_MESSAGING', 1);

require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Last strings of a message are provided here and finally the email message is sent.
 *
 * @param $sender   ... the user who sends this message
 * @param $receiver ... the user who receives this message
 * @param stdClass $slot  ... the time slot
 * @param string $type  ... messagetype
 * @param null $digest  ... if this is a email sent by cron to the teachers with all appointments lying ahead
 * @param array $customdata ... additional optional message-relevant data
 * @return bool|mixed  ... whether message was sent successfully or not
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_send_message($sender, $receiver, $slot, $type, $digest = null, $customdata = [],
        $trainercheck = false) {
    global $DB, $CFG;

    if (!isset($slot->organizerid)) {
        $slot = $slot->get_slot();
    }
    $organizerid = $slot->organizerid;

    [$cm, $course, $organizer, $context] = organizer_get_course_module_data(0, $organizerid);

    $strings = organizer_check_messagerights($sender, $receiver, $cm, $course, $organizer, $context, $trainercheck);
    if (!$strings) {
        return false;
    }
    $strings->date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
    $strings->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
    $strings->location = $slot->location != '' ? $slot->location : get_string('nolocationplaceholder', 'organizer');

    $namesplit = explode(':', $type);

    if ($namesplit[0] == "edit_notify_student" || $namesplit[0] == "edit_notify_teacher") {
        if ($slot->teachervisible == 1 || $namesplit[0] == "edit_notify_teacher") {
            if (isset($slot->slotid)) {
                $trainers = organizer_get_slot_trainers($slot->slotid);
            } else {
                $trainers = organizer_get_slot_trainers($slot->id);
            }
            $teachers = [];
            foreach ($trainers as $trainerid) {
                $teachers[] = fullname($DB->get_record('user', ['id' => $trainerid]), true);
            }
            $strings->slot_teacher = implode(', ', $teachers);
        } else {
            $strings->slot_teacher = get_string('teacherinvisible', 'organizer');
        }
        $location = organizer_location_link($slot);
        $strings->slot_location = $location != '' ? $location : '-';
        $strings->slot_maxparticipants = $slot->maxparticipants;
        $comments = organizer_filter_text($slot->comments);
        $strings->slot_comments = $comments != '' ? $comments : '-';
    }

    if ($namesplit[0] == "assign_notify_student" || $namesplit[0] == "assign_notify_teacher") {
        if ($slot->teachervisible == 1 || $namesplit[0] == "assign_notify_teacher") {
            if (isset($slot->slotid)) {
                $trainers = organizer_get_slot_trainers($slot->slotid);
            } else {
                $trainers = organizer_get_slot_trainers($slot->id);
            }
            $teachers = [];
            foreach ($trainers as $trainerid) {
                $teachers[] = fullname($DB->get_record('user', ['id' => $trainerid]), true);
            }
            $strings->slot_teacher = implode(', ', $teachers);
        } else {
            $strings->slot_teacher = get_string('teacherinvisible', 'organizer');
        }
        $strings->slot_location = organizer_location_link($slot);
        if (isset($customdata['participantname'])) {
            $strings->participantname = $customdata['participantname'];
        }
        if (isset($customdata['groupname'])) {
            $strings->groupname = $customdata['groupname'];
        }
    }

    if ($namesplit[0] == "appointment_reminder_student") {
        if (isset($slot->slotid)) {
            $trainers = organizer_get_slot_trainers($slot->slotid);
        } else {
            $trainers = organizer_get_slot_trainers($slot->id);
        }
        $strings->sendername = "";
        $conn = "";
        foreach ($trainers as $trainerid) {
            $strings->sendername .= $conn . fullname($DB->get_record('user', ['id' => $trainerid]), true);
            $conn = ", ";
        }
    }

    $courseurl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id]);
    $strings->courselink = html_writer::link($courseurl, $course->fullname);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        if (!isset($strings->groupname)) {
            if ($namesplit[0] == 'register_notify_teacher') {
                $groupuser = isset($sender->id) ? $sender->id : $sender;
            } else {
                $groupuser = isset($receiver->id) ? $receiver->id : $receiver;
            }
            if ($group = organizer_fetch_user_group($groupuser, $organizerid)) {
                $groupname = organizer_fetch_groupname($group->id);
                $strings->groupname = $groupname;
            } else {
                $strings->groupname = "?";
            }
        }
        $type .= ":group";
    }

    if ($namesplit[0] == "eval_notify_newappointment") {
        $namesplit[0] = "eval_notify";
    }

    if (!empty($customdata) && isset($customdata['showsendername'])) {
        if ($customdata['showsendername'] == 1) {
            $strings->sendername = get_string('with', 'organizer') . ' ' . $strings->sendername;
        } else {
            $strings->sendername = '';
        }
    }

    $message = organizer_build_message($namesplit, $cm, $course, $organizer, $sender, $receiver, $digest,
        $type, $strings, $customdata);

    $message->contexturl        = $CFG->wwwroot . '/mod/organizer/view.php?id=' . $cm->id;
    $message->contexturlname    = $organizer->name;

    if (ORGANIZER_ENABLE_MESSAGING) {
        return message_send($message);
    } else {
        return false;
    }
}

/**
 * Sends a reminder message from the organizer.
 *
 * This function sends a message reminder based on the organizer ID, type, and other optional parameters
 * such as group name and message digest. It ensures the sender has the appropriate rights and builds
 * the message content dynamically before sending it.
 *
 * @param stdClass|int $sender The sender of the message, can be an object or user ID.
 * @param stdClass|int $receiver The receiver of the message, can be an object or user ID.
 * @param int $organizerid The ID of the organizer related to the message.
 * @param string $type The type of message being sent (e.g., notification, reminder).
 * @param string|null $groupname (Optional) The name of the group, if applicable.
 * @param string|null $digest (Optional) Additional digest data for the message.
 * @param array $customdata (Optional) Custom data to include in the message.
 *
 * @return bool True if the message was sent successfully, false otherwise.
 */
function organizer_send_message_reminder($sender, $receiver, $organizerid, $type, $groupname = null, $digest = null,
                                         $customdata = []) {

    [$cm, $course, $organizer, $context] = organizer_get_course_module_data(null, $organizerid);

    $strings = organizer_check_messagerights($sender, $receiver, $cm, $course, $organizer, $context, true);
    if (!$strings) {
        return false;
    }

    $namesplit = explode(':', $type);

    $courseurl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id]);
    $strings->courselink = html_writer::link($courseurl, $strings->coursefullname);

    if ($groupname) {
        $strings->groupname = $groupname;
        $type .= ":group";
    } else {
        $strings->groupname = "";
    }

    $message = organizer_build_message($namesplit, $cm, $course, $organizer, $sender, $receiver, $digest,
        $type, $strings, $customdata);

    if (ORGANIZER_ENABLE_MESSAGING) {
        return message_send($message);
    } else {
        return false;
    }
}


/**
 * Sends a message from the trainer associated with a specific slot.
 *
 * This function retrieves the trainer associated with the provided slot ID
 * and sends a message to the specified receiver. It uses the `organizer_send_message`
 * function to handle the message composition and sending process.
 *
 * @param stdClass|int $receiver The receiver of the message, can be an object or user ID.
 * @param stdClass $slot The slot object containing slot-specific data.
 * @param string $type The type of message being sent (e.g., notification type).
 * @param string|null $digest (Optional) Additional digest data for the message.
 * @param array $customdata (Optional) Custom data to include in the message.
 *
 * @return bool True if the message is successfully sent, false otherwise.
 */
function organizer_send_message_from_trainer($receiver, $slot, $type, $digest = null, $customdata = []) {
    global $DB;

    $success = false;

    if ($trainer = $DB->get_field('organizer_slot_trainer', 'trainerid', ['slotid' => $slot->slotid])) {
        // Send message from trainer.
        $success = organizer_send_message($trainer, $receiver, $slot, $type, $digest, $customdata);
    }

    return $success;

}


/**
 * Generates the HTML content for an email message.
 *
 * This function creates a structured HTML template for email content.
 * It includes navigation links, the message content, and a link to the organizer view page.
 *
 * @param string $content The main content/message of the email.
 * @param stdClass $organizer The organizer object containing organizer-related data.
 * @param stdClass $cm The course module object.
 * @param stdClass $course The course object containing course-related data.
 *
 * @return string The generated HTML content for the email.
 */
function organizer_make_html($content, $organizer, $cm, $course) {
    global $CFG;

    $posthtml = '<html>';
    $posthtml .= '<head>';
    // Html head empty.
    $posthtml .= '</head>';
    $posthtml .= "<body id=\"email\">";
    $posthtml .= '<div class="navbar">' . '<a target="_blank" href="' . $CFG->wwwroot . '/course/view.php?id='
        . $course->id . '">' . organizer_filter_text($course->shortname) . '</a> &raquo; ' . '<a target="_blank" href="' .
        $CFG->wwwroot . '/mod/organizer/index.php?id=' . $course->id . '">' . get_string('modulenameplural', 'organizer')
        . '</a> &raquo; ' . '<a target="_blank" href="' . $CFG->wwwroot . '/mod/organizer/view.php?id=' . $cm->id
        . '">' . organizer_filter_text($organizer->name) . '</a>' . '</div>';
    $posthtml .= '<div id="content"><p>' . str_replace("\n", '<br />', $content) . '</p></div>';
    $link = $CFG->wwwroot . '/mod/organizer/view.php?id=' . $cm->id;
    $posthtml .= '<div id="link"><p>' . get_string('maillink', 'organizer', $link) . '</p></div>';
    $posthtml .= '</body></html>';

    return $posthtml;
}


/**
 * Prepares and sends a message based on the given data and message type.
 *
 * Depending on the message type, this function handles sending notifications
 * to students or trainers associated with the organizer slots or appointments.
 * It utilizes other helper functions for retrieving necessary data and sending
 * messages.
 *
 * @param stdClass $data The data containing slot or appointment information necessary for the message.
 * @param string $type The type of message being sent (e.g., 'edit_notify_student', 'edit_notify_teacher', etc.).
 *
 * @return bool True if the message was successfully sent, false otherwise.
 */
function organizer_prepare_and_send_message($data, $type) {
    global $DB, $USER;

    $sentok = false;

    switch ($type) {
        case 'edit_notify_student':
            foreach ($data->slots as $slotid) {
                $apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slotid]);
                $slot = $DB->get_record('organizer_slots', ['id' => $slotid]);
                $trainers = organizer_get_slot_trainers($slot->id);
                $trainerid = reset($trainers);
                $customdata = [];
                if ($slot->teachervisible == 1) {
                    $customdata['showsendername'] = true;
                } else {
                    $customdata['showsendername'] = false;
                }
                foreach ($apps as $app) {
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    // Edit notify student: Send notification to participant.
                    $sentok = organizer_send_message(intval($trainerid), intval($app->userid), $slot, $type, null,
                        $customdata, true);
                }
            }
            break;
        case 'edit_notify_teacher':
            foreach ($data->slots as $slotid) {
                $slot = $DB->get_record('organizer_slots', ['id' => $slotid]);
                $trainers = organizer_get_slot_trainers($slot->id);
                foreach ($trainers as $trainerid) {
                    if ($USER->id != $trainerid) {
                        // Edit notify trainer: Send notification to trainer.
                        $sentok = organizer_send_message(intval($USER->id), intval($trainerid), $slot, $type);
                    }
                }
            }
            break;
        case 'eval_notify_student':
            if (isset($data->apps) && count($data->apps) > 0) {
                foreach ($data->apps as $appid => $app) {
                    $app = $DB->get_record('organizer_slot_appointments', ['id' => $appid]);
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    $slot = $DB->get_record('organizer_slots', ['id' => $app->slotid]);

                    if ($app->allownewappointments == 1) {
                        $type = 'eval_notify_newappointment:student';
                    } else {
                        $type = 'eval_notify_student';
                    }
                    $customdata = [];
                    if ($slot->teachervisible == 1) {
                        $customdata['showsendername'] = true;
                    } else {
                        $customdata['showsendername'] = false;
                    }

                    // Eval notify student: Send notification to participant.
                    $sentok = organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type,
                        null, $customdata, true);
                }
            }
            break;
        case 'register_notify_teacher:register':
            $slot = $DB->get_record('organizer_slots', ['id' => $data]);
            $organizer = $DB->get_record('organizer', ['id' => $slot->organizerid]);
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_RE_UNREG ||
                    $organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                $trainers = organizer_get_slot_trainers($slot->id);
                foreach ($trainers as $trainerid) {
                    // Register notify trainer: Send notification to trainer.
                    $sentok = organizer_send_message(intval($USER->id), intval($trainerid), $slot, $type);
                }
            }
            break;
        case 'register_notify_teacher:queue':
            $slot = $DB->get_record('organizer_slots', ['id' => $data]);
            $organizer = $DB->get_record('organizer', ['id' => $slot->organizerid]);
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                $trainers = organizer_get_slot_trainers($slot->id);
                foreach ($trainers as $trainerid) {
                    // Queue notify trainer: Send notification to trainer.
                    $sentok = organizer_send_message(intval($USER->id), intval($trainerid), $slot, $type);
                }
            }
            break;
        case 'register_notify_teacher:reregister':
        case 'register_notify_teacher:unregister':
            $slot = $DB->get_record('organizer_slots', ['id' => $data]);
            $organizer = $DB->get_record('organizer', ['id' => $slot->organizerid]);
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_RE_UNREG ||
                $organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                $trainers = organizer_get_slot_trainers($slot->id);
                foreach ($trainers as $trainerid) {
                    // Reregister and unregister notify trainer: Send notification to trainer.
                    $sentok = organizer_send_message(intval($USER->id), intval($trainerid), $slot, $type);
                }
            }
            break;
        case 'register_notify_teacher:unqueue':
            $slot = $DB->get_record('organizer_slots', ['id' => $data]);
            $organizer = $DB->get_record('organizer', ['id' => $slot->organizerid]);
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                $trainers = organizer_get_slot_trainers($slot->id);
                foreach ($trainers as $trainerid) {
                    // Unqueue notify trainer: Send notification to trainer.
                    $sentok = organizer_send_message(intval($USER->id), intval($trainerid), $slot, $type);
                }
            }
            break;
        case 'group_registration_notify:student:register':
        case 'group_registration_notify:student:queue':
        case 'group_registration_notify:student:reregister':
        case 'group_registration_notify:student:unqueue':
            $slot = $DB->get_record('organizer_slots', ['id' => $data]);
            $apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slot->id]);
            foreach ($apps as $app) {
                if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                    continue;
                }
                if ($app->userid != $USER->id) {
                    // Group registrations, unregistrations, queues notify participant:Send notification to participant.
                    $sentok = organizer_send_message(intval($USER->id), intval($app->userid),
                        $slot, $type, null, null, true);
                }
            }
            break;
        case 'register_reminder_student':
            $organizerid = $data['organizer']->id;
            if ($data['organizer']->isgrouporganizer) {
                if ($group = organizer_fetch_user_group(intval($data['user']), $organizerid)) {
                    $groupname = organizer_fetch_groupname($group->id);
                } else {
                    $groupname = null;
                }
            } else {
                $groupname = null;
            }
            return organizer_send_message_reminder(intval($USER->id), intval($data['user']),
                $organizerid, $type, $groupname, null, ['custommessage' => $data['custommessage']]);
        case 'assign_notify_student':
            $slot = $DB->get_record('organizer_slots', ['id' => $data->selectedslot]);
            $customdata = [];
            $slotx = new organizer_slot($slot);
            if (!$slotx->is_past_due()) {
                if ($data->participant) {  // If organizer instance is in single mode.
                    $apps = $DB->get_records(
                        'organizer_slot_appointments', ['slotid' => $data->selectedslot, 'userid' => $data->participant]
                    );
                } else { // If organizer instance is group mode.
                    $apps = $DB->get_records(
                        'organizer_slot_appointments', ['slotid' => $data->selectedslot, 'groupid' => $data->group]
                    );
                    if ($groupname = organizer_fetch_groupname($data->group)) {
                        $customdata['groupname'] = $groupname;
                    } else {
                        $customdata['groupname'] = "";
                    }
                }
                foreach ($apps as $app) {
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    // Assign notify participant: Send notification to participant.
                    $sentok = organizer_send_message(intval($app->teacherapplicantid), intval($app->userid),
                        $slot, $type, null, $customdata, true);
                }
            }
            break;
        case 'assign_notify_teacher':
            $slot = $DB->get_record('organizer_slots', ['id' => $data->selectedslot]);
            $slotx = new organizer_slot($slot);
            if (!$slotx->is_past_due()) {
                if ($data->participant) {
                    $apps = $DB->get_records(
                        'organizer_slot_appointments', ['slotid' => $data->selectedslot, 'userid' => $data->participant]
                    );
                } else {
                    $apps = $DB->get_records(
                        'organizer_slot_appointments', ['slotid' => $data->selectedslot, 'groupid' => $data->group]
                    );
                }
                if ($app = reset($apps)) {
                    $trainers = organizer_get_slot_trainers($slot->id);
                    foreach ($trainers as $trainerid) {
                        if ($app->teacherapplicantid != $trainerid) {
                            $customdata = [];
                            if ($data->participant) {
                                $participant = $DB->get_record('user', ['id' => $data->participant]);
                                $customdata['participantname'] = fullname($participant, true);
                            } else {
                                $customdata['groupname'] = organizer_fetch_groupname($data->group);
                            }
                            // Assign notify trainer: Send notification to trainer.
                            $sentok = organizer_send_message(intval($app->teacherapplicantid), intval($trainerid),
                                $slotx, $type, null, $customdata);
                        }
                    }
                } else { // If no app was found there is no need to send messages.
                    $sentok = true;
                }
            }
            break;
        default:
            throw new coding_exception('Not debugged yet!');
    }
    return $sentok;
}

/**
 * Checks if organizer instance is available and if receiver has enough rights and returns message strings for
 * the message to send
 * @param user record or id $sender
 * @param user record or id $receiver
 * @param record $cm organizer coursemodule
 * @param record $course
 * @param record $organizer instance
 * @param object $context
 * @param bool $trainercheck if receiver is trainer and notifications are off don't send
 * @return bool|stdClass
 * @throws dml_exception
 */
function organizer_check_messagerights($sender, $receiver, $cm, $course, $organizer, $context, $trainercheck = false) {
    global $DB;

    $sender = is_numeric($sender) ? $DB->get_record('user', ['id' => $sender]) : $sender;
    $receiver = is_numeric($receiver) ? $DB->get_record('user', ['id' => $receiver]) : $receiver;

    $hasnoroles = !count(get_user_roles($context, $receiver->id));
    $now = time();
    $instancenotactive = !$cm->visible || (isset($cm->availablefrom) && $cm->availablefrom && $cm->availablefrom > $now)
        || (isset($cm->availableuntil) && $cm->availableuntil && $cm->availableuntil < $now);
    $receiveteachermailsright = has_capability('mod/organizer:receivemessagesteacher', $context);
    $notrainermail = $trainercheck && $organizer->emailteachers == ORGANIZER_MESSAGES_NONE && $receiveteachermailsright;
    if ($instancenotactive || $hasnoroles || $notrainermail) {
        return false;
    }

    $strings = new stdClass();
    $strings->sendername = fullname($sender, true);
    $strings->receivername = fullname($receiver, true);

    $strings->organizername = organizer_filter_text($organizer->name);
    $strings->coursefullname = organizer_filter_text($course->fullname);
    $strings->courseshortname = organizer_filter_text($course->shortname);
    $strings->courseid = ($course->idnumber == "") ? "" : $course->idnumber . ' ';

    return $strings;
}

/**
 * Builds a message object for notifications in the organizer module.
 *
 * This function constructs a notification message based on the provided parameters.
 * It supports digest mode, multiple message types, and custom data replacements.
 *
 * @param array $namesplit The split parts of the message name.
 * @param object $cm The course module instance related to the organizer.
 * @param object $course The course record related to the organizer.
 * @param object $organizer The organizer instance.
 * @param object $sender The user sending the message.
 * @param object $receiver The user receiving the message.
 * @param string|null $digest Optional digest message string.
 * @param string $type The type of the message.
 * @param object $strings A collection of message strings.
 * @param array $customdata Associative array of additional custom data for the message.
 *
 * @return message The constructed message object ready to be sent.
 * @throws coding_exception
 */
function organizer_build_message($namesplit, $cm, $course, $organizer, $sender, $receiver, $digest,
                                 $type, $strings, $customdata) {

    $messagename = count($namesplit) == 1 ? "$namesplit[0]" : "$namesplit[0]_$namesplit[1]";
    $strings->location = $strings->location ?? false ? $strings->location : get_string('nolocationplaceholder', 'organizer');
    if (isset($digest)) {
        $strings->digest = $digest;
        $type .= ":digest";
    }

    $message = new message();
    $message->component = 'mod_organizer';
    $message->name = $messagename;
    $message->courseid = $cm->course;
    $message->notification = 1;
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->userfrom = $sender;
    $message->userto = $receiver;
    $message->subject = get_string("$type:subject", 'organizer', $strings);
    $message->fullmessage = get_string("$type:fullmessage", 'organizer', $strings);
    $message->fullmessagehtml = organizer_make_html(
        get_string("$type:fullmessage", 'organizer', $strings), $organizer, $cm, $course
    );
    if (isset($customdata['custommessage'])) {
        $message->fullmessage = str_replace('{$a->custommessage}', $customdata['custommessage'], $message->fullmessage);
        $message->fullmessagehtml = str_replace('{$a->custommessage}', $customdata['custommessage'], $message->fullmessagehtml);
    }
    $message->smallmessage = get_string("$type:smallmessage", 'organizer', $strings);

    return $message;
}
