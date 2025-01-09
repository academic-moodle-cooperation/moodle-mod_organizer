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
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_availability\info_module;
use mod_organizer\event\appointment_list_printed;
use mod_organizer\MTablePDF;

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
    $params = ['trainerid' => $trainerid, 'modulename' => 'organizer', 'startdate1' => $startdate,
        'enddate1' => $enddate, 'startdate2' => $startdate, 'enddate2' => $enddate, 'trainerid2' => $trainerid,
        'newslotid' => $newslotid, 'startdate3' => $startdate, 'enddate3' => $enddate, 'startdate4' => $startdate,
        'enddate4' => $enddate];
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
 * Get link to the (actual) user
 * @param number $user
 * @return boolean|string
 */
function organizer_get_name_link($user = 0) {
    global $DB, $USER, $COURSE;
    if (!$user) {
        $user = $USER;
    } else if (is_number($user)) {
        $user = $DB->get_record('user', ['id' => $user]);
    } else if (!($user instanceof stdClass)) {
        return false;
    }

    $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $COURSE->id]);
    $name = get_string('fullname_template', 'organizer', $user);
    return html_writer::link($profileurl, $name);
}

/**
 * Gets a user's profile picture along with their name linked to their profile.
 *
 * @param int|stdClass $user Optional. A user ID or a user object. Defaults to the currently logged-in user.
 * @return false|string HTML link to the user's profile picture and name, or false if the user is invalid.
 */
function organizer_get_userpicture_link($user = 0) {
    global $DB, $USER, $COURSE;
    if (!$user) {
        $user = $USER;
    } else if (is_number($user)) {
        $user = $DB->get_record('user', ['id' => $user]);
    } else if (!($user instanceof stdClass)) {
        return false;
    }

    $initials = mb_strtoupper(
        mb_substr($user->firstname, 0, 1, 'UTF-8') .
        mb_substr($user->lastname, 0, 1, 'UTF-8'),
        'UTF-8');
    $profileurl = new moodle_url('/user/view.php', ['id' => $user->id, 'course' => $COURSE->id]);
    $name = get_string('fullname_template', 'organizer', $user);
    $spaninit = html_writer::span($initials, 'userinitials size-35');
    $spantext = html_writer::span($name, 'userinitialstext');
    return html_writer::link($profileurl, $spaninit.$spantext, ['title' => $name, 'class' => 'd-inline-block aabtn']);
}

/**
 * Checks if the given events are in the given time frame.
 *
 * @param  number    $from
 * @param  number to
 * @return array an array of events
 */
function organizer_check_collision($from, $to, $eventsandslots) {
    $collidingevents = [];
    foreach ($eventsandslots as $event) {
        $eventfrom = $event->timestart;
        $eventto = $eventfrom + $event->timeduration;

        if (organizer_between($from, $eventfrom, $eventto) || organizer_between($to, $eventfrom, $eventto)
            || organizer_between($eventfrom, $from, $to) || organizer_between($eventto, $from, $to)
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
function organizer_between($num, $lower, $upper) {
    return $num > $lower && $num < $upper;
}

/**
 *
 * @param array $data
 * @return array|number[]|string[]|NULL[][]
 */
function organizer_add_new_slots($data) {
    global $DB;

    $count = [];

    $cm = get_coursemodule_from_id('organizer', $data->id);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance]);
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
                throw new coding_exception('Duration is invalid (not set or < 1). No slots will be added.');
            }

            if (!isset($data->gap) || $data->gap < 0) {
                throw new coding_exception('Gap is invalid (not set or < 0). No slots will be added. Contact support!');
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
                    $eventids = [];
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
                                    ' ' . get_string('fromdate') . ': ' . userdate($collision->timestart,
                                        get_string('fulldatetimetemplate', 'organizer')) .
                                    ' ' . get_string('todate') . ': ' . userdate($collision->timestart + $collision->timeduration,
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

    return [$count, $slotsnotcreatedduetodeadline, $slotsnotcreatedduetopasttime, $collisionmessages];
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

/**
 * Adds a calendar event for a specific organizer slot.
 *
 * This function either creates or updates a calendar event in Moodle for a given
 * organizer slot and optionally associates it with a specific user or updates an existing
 * event by its event ID.
 *
 * @param int $cmid The course module ID of the organizer.
 * @param stdClass|int $slot The organizer slot object or its ID.
 * @param int|null $userid The ID of the user associated with the event (optional).
 * @param int|null $eventid The ID of an existing event to update (optional).
 * @return bool True if the calendar event was successfully added or updated; false otherwise.
 */
function organizer_add_event_slot($cmid, $slot, $userid = null, $eventid = null) {
    global $DB;

    if (is_number($slot)) {
        $slot = $DB->get_record('organizer_slots', ['id' => $slot]);
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', ['id' => $cm->course]);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance]);

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
        $apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slot->id]);
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
        $apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slot->id]);
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
            [$eventid], $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_SLOT,
            $userid, $slot->starttime, $slot->duration, 0, $slot->id
        );
    } else {
        return organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_SLOT,
                $userid, $slot->starttime, $slot->duration, 0, $slot->id
        );
    }
}

/**
 * Adds an appointment event to the calendar.
 *
 * This function creates or updates calendar events for appointments in an organizer activity.
 *
 * @param int $cmid The course module ID for the organizer activity.
 * @param stdClass|int $appointment The appointment object or its ID.
 * @return void
 * @throws dml_exception If a database error occurs.
 */
function organizer_add_event_appointment($cmid, $appointment) {
    global $DB;

    if (is_number($appointment)) {
        $appointment = $DB->get_record('organizer_slot_appointments', ['id' => $appointment]);
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', ['id' => $cm->course]);
    $slot = $DB->get_record('organizer_slots', ['id' => $appointment->slotid]);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance]);

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
            [$appointment->eventid], $organizer, $eventtitle, $eventdescription,
            ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT, $appointment->userid, $slot->starttime,
                $slot->duration, $groupid, $appointment->id
        );
    }

    return $eventid;

}

/**
 * Adds or updates a calendar event for an appointment trainer.
 *
 * This function either creates or updates calendar events for
 * a trainer associated with an appointment. If no specific trainer
 * is provided, the function processes all trainers associated with
 * the appointment's time slot.
 *
 * @param int $cmid The course module ID of the organizer.
 * @param int|stdClass $appointment The appointment ID or appointment record.
 * @param int|null $trainerid Optional trainer ID. If not provided, all trainers
 *     associated with the slot will be processed.
 * @return bool True if the operation is successful.
 */
function organizer_add_event_appointment_trainer($cmid, $appointment, $trainerid = null) {
    global $DB;

    if (is_number($appointment)) {
        $appointment = $DB->get_record('organizer_slot_appointments', ['id' => $appointment]);
    }

    $cm = get_coursemodule_from_id('organizer', $cmid);
    $course = $DB->get_record('course', ['id' => $cm->course]);
    $slot = $DB->get_record('organizer_slots', ['id' => $appointment->slotid]);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance]);

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

/**
 * Updates the comments of an appointment in the database.
 *
 * This function retrieves the appointment record by its ID and updates the
 * associated comments field. If no comments are provided, the comments field
 * is set to an empty string.
 *
 * @param int $appid The ID of the appointment to be updated.
 * @param string|null $comments The new comments for the appointment. If null, the comments will be cleared.
 * @return bool True if the update was successful, false otherwise.
 */
function organizer_update_comments($appid, $comments) {
    global $DB;

    $appointment = $DB->get_record('organizer_slot_appointments', ['id' => $appid]);

    if (isset($comments)) {
        $appointment->comments = $comments;
    } else {
        $appointment->comments = '';
    }
    return $DB->update_record('organizer_slot_appointments', $appointment);
}

