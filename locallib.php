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
 * @package   mod_organizer
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');

/**
 *
 * @param int $trainerid
 * @param int $newslotid
 * @param number $startdate
 * @param number $enddate
 * @return array
 */
function organizer_load_eventsandslots($trainerid, $newslotid, $startdate, $enddate) {
    global $DB;

    // Get all slots of this trainer and all non-organizer events in the given timeframe.
    $params = array('trainerid' => $trainerid, 'modulename' => 'organizer', 'startdate1' => $startdate,
        'enddate1' => $enddate, 'startdate2' => $startdate, 'enddate2' => $enddate, 'trainerid2' => $trainerid,
        'newslotid' => $newslotid, 'startdate3' => $startdate, 'enddate3' => $enddate, 'startdate4' => $startdate,
        'enddate4' => $enddate);
    $query = "SELECT {event}.id as id, {event}.name, {event}.timestart, {event}.timeduration, 'event' as typ
              FROM {event}
              INNER JOIN {user} ON {user}.id = {event}.userid
              WHERE {event}.userid = :trainerid AND {event}.modulename <> :modulename
              AND (
                  {event}.timestart >= :startdate1 AND
                  {event}.timestart < :enddate1
                  OR
                  {event}.timestart + {event}.timeduration >= :startdate2 AND
                  {event}.timestart + {event}.timeduration < :enddate2
                  )

              UNION

              SELECT {organizer_slots}.id as id, {organizer_slots}.location as name, {organizer_slots}.starttime as timestart,
              {organizer_slots}.duration as timeduration, 'slot' as typ
              FROM {organizer_slots}
              INNER JOIN {organizer_slot_trainer} ON {organizer_slot_trainer}.slotid = {organizer_slots}.id
              WHERE {organizer_slot_trainer}.trainerid = :trainerid2
              AND {organizer_slots}.id <> :newslotid
              AND (
                  {organizer_slots}.starttime >= :startdate3 AND
                  {organizer_slots}.starttime < :enddate3
                  OR {organizer_slots}.starttime + {organizer_slots}.duration >= :startdate4 AND
                  {organizer_slots}.starttime + {organizer_slots}.duration < :enddate4
                  )
              ";

    return $DB->get_records_sql($query, $params);
}
/**
 *
 * @param number $user
 * @return boolean|string
 */
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
 *
 * @param  number    $from
 * @param  number to
 * @return array an array of events
 */
function organizer_check_collision($from, $to, $eventsandslots) {
    $collidingevents = array();
    foreach ($eventsandslots as $event) {
        $eventfrom = $event->timestart;
        $eventto = $eventfrom + $event->timeduration;

        if (between($from, $eventfrom, $eventto) || between($to, $eventfrom, $eventto)
            || between($eventfrom, $from, $to) || between($eventto, $from, $to)
            || $from == $eventfrom || $eventfrom == $eventto
        ) {
            $collidingevents[] = $event;
        }
    }
    return $collidingevents;
}
/**
 *
 * @param number $num
 * @param number $lower
 * @param number $upper
 * @return boolean
 */
function between($num, $lower, $upper) {
    return $num > $lower && $num < $upper;
}
/**
 *
 * @param array $data
 * @return array|number[]|string[]|NULL[][]
 */
function organizer_add_new_slots($data) {
    global $DB;

    $count = array();

    $cm = get_coursemodule_from_id('organizer', $data->id);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));
    $organizerconfig = get_config('organizer');
    $relativedeadline = $organizer->relativedeadline;

    if (!isset($data->newslots)) {
        return $count;
    }

    $collisionmessages = "";
    $startdate = $data->startdate;
    $enddate = $data->enddate + 86400;
    $date = time();
    $slotsnotcreatedduetodeadline = 0;
    $slotsnotcreatedduetopasttime = 0;

    for ($daydate = $startdate; $daydate <= $enddate; $daydate = strtotime('+1 day', $daydate)) {

        $weekday = date('N', $daydate) - 1;
        foreach ($data->newslots as $slot) {
            if ($slot['day'] != $weekday || $slot['day'] == -1 || $slot['dayto'] == -1 ) {
                continue;
            }
            $slot['from'] = $slot['fromh'] + $slot['fromm'];
            $slot['to'] = $slot['toh'] + $slot['tom'];
            $slot['datefrom'] = $daydate + $slot['from'];
            $slot['dateto'] = organizer_get_dayto($slot['dayto'], $daydate);
            $slot['dateto'] = $slot['dateto'] + $slot['to'];
            if ($slot['datefrom'] < $startdate || $slot['datefrom'] > $enddate) {
                continue;
            }
            while ($slot['dateto'] < $slot['datefrom']) {
                $slot['dateto'] += (7 * 86400);
            }

            $newslot = new stdClass();
            $newslot->maxparticipants = $data->maxparticipants;
            $newslot->visibility = $data->visibility;
            $newslot->timemodified = time();
            $trainerids = $data->trainerid;
            $newslot->teachervisible = isset($data->teachervisible) ? 1 : 0;
            $newslot->notificationtime = $data->notificationtime;
            $newslot->availablefrom = isset($data->availablefrom) ? $data->availablefrom : 0;
            $newslot->location = $data->location;
            $newslot->locationlink = $data->locationlink;
            $newslot->isgroupappointment = $organizer->isgrouporganizer;
            $newslot->duration = $data->duration;
            $newslot->comments = (isset($data->comments)) ? $data->comments : '';
            $newslot->organizerid = $organizer->id;
            $newslot->visible = $slot['visible'];

            if (!isset($data->duration) || $data->duration < 1) {
                print_error('Duration is invalid (not set or < 1). No slots will be added. Contact support!');
            }

            if (!isset($data->gap) || $data->gap < 0) {
                print_error('Gap is invalid (not set or < 0). No slots will be added. Contact support!');
            }

            $dateto = $enddate < $slot['dateto'] ? $enddate : $slot['dateto'];
            for ($time = $slot['datefrom']; $time + $data->duration <= $dateto; $time += ($data->duration + $data->gap)) {

                if ($time - $date < $relativedeadline && $time - $date > 0 ) {
                    $slotsnotcreatedduetodeadline++;
                } else if ($time - $date < 0 && $organizerconfig->allowcreationofpasttimeslots == false) {
                    $slotsnotcreatedduetopasttime++;
                } else {
                    $newslot->starttime = $time;
                    // NEW SLOTS ARE MADE HERE!
                    $newslot->id = $DB->insert_record('organizer_slots', $newslot);

                    $newtrainerslot = new stdClass();
                    $eventids = array();
                    foreach ($trainerids as $trainerid) {
                        $newtrainerslot->slotid = $newslot->id;
                        $newtrainerslot->trainerid = $trainerid;
                        $newtrainerslot->id = $DB->insert_record('organizer_slot_trainer', $newtrainerslot);
                        // If empty slots generate events -> an event per trainer is created.
                        // And eventid is stored in table organizer_slot_trainer.
                        if (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation) {
                            $newtrainerslot->eventid = organizer_add_event_slot($data->id, $newslot, $trainerid);
                            $DB->update_record('organizer_slots', $newslot);
                            $DB->update_record('organizer_slot_trainer', $newtrainerslot);
                            $eventids[] = $newtrainerslot->eventid;
                        }
                        $eventsandslots = organizer_load_eventsandslots($trainerid, $newslot->id, $newslot->starttime,
                            $newslot->starttime + $newslot->duration);
                        if ($collisions = organizer_check_collision($newslot->starttime,
                            $newslot->starttime + $newslot->duration, $eventsandslots)) {
                            $head = true;
                            $collisionmessage = "";
                            foreach ($collisions as $collision) {
                                if ($head) {
                                    $collisionmessage .= '<span class="warning">' .
                                        get_string('collision', 'organizer') . '</span><br />';
                                    $head = false;
                                }
                                if ($collision->typ == 'slot') {
                                    $name = "<strong>" . get_string('timeslot', 'organizer') . "</strong> " .
                                        get_string('location', 'organizer') . ': ' . $collision->name;
                                } else {
                                    $name = "<strong>" . get_string('event', 'organizer') . " '" . $collision->name . "'</strong>";
                                }
                                $collisionmessage .= $name .
                                    ' ' . get_string('from') . ': ' . userdate($collision->timestart,
                                        get_string('fulldatetimetemplate', 'organizer')) .
                                    ' ' . get_string('to') . ': ' . userdate($collision->timestart + $collision->timeduration,
                                        get_string('fulldatetimetemplate', 'organizer')) .
                                    '<br />';
                            }

                            $collisionmessages .= $collisionmessage;
                        }
                    }
                    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT) {
                        organizer_create_coursegroup($newslot);
                    }
                    $count[] = $newslot->id;
                }
            }
        } // End foreach slot
    } // End for week

    return array($count, $slotsnotcreatedduetodeadline, $slotsnotcreatedduetopasttime, $collisionmessages);
}
/**
 *
 * @param int $slotdate
 * @param int $time
 * @return number
 */
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
/**
 *
 * @param int $dayto
 * @param number $dateday
 * @return NULL
 */
