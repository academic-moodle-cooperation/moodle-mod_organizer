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
 * locallib.php
 *
 * @package       mod_organizer
 * @author        Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

if (!function_exists('sem_get')) {
    function sem_get($key) {
        global $CFG;
        if (!is_dir($CFG->dataroot . '/temp/mod/organizer')) {
            mkdir($CFG->dataroot . '/temp/mod/organizer', 0777, true);
        }
        return fopen($CFG->dataroot . '/temp/mod/organizer/organizer_' . $key . '.sem', 'w+');
    }

    function sem_acquire($semid) {
        return flock($semid, LOCK_EX);
    }

    function sem_release($semid) {
        return flock($semid, LOCK_UN);
    }
}

function organizer_load_events($teacherid, $startdate, $enddate) {
    global $DB;

    $params = array('teacherid' => $teacherid, 'startdate1' => $startdate, 'enddate1' => $enddate,
            'startdate2' => $startdate, 'enddate2' => $enddate);
    $query = "SELECT {event}.id, {event}.name, {event}.timestart, {event}.timeduration FROM {event}
            INNER JOIN {user} ON {user}.id = {event}.userid
            WHERE {user}.id = :teacherid AND ({event}.timestart >= :startdate1
                AND {event}.timestart < :enddate1 OR ({event}.timestart + {event}.timeduration) >= :startdate2
                AND ({event}.timestart + {event}.timeduration) < :enddate2)";

    return $DB->get_records_sql($query, $params);
}

function organizer_get_name_link($user = 0) {
    global $DB, $USER, $COURSE;
    if (!$user) {
        $user = $USER;
    } else if (is_number($user)) {
        $user = $DB->get_record('user', array('id' => $user));
    } else if (!($user instanceof stdClass)) {
        return false;
    }

    $profileurl = new moodle_url('/user/view.php', array('id' => $user->id, 'course' => $COURSE->id));
    $name = get_string('fullname_template', 'organizer', $user);
    return html_writer::link($profileurl, $name);
}

/**
 * Checks if the given events are in the given time frame.
 * @param unixtime $from
 * @param unixtime to
 * @return array an array of events
 */
function organizer_check_collision($from, $to, $events) {
    $collidingevents = array();
    foreach ($events as $event) {
        $eventfrom = $event->timestart;
        $eventto = $eventfrom + $event->timeduration;

        if (between($from, $eventfrom, $eventto) || between($to, $eventfrom, $eventto)
                || between($eventfrom, $from, $to) || between($eventto, $from, $to)
                || $from == $eventfrom || $eventfrom == $eventto) {
            $collidingevents[] = $event;
        }
    }
    return $collidingevents;
}

function between($num, $lower, $upper) {
    return $num > $lower && $num < $upper;
}

function organizer_add_appointment_slots($data) {
    global $DB;

    $count = array();

    $cm = get_coursemodule_from_id('organizer', $data->id);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    if (!isset($data->newslots)) {
        return $count;
    }

    $timezone = new DateTimeZone(date_default_timezone_get());
    $startdate = $data->startdate;
    $collisionmessages = "";

    foreach ($data->newslots as $slot) {
        if (!$slot['selected']) {
            continue;
        }

        $slot['date'] = organizer_get_day_date($slot['day'], $startdate);
        $transitions = $timezone->getTransitions($slot['date'], $slot['date'] + $slot['from']);
        $dstoffset = 0;

        foreach ($transitions as $trans) {
            $dstoffset += $trans['isdst'] ? ($trans['offset']) : ( + $trans['offset']);
        }

        $newslot = new stdClass();
        $newslot->maxparticipants = $data->maxparticipants;
        $newslot->visibility = $data->visibility;
        $newslot->timemodified = time();
        $newslot->teacherid = $data->teacherid;
        $newslot->teachervisible = isset($data->teachervisible) ? 1 : 0;
        $newslot->notificationtime = $data->notificationtime;
        $newslot->availablefrom = isset($data->availablefrom) ? $data->availablefrom : 0;
        $newslot->location = $data->location;
        $newslot->locationlink = $data->locationlink;
        $newslot->isgroupappointment = $organizer->isgrouporganizer;
        $newslot->duration = $data->duration;
        $newslot->comments = (isset($data->comments)) ? $data->comments : '';
        $newslot->organizerid = $organizer->id;

        if (!isset($data->duration) || $data->duration < 1) {
            print_error('Duration is invalid (not set or < 1). No slots will be added. Contact support!');
        }

        if (!isset($data->gap) || $data->gap < 0) {
            print_error('Gap is invalid (not set or < 0). No slots will be added. Contact support!');
        }

        for ($time = $slot['from']; $time + $data->duration <= $slot['to']; $time += ($data->duration+$data->gap) ) {

            $newslot->starttime = organizer_get_slotstarttime($slot['date'], $time);
            $newslot->id = $DB->insert_record('organizer_slots', $newslot);

            $newslot->eventid = organizer_add_event_slot($data->id, $newslot);
            $DB->update_record('organizer_slots', $newslot);
            unset($newslot->eventid);

            $events = organizer_load_events($newslot->teacherid, $newslot->starttime,
                        $newslot->starttime + $newslot->duration);
            $collisions = organizer_check_collision($newslot->starttime,
                        $newslot->starttime + $newslot->duration, $events);
            $head = true;
            $collisionmessage = "";
            foreach ($collisions as $event) {
                if ($head) {
                    $collisionmessage .= '<span class="error">' . get_string('collision', 'organizer') .'</span><br />';
                    $head = false;
                }
                $collisionmessage .= '<strong>' . $event->name . '</strong> from '
                        . userdate($event->timestart,
                            get_string('fulldatetimetemplate', 'organizer')) . ' to '
                        . userdate($event->timestart + $event->timeduration,
                            get_string('fulldatetimetemplate', 'organizer')) . '<br />';
            }

            $count[] = $newslot->id;
            $collisionmessages .= $collisionmessage;
        }
    }

    return array($count, $collisionmessages);
}

