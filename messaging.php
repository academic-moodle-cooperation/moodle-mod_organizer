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
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_ENABLE_MESSAGING', 1);

require_once(dirname(__FILE__) . '/locallib.php');

function organizer_send_message($sender, $receiver, $slot, $type, $digest = null, $customdata = array()) {
    global $DB;

    if ($type == 'register_reminder:student') {
        $organizer = $slot;
    } else {
        $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
    }

    $sender = is_int($sender) ? $DB->get_record('user', array('id' => $sender)) : $sender;
    $receiver = is_int($receiver) ? $DB->get_record('user', array('id' => $receiver)) : $receiver;

    $module = $DB->get_record('modules', array('name' => 'organizer'));
    $cm = $DB->get_record('course_modules', array('module' => $module->id, 'instance' => $organizer->id));
    $course = $DB->get_record('course', array('id' => $cm->course));
    $context = context_course::instance($cm->course);
    $roles = get_user_roles($context, $receiver->id);

    $now = time();
    if (!$cm->visible || (isset($cm->availablefrom) && $cm->availablefrom && $cm->availablefrom > $now) ||
            (isset($cm->availableuntil) && $cm->availableuntil && $cm->availableuntil < $now) || count($roles) == 0) {
        return false;
    }

    $namesplit = explode(':', $type);

    $strings = new stdClass();
    $strings->sendername = fullname($sender, true);
    $strings->receivername = fullname($receiver, true);

    if ($type != 'register_reminder:student') {
        $strings->date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
        $strings->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
        $strings->location = $slot->location;
    }

    $strings->organizername = $organizer->name;
    $strings->coursefullname = $course->fullname;
    $strings->courseshortname = $course->shortname;
    $strings->courseid = ($course->idnumber == "") ? "" : $course->idnumber . ' ';

    if ($namesplit[0] == "edit_notify") {
        $strings->slot_teacher = $slot->teachervisible == 1 ?
           fullname($DB->get_record('user', array('id' => $slot->teacherid)), true) : get_string('teacherinvisible', 'organizer');
        $strings->slot_location = organizer_location_link($slot);
        $strings->slot_maxparticipants = $slot->maxparticipants;
        $strings->slot_comments = s($slot->comments);
    }


    if ($namesplit[0] == "assign_notify") {
        $strings->slot_teacher = $slot->teachervisible == 1 ?
           fullname($DB->get_record('user', array('id' => $slot->teacherid)), true) : get_string('teacherinvisible', 'organizer');
        $strings->slot_location = organizer_location_link($slot);
		if (isset($customdata['participantname'])) {
			$strings->participantname = $customdata['participantname'];
		}
		if (isset($customdata['groupname'])) {
			$strings->groupname = $customdata['groupname'];
		}
	}

    $courseurl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id));
    $strings->courselink = html_writer::link($courseurl, $course->fullname);

    if ($organizer->isgrouporganizer) {
        if (strpos($type, 'register_notify') !== false || strpos($type, 'group_registration_notify') !== false) {
            $group = groups_get_user_groups($organizer->course, $sender->id);
            $group = reset($group);
            $group = reset($group);
            $group = groups_get_group($group);
        } else {
            $group = groups_get_user_groups($organizer->course, $receiver->id);
            $group = reset($group);
            $group = reset($group);
            $group = groups_get_group($group);
        }
        $strings->groupname = isset($group->name) ? $group->name : "groupname";
        $type .= ":group";
    }

    if ($namesplit[0] == "eval_notify_newappointment") {
        $namesplit[0] = "eval_notify";
    }

    $message = new stdClass();
    $message->modulename = 'organizer';
    $message->component = 'mod_organizer';
    $message->name = "$namesplit[0]:$namesplit[1]";
    $message->notification = 1;
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->userfrom = $sender;
    $message->userto = $receiver;

    if (isset($digest)) {
        $strings->digest = $digest;
        $type .= ":digest";
    }

    $message->subject = get_string("$type:subject", 'organizer', $strings);
    $message->message = get_string("$type:fullmessage", 'organizer', $strings);
    $message->fullmessage = get_string("$type:fullmessage", 'organizer', $strings);
    $message->fullmessagehtml = organizer_make_html(get_string("$type:fullmessage", 'organizer', $strings), $organizer, $cm,
            $course);

    if (isset($customdata['custommessage'])) {
        $message->fullmessage = str_replace('{$a->custommessage}', $customdata['custommessage'], $message->fullmessage);
        $message->fullmessagehtml = str_replace('{$a->custommessage}', $customdata['custommessage'], $message->fullmessagehtml);
    }

    $message->smallmessage = get_string("$type:smallmessage", 'organizer', $strings);

    if (ORGANIZER_ENABLE_MESSAGING) {
        return message_send($message);
    } else {
        return false;
    }
}