function organizer_get_dayto($dayto, $dateday) {

    switch($dayto) {
        case 0:  // Monday.
            if (date('l', $dateday) == 'Monday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Monday", $dateday);
            }
        break;
        case 1:  // Tuesday.
            if (date('l', $dateday) == 'Tuesday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Tuesday", $dateday);
            }
        break;
        case 2:  // Wednesday.
            if (date('l', $dateday) == 'Wednesday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Wednesday", $dateday);
            }
        break;
        case 3:  // Thursday.
            if (date('l', $dateday) == 'Thursday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Thursday", $dateday);
            }
        break;
        case 4:  // Friday.
            if (date('l', $dateday) == 'Friday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Friday", $dateday);
            }
        break;
        case 5:  // Saturday.
            if (date('l', $dateday) == 'Saturday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Saturday", $dateday);
            }
        break;
        case 6:  // Sunday.
            if (date('l', $dateday) == 'Sunday') {
                $date = $dateday;
            } else {
                $date = strtotime("next Sunday", $dateday);
            }
        break;
        default:
            $date = null;
    }

    return $date;
}

function organizer_add_day_to_date($date, $days) {

}

function organizer_add_event_slot($cmid, $slot, $userid = null, $eventid = null) {
    global $DB;

    if (is_number($slot)) {
        $slot = $DB->get_record('organizer_slots', array('id' => $slot));
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', array('id' => $cm->course));
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    $a = new stdClass();

    $courseurl = new moodle_url("/course/view.php?id={$course->id}");
    $coursename = organizer_filter_text($course->fullname);
    $a->coursename = $coursename;
    $a->courselink = html_writer::link($courseurl, $coursename);

    $organizerurl = new moodle_url("/mod/organizer/view.php?id={$cm->id}");
    $organizername = organizer_filter_text($organizer->name);
    $a->organizername = $organizername;
    $a->organizerlink = html_writer::link($organizerurl, $organizername);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
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
            $a->with = "-";
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
            $a->with = "-";
            $a->participants = get_string('eventnoparticipants', 'organizer');
        }
    }

    if ($slot->locationlink) {
        $a->location = html_writer::link($slot->locationlink, $slot->location);
    } else {
        $a->location = $slot->location;
    }

    $eventtitle = get_string('eventtitle', 'organizer', $a);
    $eventdescription = get_string('eventtemplatewithoutlinks', 'organizer', $a);
    if ($slot->comments != "") {
        $eventdescription .= get_string('eventtemplatecomment', 'organizer', $slot->comments);
    }

    if ($eventid) {
        return organizer_change_calendarevent(
            array($eventid), $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_SLOT,
            $userid, $slot->starttime, $slot->duration, 0, $slot->id
        );
    } else {
        return organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_SLOT,
                $userid, $slot->starttime, $slot->duration, 0, $slot->id
        );
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

    $a = organizer_add_event_appointment_strings($course, $organizer, $cm, $slot);

    // Calendar events for participants info fields.
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $a->appwith = get_string('eventappwith:group', 'organizer');
        $a->with = get_string('eventwith', 'organizer');
        $users = groups_get_members(0);
        $groupname = "";
        $groupid = 0;
        if ($group = groups_get_group($appointment->groupid)) {
            $groupid = $group->id;
            $users = groups_get_members($groupid);
            $groupname = $group->name;
        }
        $memberlist = "";
        if ($slot->teachervisible) {
            $conn = "";
            $trainers = organizer_get_slot_trainers($slot->id);
            foreach ($trainers as $trainerid) {
                $memberlist .= $conn . organizer_get_name_link($trainerid);
                $conn = ", ";
            }
            $memberlist .= " {$groupname} ";
        } else {
            $memberlist = get_string('eventteacheranonymous', 'organizer') . " {$groupname} ";
        }
        $a->with = $memberlist;
        $memberlist = "";
        foreach ($users as $user) {
            $memberlist .= organizer_get_name_link($user->id) . ", ";
        }
        $memberlist = trim($memberlist, ", ");
        $a->participants = $memberlist;
    } else {
        $groupid = 0;
        $a->appwith = get_string('eventappwith:single', 'organizer');
        $a->with = get_string('eventwith', 'organizer');
        if ($slot->teachervisible) {
            $conn = "";
            $trainers = organizer_get_slot_trainers($slot->id);
            $trainerlist = "";
            foreach ($trainers as $trainerid) {
                $trainerlist .= $conn . organizer_get_name_link($trainerid);
                $conn = ", ";
            }
            $a->participants = $trainerlist;
        } else {
            $a->participants = get_string('eventteacheranonymous', 'organizer');
        }
    }

    $eventtitle = get_string('eventtitle', 'organizer', $a);
    $eventdescription = get_string('eventtemplatewithoutlinks', 'organizer', $a);

    // Create new appointment event or update existent appointment event for participants.
    if (!isset($appointment->eventid) || !$appointment->eventid) {
        $eventid = organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT,
            $appointment->userid, $slot->starttime, $slot->duration, $groupid, $appointment->id
        );
    } else {
        $eventid = organizer_change_calendarevent(
            array($appointment->eventid), $organizer, $eventtitle, $eventdescription,
            ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT, $appointment->userid, $slot->starttime,
                $slot->duration, $groupid, $appointment->id
        );
    }

    return $eventid;

}