function organizer_get_slotstarttime($slotdate, $time) {
    $t = new DateTime();
    $t->setTimestamp($slotdate); // Sets the day.
    $h = $time / 3600 % 24;
    $m = $time / 60 % 60;
    $s = $time % 60;
    $t->setTime($h, $m, $s); // Set time of day.
    $starttime = $t->getTimestamp();
    return $starttime;
}

function organizer_get_day_date($day, $startdate) {
    $date = null;

    switch($day) {
        case 0:
            if(date('w', $startdate) == 1) {
                $date = $startdate;
            } else {
                $date = strtotime("next Monday", $startdate);
            }
            break;
        case 1:
            if(date('w', $startdate) == 2) {
                $date = $startdate;
            } else {
                $date = strtotime("next Tuesday", $startdate);
            }
            break;
        case 2:
            if(date('w', $startdate) == 3) {
                $date = $startdate;
            } else {
                $date = strtotime("next Wednesday", $startdate);
            }
            break;
        case 3:
            if(date('w', $startdate) == 4) {
                $date = $startdate;
            } else {
                $date = strtotime("next Thursday", $startdate);
            }
            break;
        case 4:
            if(date('w', $startdate) == 5) {
                $date = $startdate;
            } else {
                $date = strtotime("next Friday", $startdate);
            }
            break;
        case 5:
            if(date('w', $startdate) == 6) {
                $date = $startdate;
            } else {
                $date = strtotime("next Saturday", $startdate);
            }
            break;
        case 6:
            if(date('w', $startdate) == 7) {
                $date = $startdate;
            } else {
                $date = strtotime("next Sunday", $startdate);
            }
            break;
        default:
            $date = null;
    }

    return $date;
}