function organizer_make_html($content, $organizer, $cm, $course) {
    global $CFG;

    $posthtml = '<html>';
    $posthtml .= '<head>';
    // Html head empty.
    $posthtml .= '</head>';
    $posthtml .= "<body id=\"email\">";
    $posthtml .= '<div class="navbar">' . '<a target="_blank" href="' . $CFG->wwwroot . '/course/view.php?id='
            . $course->id . '">' . $course->shortname . '</a> &raquo; ' . '<a target="_blank" href="' . $CFG->wwwroot
            . '/mod/organizer/index.php?id=' . $course->id . '">' . get_string('modulenameplural', 'organizer')
            . '</a> &raquo; ' . '<a target="_blank" href="' . $CFG->wwwroot . '/mod/organizer/view.php?id=' . $cm->id
            . '">' . format_string($organizer->name, true) . '</a>' . '</div>';
    $posthtml .= '<div id="content"><p>' . str_replace("\n", '<br />', $content) . '</p></div>';
    $link = $CFG->wwwroot . '/mod/organizer/view.php?id=' . $cm->id;
    $posthtml .= '<div id="link"><p>' . get_string('maillink', 'organizer', $link) . '</p></div>';
    $posthtml .= '</body></html>';

    return $posthtml;
}

function organizer_prepare_and_send_message($data, $type) {
    global $DB, $USER;

    require_once('lib.php');

    switch ($type) {
        case 'edit_notify:student':
            foreach ($data->slots as $slotid) {
                $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid));
                $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
                foreach ($apps as $app) {
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    organizer_send_message(intval($slot->teacherid), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'edit_notify:teacher':
            foreach ($data->slots as $slotid) {
                $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
                if ($USER->id != $slot->teacherid) {
                    organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
                }
            }
            break;
        case 'eval_notify:student':
            if (isset($data->apps) && count($data->apps) > 0) {
                foreach ($data->apps as $appid => $app) {
                    $app = $DB->get_record('organizer_slot_appointments', array('id' => $appid));
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));

                    if ($app->allownewappointments == 1) {
                        $type = 'eval_notify_newappointment:student';
                    }

                    organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'register_notify:teacher:register': // TODO: check how it was actually originally defined.
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'register_notify:teacher:queue': 
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'register_notify:teacher:reregister':
        case 'register_notify:teacher:unregister':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_RE_UNREG || $organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'register_notify:teacher:unqueue':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_RE_UNREG || $organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'group_registration_notify:student:register':
        case 'group_registration_notify:student:queue':
        case 'group_registration_notify:student:reregister':
        case 'group_registration_notify:student:unregister':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
            foreach ($apps as $app) {
                if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                    continue;
                }
                if ($app->userid != $USER->id) {
                    organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'group_registration_notify:student:unqueue':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
            foreach ($apps as $app) {
                if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                    continue;
                }
                if ($app->userid != $USER->id) {
                    organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'register_reminder:student':
            return organizer_send_message(intval($USER->id), intval($data['user']),
                $data['organizer'], $type, null, array('custommessage' => $data['custommessage']));
 			break;
        case 'assign_notify:student':
			$slot = $DB->get_record('organizer_slots', array('id' => $data->selectedslot));
			if($data->participant) {
				$apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $data->selectedslot, 'userid' => $data->participant));
			} else {
				$apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $data->selectedslot, 'groupid' => $data->group));
			}
			foreach ($apps as $app) {
				if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
					continue;
				}
				organizer_send_message(intval($app->teacherapplicantid), intval($app->userid), $slot, $type);
            }
            break;
        case 'assign_notify:teacher':
			$slot = $DB->get_record('organizer_slots', array('id' => $data->selectedslot));
			if($data->participant) {
				$apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $data->selectedslot, 'userid' => $data->participant));
			} else {
				$apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $data->selectedslot, 'groupid' => $data->group));
			}
			$app = reset($apps);
			if ($app->teacherapplicantid != $slot->teacherid) {
				$customdata = array();
				if($data->participant) {
					$participant = $DB->get_record('user', array('id' => $data->participant));
					$customdata['participantname'] = fullname($participant, true);
				} else {
					$customdata['groupname'] = organizer_fetch_groupname($data->group);
				}
				organizer_send_message(intval($app->teacherapplicantid), intval($slot->teacherid), $slot, $type, null, $customdata);
			}
            break;			
        default:
            print_error('Not debugged yet!');
    }
    return;
}