function organizer_add_event_appointment_trainer($cmid, $appointment, $trainerid = null) {
    global $DB;

    if (is_number($appointment)) {
        $appointment = $DB->get_record('organizer_slot_appointments', array('id' => $appointment));
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', array('id' => $cm->course));
    $slot = $DB->get_record('organizer_slots', array('id' => $appointment->slotid));
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    if (!$trainerid) {
        // Create or transform to appointment events for the slot for each trainer.
        $trainers = organizer_get_slot_trainers($slot->id);
        foreach ($trainers as $trainerid) {
            organizer_change_calendarevent_trainer($trainerid, $course, $cm, $organizer, $appointment, $slot);
        }
    } else {
        organizer_change_calendarevent_trainer($trainerid, $course, $cm, $organizer, $appointment, $slot);
    }

    return true;

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

function organizer_update_slot($data) {
    global $DB;

    $slot = new stdClass();

    $modified = false;
    $trainermodified = false;
    if ($data->mod_visible == 1) {
        $allslotsvisible = $data->visible;
        $modified = true;
    }
    if ($data->mod_trainerid == 1) {
        $trainermodified = true;
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
        $slot->comments = $data->comments;
        $modified = true;
    }

    if ($modified || $trainermodified) {
        $organizer = organizer_get_organizer();
        foreach ($data->slots as $slotid) {
            $slot->id = $slotid;
            $appcount = organizer_count_slotappointments(array($slotid));
            $maxparticipants = $DB->get_field('organizer_slots', 'maxparticipants', array('id' => $slotid));
            $slotmodified = (int)$maxparticipants != (int)$data->maxparticipants;
            // Make shure that a new maxparticipant value is not higher than the amount of the slot's bookings.
            if ($data->mod_maxparticipants == 1 && $appcount > $data->maxparticipants) {
                $slot->maxparticipants = $maxparticipants;
                // Check if there are waiting list entries in case of increased places.
            } else if ($modified && $data->mod_maxparticipants == 1  && $data->maxparticipants > $appcount) {
                $freeslots = (int)$data->maxparticipants - (int)$appcount;
                if (organizer_hasqueue($organizer->id)) {
                    for ($i = 0; $i < $freeslots; $i++) {
                        $slotx = new organizer_slot($slotid);
                        if (organizer_is_group_mode()) {
                            if ($next = $slotx->get_next_in_queue_group()) {
                                // The moodlegroup groupmode does not allow more than one group, so following code is..
                                // Not Used At the moment.
                                if (organizer_register_appointment($slotid, $next->groupid, 0, true, null, true)) {
                                    organizer_delete_from_queue($slotid, null, $next->groupid);
                                }
                            } else {
                                break;
                            }
                        } else {
                            $next = $slotx->get_next_in_queue();
                            if ($next) {
                                if (organizer_register_appointment($slotid, 0, $next->userid, true, null, true)) {
                                    organizer_delete_from_queue($slotid, $next->userid);
                                }
                            } else {
                                break;
                            }
                        }
                    }
                }
            }
            if ($appcount) {
                $slot->visible = 1;
                $slotmodified = true;
            } else {
                if (isset($allslotsvisible)) {
                    $slot->visible = $allslotsvisible;
                    $slotmodified = true;
                } else {
                    unset($slot->visible);
                }
            }

            if ($slotmodified  || $modified) {
                $DB->update_record('organizer_slots', $slot);
            }

            if ($trainermodified) {
                $trainers = organizer_get_slot_trainers($slot->id);
                if ($deletions = array_diff($trainers, $data->trainerid)) {
                    list($insql, $inparams) = $DB->get_in_or_equal($deletions, SQL_PARAMS_NAMED);
                    $eventids = $DB->get_fieldset_select(
                            'organizer_slot_trainer', 'eventid', 'slotid = ' . $slot->id . ' AND trainerid ' . $insql, $inparams
                    );
                    foreach ($eventids as $eventid) {
                        $DB->delete_records('event', array('id' => $eventid));
                    }
                    $DB->delete_records_select(
                            'organizer_slot_trainer', 'slotid = ' . $slot->id . ' AND trainerid ' . $insql, $inparams
                    );
                    if ($organizer->includetraineringroups && ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
                        $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT)) {
                        foreach ($deletions as $trainerid) {
                            organizer_groupsynchronization($slot->id, $trainerid, 'remove');
                        }
                    }
                }
                if ($inserts = array_diff($data->trainerid, $trainers)) {
                    $record = new stdClass();
                    foreach ($inserts as $trainerid) {
                        $record->slotid = $slotid;
                        $record->trainerid = $trainerid;
                        $DB->insert_record('organizer_slot_trainer', $record);
                        if ($apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid))) {
                            list($cm, , , , ) = organizer_get_course_module_data();
                            foreach ($apps as $app) {
                                organizer_add_event_appointment_trainer($cm->id, $app, $trainerid);
                            }
                        }
                        if ($organizer->includetraineringroups && ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
                                $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT)) {
                            organizer_groupsynchronization($slot->id, $trainerid, 'add');
                        }
                    }
                }
            }
            if ($apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid))) {
                // Add or update appointment events for participants.
                foreach ($apps as $app) {
                    organizer_add_event_appointment($data->id, $app);
                }
            } else {
                // If empty slots events are activated create them now for this slot.
                $updatedslot = $DB->get_record('organizer_slots', array('id' => $slotid));
                if (!$nocalendareventslotcreation = $DB->get_field('organizer', 'nocalendareventslotcreation',
                    array('id' => $updatedslot->organizerid))) {
                    $trainers = $DB->get_records('organizer_slot_trainer', array('slotid' => $slotid));
                    foreach ($trainers as $trainer) {
                        organizer_add_event_slot($data->id, $updatedslot, $trainer->trainerid, $trainer->eventid);
                    }
                }
            }
        }
    }

    return $data->slots;
}

function organizer_delete_appointment_slot($id) {
    global $DB, $USER;

    if (!$DB->get_record('organizer_slots', array('id' => $id))) {
        return false;
    }

    // If student is registered to this slot, send a message.
    $appointments = $DB->get_records('organizer_slot_appointments', array('slotid' => $id));
    $notifiedusers = 0;
    if (count($appointments) > 0) {
        // Someone was already registered to this slot.
        $slot = new organizer_slot($id);

        foreach ($appointments as $appointment) {
            $reciever = intval($appointment->userid);
            organizer_send_message($USER, $reciever, $slot, 'slotdeleted_notify_student');
            $DB->delete_records('event', array('id' => $appointment->eventid));
            $notifiedusers++;
        }
    }

    $trainers = organizer_get_slot_trainers($id);
    foreach ($trainers as $trainerid) {
        $slottrainer = $DB->get_record('organizer_slot_trainer', array('slotid' => $id, 'trainerid' => $trainerid));
        $DB->delete_records('event', array('id' => $slottrainer->eventid));
        $DB->delete_records('organizer_slot_trainer', array('id' => $slottrainer->id));
    }
    $DB->delete_records('organizer_slot_appointments', array('slotid' => $id));
    $DB->delete_records('organizer_slots', array('id' => $id));

    return $notifiedusers;
}

function organizer_delete_appointment($id) {
    global $DB, $USER;

    if (!$appointment = $DB->get_record('organizer_slot_appointments', array('id' => $id))) {
        return false;
    }

    // Send a message to the participant.
    $slot = new organizer_slot($appointment->slotid);
    $receiver = intval($appointment->userid);
    organizer_send_message($USER, $receiver, $slot, 'appointmentdeleted_notify_student');
    $DB->delete_records('event', array('id' => $appointment->eventid));
    $DB->delete_records('organizer_slot_appointments', array('id' => $id));

    return true;
}

function organizer_delete_appointment_group($slotid, $groupid) {
    global $DB, $USER;

    $slot = new organizer_slot($slotid);

    if (!$appointments = $DB->get_records('organizer_slot_appointments',
        array('slotid' => $slotid, 'groupid' => $groupid))) {
        return false;
    }

    foreach ($appointments as $appointment) {
        // Send a message to the participant.
        $receiver = intval($appointment->userid);
        organizer_send_message($USER, $receiver, $slot, 'appointmentdeleted_notify_student');
        $DB->delete_records('event', array('id' => $appointment->eventid));
        $DB->delete_records('organizer_slot_appointments', array('id' => $appointment->id));
    }

    return true;
}