function organizer_add_event_slot($cmid, $slot) {
    global $DB;

    if (is_number($slot)) {
        $slot = $DB->get_record('organizer_slots', array('id' => $slot));
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', array('id' => $cm->course));
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    $a = new stdClass();

    $courseurl = new moodle_url("/course/view.php?id={$course->id}");
    $a->coursename = $course->fullname;
    $a->courselink = html_writer::link($courseurl, $course->fullname);

    $organizerurl = new moodle_url("/mod/organizer/view.php?id={$cm->id}");
    $a->organizername = $organizer->name;
    $a->organizerlink = html_writer::link($organizerurl, $organizer->name);

    if ($organizer->isgrouporganizer) {
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
        $app = reset($apps);
        if (isset($slot->eventid) && $app) {
            $a->appwith = get_string('eventappwith:group', 'organizer');
            $a->with = get_string('eventwith', 'organizer');
            $group = groups_get_group($app->groupid);
            $users = groups_get_members($group->id);
            $memberlist = "{$group->name} (";
            foreach ($users as $user) {
                $memberlist .= organizer_get_name_link($user->id) . ", ";
            }
            $memberlist = trim($memberlist, ", ");
            $memberlist .= ")";
            $a->participants = $memberlist;
        } else {
            $a->appwith = get_string('eventappwith:group', 'organizer');
            $a->with = get_string('eventwithout', 'organizer');
            $a->participants = get_string('eventnoparticipants', 'organizer');
        }
    } else {
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
        if (isset($slot->eventid) && count($apps)) {
            $a->appwith = get_string('eventappwith:single', 'organizer');
            $a->with = get_string('eventwith', 'organizer');
            $a->participants = "";
            foreach ($apps as $app) {
                $a->participants .= organizer_get_name_link($app->userid) . ", ";
            }
            $a->participants = trim($a->participants, ", ");
        } else {
            $a->appwith = get_string('eventappwith:single', 'organizer');
            $a->with = get_string('eventwithout', 'organizer');
            $a->participants = get_string('eventnoparticipants', 'organizer');
        }
    }

    if ($slot->locationlink) {
        $a->location = html_writer::link($slot->locationlink, $slot->location);
    } else {
        $a->location = $slot->location;
    }

    $event = new stdClass();
    $event->name = get_string('eventtitle', 'organizer', $a);
    $event->description = get_string('eventtemplate', 'organizer', $a);
    if ($slot->comments != "") {
        $event->description .= get_string('eventtemplatecomment', 'organizer', $slot->comments);
    }
    $event->format = 1;
    $event->courseid = 0;
    $event->groupid = 0;
    $event->userid = $slot->teacherid;
    $event->repeatid = 0;
    $event->modulename = 0;
    $event->instance = $cmid;
    $event->eventtype = 'user';
    $event->timestart = $slot->starttime;
    $event->timeduration = $slot->duration;
    $event->visible = 1;
    $event->sequence = 1;
    $event->timemodified = time();

    if (isset($slot->eventid)) {
        $event->id = $slot->eventid;
        $DB->update_record('event', $event);
        return $event->id;
    } else {
        return $DB->insert_record('event', $event);
    }
}

function organizer_add_event_appointment($cmid, $appointment) {
    global $DB;

    if (is_number($appointment)) {
        $appointment = $DB->get_record('organizer_slot_appointments', array('id' => $appointment));
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', array('id' => $cm->course));
    $slot = $DB->get_record('organizer_slots', array('id' => $appointment->slotid));
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    $a = new stdClass();

    $courseurl = new moodle_url("/course/view.php?id={$course->id}");
    $a->coursename = $course->fullname;
    $a->courselink = html_writer::link($courseurl, $course->fullname);

    $organizerurl = new moodle_url("/mod/organizer/view.php?id={$cm->id}");
    $a->organizername = $organizer->name;
    $a->organizerlink = html_writer::link($organizerurl, $organizer->name);

    if ($organizer->isgrouporganizer) {
        $a->appwith = get_string('eventappwith:group', 'organizer');
        $a->with = get_string('eventwith', 'organizer');
        $group = groups_get_group($appointment->groupid);
        $users = groups_get_members($group->id);
        if ($slot->teachervisible) {
            $memberlist = organizer_get_name_link($slot->teacherid) . " ({$group->name}: ";
        } else {
            $memberlist = get_string('eventteacheranonymous', 'organizer') . " ({$group->name}: ";
        }
        foreach ($users as $user) {
            $memberlist .= organizer_get_name_link($user->id) . ", ";
        }
        $memberlist = trim($memberlist, ", ");
        $memberlist .= ")";
        $a->participants = $memberlist;
    } else {
        $a->appwith = get_string('eventappwith:single', 'organizer');
        $a->with = get_string('eventwith', 'organizer');
        if ($slot->teachervisible) {
            $a->participants = organizer_get_name_link($slot->teacherid);
        } else {
            $a->participants = get_string('eventteacheranonymous', 'organizer');
        }
    }

    if ($slot->locationlink) {
        $a->location = html_writer::link($slot->locationlink, $slot->location);
    } else {
        $a->location = $slot->location;
    }

    $a->description = $slot->comments;

    $event = new stdClass();
    $event->name = get_string('eventtitle', 'organizer', $a);
    $event->description = get_string('eventtemplate', 'organizer', $a);
    $event->format = 1;
    $event->courseid = 0;
    $event->groupid = 0;
    $event->userid = $appointment->userid;
    $event->repeatid = 0;
    $event->modulename = 0;
    $event->instance = $cmid;
    $event->eventtype = 'user';
    $event->timestart = $slot->starttime;
    $event->timeduration = $slot->duration;
    $event->visible = 1;
    $event->sequence = 1;
    $event->timemodified = time();

    if (isset($appointment->eventid)) {
        $event->id = $appointment->eventid;
        $DB->update_record('event', $event);
        return $event->id;
    } else {
        return $DB->insert_record('event', $event);
    }
}

function organizer_update_comments($appid, $comments) {
    global $DB;

    $appointment = $DB->get_record('organizer_slot_appointments', array('id' => $appid));

    if (isset($comments)) {
        $appointment->comments = $comments;
    } else {
        $appointment->comments = '';
    }
    return $DB->update_record('organizer_slot_appointments', $appointment);
}

function organizer_update_appointment_slot($data) {
    global $DB;

    $slot = new stdClass();
    $event = new stdClass();

    $modified = false;
    if ($data->mod_teacherid == 1) {
        $event->userid = $slot->teacherid = $data->teacherid;
        $modified = true;
    }
    if ($data->mod_visibility == 1) {
        $slot->visibility = $data->visibility;
        $modified = true;
    }
    if ($data->mod_location == 1) {
        $slot->location = $data->location;
        $modified = true;
    }
    if ($data->mod_locationlink == 1) {
        $slot->locationlink = $data->locationlink;
        $modified = true;
    }
    if ($data->mod_maxparticipants == 1) {
        $slot->maxparticipants = $data->maxparticipants;
        $modified = true;
    }
    if ($data->mod_notificationtime == 1) {
        $slot->notificationtime = $data->notificationtime;
        $modified = true;
    }
    if ($data->mod_availablefrom == 1) {
        if (is_array($data->availablefrom)) {
            $slot->availablefrom = 0;
        } else {
            $slot->availablefrom = $data->availablefrom;
        }
        $modified = true;
    }
    if ($data->mod_teachervisible == 1) {
        $slot->teachervisible = $data->teachervisible;
        $modified = true;
    }
    if ($data->mod_comments == 1) {
        $event->description = $slot->comments = $data->comments;
        $modified = true;
    }

    if ($modified) {
        foreach ($data->slots as $slotid) {
            $slot->id = $slotid;
            $appcount = count($DB->get_records('organizer_slot_appointments', array('slotid' => $slotid)));
            $maxparticipants = $DB->get_field('organizer_slots', 'maxparticipants', array('id' => $slotid));
            if ($data->mod_maxparticipants == 1 && $appcount > $data->maxparticipants) {
                $slot->maxparticipants = $maxparticipants;
            }

            $DB->update_record('organizer_slots', $slot);

            $updatedslot = $DB->get_record('organizer_slots', array('id' => $slotid));

            organizer_add_event_slot($data->id, $updatedslot, $updatedslot->eventid);

            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid));
            foreach ($apps as $app) {
                organizer_add_event_appointment($data->id, $app, $app->eventid);
            }
        }
    }

    return $data->slots;
}