/**
 * Updates various properties of a slot, handling changes to trainers, visibility,
 * location, and other settings, as well as ensuring that max participants are
 * correctly handled, especially in the presence of a queue.
 *
 * This function modifies the slot based on the provided data, including updating
 * fields such as location, visibility, teacher's availability, and notification time.
 * When the maximum number of participants is updated, it ensures that the change doesn't
 * violate existing bookings and manages participants in the queue accordingly.
 *
 * @param stdClass $data The data object containing slot properties and modifications.
 * @return void
 */
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
            $appcount = organizer_count_slotappointments([$slotid]);
            $maxparticipants = $DB->get_field('organizer_slots', 'maxparticipants', ['id' => $slotid]);
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
                    if (empty($deletions)) {
                        $deletions = [0];
                    }
                    [$insql, $inparams] = $DB->get_in_or_equal($deletions, SQL_PARAMS_NAMED);
                    $eventids = $DB->get_fieldset_select(
                            'organizer_slot_trainer', 'eventid', 'slotid = ' . $slot->id . ' AND trainerid ' . $insql, $inparams
                    );
                    foreach ($eventids as $eventid) {
                        $DB->delete_records('event', ['id' => $eventid]);
                    }
                    $DB->delete_records_select(
                            'organizer_slot_trainer', 'slotid = ' . $slot->id . ' AND trainerid ' . $insql, $inparams
                    );
                    if ($organizer->includetraineringroups &&
                        ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
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
                        if ($apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slotid])) {
                            [$cm, , , , ] = organizer_get_course_module_data();
                            foreach ($apps as $app) {
                                organizer_add_event_appointment_trainer($cm->id, $app, $trainerid);
                            }
                        }
                        if ($organizer->includetraineringroups &&
                            ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING ||
                                $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT)) {
                            organizer_groupsynchronization($slot->id, $trainerid, 'add');
                        }
                    }
                }
            }
            if ($apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slotid])) {
                // Add or update appointment events for participants.
                foreach ($apps as $app) {
                    organizer_add_event_appointment($data->id, $app);
                }
            } else {
                // If empty slots events are activated create them now for this slot.
                $updatedslot = $DB->get_record('organizer_slots', ['id' => $slotid]);
                if (!$nocalendareventslotcreation = $DB->get_field('organizer', 'nocalendareventslotcreation',
                    ['id' => $updatedslot->organizerid])) {
                    $trainers = $DB->get_records('organizer_slot_trainer', ['slotid' => $slotid]);
                    foreach ($trainers as $trainer) {
                        organizer_add_event_slot($data->id, $updatedslot, $trainer->trainerid, $trainer->eventid);
                    }
                }
            }
        }
    }

    return $data->slots;
}

/**
 * Deletes an appointment slot from the organizer.
 *
 * @param int $id The ID of the slot to be deleted.
 * @return int|false The number of users notified about the deletion, or false if the slot does not exist.
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_delete_appointment_slot($id) {
    global $DB, $USER;

    if (!$DB->get_record('organizer_slots', ['id' => $id])) {
        return false;
    }

    // If student is registered to this slot, send a message.
    $appointments = $DB->get_records('organizer_slot_appointments', ['slotid' => $id]);
    $notifiedusers = 0;
    if (count($appointments) > 0) {
        $slotx = new organizer_slot($id);
        foreach ($appointments as $appointment) {
            $receiver = intval($appointment->userid);
            // App delete when slot delete: Send notification to participant.
            organizer_send_message($USER, $receiver, $slotx, 'slotdeleted_notify_student', null, null, true);
            $DB->delete_records('event', ['id' => $appointment->eventid]);
            $notifiedusers++;
        }
    }

    $trainers = organizer_get_slot_trainers($id);
    foreach ($trainers as $trainerid) {
        $slottrainer = $DB->get_record('organizer_slot_trainer', ['slotid' => $id, 'trainerid' => $trainerid]);
        $DB->delete_records('event', ['id' => $slottrainer->eventid]);
        $DB->delete_records('organizer_slot_trainer', ['id' => $slottrainer->id]);
    }
    $DB->delete_records('organizer_slot_appointments', ['slotid' => $id]);
    $DB->delete_records('organizer_slots', ['id' => $id]);

    return $notifiedusers;
}

/**
 * Deletes a specific user's appointment.
 *
 * If a group ID is provided, all queue entries for the group in the specified slot
 * will be deleted. If no group ID is provided, the specific user's slot queue entry
 * will be deleted.
 *
 * @param $id
 * @return bool True if the deletion was successful, false otherwise.
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_delete_appointment($id, $withnotification = true) {
    global $DB, $USER;

    if (!$appointment = $DB->get_record('organizer_slot_appointments', ['id' => $id])) {
        return false;
    }

    $slot = $DB->get_record('organizer_slots', ['id' => $appointment->slotid]);
    if ($withnotification) {
        // Send a message to the participant.
        $receiver = intval($appointment->userid);
        organizer_send_message($USER, $receiver, $slot, 'appointmentdeleted_notify_student');
    }
    if (ORGANIZER_DELETE_EVENTS) {
        $DB->delete_records('event', ['id' => $appointment->eventid]);
        $DB->delete_records('event', ['uuid' => $appointment->id,
            'modulename' => 'organizer', 'instance' => $slot->organizerid,
            'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT]);
    }
    $DB->delete_records('organizer_slot_appointments', ['id' => $id]);

    return true;
}

/**
 * Deletes all appointments of the group members from a slot from an organizer.
 *
 * @param $slotid
 * @param int|null $groupid The ID of the group, or null for individual users.
 * @return bool True if successful, false if no records were found.
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_delete_appointment_group($slotid, $groupid) {
    global $DB, $USER;

    $slot = $DB->get_record('organizer_slots', ['id' => $slotid]);

    if (!$appointments = $DB->get_records('organizer_slot_appointments',
        ['slotid' => $slotid, 'groupid' => $groupid])) {
        return false;
    }

    foreach ($appointments as $appointment) {
        // Send a message to the participant.
        $receiver = intval($appointment->userid);
        // App delete group: Send notification to participant.
        organizer_send_message($USER, $receiver, $slot, 'appointmentdeleted_notify_student', null, null, true);
        $DB->delete_records('event', ['id' => $appointment->eventid]);
        $DB->delete_records('organizer_slot_appointments', ['id' => $appointment->id]);
    }

    return true;
}

/**
 * Deletes a user or group's queue entries for a specific slot in an organizer.
 *
 * @param int $slotid The ID of the slot.
 * @param int $userid The ID of the user whose queue entry is to be deleted.
 * @param null $groupid Optional. The ID of the group whose queue entries are to be deleted.
 *                          Defaults to null for individual users.
 * @return bool True if the operation was successful, false otherwise.
 * @throws dml_exception
 */
function organizer_delete_from_queue($slotid, $userid, $groupid = null) {
    global $DB;

    if ($groupid) {
        $queueentries = $DB->get_records('organizer_slot_queues', ['slotid' => $slotid, 'groupid' => $groupid]);
        foreach ($queueentries as $entry) {
            $DB->delete_records('event', ['id' => $entry->eventid]);
            $DB->delete_records('organizer_slot_queues', ['id' => $entry->id]);
        }
    } else {
        if (!$queueentry = $DB->get_record('organizer_slot_queues', ['slotid' => $slotid, 'userid' => $userid])) {
            return false;
        } else {
            $DB->delete_records('event', ['id' => $queueentry->eventid]);
            $DB->delete_records('organizer_slot_queues', ['slotid' => $slotid, 'userid' => $userid]);
        }
    }

    return true;
}

/**
 * Deletes a user from any queue in the organizer.
 *
 * @param int $organizerid The ID of the organizer.
 * @param int $userid The ID of the user to be removed from the queue.
 * @param int|null $groupid Optional. The ID of the group to be removed from the queue. Defaults to null for individual users.
 * @return bool True if the operation was successful, false otherwise.
 */
function organizer_delete_user_from_any_queue($organizerid, $userid, $groupid = null) {
    global $DB;

    if ($groupid) {
        $params = ['organizerid' => $organizerid, 'groupid' => $groupid];
        $slotquery = 'SELECT q.id, q.eventid
					  FROM {organizer_slots} s
					  INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
					  WHERE s.organizerid = :organizerid AND q.groupid = :groupid';
    } else {
        $params = ['organizerid' => $organizerid, 'userid' => $userid];
        $slotquery = 'SELECT q.id, q.eventid
					  FROM {organizer_slots} s
					  INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
					  WHERE s.organizerid = :organizerid AND q.userid = :userid';
    }

    $slotqueues = $DB->get_records_sql($slotquery, $params);

    foreach ($slotqueues as $slotqueue) {
        $DB->delete_records('event', ['id' => $slotqueue->eventid]);
        $DB->delete_records('organizer_slot_queues', ['id' => $slotqueue->id]);
    }

    return true;
}