function organizer_delete_from_queue($slotid, $userid, $groupid = null) {
    global $DB;

    if ($groupid) {
        $queueentries = $DB->get_records('organizer_slot_queues', array('slotid' => $slotid, 'groupid' => $groupid));
        foreach ($queueentries as $entry) {
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

    if ($groupid) {
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

    $slotqueues = $DB->get_records_sql($slotquery, $params);

    foreach ($slotqueues as $slotqueue) {
        $DB->delete_records('event', array('id' => $slotqueue->eventid));
        $DB->delete_records('organizer_slot_queues', array('id' => $slotqueue->id));
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
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $groupid) {
        $memberids = $DB->get_fieldset_select(
            'groups_members', 'userid', "groupid = :groupid",
            array('groupid' => $groupid)
        );

        foreach ($memberids as $memberid) {
            $ok = organizer_queue_single_appointment($slotid, $memberid, $userid, $groupid);
        }
    } else {
        $ok = organizer_queue_single_appointment($slotid, $userid);
    }

    return $ok;
}

function organizer_register_appointment($slotid, $groupid = 0, $userid = 0,
                                        $sendmessage = false, $teacherapplicantid = null, $slotnotfull = false) {
    global $DB, $USER;

    if (!$userid) {
        $userid = $USER->id;
    }
    $slot = new organizer_slot($slotid);
    if (!$slotnotfull) {
        if ($slot->is_full()) {
            return organizer_add_to_queue($slot, $groupid, $userid);
        }
    }

    if ($sendmessage) {
        $trainers = organizer_get_slot_trainers($slot->get_slot()->id);
        if ($slot->get_slot()->teachervisible && !empty($trainers)) {
            $trainerid = reset($trainers);
            $from = core_user::get_user($trainerid);
        } else {
            $from = core_user::get_noreply_user();
        }
    }

    $organizer = $slot->get_organizer();
    $ok = true;
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");
        $generatetrainerevents = true;
        foreach ($memberids as $memberid) {
            if ($ok = organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid,
                $teacherapplicantid, $generatetrainerevents, null, $organizer->id)) {
                if ($sendmessage) {
                    $receiver = core_user::get_user($memberid);
                    /* Here we have some overlap with functions organizer_send_message_from_trainer,
                     * but they don't fit exactly for our usecase. */
                    organizer_send_message($from, $receiver, $slot, 'register_promotion_student');
                }
                $generatetrainerevents = false;
            }
        }
    } else {
        if ($ok = organizer_register_single_appointment($slotid, $userid, 0, 0,
            $teacherapplicantid, true, null, $organizer->id)) {
            if ($sendmessage) {
                $receiver = core_user::get_user($userid);
                /* Here we have some overlap with functions organizer_send_message_from_trainer,
                 * but they don't fit exactly for our usecase. */
                organizer_send_message($from, $receiver, $slot, 'register_promotion_student');
            }
        }
    }

    $DB->delete_records('event', array('modulename' => 'organizer', 'eventtype' => 'Slot', 'uuid' => $slotid));

    return $ok;
}

function organizer_register_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0,
                                               $teacherapplicantid = null, $trainerevents = false, $trainerid = null, $ogranizerid = null) {
    global $DB;

    list($cm, , $organizer, ) = organizer_get_course_module_data(null, $ogranizerid);

    $appointment = new stdClass();
    $appointment->slotid = $slotid;
    $appointment->userid = $userid;
    $appointment->groupid = 0;  // Group members get single events.
    $appointment->applicantid = $applicantid ? $applicantid : $userid;
    $appointment->notified = 0;
    $appointment->attended = null;
    $appointment->grade = null;
    $appointment->feedback = '';
    $appointment->comments = '';
    $appointment->teacherapplicantid = $teacherapplicantid;
    $appointment->teacherapplicanttimemodified = strtotime("now");

    // A NEW APPOINTMENT IS BORN.
    $appointment->id = $DB->insert_record('organizer_slot_appointments', $appointment);

    $appointment->eventid = organizer_add_event_appointment($cm->id, $appointment);

    $appointment->groupid = $groupid;

    if ($trainerevents) {
        organizer_add_event_appointment_trainer($cm->id, $appointment, $trainerid);
    }

    $DB->update_record('organizer_slot_appointments', $appointment);

    if (organizer_hasqueue($organizer->id)) {
        if ($groupid) {
            $booked = organizer_count_bookedslots($organizer->id, null, $groupid);
        } else {
            $booked = organizer_count_bookedslots($organizer->id, $userid, null);
        }
        if (organizer_multiplebookings_status($booked, $organizer) == USERSLOTS_MAX_REACHED) {
            organizer_delete_user_from_any_queue($organizer->id, $userid);
        }
    }

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
            $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT) {
        organizer_groupsynchronization($slotid, $userid, 'add');
    }

    return $appointment->id;
}

function organizer_queue_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0) {
    global $DB;

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

    $appointment->id = $DB->insert_record('organizer_slot_queues', $appointment);

    return $appointment->id;
}

function organizer_reregister_appointment($slotid, $groupid = 0) {
    global $DB, $USER;

    $params = array('slotid' => $slotid);
    $query = "SELECT s.organizerid FROM {organizer_slots} s
                  WHERE s.id = :slotid ";
    $organizerid = $DB->get_field_sql($query, $params);
    $organizer = $DB->get_record('organizer', array('id' => $organizerid));

    $slot = new organizer_slot($slotid);
    if ($slot->is_full()) {
        return false;
    }

    $okregister = true;
    $okunregister = true;
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        // Events for the trainers will only be generated with the first participants registration.
        $generatetrainerevents = true;
        foreach ($memberids as $memberid) {
            if ($app = organizer_get_last_user_appointment($slot->organizerid, $memberid)) {
                $okunregister = organizer_unregister_single_appointment($app->slotid, $memberid);
            }
            $okregister = organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid,
                null, $generatetrainerevents);
            $generatetrainerevents = false;
        }
    } else {
        if ($app = organizer_get_last_user_appointment($slot->organizerid)) {
            $okunregister = organizer_unregister_single_appointment($app->slotid, $USER->id);
        }
        $okregister = organizer_register_single_appointment($slotid, $USER->id, 0, 0, null, true);
    }

    if (organizer_hasqueue($slot->organizerid)) {
         $slotx = new organizer_slot($app->slotid);
        if (organizer_is_group_mode()) {
            if ($next = $slotx->get_next_in_queue_group()) {
                $okregister = organizer_register_appointment($app->slotid, $next->groupid, 0, true);
                organizer_delete_from_queue($app->slotid, null, $next->groupid);
            }
        } else {
            if ($next = $slotx->get_next_in_queue()) {
                $okregister = organizer_register_appointment($app->slotid, 0, $next->userid, true);
                organizer_delete_from_queue($app->slotid, $next->userid);
            }
        }
    }

    if (isset($app->slotid) && (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation)) {
        $course = $DB->get_record('course', array('id' => $organizer->course), 'id', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
        if (!$participants = organizer_fetch_slotparticipants($app->slotid)) {
            $trainers = organizer_get_slot_trainers($app->slotid);
            foreach ($trainers as $trainer) {
                if ($eventid = $DB->get_field('organizer_slot_trainer', 'eventid',
                    array('slotid' => $app->slotid, 'trainerid' => $trainer))) {
                    $DB->delete_records('organizer_slot_trainer', array('slotid' => $app->slotid, 'trainerid' => $trainer));
                }
                $DB->delete_records('event', array('id' => $eventid));
                // Create slot event per trainer if instance config "empty slot events" is on.
                if (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation) {
                    $neweventid = organizer_add_event_slot($cm->id, $app->slotid, $trainer);
                    if ($record = $DB->get_record('organizer_slot_trainer', array("slotid" => $app->slotid,
                        "trainerid" => $trainer))) {
                        $record->eventid = $neweventid;
                        $DB->update_record('organizer_slot_trainer', $record);
                    } else {
                        $record = new stdClass();
                        $record->trainerid = $trainer;
                        $record->slotid = $app->slotid;
                        $record->eventid = $neweventid;
                        $DB->insert_record('organizer_slot_trainer', $record);
                    }
                }
            }
        }
    }

    return $okregister && $okunregister;
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
            if ($next = $slotx->get_next_in_queue_group()) {
                organizer_register_appointment($slotid, $next->groupid, 0, true);
                organizer_delete_from_queue($slotid, null, $next->groupid);
                $booked = organizer_count_bookedslots($organizerid, null, $next->groupid);
                if (organizer_multiplebookings_status($booked, $organizerid) == USERSLOTS_MAX_REACHED) {
                    organizer_delete_user_from_any_queue($organizerid, null, $next->groupid);
                }
            }
        } else {
            if ($next = $slotx->get_next_in_queue()) {
                organizer_register_appointment($slotid, 0, $next->userid, true);
                organizer_delete_from_queue($slotid, $next->userid);
                $booked = organizer_count_bookedslots($organizerid, $next->userid, null);
                if (organizer_multiplebookings_status($booked, $organizerid) == USERSLOTS_MAX_REACHED) {
                    organizer_delete_user_from_any_queue($organizerid, $next->userid, null);
                }
            }
        }
    }

    // If no remaining participants: Delete all trainer events of this slot.
    if (!$participants = organizer_fetch_slotparticipants($slotid)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizerid));
        $course = $DB->get_record('course', array('id' => $organizer->course), 'id', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
        $trainers = organizer_get_slot_trainers($slotid);
        foreach ($trainers as $trainer) {
            if ($record = $DB->get_record('organizer_slot_trainer', array('slotid' => $slotid, 'trainerid' => $trainer))) {
                $DB->delete_records('event', array('id' => $record->eventid));
                if (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation) {
                    $neweventid = organizer_add_event_slot($cm->id, $slotid, $trainer);
                    $record->eventid = $neweventid;
                } else {
                    $record->eventid = null;
                }
                $DB->update_record('organizer_slot_trainer', $record);
            } else {
                if (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation) {
                    $record = new stdClass();
                    $record->trainerid = $trainer;
                    $record->slotid = $slotid;
                    $DB->insert_record('organizer_slot_trainer', $record);
                }
            }
        }
    }

    return $ok;
}