function organizer_security_check_slots($slots) {
    global $DB;

    if (!isset($slots)) {
        return true;
    }

    $organizer = organizer_get_organizer();
    list($insql, $inparams) = $DB->get_in_or_equal($slots, SQL_PARAMS_NAMED);

    $params = array_merge(array('organizerid' => $organizer->id), $inparams);
    $query = "SELECT * FROM {organizer_slots}
            WHERE {organizer_slots}.organizerid = :organizerid AND {organizer_slots}.id $insql";

    $records = $DB->get_records_sql($query, $params);

    return count($slots) == count($records);
}

function organizer_security_check_apps($apps) {
    global $DB;

    if (!isset($apps)) {
        return true;
    }

    $organizer = organizer_get_organizer();
    list($insql, $inparams) = $DB->get_in_or_equal($apps, SQL_PARAMS_NAMED);

    $params = array_merge(array('organizerid' => $organizer->id), $inparams);
    $query = "SELECT {organizer_slot_appointments}.* FROM {organizer_slot_appointments}
            INNER JOIN {organizer_slots} ON {organizer_slots}.id = {organizer_slot_appointments}.slotid
            WHERE {organizer_slots}.organizerid = :organizerid AND {organizer_slot_appointments}.id $insql";

    $records = $DB->get_records_sql($query, $params);

    return count($apps) == count($records);
}

function organizer_delete_appointment_slot($id) {
    global $DB, $USER;

    if (!$DB->get_record('organizer_slots', array('id' => $id))) {
        return false;
    }

    $eventid = $DB->get_field('organizer_slots', 'eventid', array('id' => $id));

    // If student is registered to this slot, send a message.
    $appointments = $DB->get_records('organizer_slot_appointments', array('slotid' => $id));

    $notifiedusers = 0;

    if (count($appointments) > 0) {
        // Someone was already registered to this slot.
        $slot = new organizer_slot($id);

        foreach ($appointments as $appointment) {
            $reciever = intval($appointment->userid);
            organizer_send_message($USER, $reciever, $slot, 'slotdeleted_notify_student');
            $notifiedusers++;
        }
    }

    $DB->delete_records('event', array('id' => $eventid));
    $DB->delete_records('organizer_slot_appointments', array('slotid' => $id));
    $DB->delete_records('organizer_slots', array('id' => $id));

    return $notifiedusers;
}