/**
 * Adds a user (or a group) to the queue for a particular organizer slot.
 *
 * If the organizer is configured for groups, all members of the group are added to the queue.
 * Otherwise, only the specified user is added. This operation is only allowed if the organizer
 * has queuing enabled.
 *
 * @param organizer_slot $slotobj The slot object representing the organizer slot.
 * @param int $groupid Optional. The ID of the group to add to the queue. Defaults to 0 for individuals.
 * @param int $userid Optional. The ID of the user to add to the queue. Defaults to the current user if not provided.
 * @return bool True if the operation was successful, false otherwise.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_add_to_queue(organizer_slot $slotobj, $groupid = 0, $userid = 0) {
    global $USER;

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
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);
        $members = get_enrolled_users($context, 'mod/organizer:register', $groupid, 'u.id', null, 0, 0, true);

        foreach ($members as $member) {
            $ok = organizer_queue_single_appointment($slotid, $member->id, $userid, $groupid);
        }
    } else {
        $ok = organizer_queue_single_appointment($slotid, $userid);
    }

    return $ok;
}

/**
 * Registers an appointment for a specific slot in the organizer module.
 *
 * This function allows either a single user or a group to be registered for an appointment
 * in the specified slot. If the slot is already full, the user or group is added to the queue
 * (if queuing is enabled). Notifications can be sent to the users involved and/or trainers
 * based on the function's parameters.
 *
 * @param int $slotid The ID of the organizer slot to register the appointment for.
 * @param int $groupid Optional. The ID of the group to register. Defaults to 0 for individuals.
 * @param int $userid Optional. The ID of the user to register. Defaults to the current user if not provided.
 * @param bool $sendmessage Optional. Whether to send a message to the participants or trainers. Defaults to false.
 * @param null $teacherapplicantid Optional. The ID of the teacher who applies for the registration. Defaults to null.
 * @param bool $slotnotfull Optional. Indicates whether the function should bypass the "full slot" check. Defaults to false.
 * @return bool True if the registration or queuing operation was successful, false otherwise.
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_register_appointment($slotid, $groupid = 0, $userid = 0, $sendmessage = false,
                                        $teacherapplicantid = null, $slotnotfull = false) {
    global $DB, $USER;

    if (!$userid) {
        $userid = $USER->id;
    }
    $slotx = new organizer_slot($slotid);
    if (!$slotnotfull) {
        if ($slotx->is_full()) {
            return organizer_add_to_queue($slotx, $groupid, $userid);
        }
    }

    if ($sendmessage) {
        $trainers = organizer_get_slot_trainers($slotx->get_id());
        if ($slotx->get_slot()->teachervisible && !empty($trainers)) {
            $trainerid = reset($trainers);
            $from = core_user::get_user($trainerid);
        } else {
            $from = core_user::get_noreply_user();
        }
    }

    $organizer = $slotx->get_organizer();
    $ok = true;
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);
        $members = get_enrolled_users($context, 'mod/organizer:register', $groupid, 'u.id', null, 0, 0, true);
        $generatetrainerevents = true;
        foreach ($members as $member) {
            if ($ok = organizer_register_single_appointment($slotid, $member->id, $USER->id, $groupid,
                $teacherapplicantid, $generatetrainerevents, null, $organizer->id)) {
                if ($sendmessage) {
                    $receiver = core_user::get_user($member->id);
                    // Register App groupmode: Send notification to group member.
                    organizer_send_message($from, $receiver, $slotx, 'register_promotion_student', null, null, true);
                }
                $generatetrainerevents = false;
            }
        }
    } else {
        if ($ok = organizer_register_single_appointment($slotid, $userid, 0, 0,
            $teacherapplicantid, true, null, $organizer->id)) {
            if ($sendmessage) {
                $receiver = core_user::get_user($userid);
                // Register App single mode: Send notification to participant.
                organizer_send_message($from, $receiver, $slotx, 'register_promotion_student');
            }
        }
    }

    $DB->delete_records('event', ['modulename' => 'organizer', 'eventtype' => 'Slot', 'uuid' => $slotid]);

    return $ok;
}

/**
 * Registers a single appointment in the system.
 *
 * @param int $slotid The ID of the organizer slot to register the appointment for.
 * @param int $userid The ID of the user to register.
 * @param int $applicantid Optional. The ID of the applicant registering the appointment. Defaults to 0.
 * @param int $groupid Optional. The ID of the group to register. Defaults to 0 for individuals.
 * @param null $teacherapplicantid Optional. The ID of the teacher applying for registration. Defaults to null.
 * @param bool $trainerevents Optional. Whether to generate trainer-specific events. Defaults to false.
 * @param null $trainerid Optional. The ID of the trainer for the event. Defaults to null if not provided.
 * @param null $ogranizerid The ID of the organizer. Defaults to null if not provided.
 *
 * @return int The ID of the newly inserted appointment record.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_register_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0,
    $teacherapplicantid = null, $trainerevents = false, $trainerid = null, $ogranizerid = null) {
    global $DB;

    [$cm, , $organizer, ] = organizer_get_course_module_data(null, $ogranizerid);

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
    if ($teacherapplicantid ?? false) {
        $appointment->teacherapplicantid = $teacherapplicantid;
        $appointment->teacherapplicanttimemodified = strtotime("now");
    } else {
        $appointment->registrationtime = strtotime("now");
    }

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

/**
 * Adds a single appointment to the queue for a specific slot and user.
 *
 * @param int $slotid The ID of the organizer slot to queue the appointment for.
 * @param int $userid The ID of the user to queue.
 * @param int $applicantid Optional. The ID of the applicant queuing the appointment. Defaults to 0.
 * @param int $groupid Optional. The ID of the group to queue. Defaults to 0 for individuals.
 *
 * @return int The ID of the newly inserted queue record.
 */
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

/**
 * Re-registers an appointment for a given slot and optionally for a specified group.
 *
 * This function unregisters previous appointments for the user or group and registers them
 * for the specified slot. It also handles synchronization of group appointments and updates
 * related queue entries.
 *
 * @param int $slotid The ID of the slot to register for.
 * @param int $groupid Optional. The ID of the group to register. Defaults to 0 for individual users.
 *
 * @return bool True if the re-registration is successful, false otherwise.
 */