function organizer_unregister_single_appointment($slotid, $userid, $organizer = null) {
    global $DB;

    if (empty($organizer)) {
        $organizer = organizer_get_organizer();
    }

    $ok = false;
    if ($appointment = $DB->get_record('organizer_slot_appointments', array('userid' => $userid, 'slotid' => $slotid))) {
        $DB->delete_records('event', array('id' => $appointment->eventid));
        $ok = $DB->delete_records('organizer_slot_appointments', array('userid' => $userid, 'slotid' => $slotid));
        if (!$apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid))) {
            $DB->delete_records('event', array('modulename' => 'organizer', 'eventtype' => 'Appointment',
                'uuid' => $appointment->id));
        } else {
            // To refresh description text of trainer's appointment events.
            foreach ($apps as $app) {
                $course = $DB->get_record('course', array('id' => $organizer->course), 'id', MUST_EXIST);
                $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
                organizer_add_event_appointment_trainer($cm->id, $app);
            }
        }
    }
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
            $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT) {
        organizer_groupsynchronization($slotid, $userid, 'remove');
    }

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

            $DB->update_record('organizer_slot_appointments', $newapp);

            organizer_update_grades($organizer, $newapp->userid);

            $slotids[] = $newapp->slotid;
        }
    }

    return $slotids;
}

function organizer_get_course_module_data($id = null, $n = null) {
    global $DB;

    $id = $id == null ? optional_param('id', 0, PARAM_INT) : $id; // Course_module ID, or.
    $n = $n == null ? optional_param('o', 0, PARAM_INT) : $n; // Organizer instance ID.

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
        $instance->cm = get_coursemodule_from_instance(
            'organizer', $instance->organizer->id, $instance->course->id,
            false, MUST_EXIST
        );
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
    return $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS;
}

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
        $group = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        return $group;
    } else {
        return false;
    }
}

function organizer_fetch_user_group($userid, $id = null) {
    global $DB;

    $cm = get_coursemodule_from_instance('organizer', $id, 0, false, MUST_EXIST);

    $params = array('groupingid' => $cm->groupingid, 'userid' => $userid);
    $query = "SELECT {groups}.id FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid
                AND {groups_members}.userid = :userid";
    $group = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
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
    g.name AS groupname,
    CASE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
    WHEN 0 THEN 1
    ELSE (SELECT COUNT(a2.slotid) FROM {organizer_slot_appointments} a2 WHERE a2.slotid = a.slotid)
    END AS rowspan,
	a.teacherapplicantid

    FROM {organizer_slots} s
    LEFT JOIN {organizer_slot_appointments} a ON a.slotid = s.id
    LEFT JOIN {user} u ON a.userid = u.id
    LEFT JOIN {groups} g ON a.groupid = g.id

    WHERE s.id $insql
    ";

    if ($orderby == " " || $orderby == "") {
        $query .= "ORDER BY s.starttime ASC, s.id,
        u.lastname ASC,
        u.firstname ASC";
    } else {
        $query .= "ORDER BY " . $orderby;
    }

    $params = array_merge($params, $inparams);
    return $DB->get_records_sql($query, $params);
}

/**
 * Waiting list
 * Checks whether an organizer instance supports waiting queues.
 *
 * @param  int $organizerid The ID of the organizer instance.
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
    if ($organizer->grade != 0) {
        return 1;
    } else {
        return 0;
    }
}

function organizer_get_teacherapplicant_output($teacherapplicantid, $teacherapplicanttimemodified=null, $printable=false) {
    global $DB;

    $output = "";

    if (is_numeric($teacherapplicantid)) {
        if (!$printable) {
            $timestampstring = $teacherapplicanttimemodified != null ? "\n" .
                    userdate($teacherapplicanttimemodified, get_string('fulldatetimetemplate', 'organizer')) : "";
            if ($trainer = $DB->get_record('user', array('id' => $teacherapplicantid), 'lastname,firstname')) {
                $output = " <span style= 'cursor:help;' title='" . get_string('slotassignedby', 'organizer') . " " .
                $trainer->firstname . " " . $trainer->lastname . $timestampstring ."'>[" . $trainer->firstname[0] .
                        $trainer->lastname[0] . "]</span>";
            }
        } else {
            if ($trainer = $DB->get_record('user', array('id' => $teacherapplicantid), 'lastname,firstname')) {
                $output = "[" . $trainer->firstname[0] . $trainer->lastname[0] . "]";
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

/*
 * Are there appointments for this slot?.
 */
function organizer_count_slotappointments($slots) {
    global $DB;

    $apps = 0;
    foreach ($slots as $slot) {
        $apps += $DB->count_records('organizer_slot_appointments', array('slotid' => $slot));
    }

    return $apps;
}

function organizer_sortout_hiddenslots($slots) {
    global $DB;

    foreach ($slots as $slot) {
        if (!$visible = $DB->get_field('organizer_slots', 'visible', array('id' => $slot))) {
            if (($key = array_search($slot, $slots)) !== false) {
                unset($slots[$key]);
            }
        }
    }

    return $slots;
}