function organizer_delete_from_queue($slotid, $userid, $groupid = null) {
    global $DB;

    if($groupid) {
		$queueentries = $DB->get_records('organizer_slot_queues', array('slotid' => $slotid, 'groupid' => $groupid));
		foreach($queueentries as $entry) {
			$DB->delete_records('event', array('id' => $entry->eventid));
            $DB->delete_records('organizer_slot_queues', array('id' => $entry->id));
        }
    } else {
		if (!$queueentry = $DB->get_record('organizer_slot_queues', array('slotid' => $slotid, 'userid' => $userid))) {
			return false;
		} else {
			$DB->delete_records('event', array('id' => $queueentry->eventid));
			$DB->delete_records('organizer_slot_queues', array('slotid' => $slotid, 'userid' => $userid));
		}
	}

    return true;
}

function organizer_delete_user_from_any_queue($organizerid, $userid, $groupid = null) {
    global $DB;

    if($groupid) {
		$params = array('organizerid' => $organizerid, 'groupid' => $groupid);
		$slotquery = 'SELECT q.id, q.eventid
					FROM {organizer_slots} s
					INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
					WHERE s.organizerid = :organizerid AND q.groupid = :groupid';
    } else {
		$params = array('organizerid' => $organizerid, 'userid' => $userid);
		$slotquery = 'SELECT q.id, q.eventid
					FROM {organizer_slots} s
					INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
					WHERE s.organizerid = :organizerid AND q.userid = :userid';
	}

	$slot_queues = $DB->get_records_sql($slotquery, $params);

	foreach ($slot_queues as $slot_queue) {
		$DB->delete_records('event', array('id' => $slot_queue->eventid));
		$DB->delete_records('organizer_slot_queues', array('id' => $slot_queue->id));
	}

    return true;
}

function organizer_add_to_queue(organizer_slot $slotobj, $groupid = 0, $userid = 0) {
	global $DB, $USER;

	if (!$userid) {
		$userid = $USER->id;
	}

   	$organizer = $slotobj->get_organizer();
  	if (!$organizer->queue) {
       	return false;
   	}
    $slotid = $slotobj->get_slot()->id;

    $ok = true;
    if ($organizer->isgrouporganizer && $groupid) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = :groupid",
                array('groupid' => $groupid));

        foreach ($memberids as $memberid) {
            $ok = organizer_queue_single_appointment($slotid, $memberid, $userid, $groupid);
        }
    } else {
        $ok = organizer_queue_single_appointment($slotid, $userid);
    }

    // TODO  create new event for queueing
	//    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
	//    organizer_add_event_slot($cm->id, $slotobj->get_slot());

    return $ok;
}

// Waiting list: changed function
function organizer_register_appointment($slotid, $groupid = 0, $userid = 0, $sendmessage = false, $teacherapplicantid = null) {
    global $DB, $USER, $CFG;

    if (!$userid) {
        $userid = $USER->id;
    }
	$slot = new organizer_slot($slotid);
    if ($slot->is_full()) {
    	return organizer_add_to_queue($slot, $groupid, $userid);
    }
	
	$semaphore = sem_get($slotid);
    sem_acquire($semaphore);

    $ok = true;
    $recipents = array();
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        foreach ($memberids as $memberid) {
            $ok = organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid, $teacherapplicantid);
            $recipents[] = $memberid;
        }
    } else {
        $ok = organizer_register_single_appointment($slotid, $userid, 0, 0, $teacherapplicantid);
        $recipents[] = $userid;
    }

    if ($sendmessage) {
        $mail = get_mailer();
        $mail->Subject = get_string('queuesubject', 'organizer');
        $mail->Body = get_string('queuebody', 'organizer');
        if ($slot->get_slot()->teachervisible) {
            $teacher = $DB->get_record('user', array('id' => $slot->get_slot()->teacherid));
            $mail->From = $teacher->email;
            $mail->FromName = fullname($teacher);
        } else {
            $mail->From = $CFG->noreplyaddress;
        }
        foreach ($recipents as $userid) {
            $address = $DB->get_field('user', 'email', array('id' => $userid));
            $mail->addAddress($address);
        }
        $mail->send();
    }

    $cm = organizer_get_cm();
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    organizer_add_event_slot($cm->id, $slot);
    sem_release($semaphore);

    return $ok;
}