function organizer_reregister_appointment($slotid, $groupid = 0) {
    global $DB, $USER;

    $params = ['slotid' => $slotid];
    $query = "SELECT s.organizerid FROM {organizer_slots} s
                  WHERE s.id = :slotid ";
    $organizerid = $DB->get_field_sql($query, $params);
    $organizer = $DB->get_record('organizer', ['id' => $organizerid]);

    $slotx = new organizer_slot($slotid);
    if ($slotx->is_full()) {
        return false;
    }

    $okregister = true;
    $okunregister = true;
    if (organizer_is_group_mode()) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);
        $members = get_enrolled_users($context, 'mod/organizer:register', $groupid, 'u.id', null, 0, 0, true);
        // Events for the trainers will only be generated with the first participants registration.
        $generatetrainerevents = true;
        foreach ($members as $member) {
            if ($app = organizer_get_last_user_appointment($organizer, $member->id)) {
                $okunregister = organizer_unregister_single_appointment($app->slotid, $member->id, $organizer);
            }
            $okregister = organizer_register_single_appointment($slotid, $member->id, $USER->id, $groupid,
                null, $generatetrainerevents);
            $generatetrainerevents = false;
        }
    } else {
        if ($app = organizer_get_last_user_appointment($organizer)) {
            $okunregister = organizer_unregister_single_appointment($app->slotid, $USER->id, $organizer);
        }
        $okregister = organizer_register_single_appointment($slotid, $USER->id, 0, 0, null, true);
    }

    if (organizer_hasqueue($organizerid)) {
         $slotx = new organizer_slot($slotid);
        if (organizer_is_group_mode()) {
            if ($next = $slotx->get_next_in_queue_group()) {
                $okregister = organizer_register_appointment($slotid, $next->groupid, 0, true);
                organizer_delete_from_queue($slotid, null, $next->groupid);
            }
        } else {
            if ($next = $slotx->get_next_in_queue()) {
                $okregister = organizer_register_appointment($slotid, 0, $next->userid, true);
                organizer_delete_from_queue($slotid, $next->userid);
            }
        }
    }

    if (isset($app->slotid) && (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation)) {
        $course = $DB->get_record('course', ['id' => $organizer->course], 'id', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
        if (!organizer_fetch_slotparticipants($app->slotid)) {
            $trainers = organizer_get_slot_trainers($app->slotid);
            foreach ($trainers as $trainer) {
                if ($eventid = $DB->get_field('organizer_slot_trainer', 'eventid',
                    ['slotid' => $app->slotid, 'trainerid' => $trainer])) {
                    $DB->delete_records('organizer_slot_trainer', ['slotid' => $app->slotid, 'trainerid' => $trainer]);
                }
                $DB->delete_records('event', ['id' => $eventid]);
                // Create slot event per trainer if instance config "empty slot events" is on.
                if (!isset($organizer->nocalendareventslotcreation) || !$organizer->nocalendareventslotcreation) {
                    $neweventid = organizer_add_event_slot($cm->id, $app->slotid, $trainer);
                    if ($record = $DB->get_record('organizer_slot_trainer', ["slotid" => $app->slotid,
                        "trainerid" => $trainer])) {
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

/**
 * Unregisters an appointment for a given slot for a group or a user.
 *
 * @param int $slotid The ID of the slot to unregister from.
 * @param int $groupid The ID of the group to unregister, or 0 for individual users.
 * @param int $organizerid The ID of the organizer instance.
 * @return bool Returns true if the unregistration process completed successfully for all.
 */
function organizer_unregister_appointment($slotid, $groupid, $organizerid) {
    global $DB, $USER;

    $ok = true;
    $organizer = $DB->get_record('organizer', ['id' => $organizerid]);

    if ($groupid) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);
        $members = get_enrolled_users($context, 'mod/organizer:register', $groupid, 'u.id', null, 0, 0, true);

        foreach ($members as $member) {
            $ok = organizer_unregister_single_appointment($slotid, $member->id, $organizer);
        }
    } else {
        $ok = organizer_unregister_single_appointment($slotid, $USER->id, $organizer);
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
        $organizer = $DB->get_record('organizer', ['id' => $organizerid]);
        $course = $DB->get_record('course', ['id' => $organizer->course], 'id', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
        $trainers = organizer_get_slot_trainers($slotid);
        foreach ($trainers as $trainer) {
            if ($record = $DB->get_record('organizer_slot_trainer', ['slotid' => $slotid, 'trainerid' => $trainer])) {
                $DB->delete_records('event', ['id' => $record->eventid]);
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

/**
 * Unregisters a single appointment for a given user and slot.
 *
 * This function removes an appointment record for a user from a specific slot,
 * along with any associated event records. It also handles group synchronization
 * if the organizer employs group booking modes.
 *
 * @param int $slotid The ID of the slot from which the user is to be unregistered.
 * @param int $userid The ID of the user to be unregistered from the slot.
 * @param stdClass|null $organizer The organizer object, or null to retrieve it dynamically.
 *
 *
 * @return bool True if the unregistration is successful; false otherwise.
 */
function organizer_unregister_single_appointment($slotid, $userid, $organizer = null) {
    global $DB;

    if (empty($organizer)) {
        $organizer = organizer_get_organizer();
    }

    $ok = false;
    if ($appointment = $DB->get_record('organizer_slot_appointments', ['userid' => $userid, 'slotid' => $slotid])) {
        $DB->delete_records('event', ['id' => $appointment->eventid]);
        $ok = $DB->delete_records('organizer_slot_appointments', ['userid' => $userid, 'slotid' => $slotid]);
        if (!$apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slotid])) {
            $DB->delete_records('event', ['modulename' => 'organizer', 'eventtype' => 'Appointment',
                'uuid' => $appointment->id]);
        } else {
            // To refresh description text of trainer's appointment events.
            foreach ($apps as $app) {
                $course = $DB->get_record('course', ['id' => $organizer->course], 'id', MUST_EXIST);
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

/**
 * Evaluates and updates slots based on the given data.
 *
 * This function processes the provided appointment data, updates the attendance,
 * grades, and feedback for each appointment, and recalculates grades for users
 * when necessary. It also returns a list of slot IDs that were modified.
 *
 * @param stdClass $data The data object containing appointment information and slot enablement data.

 * @return array An array of slot IDs that were evaluated or updated.
 */
function organizer_evaluate_slots($data) {
    global $DB;

    $organizer = organizer_get_organizer();

    $slotids = [];

    if (isset($data->apps) && count($data->apps) > 0 && isset($data->slotenable)) {
        foreach ($data->apps as $appid => $app) {
            $newapp = $DB->get_record('organizer_slot_appointments', ['id' => $appid]);
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

/**
 * Retrieves the course module data based on the provided parameters or current request parameters.
 *
 * This function attempts to load the course module, course, organizer, and context data
 * either using the provided `$id` or `$n` parameters, or using the optional parameters from
 * the current HTTP request. It ensures that the required details are fetched and throws
 * an exception if neither `$id` nor `$n` is specified.
 *
 * @param int|null $id Optional course module ID.
 * @param int|null $n Optional organizer instance ID.

 * @return array An array containing the course module, course, organizer, and context objects.
 * @throws coding_exception If neither a course module ID nor an instance ID is specified.
 */
function organizer_get_course_module_data($id = null, $n = null) {
    global $DB;

    $id = $id === null ? optional_param('id', 0, PARAM_INT) : $id; // Course_module ID, or.
    $n = $n == null ? optional_param('o', 0, PARAM_INT) : $n; // Organizer instance ID.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $cm->course], '*', MUST_EXIST);
        $organizer = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', ['id' => $n], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $organizer->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        throw new coding_exception('organizer_get_course_module_data: You must specify a course_module ID or an instance ID');
    }

    $context = context_module::instance($cm->id, MUST_EXIST);

    return [$cm, $course, $organizer, $context];
}

/**
 * Retrieves the organizer's course module data.
 *
 * This function fetches and returns the required information for the organizer,
 * specifically the course module, course, organizer, and context. It uses
 * parameters from the current HTTP request (`id` or `o`) and ensures the
 * necessary details are fetched. The returned data is structured
 * as object properties for better accessibility.

 * @return stdClass An object containing the course module (`cm`), course (`course`),
 *                  organizer (`organizer`), and context (`context`) objects.
 *
 * @throws coding_exception If neither a course module ID (`id`) nor an instance ID (`o`) is specified.
 */
function organizer_get_course_module_data_new() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    $instance = new stdClass();

    if ($id) {
        $instance->cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $instance->course = $DB->get_record('course', ['id' => $instance->cm->course], '*', MUST_EXIST);
        $instance->organizer = $DB->get_record('organizer', ['id' => $instance->cm->instance], '*', MUST_EXIST);
    } else if ($n) {
        $instance->organizer = $DB->get_record('organizer', ['id' => $n], '*', MUST_EXIST);
        $instance->course = $DB->get_record('course', ['id' => $instance->organizer->course], '*', MUST_EXIST);
        $instance->cm = get_coursemodule_from_instance(
            'organizer', $instance->organizer->id, $instance->course->id,
            false, MUST_EXIST
        );
    } else {
        throw new coding_exception('organizer_get_course_module_data_new: You must specify a course_module ID or an instance ID');
    }

    $instance->context = context_module::instance($instance->cm->id, MUST_EXIST);

    return $instance;
}

/**
 * Retrieves the organizer record based on the current HTTP request.
 *
 * This function fetches and returns the organizer instance record from the
 * database. It uses the course module ID (`id`) or the organizer instance ID
 * (`o`) provided in the HTTP request parameters. If neither is supplied, it
 * throws an exception.

 * @return stdClass The organizer instance record from the database.
 *
 * @throws coding_exception If neither a course module ID (`id`) nor an instance ID (`o`) is specified.
 */
function organizer_get_organizer() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $organizer = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', ['id' => $n], '*', MUST_EXIST);
    } else {
        throw new coding_exception('organizer_get_organizer: You must specify a course_module ID or an instance ID');
    }

    return $organizer;
}

/**
 * Retrieves the course module (cm) object
 * based on the current HTTP request parameters.
 *
 * This function uses either the course module ID (`id`) or the organizer instance ID (`o`)
 * provided in the HTTP request parameters to fetch the data. If neither parameter is given,
 * an exception is thrown.
 *
 * @return stdClass An object containing the course module (`cm`) object.
 *
 * @throws coding_exception If neither a course module ID (`id`) nor an instance ID (`o`) is specified.
 */
function organizer_get_cm() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', ['id' => $n], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $organizer->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        throw new coding_exception('organizer_get_cm: You must specify a course_module ID or an instance ID');
    }

    return $cm;
}

/**
 * Retrieves the context of the organizer module based on the current HTTP request.
 *
 * This function determines the context of the organizer module using either a
 * course module ID (`id`) or an organizer instance ID (`o`) provided in the
 * HTTP request parameters. If neither is provided, an exception is thrown.
 *
 * The result is a module-level context object that can be used for permission
 * checks and other context-specific operations.
 *
 * @return context_module The context object of the organizer module.
 *
 * @throws coding_exception If neither a course module ID (`id`) nor an instance ID (`o`) is specified.
 */
function organizer_get_context() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // Course_module ID, or.
    $n = optional_param('o', 0, PARAM_INT); // Organizer instance ID - it should be named as the first character of the module.

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', ['id' => $n], '*', MUST_EXIST);
        $course = $DB->get_record('course', ['id' => $organizer->course], '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        throw new coding_exception('organizer_get_context: You must specify a course_module ID or an instance ID');
    }

    $context = context_module::instance($cm->id, MUST_EXIST);

    return $context;
}

/**
 * Determines whether the organizer is operating in group mode or not.
 *
 * This function checks the group mode of the organizer associated with the current course module.
 * If the 'isgrouporganizer' property matches the defined group mode constant, the function returns true.
 *
 * @return bool Returns true if the organizer is in group mode, false otherwise.
 *
 * @throws \moodle_exception If the course module cannot be determined.
 */
function organizer_is_group_mode() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
    return $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS;
}

/**
 * Checks whether queueing is enabled for the organizer module.
 *
 * This function determines if the organizer instance has the `queue` property set,
 * indicating that queueing functionality is available.
 *
 * @return bool Returns true if queueing is enabled, false otherwise.
 *
 * @throws \dml_exception If there is an error with the database query.
 * @throws \moodle_exception If the course module cannot be retrieved.
 */
function organizer_is_queueable() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
    return $organizer->queue;
}

/**
 * Fetches the current user's group in a course module for the organizer.
 *
 * This function determines if the organizer is operating in group mode and
 * retrieves the user's group if they belong to one. It performs a SQL query
 * to fetch the group details based on the user's ID and the course module's grouping ID.
 *
 * @return \stdClass|false The group object if found, or false if the organizer is not in group mode.
 *
 * @throws \dml_exception If there is an error while performing the database query.
 * @throws \moodle_exception If the course module cannot be determined.
 */
function organizer_fetch_my_group() {

    if (organizer_is_group_mode()) {
        global $DB, $USER;
        $id = optional_param('id', 0, PARAM_INT);
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);

        if ($cm->groupingid) {
            $params = ['groupingid' => $cm->groupingid, 'userid' => $USER->id];
            $query = "SELECT {groups}.* FROM {groups}
                    INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                    INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                    WHERE {groupings_groups}.groupingid = :groupingid
                    AND {groups_members}.userid = :userid
                    ORDER BY {groups}.name ASC";
        } else {
            $params = ['courseid' => $cm->course, 'userid' => $USER->id];
            $query = "SELECT {groups}.* FROM {groups}
                    INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                    WHERE {groups}.courseid = :courseid
                    AND {groups_members}.userid = :userid
                    ORDER BY {groups}.name ASC";
        }
        $group = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
        return $group;
    } else {
        return false;
    }
}

/**
 * Fetches the group of a specified user in a particular organizer module instance.
 *
 * This function retrieves the user's group within the context of the given organizer instance.
 * It queries the database to find the group details based on the user ID and the grouping ID
 * associated with the organizer's course module.
 *
 * @param int $userid The ID of the user whose group is to be fetched.
 * @param int|null $id The ID of the organizer instance. Defaults to null.
 *
 * @return \stdClass|false Returns the group object if found, or false if the user is not part of a group.
 *
 * @throws \dml_exception If there is an error while querying the database.
 * @throws \moodle_exception If the course module cannot be determined.
 */
function organizer_fetch_user_group($userid, $id = null) {
    global $DB;

    $cm = get_coursemodule_from_instance('organizer', $id, 0, false, MUST_EXIST);

    if ($cm->groupingid) {
        $params = ['groupingid' => $cm->groupingid, 'userid' => $userid];
        $query = "SELECT {groups}.id FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid
                AND {groups_members}.userid = :userid";
    } else {
        $params = ['courseid' => $cm->course, 'userid' => $userid];
        $query = "SELECT {groups}.id FROM {groups}
                INNER JOIN {groups_members} ON {groups}.id = {groups_members}.groupid
                WHERE {groups}.courseid = :courseid
                AND {groups_members}.userid = :userid";
    }
    $group = $DB->get_record_sql($query, $params, IGNORE_MULTIPLE);
    return $group;
}

/**
 * Fetches the 'hidecalendar' field for a specific organizer instance.
 *
 * This function retrieves the value of the 'hidecalendar' field
 * from the organizer table for the specified course module.
 *
 * @return int|string The value of the 'hidecalendar' field for the organizer instance.
 *
 * @throws \dml_exception If there is an error while querying the database.
 * @throws \moodle_exception If the course module with the given ID cannot be found.
 */
function organizer_fetch_hidecalendar() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $hidecalendar = $DB->get_field('organizer', 'hidecalendar', ['id' => $cm->instance], MUST_EXIST);
    return $hidecalendar;
}

/**
 * Fetches table entries for given slots.
 *
 * This function retrieves details of organizer slots and their associated appointments, users,
 * and groups from the database. It takes a list of slot IDs as input and an optional order by
 * clause for customizing the sorting of results.
 *
 * @param array $slots An array of slot IDs for which table entries need to be fetched.
 * @param string $orderby (Optional) The column(s) by which the results should be ordered.
 *                          Defaults to "starttime ASC, id, lastname ASC, firstname ASC".
 *
 * @return array An associative array of records, with each record containing details of slots, appointments, users, and groups.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_fetch_table_entries($slots, $orderby="") {
    global $DB;

    if (empty($slots)) {
        $slots = [0];
    }
    [$insql, $inparams] = $DB->get_in_or_equal($slots, SQL_PARAMS_NAMED);

    $params = [];
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
    if ($DB->get_field('organizer', 'queue', ['id' => $organizerid])) {
        $result = true;
    }
    return $result;
}

/**
 * Determines if grading is enabled for the current organizer instance.
 *
 * This function checks if grading is enabled for the organizer instance
 * identified by a course module ID passed via a request parameter. If the
 * grade field of the organizer record is not equal to 0, grading is considered
 * enabled.
 *
 * @return int Returns 1 if grading is enabled, or 0 if grading is disabled.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_with_grading() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
    if ($organizer->grade != 0) {
        return 1;
    } else {
        return 0;
    }
}

/**
 * Generates HTML or printable output for a teacher applicant based on their ID and modification time.
 *
 * This function retrieves the information of the teacher applicant, specifically their first and last name,
 * and formats the output based on whether it's to be displayed in an HTML interface or in a printable format.
 *
 * When $printable is false, the function also includes a tooltip displaying additional information such as
 * when the data was last modified. If $printable is true, a simple formatted string is returned.
 *
 * @param int $teacherapplicantid The ID of the teacher applicant.
 * @param int|null $teacherapplicanttimemodified The timestamp of the teacher applicant's last modification. Optional.
 * @param bool $printable If true, formats the output for printing. If false, formats it for HTML display.
 *
 * @return string Returns the formatted output containing initials of the teacher applicant, optionally with additional details.
 *
 * @throws \dml_exception If there's an error in fetching records from the database.
 */
function organizer_get_teacherapplicant_output($teacherapplicantid, $teacherapplicanttimemodified=null, $printable=false) {
    global $DB;

    $output = "";

    if (is_numeric($teacherapplicantid)) {
        if (!$printable) {
            $timestampstring = $teacherapplicanttimemodified != null ? "\n" .
                    userdate($teacherapplicanttimemodified, get_string('fulldatetimetemplate', 'organizer')) : "";
            if ($trainer = $DB->get_record('user', ['id' => $teacherapplicantid], 'lastname,firstname')) {
                $output = " <span style= 'cursor:help;' title='" . get_string('slotassignedby', 'organizer') . " " .
                $trainer->firstname . " " . $trainer->lastname . $timestampstring ."'>[" . $trainer->firstname[0] .
                        $trainer->lastname[0] . "]</span>";
            }
        } else {
            if ($trainer = $DB->get_record('user', ['id' => $teacherapplicantid], 'lastname,firstname')) {
                $output = "[" . $trainer->firstname[0] . $trainer->lastname[0] . "]";
            }
        }
    }

    return $output;

}

/**
 * Fetches the name of a group based on the provided group ID.
 *
 * This function retrieves the name of a group from the 'groups' database table,
 * identified by its unique ID.
 *
 * @param int $groupid The ID of the group whose name is to be fetched.
 *
 * @return string|null The name of the group if found, or null if not found.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_fetch_groupname($groupid) {
    global $DB;

    $groupname = $DB->get_field('groups', 'name', ['id' => $groupid]);

    return $groupname;
}

/**
 * Fetches all users in a group based on the provided group ID.
 *
 * This function retrieves the members of a specific group from the 'groups_members'
 * database table by performing a JOIN on the 'user' table to fetch the user's details.
 *
 * @param int $groupid The ID of the group whose members are to be retrieved.
 *
 * @return array An array of user objects, where each object contains the user's ID,
 *               lastname, and firstname. Returns an empty array if no users are found.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_fetch_groupusers($groupid) {
    global $DB;

    $query = "SELECT u.id, u.lastname,u.firstname FROM {groups_members} g
            INNER JOIN {user} u ON u.id = g.userid
            WHERE g.groupid = :groupid";
    $par = ['groupid' => $groupid];
    $users = $DB->get_records_sql($query, $par);

    if (!$users || count($users) == 0) {
        return [];
    }

    return $users;
}

/**
 * Counts the total number of appointments for the given slots.
 *
 * This function iterates over a set of slot IDs, queries the database
 * to count the number of appointments in each slot, and returns the total count.
 *
 * @param array $slots An array of slot IDs for which appointment counts are to be determined.
 *
 * @return int The total number of appointments across all provided slots.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_count_slotappointments($slots) {
    global $DB;

    $apps = 0;
    foreach ($slots as $slot) {
        $apps += $DB->count_records('organizer_slot_appointments', ['slotid' => $slot]);
    }

    return $apps;
}

/**
 * Filters out hidden slots from the provided list of slot IDs.
 *
 * This function checks the visibility of each slot in the 'organizer_slots' table
 * and removes any slots that are marked as not visible.
 *
 * @param array $slots An array of slot IDs to be filtered.
 *
 * @return array The filtered array of slot IDs with only visible slots included.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_sortout_hiddenslots($slots) {
    global $DB;

    foreach ($slots as $slot) {
        if (!$visible = $DB->get_field('organizer_slots', 'visible', ['id' => $slot])) {
            if (($key = array_search($slot, $slots)) !== false) {
                unset($slots[$key]);
            }
        }
    }

    return $slots;
}

/**
 * Retrieves the identity of a user based on defined configuration settings.
 *
 * This function returns an identifying value (such as the user ID number or email)
 * for the given user, depending on the system's configuration settings. It ensures
 * that sensitive information is not displayed if the configuration prohibits it.
 *
 * @param mixed $user A user object or the user's ID. If a numeric value is supplied, it is treated as the user ID.
 *
 * @return string The identity of the user based on the configuration, or an empty string if not applicable.
 *
 * @throws \dml_exception If a database query error occurs.
 */
function organizer_get_user_identity($user) {
    global $CFG, $DB;

    if (get_config('organizer', 'dontshowidentity')) {
        return "";
    }
    if (is_object($user)) {
        $id = $user->id;
    } else {
        if (is_numeric($user)) {
            $id = $user;
        } else {
            return "";
        }
    }

    $identity = "";
    $identityfields = explode(',', $CFG->showuseridentity);
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

    $slot = $DB->get_record('organizer_slots', ['id' => $slotid]);
    if ($slot->coursegroup) {
        $coursegroup = $DB->get_field('groups', 'id', ['id' => $slot->coursegroup]);
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

/**
 * Creates a course group for the specified slot and organizer.
 *
 * This function generates a new course group for the provided slot and organizer
 * if it does not already exist. The group is named based on the organizer's name,
 * slot's start time, and course ID. Additionally, if the organizer includes trainers
 * in the group, the slot trainers are added as members upon creation.
 *
 * @param object $slot The slot object containing the relevant information (e.g., organizer ID, start time, etc.).
 * @return int The ID of the newly created course group.
 *
 * @throws dml_exception If a database query error occurs.
 */
function organizer_create_coursegroup($slot) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $organizer = $DB->get_record('organizer', ['id' => $slot->organizerid], 'name,course,includetraineringroups');
    $group = new stdClass();
    $group->courseid = $organizer->course;
    $group->name = organizer_create_coursegroupname($organizer->name, $slot->starttime, $organizer->course);
    $time = time();
    $group->timecreated = $time;
    $group->timemodified = $time;
    if ($groupid = groups_create_group($group)) {
        $DB->set_field('organizer_slots', 'coursegroup', $groupid, ['id' => $slot->id]);
        if ($organizer->includetraineringroups) {
            $trainers = organizer_get_slot_trainers($slot->id);
            foreach ($trainers as $trainerid) {
                groups_add_member($groupid, $trainerid);
            }
        }
    }

    return $groupid;
}

/**
 * Creates a unique name for a course group based on the provided slot and course data.
 *
 * This function constructs a name using the organizer's name, slot's start time, and course ID,
 * ensuring uniqueness by appending an incremented identifier if other groups share
 * a similar name pattern.
 *
 * @param string $name The name of the organizer.
 * @param int $time The start time (timestamp) of the slot.
 * @param int $courseid The ID of the course.
 * @return string A unique name for the course group.
 * @throws dml_exception If a database error occurs during the query.
 */
function organizer_create_coursegroupname($name, $time, $courseid) {
    global $DB;

    $coursename = str_replace("_", "-", $name) . " ";
    $coursename .= date('Y-m-d H:i', $time);
    $params = ['coursename' => '%' . $coursename . '%', 'courseid' => $courseid];
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

/**
 * Deletes a course group based on the provided group ID or slot ID.
 *
 * This function deletes a course group from the database and ensures cleanup
 * by using either the $groupid directly or deriving it based on the $slotid.
 *
 * @param int $groupid The ID of the group to delete. If null, it will be derived from the slot ID.
 * @param int|null $slotid The slot ID used to retrieve the corresponding group ID if $groupid is not provided.
 * @return bool True if the group was successfully deleted, false otherwise.
 *
 * @throws dml_exception If a database error occurs while querying or deleting.
 */
function organizer_delete_coursegroup($groupid, $slotid=null) {
    global $DB, $CFG;

    require_once($CFG->dirroot.'/group/lib.php');

    $ok = false;
    if (is_number($slotid)) {
        $params = ['slotid' => $slotid];
        $query = "SELECT s.coursegroup FROM {organizer_slots} s
                  WHERE s.id = :slotid ";
        $groupid = $DB->get_field_sql($query, $params);
    }
    if (is_number($groupid)) {
        $ok = groups_delete_group($groupid);
    }
        return $ok;
}

/**
 * Fetches all slots linked to a given organizer.
 *
 * This function retrieves all slots associated with a specific organizer
 * by querying the database, using the provided organizer ID as the filter condition.
 *
 * @param int $organizerid The ID of the organizer whose slots are to be fetched.
 * @return array An array of slots matching the organizer ID.
 * @throws dml_exception If a database error occurs during the query.
 */
function organizer_fetch_allslots($organizerid) {
    global $DB;

    $slots = $DB->get_records_select('organizer_slots', 'organizerid = :organizerid', ['organizerid' => $organizerid]);

    return $slots;
}

/**
 * Fetches all appointments linked to a given organizer instance.
 *
 * This function retrieves all appointments associated with a specific organizer
 * by querying the database, with the provided organizer ID as the filter condition.
 * The results include details such as appointment ID, group ID, user ID, and event ID.
 *
 * @param int $organizerid The ID of the organizer whose appointments are to be fetched.
 * @return array An array of appointments with keys as appointment IDs and details as values.
 * @throws dml_exception If a database error occurs during the query.
 */
function organizer_fetch_allappointments($organizerid) {
    global $DB;

    $params = ['organizerid' => $organizerid];
    $query = "SELECT a.id, a.groupid, a.userid, a.eventid FROM {organizer_slots} s INNER JOIN {organizer_slot_appointments} a
              ON s.id = a.slotid
              WHERE s.organizerid = :organizerid ";
    $apps = $DB->get_records_sql($query, $params);

    return $apps;
}

/**
 * Fetches a list of participants for a specific slot.
 *
 * This function retrieves the user IDs of all participants registered
 * for a given slot by querying the database.
 *
 * @param int $slotid The ID of the slot whose participants are to be fetched.
 * @return array An array of user IDs of the participants in the slot.
 * @throws dml_exception If a database error occurs during the query.
 */
function organizer_fetch_slotparticipants($slotid) {
    global $DB;

    $participants = $DB->get_fieldset_select('organizer_slot_appointments', 'userid',
        'slotid = :slotid', ['slotid' => $slotid]);

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
    $profilefields['grade'] = organizer_filter_text(get_string('gradenoun'));
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

/**
 * Retrieves a list of allowed printable user fields for slots.
 *
 * This function fetches the list of selectable profile fields for use in slot
 * printing, based on either the site-wide organizer configuration or default values.
 * The fields are used to define the information displayed for users in slot details.
 *
 * @return array Returns an associative array of allowed fields where the key is the field
 *               identifier and the value is the field's display name.
 */
function organizer_get_allowed_printslotuserfields() {
    $selectableprofilefields = organizer_printslotuserfields();
    $selectedprofilefields = [];

    $organizerconfig = get_config('organizer');
    if (isset($organizerconfig->allowedprofilefieldsprint)) {
        $selectedprofilefields = ['' => '--'];
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
        $selectedprofilefields['grade'] = get_string('gradenoun');
        $selectedprofilefields['feedback'] = get_string('feedback');
        $selectedprofilefields['signature'] = get_string('signature', 'organizer');
    }
    return $selectedprofilefields;
}

/**
 * Fetches the details of users assigned to a specific slot along with their profile information.
 *
 * This function constructs and executes an SQL query to retrieve user details,
 * including social profile fields and other user-related information for a given slot ID.
 * The result includes data like name, email, group name, and more.
 *
 * @param int $slot The ID of the slot to fetch user details for.
 * @return array An array of user details records, where each record is an associative array
 *               containing user-specific fields and additional information.
 * @throws dml_exception If an error occurs while executing the database query.
 */
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
        $socialjoin .= " LEFT JOIN {user_info_data} $tablename ON $tablename.userid=a.userid AND $tablename.fieldid = :$paramname";
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

/**
 * Filters and processes the given text using the appropriate filters for the current page context.
 *
 * This function sets up the page for filters, ensuring global configurations are activated,
 * and then applies the filters on the provided text. The filtered string is then returned.
 *
 * @param string $text The input text to be filtered.
 * @return string The filtered text after applying all active filters.
 */
function organizer_filter_text($text) {
    global $PAGE;

    $context = $PAGE->context;
    $filtermanager = filter_manager::instance();
    $filtermanager->setup_page_for_filters($PAGE, $context); // Setup global stuff filters may have.
    $text = $filtermanager->filter_string($text, $context);

    return $text;
}

/**
 * Retrieves information about users associated with a specific slot ID.
 *
 * This function fetches a list of user IDs linked to a given slot ID and
 * formats them into a comma-separated string of user links. Each user link
 * can provide a clickable representation of the names.
 *
 * @param int $slotid The ID of the slot to retrieve users for.
 * @return string A comma-separated string of user links representing the users in the slot.
 * @throws dml_exception If a database error occurs during fetching user data.
 */
function organizer_get_users_of_slot($slotid) {
    global $DB;

    $usersofslot = "";
    $con = "";
    $users = $DB->get_fieldset_select('organizer_slot_appointments', 'userid', "slotid = :slotid",
        ['slotid' => $slotid]);
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
    $trainerlang = $DB->get_field('user', 'lang', ['id' => $trainerid]);
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
    $params = ['slotid' => $slot->id, 'trainerid' => $trainerid];
    $query = "SELECT e.id FROM {event} e
                  INNER JOIN {organizer_slot_trainer} t ON e.id = t.eventid
                  WHERE t.slotid = :slotid AND t.trainerid = :trainerid";
    // Create new appointment event or update existent appointment event for trainers.
    if (!$teventid = $DB->get_field_sql($query, $params)) {
        $teventid = organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT,
            $trainerid, $slot->starttime, $slot->duration, 0, $appointment->id
        );
        $DB->set_field('organizer_slot_trainer', 'eventid', $teventid, ['slotid' => $slot->id, 'trainerid' => $trainerid]);
    } else {
        organizer_change_calendarevent(
            [$teventid], $organizer, $eventtitle, $eventdescription,
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
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_XLSX);
            break;
        case 'xls':
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_XLS);
            break;
        case 'ods':
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_ODS);
            break;
        case 'csv_comma':
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_CSV_COMMA);
            break;
        case 'csv_tab':
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_CSV_TAB);
            break;
        default:
            $mpdftable->setOutputFormat(MTablePDF::OUTPUT_FORMAT_PDF);
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

    $organizer = $DB->get_record('organizer', ['id' => $cm->instance]);

    require_capability('mod/organizer:printslots', $context);

    $event = appointment_list_printed::create(
        [
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context,
        ]
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
        $entries = organizer_get_reg_status_table_entries_group($params);
    } else {
        $entries = organizer_get_reg_status_table_entries($params);
    }
    return $entries;
}

/**
 * Obtains slots parameters if present
 *
 * @return array slots
 * @throws coding_exception
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
 * How many slots a participant has booked
 *
 * @param int $organizerid ID of organizer instance
 * @param int $userid ID of user
 * @param int $groupid ID of group (if instance is groupmode)
 *
 * @return int $slots
 * @throws dml_exception
 */
function organizer_count_bookedslots($organizerid, $userid = null, $groupid = null) {
    global $DB, $USER;

    if ($userid == null && $groupid == null) {
        $userid = $USER->id;
    }
    if ($userid) {
        $paramssql = ['userid' => $userid, 'organizerid' => $organizerid];
        $query = "SELECT count(*) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid";
    } else {
        $paramssql = ['groupid' => $groupid, 'organizerid' => $organizerid];
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
        $organizer = $DB->get_record('organizer', ['id' => $organizer], 'userslotsmin, userslotsmax', MUST_EXIST);
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
 * How many bookings a participant has left
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
        $paramssql = ['userid' => $userid, 'organizerid' => $organizer->id];
        $query = "SELECT count(*) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid";
    } else {
        $paramssql = ['groupid' => $groupid, 'organizerid' => $organizer->id];
        $query = "SELECT count(DISTINCT s.id) FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.groupid = :groupid";
    }
    $bookedslots = $DB->count_records_sql($query, $paramssql);

    $slotsleft = $organizer->userslotsmax - $bookedslots;

    return $slotsleft < 0 ? 0 : $slotsleft;
}

/**
 * Returns amount of course participants who have not booked the minimum of slots and have booked the maximum of slots.
 *
 * @param object $organizer organizer instance
 * @param boolean $groupmode is organizer instance in groupmode
 * @param objects $entries of registration view
 * @param int $min required minimum of slots a user/group has to book
 * @param int $max maximum of slots a user/group is allowed to book
 *
 * @return array $entries, $underminimum: participants booked under minimum, $maxreached: participants
 * who have reached the max
 */
function organizer_registration_statistics($organizer, $groupmode, $entries, $min, $max) {
    global $DB;

    $countentries = 0;
    $underminimum = 0;
    $maxreached = 0;

    $context = context_course::instance($organizer->course);
    $entryids = [];
    if ($groupmode) {
        $currentgroup = 0;
        foreach ($entries as $entry) {
            $entryids[] = $entry->id;
            if ($entry->id != $currentgroup) {
                if (count_enrolled_users($context, 'mod/organizer:register', $entry->id, true)) {
                    $countentries++;
                }
                $currentgroup = $entry->id;
            }
        }
    } else {
        foreach ($entries as $entry) {
            // Count participants and not bookings.
            if (!in_array($entry->id, $entryids)) {
                $entryids[] = $entry->id;
                $countentries++;
            }
        }
    }
    if (empty($entryids)) {
        $entryids = [0];
    }
    [$insql, $paramssql] = $DB->get_in_or_equal($entryids, SQL_PARAMS_NAMED);

    if ($groupmode) {
        $where = 's.organizerid = ' . $organizer->id . ' AND a.groupid ' . $insql;
        $query = "SELECT a.groupid, count(DISTINCT s.id) as apps FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE $where
            GROUP BY a.groupid";
    } else {
        $where = 's.organizerid = ' . $organizer->id . ' AND a.userid ' . $insql;
        $query = "SELECT a.userid, count(DISTINCT a.id) as apps FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE $where
            GROUP BY a.userid";
    }
    $groupedentries = $DB->get_recordset_sql($query, $paramssql);
    $countgroupedentries = 0;
    foreach ($groupedentries as $entry) {
        $countgroupedentries++;
        if ($entry->apps < $min) {
            $underminimum++;
        } else if ($entry->apps >= $max) {
            $maxreached++;
        }
    }
    $underminimum += $countentries - $countgroupedentries;

    return [$countentries, $underminimum, $maxreached];
}

/**
 * Returns amount of course participants who have not booked the minimum of slots yet.
 *
 * @param object $organizer organizer instance
 * @param object $cm course module data of instance
 *
 * @return object with strings: registered, attended, total
 */
function organizer_get_counters($organizer, $cm = null) {
    global $DB;

    if (!$cm) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
    }
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        if ($cm->groupingid) {
            $groups = groups_get_all_groups($cm->course, null, $cm->groupingid, 'g.id');
        } else {
            $groups = groups_get_all_groups($cm->course, null, null, 'g.id');
        }
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
        $students = get_enrolled_users($context, 'mod/organizer:register', null, 'u.id', null, 0, 0, true);
        $info = new info_module(cm_info::create($cm));
        $filtered = $info->filter_user_list($students);
        $studentids = array_keys($filtered);
        $havebookings = $DB->get_fieldset_sql('SELECT DISTINCT sa.userid
        FROM {organizer_slot_appointments} sa INNER JOIN {organizer_slots} s ON sa.slotid = s.id
        WHERE s.organizerid = :organizerid', ['organizerid' => $organizer->id]
        );
        $participants = array_merge($studentids, $havebookings);
        $attended = 0;
        $registered = 0;
        foreach ($participants as $participant) {
            $apps = organizer_get_all_user_appointments($organizer, $participant);
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

/**
 * Returns the html of a status bar indicating the user's status regarding his bookings.
 *
 * @param int $bookings amount of user bookings
 * @param int $max max amount of bookings per user
 * @param boolean $minreached if user has reached minimum of bookings
 * @param string $statusmsg to be written
 * @param string $msg for the tooltip
 *
 * @return object $out html output of status bar
 */
function organizer_userstatus_bar($bookings, $max, $minreached, $statusmsg, $msg) {

    $out = html_writer::start_div('userstatusbar_tr', ['title' => $msg]);
    if ($minreached) {
        $classstrfull = 'fa fa-check-circle fa-2x mr-2 text-success';
        $classstrempty = 'fa fa-circle-thin fa-2x mr-2 text-success';
        $classstatusmsg = 'text-success font-weight-bolder';
    } else {
        $classstrfull = 'fa fa-check-circle fa-2x mr-1 text-info';
        $classstrempty = 'fa fa-circle-thin fa-2x mr-1 text-info';
        $classstatusmsg = 'text-info font-weight-bolder';
    }
    $i = 1;
    while ($i <= (int) $bookings) {
        $out .= organizer_get_fa_icon($classstrfull, $statusmsg);
        $i++;
    }
    while ($i <= (int) $max) {
        $out .= organizer_get_fa_icon($classstrempty, $statusmsg);
        $i++;
    }
    $out .= html_writer::span($statusmsg, $classstatusmsg);
    $out .= html_writer::span($msg, 'ml-1 font-italic');
    $out .= html_writer::end_div();

    return $out;

}

/**
 * Generates a plain date-time string representation for a given slot.
 *
 * This function takes a slot object and returns a string that represents
 * the start and end time of the slot in a formatted manner. If the slot's start
 * and end time fall on the same day, the format will include only the time zone within
 * that day. Otherwise, the format will include the full date and time for both start
 * and end time.
 *
 * @param stdClass $slot An object containing slot information with properties:
 *  - starttime (int): The start timestamp of the slot.
 *  - duration (int): The duration of the slot in seconds.
 *
 * @return string A string representing the date and time range of the given slot,
 *                or '-' if the slot or its starting time is not set.
 */
function organizer_date_time_plain($slot) {
    if (!isset($slot) || !isset($slot->starttime)) {
        return '-';
    }

    [$unitname, $value] = organizer_figure_out_unit($slot->duration);
    $duration = ($slot->duration / $value) . ' ' . $unitname;

    // If slot is within a day.
    if (userdate($slot->starttime, get_string('datetemplate', 'organizer')) ==
        userdate($slot->starttime + $slot->duration, get_string('datetemplate', 'organizer'))) {
        $datefrom = userdate($slot->starttime, get_string('datetemplate', 'organizer')) . " " .
            userdate($slot->starttime, get_string('timetemplate', 'organizer'));
        $dateto = userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
    } else {
        $datefrom = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer')) . " " .
            userdate($slot->starttime, get_string('timetemplate', 'organizer'));
        $dateto = userdate($slot->starttime + $slot->duration, get_string('fulldatetemplate', 'organizer')) . " " .
            userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
    }
    $datestr = "$datefrom-$dateto";
    return $datestr;

}

/**
 * Sends reminders to users or groups.
 *
 * This function is used to send reminder notifications to participants
 * that are associated with an organizer. Recipients can be specified
 * individually or as a list. If no recipients are provided, it attempts
 * to send reminders to all users or groups based on the organizer mode.
 *
 * @param int|null $recipient Optional. The ID of a single recipient to send the reminder to.
 * @param array $recipients Optional. An array of user IDs to send the reminders to.
 * @param string $custommessage Optional. A custom message to include in the reminder.
 *
 * @return int The count of successfully sent reminders.
 *
 * @throws dml_exception If there is an error in database access operations.
 */
function organizer_remind_all($recipient = null, $recipients = [], $custommessage = "") {
    global $DB;

    [$cm, , $organizer, $context] = organizer_get_course_module_data();

    $checkenough = false;
    if ($recipient != null) {
        if (!organizer_is_group_mode()) {
            $entries = $DB->get_records_list('user', 'id', [$recipient]);
        } else {
            $entries = get_enrolled_users($context, 'mod/organizer:register',
                $recipient, 'u.id', null, null, null, true);
        }
    } else if ($recipients) {
        $entries = $DB->get_records_list('user', 'id', $recipients);
    } else if (!organizer_is_group_mode()) {
        $entries = get_enrolled_users($context, 'mod/organizer:register');
        $checkenough = true;
    } else {
        $query = "SELECT u.* FROM {user} u
            INNER JOIN {groups_members} gm ON u.id = gm.userid
            INNER JOIN {groups} g ON gm.groupid = g.id
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :grouping";
        $par = ['grouping' => $cm->groupingid];
        $entries = $DB->get_records_sql($query, $par);
        $checkenough = true;
    }

    $nonrecepients = [];
    if ($checkenough) {
        foreach ($entries as $entry) {
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                if (organizer_multiplebookings_status(
                        organizer_count_bookedslots($organizer->id, null, $entry->id),
                        $organizer) == USERSLOTS_MIN_REACHED) {
                    $nonrecepients[] = $entry->id;
                }
            } else {
                if (organizer_multiplebookings_status(
                        organizer_count_bookedslots($organizer->id, $entry->id, null),
                        $organizer) == USERSLOTS_MIN_REACHED) {
                    $nonrecepients[] = $entry->id;
                }
            }
        }
    }

    $count = 0;
    foreach ($entries as $entry) {
        if (!in_array($entry->id, $nonrecepients)) {
            organizer_prepare_and_send_message(
                ['user' => $entry->id, 'organizer' => $organizer,
                    'custommessage' => $custommessage], 'register_reminder_student'
            );
            $count++;
        }
    }
    return $count;
}

/**
 * Get array of recipients which have not reached the minimum of bookings.
 *
 * This function allows sending reminders to a specific user, a group of users, or all
 * eligible users as per the organizer's mode. It also ensures recipients meet specific
 * criteria before sending, and optionally includes a custom message.
 *
 * @param int|null $recipient Optional. The ID of a single recipient to send the reminder to.
 * @param array $recipients Optional. An array of user IDs to send the reminders to.
 * @param string $custommessage Optional. A custom message to include in the reminder.
 *
 * @return int The number of successfully sent reminders.
 *
 * @throws dml_exception If there is an error in database access operations.
 */
function organizer_get_reminder_recipients($organizer) {

    $params = ['group' => 0, 'sort' => '', 'dir' => '', 'psort' => '', 'pdir' => ''];
    $recipients = [];
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        [$cm, $course, $organizer, $context] = organizer_get_course_module_data(null, $organizer->id);
        $entries = organizer_get_reg_status_table_entries_group($params);
    } else {
        $entries = organizer_get_reg_status_table_entries($params);
    }
    if ($entries) {
        // Select all users which have not reached the minimum of bookings.
        foreach ($entries as $entry) {
            $in = false;
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                if (organizer_multiplebookings_status(
                        organizer_count_bookedslots($organizer->id, null, $entry->id),
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
                if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                    $members = organizer_fetch_groupusers($entry->id);
                    foreach ($members as $member) {
                        if (has_capability('mod/organizer:register', $context, $member->id, false)) {
                            $recipients[] = $member->id;
                        }
                    }
                } else {
                    $recipients[] = $entry->id;
                }
            }
        }
    }
    if (is_object($entries)) {
        $entries->close();
    }

    return $recipients;
}

/**
 * Adds a "limitedwidth" class to the page body if the limited width option is enabled.
 *
 * This function checks the configuration for the organizer module to determine
 * if a limited width setting is enabled. If enabled, it applies the `limitedwidth`
 * class to the page body.
 *
 * @return bool True if the limited width class is added, false otherwise.
 */
function organizer_get_limitedwidth() {
    global $PAGE;
    $organizerconfig = get_config('organizer');
    if (isset($organizerconfig->limitedwidth) && $organizerconfig->limitedwidth == 1) {
        $PAGE->add_body_class('limitedwidth');
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if the daily booking limit for a user or group has been reached.
 *
 * This function verifies whether the number of appointments booked by a user or group
 * for the given organizer has reached the maximum allowed per day. If no daily limit
 * is set (`$organizer->userslotsdailymax` is empty or `0`), the function will always return `false`.
 *
 * @param stdClass $organizer The organizer instance containing the configuration.
 * @param int|null $userid The ID of the user to check, or null if checking by group.
 * @param int|null $groupid The ID of the group to check, or null if checking by user.
 *
 * @return bool True if the daily booking limit has been reached, false otherwise.
 *
 * @throws dml_exception If there is an error in database access operations.
 */
function organizer_userslotsdailylimitreached($organizer, $userid, $groupid) {
    global $DB;

    if ($organizer->userslotsdailymax) {
        $today = strtotime("-1 day");
        if ($groupid) {
            $params = ['groupid' => $groupid, 'organizerid' => $organizer->id, 'today' => $today];
            $query = 'SELECT COUNT(DISTINCT(s.id)) FROM {organizer_slot_appointments} a
                INNER JOIN {organizer_slots} s ON s.id = a.slotid
                WHERE a.groupid = :groupid AND s.organizerid = :organizerid
                AND a.registrationtime >= :today
                ';
        } else {
            $params = ['userid' => $userid, 'organizerid' => $organizer->id, 'today' => $today];
            $query = 'SELECT COUNT(a.id) FROM {organizer_slot_appointments} a
                INNER JOIN {organizer_slots} s ON s.id = a.slotid
                WHERE a.userid = :userid AND s.organizerid = :organizerid
                AND a.registrationtime >= :today
                ';
        }
        $bookingstoday = $DB->count_records_sql($query, $params);

        return $bookingstoday >= $organizer->userslotsdailymax;

    } else {

        return false;

    }

}