function organizer_get_user_identity($user) {
    global $CFG, $DB;

    $identity = "";
    $identityfields = explode(',', $CFG->showuseridentity);

    if (is_object($user)) {
        $id = $user->id;
    } else {
        if (is_numeric($user)) {
            $id = $user;
        } else {
            return "";
        }
    }
    if (in_array('idnumber', $identityfields)) {
        $identity = $DB->get_field_select('user', 'idnumber', "id = {$id}");
    } else {
        if (in_array('email', $identityfields)) {
            $identity = $DB->get_field_select('user', 'email', "id = {$id}");
        }
    }

    return $identity;

}

/**
 * Call only if group creation from slots is active!
 * Add or remove trainer or participant to/from the slot group.
 * Create slot group if there is none.
 *
 * @param $slotid ID of slot
 * @param $userid participant or trainer ID
 * @param $action 'add' to group or 'remove' from group
 * @return bool default: true
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_groupsynchronization($slotid, $userid, $action) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    if ($slot->coursegroup) {
        $coursegroup = $DB->get_field('groups', 'id', array('id' => $slot->coursegroup));
    } else {
        $coursegroup = false;
    }
    // If there is no group create a new one.
    if (!$coursegroup) {
        $coursegroup = organizer_create_coursegroup($slot);
        // Present slot trainers included, makes trainer 'add' redundant.
    }
    if ($action == 'add') {
        groups_add_member($coursegroup, $userid);
    } else {
        groups_remove_member($coursegroup, $userid);
    }

    return true;

}

function organizer_create_coursegroup($slot) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid), 'name,course,includetraineringroups');
    $group = new stdClass();
    $group->courseid = $organizer->course;
    $group->name = organizer_create_coursegroupname($organizer->name, $slot->starttime, $organizer->course);
    $time = time();
    $group->timecreated = $time;
    $group->timemodified = $time;
    if ($groupid = groups_create_group($group)) {
        $DB->set_field('organizer_slots', 'coursegroup', $groupid, array('id' => $slot->id));
        if ($organizer->includetraineringroups) {
            $trainers = organizer_get_slot_trainers($slot->id);
            foreach ($trainers as $trainerid) {
                groups_add_member($groupid, $trainerid);
            }
        }
    }

    return $groupid;
}

function organizer_create_coursegroupname($name, $time, $courseid) {
    global $DB;

    $coursename = str_replace("_", "-", $name) . " ";
    $coursename .= date('Y-m-d H:i', $time);
    $params = array('coursename' => '%' . $coursename . '%', 'courseid' => $courseid);
    $query = "SELECT name FROM {groups}
              WHERE courseid = :courseid AND " . $DB->sql_like('name', ':coursename') . "
              ORDER BY name ASC";

    $records = $DB->get_records_sql($query, $params);
    $max = 0;
    foreach ($records as $id => $record) {
        $namearr = explode("__", $record->name);
        if (count($namearr) == 2) {
            if (is_number($namearr[1])) {
                if ($namearr[1] > $max) {
                    $max = $namearr[1];
                }
            }

        }
    }
    $max++;
    $coursename .= "__" . (string) $max;

    return $coursename;
}

function organizer_delete_coursegroup($groupid, $slotid=null) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $ok = false;
    if (is_number($slotid)) {
        $params = array('slotid' => $slotid);
        $query = "SELECT s.coursegroup FROM {organizer_slots} s
                  WHERE s.id = :slotid ";
        $groupid = $DB->get_field_sql($query, $params);
    }
    if (is_number($groupid)) {
        $ok = groups_delete_group($groupid);
    }
        return $ok;
}

function organizer_fetch_allslots($organizerid) {
    global $DB;

    $slots = $DB->get_records_select('organizer_slots', 'organizerid = :organizerid', array('organizerid' => $organizerid));

    return $slots;
}

function organizer_fetch_slotparticipants($slotid) {
    global $DB;

    $participants = $DB->get_fieldset_select('organizer_slot_appointments', 'userid',
        'slotid = :slotid', array('slotid' => $slotid));

    return $participants;
}

/**
 * Collection of printable fields of choice.
 * Used with organizer instance settings (mod_form) and as a sitewide default setting in organizer settings as well.
 *
 * @param bool $nochoiceoption ... whether there should be a no-choice option at the top of the list, default false
 * @return array ... all the choosable print options
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_printslotuserfields($nochoiceoption=false) {
    global $CFG;

    require_once($CFG->dirroot . '/user/profile/lib.php');

    $profilefields['lastname'] = organizer_filter_text(get_string('lastname'));
    $profilefields['firstname'] = organizer_filter_text(get_string('firstname'));
    $profilefields['email'] = organizer_filter_text(get_string('email'));
    $profilefields['idnumber'] = organizer_filter_text(get_string('idnumber'));
    $profilefields['attended'] = organizer_filter_text(get_string('attended', 'organizer'));
    $profilefields['grade'] = organizer_filter_text(get_string('grade', 'grades'));
    $profilefields['feedback'] = organizer_filter_text(get_string('feedback'));
    $profilefields['signature'] = organizer_filter_text(get_string('signature', 'organizer'));
    $profilefields['fullnameuser'] = organizer_filter_text(get_string('fullnameuser', 'moodle'));
    $profilefields['icq'] = organizer_filter_text(get_string('icqnumber', 'profilefield_social'));
    $profilefields['skype'] = organizer_filter_text(get_string('skypeid', 'profilefield_social'));
    $profilefields['yahoo'] = organizer_filter_text(get_string('yahooid', 'profilefield_social'));
    $profilefields['aim'] = organizer_filter_text(get_string('aimid', 'profilefield_social'));
    $profilefields['msn'] = organizer_filter_text(get_string('msnid', 'profilefield_social'));
    $profilefields['phone1'] = organizer_filter_text(get_string('phone1', 'moodle'));
    $profilefields['phone2'] = organizer_filter_text(get_string('phone2', 'moodle'));
    $profilefields['institution'] = organizer_filter_text(get_string('institution', 'moodle'));
    $profilefields['department'] = organizer_filter_text(get_string('department', 'moodle'));
    $profilefields['address'] = organizer_filter_text(get_string('address', 'moodle'));
    $profilefields['city'] = organizer_filter_text(get_string('city', 'moodle'));
    $profilefields['country'] = organizer_filter_text(get_string('country', 'moodle'));
    $profilefields['lang'] = organizer_filter_text(get_string('language', 'moodle'));
    $profilefields['timezone'] = organizer_filter_text(get_string('timezone', 'moodle'));
    $profilefields['description'] = organizer_filter_text(get_string('userdescription', 'moodle'));
    foreach (profile_get_custom_fields() as $customfield) {
        $profilefields[$customfield->id] = organizer_filter_text($customfield->name);
    }

    return $profilefields;
}


function organizer_get_allowed_printslotuserfields() {
    $selectableprofilefields = organizer_printslotuserfields();
    $selectedprofilefields = array();

    $organizerconfig = get_config('organizer');
    if (isset($organizerconfig->allowedprofilefieldsprint)) {
        $selectedprofilefields = array('' => '--');
        if ($allowedprofilefieldsprint = explode(",", $organizerconfig->allowedprofilefieldsprint)) {
            foreach ($selectableprofilefields as $key => $value) {
                if (in_array($key, $allowedprofilefieldsprint)) {
                    $selectedprofilefields[$key] = $value;
                }
            }
        }
    } else {
        $selectedprofilefields[''] = '--';
        $selectedprofilefields['lastname'] = get_string('lastname');
        $selectedprofilefields['firstname'] = get_string('firstname');
        $selectedprofilefields['email'] = get_string('email');
        $selectedprofilefields['idnumber'] = get_string('idnumber');
        $selectedprofilefields['attended'] = get_string('attended', 'organizer');
        $selectedprofilefields['grade'] = get_string('grade', 'grades');
        $selectedprofilefields['feedback'] = get_string('feedback');
        $selectedprofilefields['signature'] = get_string('signature', 'organizer');
    }
    return $selectedprofilefields;
}

function organizer_fetch_printdetail_entries($slot) {
    global $DB;

    $params = ['slotid' => $slot];

    $socialfields = ['icq', 'skype', 'yahoo', 'msn', 'aim'];
    $socialfields = $DB->get_records_list('user_info_field', 'shortname', $socialfields);
    $socialselect = '';
    $socialjoin = '';
    foreach ($socialfields as $socialfield) {
        $tablename = 'u_' . $socialfield->shortname;
        $fieldname = $socialfield->shortname;
        $paramname = 'u_' . $fieldname . '_fieldid';
        $socialselect .= "$tablename.data AS $fieldname, ";
        $socialjoin .= "LEFT JOIN {user_info_data} $tablename ON $tablename.userid = a.userid AND $tablename.fieldid = :$paramname";
        $params[$paramname] = $socialfield->id;
    }

    $query = "SELECT u.id,
                    u.firstname,
                    u.lastname,
                    u.email,
                    u.idnumber,
                    a.attended,
                    a.grade,
                    a.feedback,
                    g.name AS groupname,
                    $socialselect
                    u.phone1,
                    u.phone2,
                    u.institution,
                    u.department,
                    u.address,
                    u.city,
                    u.country,
                    u.lang,
                    u.timezone,
                    u.description
                    FROM {organizer_slots} s
                    LEFT JOIN {organizer_slot_appointments} a ON a.slotid = s.id
                    LEFT JOIN {user} u ON a.userid = u.id
                    $socialjoin
                    LEFT JOIN {groups} g ON a.groupid = g.id
                    WHERE s.id = :slotid
                    ORDER BY lastname, firstname
                  ";
    return $DB->get_records_sql($query, $params);
}

function organizer_filter_text($text) {
    global $PAGE;

    $context = $PAGE->context;
    $filtermanager = filter_manager::instance();
    $filtermanager->setup_page_for_filters($PAGE, $context); // Setup global stuff filters may have.
    $text = $filtermanager->filter_string($text, $context);

    return $text;
}

function organizer_get_users_of_slot($slotid) {
    global $DB;

    $usersofslot = "";
    $con = "";
    $users = $DB->get_fieldset_select('organizer_slot_appointments', 'userid', "slotid = :slotid",
        array('slotid' => $slotid));
    foreach ($users as $userid) {
        $usersofslot .= $con . organizer_get_name_link($userid);
        $con = ", ";
    }

    return $usersofslot;
}

/**
 * Generate object with strings used for an appointment event.
 *
 * @param object $course
 * @param object $organizer
 * @param object $cm
 * @param object $slot
 * @return stdClass
 * @throws moodle_exception
 */