function organizer_register_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0, $teacherapplicantid = null) {
    global $DB;

    list($cm, , $organizer, ) = organizer_get_course_module_data();

    $appointment = new stdClass();
    $appointment->slotid = $slotid;
    $appointment->userid = $userid;
    $appointment->groupid = $groupid;
    $appointment->applicantid = $applicantid ? $applicantid : $userid;
    $appointment->notified = 0;
    $appointment->attended = null;
    $appointment->grade = null;
    $appointment->feedback = '';
    $appointment->comments = '';
    $appointment->teacherapplicantid = $teacherapplicantid;
    $appointment->teacherapplicanttimemodified = strtotime("now");

    $appointment->eventid = organizer_add_event_appointment($cm->id, $appointment);

    $appointment->id = $DB->insert_record('organizer_slot_appointments', $appointment);

	if (organizer_hasqueue($organizer->id)) {
		$unqueue = organizer_delete_user_from_any_queue($organizer->id, $userid);
	}

    return $appointment->id;
}

// Waiting list new function
function organizer_queue_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0) {
    global $DB;

    $cm = organizer_get_cm();

    $appointment = new stdClass();
    $appointment->slotid = $slotid;
    $appointment->userid = $userid;
    $appointment->groupid = $groupid;
    $appointment->applicantid = $applicantid ? $applicantid : $userid;
    $appointment->notified = 0;
    $appointment->attended = null;
    $appointment->grade = null;
    $appointment->feedback = '';
    $appointment->comments = '';

    $appointment->eventid = organizer_add_event_appointment($cm->id, $appointment);

    $appointment->id = $DB->insert_record('organizer_slot_queues', $appointment);

    return $appointment->id;
}

// Waiting list: function changed
function organizer_reregister_appointment($slotid, $groupid = 0) {
    global $DB, $USER;

    $semaphore = sem_get($slotid);
    sem_acquire($semaphore);

    $slot = new organizer_slot($slotid);
    if ($slot->is_full()) {
        return false;
    }

	$ok_register = true;
	$ok_unregister = true;
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        foreach ($memberids as $memberid) {
            $app = organizer_get_last_user_appointment($slot->organizerid, $memberid);
            $ok_register = organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid);
            if (isset($app)) {
                $ok_unregister = organizer_unregister_single_appointment($app->slotid, $memberid);
            }
        }
    } else {
        $app = organizer_get_last_user_appointment($slot->organizerid);
        $ok_register = organizer_register_single_appointment($slotid, $USER->id);
        if (isset($app)) {
            $ok_unregister = organizer_unregister_single_appointment($app->slotid, $USER->id);
        }
    }

 	if (organizer_hasqueue($slot->organizerid)) {
	    $slotx = new organizer_slot($app->slotid);
		if (organizer_is_group_mode()) {
            if($next = $slotx->get_next_in_queue_group()) {
                $ok_register = organizer_register_appointment($app->slotid, $next->groupid, 0, true);
                organizer_delete_from_queue($app->slotid, null, $next->groupid);
            }
		} else {
            if($next = $slotx->get_next_in_queue()) {
                $ok_register = organizer_register_appointment($app->slotid, 0, $next->userid, true);
                organizer_delete_from_queue($app->slotid, $next->userid);
            }
		}
 	}

    $cm = organizer_get_cm();
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    organizer_add_event_slot($cm->id, $slot);
    sem_release($semaphore);

    return $ok_register && $ok_unregister;
}

function organizer_get_active_appointment($userid, $organizerid) {
    global $DB;

    $params = array('organizerid' => $organizerid, 'userid' => $userid);
    $query = "SELECT * FROM {organizer_slot_appointments} INNER JOIN {organizer_slots} ON
            {organizer_slot_appointments}.slotid = {organizer_slots}.id WHERE
            {organizer_slot_appointments}.userid = :userid AND {organizer_slots}.organizerid = :organizerid";
    $appointment = $DB->get_record_sql($query, $params);
    if (isset($appointment) && !isset($appointment->attended)) {
        return $appointment;
    }
    return null;
}

// Waiting list: changed function
function organizer_unregister_appointment($slotid, $groupid, $organizerid) {
    global $DB, $USER;

    $ok = true;

    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', 'groupid = ?', array($groupid));

        foreach ($memberids as $memberid) {
            $ok = organizer_unregister_single_appointment($slotid, $memberid);
        }
    } else {
        $ok = organizer_unregister_single_appointment($slotid, $USER->id);
    }

 	if (organizer_hasqueue($organizerid)) {
	    $slotx = new organizer_slot($slotid);
		if (organizer_is_group_mode()) {
            if($next = $slotx->get_next_in_queue_group()) {
                organizer_register_appointment($slotid, $next->groupid, 0, true);
                organizer_delete_from_queue($slotid, null, $next->groupid);
            }
		} else {
            if($next = $slotx->get_next_in_queue()) {
                organizer_register_appointment($slotid, 0, $next->userid, true);
                organizer_delete_from_queue($slotid, $next->userid);
            }
		}
 	}

    return $ok;
}

// Waiting list: changed function
function organizer_unregister_single_appointment($slotid, $userid) {
    global $DB;

	$ok_register = true;
	$ok_unqueue = true;
    $slotx = new organizer_slot($slotid);
    if($eventid = $DB->get_field('organizer_slot_appointments', 'eventid', array('userid' => $userid, 'slotid' => $slotid))) {
	    $deleted = $DB->delete_records('event', array('id' => $eventid));
	}

	$ok = $DB->delete_records('organizer_slot_appointments', array('userid' => $userid, 'slotid' => $slotid));
	
	return $ok;
}

function organizer_evaluate_slots($data) {
    global $DB;

    $organizer = organizer_get_organizer();

    $slotids = array();

    if (isset($data->apps) && count($data->apps) > 0 && isset($data->slotenable)) {
        foreach ($data->apps as $appid => $app) {
            $newapp = $DB->get_record('organizer_slot_appointments', array('id' => $appid));
            if (array_search($newapp->slotid, array_keys($data->slotenable)) === false) {
                continue;
            }
            $newapp->attended = $app['attended'];

            if ($organizer->grade > 0) {
                $newapp->grade = isset($app['grade']) ? $app['grade'] : -1;
            } else {
                $newapp->grade = isset($app['grade']) ? $app['grade'] : 0;
            }

            $newapp->feedback = isset($app['feedback']) ? $app['feedback'] : "";
            $newapp->allownewappointments = $app['allownewappointments'];

            $DB->update_record('organizer_slot_appointments', $newapp);

            organizer_update_grades($organizer, $newapp->userid);

            $slotids[] = $newapp->slotid;
        }
    }

    return $slotids;
}

function organizer_get_course_module_data() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $organizer->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        print_error('organizer_get_course_module_data: You must specify a course_module ID or an instance ID');
    }

    $context = context_module::instance($cm->id, MUST_EXIST);

    return array($cm, $course, $organizer, $context);
}

function organizer_get_course_module_data_new() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    $instance = new stdClass();

    if ($id) {
        $instance->cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $instance->course = $DB->get_record('course', array('id' => $instance->cm->course), '*', MUST_EXIST);
        $instance->organizer = $DB->get_record('organizer', array('id' => $instance->cm->instance), '*', MUST_EXIST);
    } else if ($n) {
        $instance->organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
        $instance->course = $DB->get_record('course', array('id' => $instance->organizer->course), '*', MUST_EXIST);
        $instance->cm = get_coursemodule_from_instance('organizer', $instance->organizer->id, $instance->course->id,
                false, MUST_EXIST);
    } else {
        print_error('organizer_get_course_module_data_new: You must specify a course_module ID or an instance ID');
    }

    $instance->context = context_module::instance($instance->cm->id, MUST_EXIST);

    return $instance;
}

function organizer_get_organizer() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
    } else {
        print_error('organizer_get_organizer: You must specify a course_module ID or an instance ID');
    }

    return $organizer;
}

function organizer_get_cm() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $organizer->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        print_error('organizer_get_cm: You must specify a course_module ID or an instance ID');
    }

    return $cm;
}

function organizer_get_context() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $organizer->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        print_error('organizer_get_context: You must specify a course_module ID or an instance ID');
    }

    $context = context_module::instance($cm->id, MUST_EXIST);

    return $context;
}


function organizer_is_group_mode() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    return $organizer->isgrouporganizer;
}

// Waiting list new function
function organizer_is_queueable() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    return $organizer->queue;
}

function organizer_fetch_my_group() {

    if (organizer_is_group_mode()) {
        global $DB, $USER;
        $id = optional_param('id', 0, PARAM_INT);
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);

        $params = array('groupingid' => $cm->groupingid, 'userid' => $USER->id);
        $query = "SELECT {groups}.* FROM {groups}
                    INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                    INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                    WHERE {groupings_groups}.groupingid = :groupingid
                    AND {groups_members}.userid = :userid
                    ORDER BY {groups}.name ASC";
        $group = $DB->get_record_sql($query, $params);
        return $group;
    } else {
        return false;
    }
}