function organizer_add_event_appointment_strings($course, $organizer, $cm, $slot) {

    $a = new stdClass();
    $a->coursename = organizer_filter_text($course->fullname);
    $a->courselink = html_writer::link(new moodle_url("/course/view.php?id={$course->id}"), $course->fullname);
    $a->organizername = organizer_filter_text($organizer->name);
    $a->organizerlink = html_writer::link(new moodle_url("/mod/organizer/view.php?id={$cm->id}"), $organizer->name);
    $a->description = $slot->comments;
    if ($slot->locationlink) {
        $a->location = html_writer::link($slot->locationlink, $slot->location);
    } else {
        $a->location = $slot->location;
    }

    return $a;
}

/**
 * Create or change an appointment event per trainer.
 *
 * @param int $trainerid
 * @param object $course
 * @param object $cm
 * @param object $organizer
 * @param object $appointment
 * @param object $slot
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_change_calendarevent_trainer($trainerid, $course, $cm, $organizer, $appointment, $slot) {
    global $DB;

    $stringman = get_string_manager();

    $a = organizer_add_event_appointment_strings($course, $organizer, $cm, $slot);
    // Use the trainer's language.
    $trainerlang = $DB->get_field('user', 'lang', array('id' => $trainerid));
    // Calendar events for trainers info fields.
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $a->appwith = $stringman->get_string('eventappwith:group', 'organizer', null, $trainerlang);
        $a->with = $stringman->get_string('eventwith', 'organizer', null, $trainerlang);
        $group = groups_get_group($appointment->groupid);
        $groupid = $group->id;
        $users = groups_get_members($groupid);
        $memberlist = "";
        foreach ($users as $user) {
            $memberlist .= organizer_get_name_link($user->id) . ", ";
        }
        $memberlist = trim($memberlist, ", ");
        $memberlist .= " {$group->name} ";
        $a->participants = $memberlist;
    } else {
        $a->appwith = $stringman->get_string('eventappwith:single', 'organizer', null, $trainerlang);
        $a->with = $stringman->get_string('eventwith', 'organizer', null, $trainerlang);
        $a->participants = organizer_get_users_of_slot($slot->id);
    }
    $eventtitle = $stringman->get_string('eventtitle', 'organizer', $a, $trainerlang);
    $eventdescription = $stringman->get_string('eventtemplatewithoutlinks', 'organizer', $a, $trainerlang);

    // Create or transform to appointment events for the slot for this trainer.
    $params = array ('slotid' => $slot->id, 'trainerid' => $trainerid);
    $query = "SELECT e.id FROM {event} e
                  INNER JOIN {organizer_slot_trainer} t ON e.id = t.eventid
                  WHERE t.slotid = :slotid AND t.trainerid = :trainerid";
    // Create new appointment event or update existent appointment event for trainers.
    if (!$teventid = $DB->get_field_sql($query, $params)) {
        $teventid = organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT,
            $trainerid, $slot->starttime, $slot->duration, 0, $appointment->id
        );
        $DB->set_field('organizer_slot_trainer', 'eventid', $teventid, array ('slotid' => $slot->id, 'trainerid' => $trainerid));
    } else {
        organizer_change_calendarevent(
            array($teventid), $organizer, $eventtitle, $eventdescription,
            ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT, $trainerid, $slot->starttime, $slot->duration, 0, $appointment->id
        );
    }
}

/**
 * Define format and generate table for printing.
 *
 * @param object $mpdftable
 * @param string $filename
 * @throws coding_exception
 */
function organizer_format_and_print($mpdftable, $filename) {

    $format = optional_param('format', 'pdf', PARAM_TEXT);

    switch($format) {
        case 'xlsx':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_XLSX);
            break;
        case 'xls':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_XLS);
            break;
        case 'ods':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_ODS);
            break;
        case 'csv_comma':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_CSV_COMMA);
            break;
        case 'csv_tab':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_CSV_TAB);
            break;
        default:
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_PDF);
            break;
    }

    $mpdftable->generate($filename);
    die();
}

/**
 * Sets userprefs for this user according to the form values transmitted and triggers a print event.
 *
 * @param object $data
 * @param object $cm
 * @param object $context
 * @return bool
 * @throws coding_exception
 * @throws dml_exception
 * @throws required_capability_exception
 */