function organizer_fetch_user_group($userid) {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);

    $params = array('groupingid' => $cm->groupingid, 'userid' => $userid);
    $query = "SELECT {groups}.id FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid
                AND {groups_members}.userid = :userid
                ORDER BY {groups}.name ASC";
    $group = $DB->get_record_sql($query, $params);
    return $group;
}

function organizer_fetch_hidecalendar() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $hidecalendar = $DB->get_field('organizer', 'hidecalendar', array('id' => $cm->instance), MUST_EXIST);
    return $hidecalendar;
}


function organizer_fetch_table_entries($slots, $orderby="") {
    global $DB;

    list($insql, $inparams) = $DB->get_in_or_equal($slots, SQL_PARAMS_NAMED);

    $params = array();
    $query = "SELECT CONCAT(s.id, COALESCE(a.id, 0)) AS mainid,
    s.id AS slotid,
    a.id,
    a.attended,
    a.grade,
    a.feedback,
    a.comments,
    s.starttime,
    s.duration,
    s.gap,
    s.location,
    s.comments AS teachercomments,
    u.firstname,
    u.lastname,
	u.email,
    u.idnumber,
    u2.firstname AS teacherfirstname,
    u2.lastname AS teacherlastname,
    g.name AS groupname,
    CASE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
    WHEN 0 THEN 1
    ELSE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
    END AS rowspan,
	a.teacherapplicantid

    FROM {organizer_slots} s
    LEFT JOIN {organizer_slot_appointments} a ON a.slotid = s.id
    LEFT JOIN {user} u ON a.userid = u.id
    LEFT JOIN {user} u2 ON s.teacherid = u2.id
    LEFT JOIN {groups} g ON a.groupid = g.id

    WHERE s.id $insql
    ";

    if ($orderby == " " || $orderby == "") {
        $query .= "ORDER BY s.starttime ASC, s.id,
        u.lastname ASC,
        u.firstname ASC,
        teacherlastname ASC,
        teacherfirstname ASC";
    } else {
        $query .= "ORDER BY " . $orderby;
    }

    $params = array_merge($params, $inparams);
    return $DB->get_records_sql($query, $params);
}

/** Waiting list
 * Checks whether an organizer instance supports waiting queues.
 *
 * @param int $organizerid The ID of the organizer instance.
 * @return boolean
 */
function organizer_hasqueue($organizerid) {
    global $DB;

    $result = false;
    if ($DB->get_field('organizer', 'queue', array('id' => $organizerid))) {
        $result = true;
    }
    return $result;
}

function organizer_with_grading() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
	if($organizer->grade!=0) {
    	return 1;
	} else {
		return 0;
	}
}

function organizer_get_teacherapplicant_output($teacherapplicantid, $teacherapplicanttimemodified=null, $printable=false) {
    global $DB;

	$output = "";
	
	if(is_numeric($teacherapplicantid)) {
		if(!$printable) {
			$timestampstring = $teacherapplicanttimemodified!=null ? "\n" . userdate($teacherapplicanttimemodified, get_string('fulldatetimetemplate', 'organizer')) : "";
			if($teacher = $DB->get_record('user', array('id' => $teacherapplicantid), 'lastname,firstname')) {
				$output = " <span style= 'cursor:help;' title='" . get_string('slotassignedby', 'organizer') . " " . 
							$teacher->firstname . " " . $teacher->lastname . $timestampstring ."'>[" . $teacher->firstname[0] . $teacher->lastname[0] . "]</span>";
			}
		} else {
			if($teacher = $DB->get_record('user', array('id' => $teacherapplicantid), 'lastname,firstname')) {
				$output = "[" . $teacher->firstname[0] . $teacher->lastname[0] . "]";
			}
		}
	}
	
	return $output;
		
}

function organizer_fetch_groupname($groupid) {
    global $DB;

    $groupname = $DB->get_field('groups', 'name', array('id' => $groupid));

    return $groupname;
}

function organizer_fetch_groupusers($groupid) {
    global $DB;

    $query = "SELECT u.id, u.lastname,u.firstname FROM {groups_members} g
            INNER JOIN {user} u ON u.id = g.userid 
            WHERE g.groupid = :groupid";
    $par = array('groupid' => $groupid);
    $users = $DB->get_records_sql($query, $par);

    if (!$users || count($users) == 0) {
        return array();
    }
		
    return $users;
}
	