function organizer_print_setuserprefs_and_triggerevent($data, $cm, $context) {
    global $DB, $PAGE;

    set_user_preference('organizer_printperpage', $data->entriesperpage);
    set_user_preference('organizer_printperpage_optimal', $data->printperpage_optimal);
    set_user_preference('organizer_textsize', $data->textsize);
    set_user_preference('organizer_pageorientation', $data->pageorientation);
    set_user_preference('organizer_headerfooter', $data->headerfooter);

    if ($data->printperpage_optimal == 1) {
        $ppp = false;
    } else {
        $ppp = $data->entriesperpage;
    }

    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    require_capability('mod/organizer:printslots', $context);

    $event = \mod_organizer\event\appointment_list_printed::create(
        array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
        )
    );
    $event->trigger();

    return $ppp;
}

/**
 * Returns the entries for the registration view.
 *
 * @param $groupmode ... whether organizer instance is group mode or single mode
 * @param $params
 * @return array|moodle_recordset  ... the entries
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_get_registrationview_entries($groupmode, $params) {
    if ($groupmode) {
        $entries = organizer_organizer_organizer_get_status_table_entries_group($params);
    } else {
        $entries = organizer_organizer_get_status_table_entries($params);
    }
    return $entries;
}

/**
 * Obtains slots parameters if present
 *
 * @return array slots
 */
function organizer_get_param_slots() {
    $slots = [];
    if (isset($_REQUEST['slots'])) { // Dirty, dirty hack...
        if (is_array($_REQUEST['slots'])) {
            $slots = optional_param_array('slots', [], PARAM_INT);
        } else {
            $slots = optional_param('slots', '', PARAM_SEQUENCE);
            $slots = explode(',', $slots);
        }
    }
    return $slots;
}

/**
 * How many slots has a participant booked
 *
 * @param int $organizerid  ID of organizer instance
 * @param int $userid ID of user
 * @param int $groupid ID of group (if instance is groupmode)
 *
 * @return int $slots
 */
function organizer_count_bookedslots($organizerid, $userid = null, $groupid = null) {
    global $DB, $USER;

    if ($userid == null && $groupid == null) {
        $userid = $USER->id;
    }
    if ($userid) {
        $paramssql = array('userid' => $userid, 'organizerid' => $organizerid);
        $query = "SELECT count(*) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid";
    } else {
        $paramssql = array('groupid' => $groupid, 'organizerid' => $organizerid);
        $query = "SELECT count(DISTINCT s.id) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.groupid = :groupid";
    }
    $slots = $DB->count_records_sql($query, $paramssql);

    return $slots;
}

/**
 * What is the multiple slots bookings status for the given amount of user bookings in this organizer instance
 *
 * @param int $slotsbooked amount of booked slots of a user
 * @param object $organizer this organizer instance
 *
 * @return int $status 0 for min not reached, 1 for min reached, 2 for max_reached
 */
function organizer_multiplebookings_status($slotsbooked, $organizer) {
    global $DB;

    if (is_number($organizer)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizer), 'userslotsmin, userslotsmax', MUST_EXIST);
    }

    if ($slotsbooked >= $organizer->userslotsmax) {
        $status = USERSLOTS_MAX_REACHED;
    } else if ($slotsbooked >= $organizer->userslotsmin) {
        $status = USERSLOTS_MIN_REACHED;
    } else {
        $status = USERSLOTS_MIN_NOT_REACHED;
    }
    return $status;
}

/**
 * How many slots has a participant left
 *
 * @param object $organizer  organizer instance
 * @param int $userid ID of user
 * @param int $groupid ID of group (if instance is groupmode)
 *
 * @return int $slotsleft
 */
function organizer_multiplebookings_slotslefttobook($organizer, $userid = null, $groupid = null) {
    global $DB, $USER;

    if ($userid == null && $groupid == null) {
        $userid = $USER->id;
    }

    if ($userid) {
        $paramssql = array('userid' => $userid, 'organizerid' => $organizer->id);
        $query = "SELECT count(*) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid";
    } else {
        $paramssql = array('groupid' => $groupid, 'organizerid' => $organizer->id);
        $query = "SELECT count(DISTINCT s.id) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.groupid = :groupid";
    }
    $bookedslots = $DB->count_records_sql($query, $paramssql);

    $slotsleft = $organizer->userslotsmax - $bookedslots;

    return $slotsleft;
}

/**
 * Returns true if at least one user booking exists in this organizer instance
 *
 * @param int $organizerid ID of organizer instance
 *
 * @return bool $exist
 */
function organizer_bookings_exist($organizerid) {
    global $DB;

    $paramssql = array('organizerid' => $organizerid);
    $query = "select a.id from {organizer_slot_appointments} a
        inner join {organizer_slots} s on a.slotid = s.id
        where s.organizerid = :organizerid";
    $exist = $DB->record_exists_sql($query, $paramssql);

    return $exist;
}

/**
 * Returns amount of course participants who have not booked the minimum of slots yet.
 *
 * @param object $organizer organizer instance
 * @param boolean $groupmode is organizer instance in groupmode
 * @param objects $entries of registration view
 *
 * @return array $entries, $underminimum: participants booked under minimum, $maxreached: participants
 * who have reached the max
 */
function organizer_multiplebookings_statistics($organizer, $groupmode, $entries) {
    $countentries = 0;
    $underminimum = 0;
    $maxreached = 0;
    $entriesdone = array();
    foreach ($entries as $entry) {
        if (!in_array($entry->id, $entriesdone)) { // No duplicates!
            $countentries++;
            if ($groupmode) {
                $booked = organizer_count_bookedslots($organizer->id, null, $entry->id);
            } else {
                $booked = organizer_count_bookedslots($organizer->id, $entry->id, null);
            }
            $status = organizer_multiplebookings_status($booked, $organizer);
            if ($status == USERSLOTS_MIN_NOT_REACHED) {
                $underminimum++;
            } else if ($status == USERSLOTS_MAX_REACHED) {
                $maxreached++;
            }
            $entriesdone[] = $entry->id;
        }
    }

    return [$countentries, $underminimum, $maxreached];
}

/**
 * Returns amount of course participants who have not booked the minimum of slots yet.
 *
 * @param object $organizer organizer instance
 * @param object $cm course module data of instance
 *
 * @return object $allparticipants, $participantsreachedminimum, $attendedparticipants
 */
function organizer_get_counters($organizer, $cm = null) {
    global $DB;

    if (!$cm) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
    }
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $params = array('groupingid' => $cm->groupingid);
        $query = 'SELECT {groups}.* FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid
                ORDER BY {groups}.name ASC';
        $groups = $DB->get_records_sql($query, $params);
        $attended = 0;
        $registered = 0;
        foreach ($groups as $group) {
            $apps = organizer_get_all_group_appointments($organizer, $group->id);
            if (organizer_multiplebookings_status(count($apps), $organizer) != USERSLOTS_MIN_NOT_REACHED) {
                $registered ++;
            }
            foreach ($apps as $app) {
                if ($app->attended == 1) {
                    $attended++;
                    break;
                }
            }
        }
        $total = count($groups);
        $a = new stdClass();
        $a->registered = $registered;
        $a->attended = $attended;
        $a->total = $total;
    } else {
        $context = context_module::instance($cm->id, MUST_EXIST);
        $participants = get_enrolled_users($context, 'mod/organizer:register');
        $attended = 0;
        $registered = 0;
        foreach ($participants as $participant) {
            $apps = organizer_get_all_user_appointments($organizer, $participant->id);
            if (organizer_multiplebookings_status(count($apps), $organizer) != USERSLOTS_MIN_NOT_REACHED) {
                $registered ++;
            }
            foreach ($apps as $app) {
                if ($app->attended == 1) {
                    $attended++;
                    break;
                }
            }
        }
        $total = count($participants);
        $a = new stdClass();
        $a->registered = $registered;
        $a->attended = $attended;
        $a->total = $total;
    }

    return $a;
}
