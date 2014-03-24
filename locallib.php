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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * locallib.php
 *
 * @package       mod_organizer
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

    function sem_acquire($sem_id) {
        return flock($sem_id, LOCK_EX);
    }

    function sem_release($sem_id) {
        return flock($sem_id, LOCK_UN);
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

function organizer_check_collision($frame, $date, $events) {
    $collidingevents = array();
    foreach ($events as $event) {
        $framefrom = $frame['from'] + $date;
        $frameto = $frame['to'] + $date;
        $eventfrom = $event->timestart;
        $eventto = $eventfrom + $event->timeduration;

        if (between($framefrom, $eventfrom, $eventto) || between($frameto, $eventfrom, $eventto)
                || between($eventfrom, $framefrom, $frameto) || between($eventto, $framefrom, $frameto)
                || $framefrom == $eventfrom || $eventfrom == $eventto) {
            $collidingevents[] = $event;
        }
    }
    return $collidingevents;
}

function organizer_add_appointment_slots($data) {
    global $DB;

    $count = array();

    $cm = get_coursemodule_from_id('organizer', $data->id);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    if (!isset($data->finalslots)) {
        return $count;
    }
    
    $timezone = new DateTimeZone(date_default_timezone_get());


    foreach ($data->finalslots as $slot) {
        if (!$slot['selected']) {
            continue;
        }
        
        $transitions = $timezone->getTransitions($slot['date'],$slot['date']+$slot['from']);
        $dstoffset = 0;
        
        foreach($transitions as $trans){
        	$dstoffset += $trans['isdst'] ? ($trans['offset']) : (+$trans['offset']); 
        }

        $newslot = new stdClass();
        $newslot->maxparticipants = $data->maxparticipants;
        $newslot->isanonymous = isset($data->isanonymous) ? 1 : 0;
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

        for ($time = $slot['from']; $time + $data->duration <= $slot['to']; $time += $data->duration) {
        	$t = new DateTime();
        	$t->setTimestamp($slot['date']); // sets the day
        	       	
        	$h = $time / 3600 % 24;
        	$m = $time / 60 % 60;
        	$s = $time % 60;

        	$t->setTime($h, $m, $s); // set time of day
        	
        	$newslot->starttime = $t->getTimestamp();

            $newslot->id = $DB->insert_record('organizer_slots', $newslot);

            $newslot->eventid = organizer_add_event_slot($data->id, $newslot);
            $DB->update_record('organizer_slots', $newslot);
            unset($newslot->eventid);

            $count[] = $newslot->id;
        }
    }

    return $count;
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
    if($slot->comments != ""){
    	$event->description .= get_string('eventtemplatecomment','organizer',$slot->comments);
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
    	if(is_array($data->availablefrom)){
    		$slot->availablefrom = 0;
    	}else{
    		$slot->availablefrom = $data->availablefrom;
    	}
        $modified = true;
    }

    if ($data->mod_teachervisible == 1) {
        $slot->teachervisible = $data->teachervisible;
        $modified = true;
    }
    if ($data->mod_isanonymous == 1) {
        $slot->isanonymous = $data->isanonymous;
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

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
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

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
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

    // if student is registered to this slot, send a message
    $appointments = $DB->get_records('organizer_slot_appointments',array('slotid'=>$id));
    
    $notified_users = 0;
    
    if(count($appointments)>0){
    	// someone was allready registered to this slot

	    $slot = new organizer_slot($id);
	    
	    
	    foreach($appointments as $appointment){
	    	
	    	$reciever = intval($appointment->userid);
	    	
		    organizer_send_message($USER,$reciever,$slot,'slotdeleted_notify:student');
		    $notified_users++;
	    }
    }
    
    $DB->delete_records('event', array('id' => $eventid));
    $DB->delete_records('organizer_slot_appointments', array('slotid'=>$id));
    $DB->delete_records('organizer_slots', array('id' => $id));

    return $notified_users;
}

function organizer_register_appointment($slotid, $groupid = 0) {
    global $DB, $USER;

    $semaphore = sem_get($slotid);
    sem_acquire($semaphore);

    $slot = new organizer_slot($slotid);
    if ($slot->is_full()) {
        return false;
    }

    $ok = true;
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        foreach ($memberids as $memberid) {
            $ok ^= organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid);
        }
    } else {
        $ok ^= organizer_register_single_appointment($slotid, $USER->id);
    }

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    organizer_add_event_slot($cm->id, $slot);
    sem_release($semaphore);

    return $ok;
}

function organizer_register_single_appointment($slotid, $userid, $applicantid = 0, $groupid = 0) {
    global $DB;

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

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

    $appointment->id = $DB->insert_record('organizer_slot_appointments', $appointment);

    return $appointment->id;
}

function organizer_reregister_appointment($slotid, $groupid = 0) {
    global $DB, $USER;

    $semaphore = sem_get($slotid);
    sem_acquire($semaphore);

    $slot = new organizer_slot($slotid);
    if ($slot->is_full()) {
        return false;
    }

    $ok = true;
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        foreach ($memberids as $memberid) {
            $app = organizer_get_last_user_appointment($slot->organizerid, $memberid);
            $ok ^= organizer_register_single_appointment($slotid, $memberid, $USER->id, $groupid);
            if (isset($app)) {
                $ok ^= organizer_unregister_single_appointment($app->slotid, $memberid);
            }
        }
    } else {
        $app = organizer_get_last_user_appointment($slot->organizerid);
        $ok ^= organizer_register_single_appointment($slotid, $USER->id);
        if (isset($app)) {
            $ok ^= organizer_unregister_single_appointment($app->slotid, $USER->id);
        }
    }

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    organizer_add_event_slot($cm->id, $slot);
    sem_release($semaphore);

    return $ok;
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

function organizer_unregister_appointment($slotid, $groupid) {
    global $DB, $USER;

    $ok = true;
    if (organizer_is_group_mode()) {
        $memberids = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$groupid}");

        foreach ($memberids as $memberid) {
            $ok ^= organizer_unregister_single_appointment($slotid, $memberid);
        }
    } else {
        $ok ^= organizer_unregister_single_appointment($slotid, $USER->id);
    }

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    organizer_add_event_slot($cm->id, $slot); //FIXME!!!

    return $ok;
}

function organizer_unregister_single_appointment($slotid, $userid) {
    global $DB;

    $app = $DB->get_record('organizer_slot_appointments', array('userid' => $userid, 'slotid' => $slotid));

    // TODO: remove the participant from the list on the other event
    $DB->delete_records('event', array('id' => $app->eventid));

    if (isset($app->attended)) {
        return true;
    } else {
        $DB->delete_records('event', array('id' => $app->eventid));
        return $DB->delete_records('organizer_slot_appointments', array('id' => $app->id));
    }
}

function organizer_evaluate_slots($data) {
    global $DB;

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

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

    $id = optional_param('id', 0, PARAM_INT); // course_module ID, or
    $n = optional_param('o', 0, PARAM_INT); // organizer instance ID - it should be named as the first character of the module

    if ($id) {
        $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    } else if ($n) {
        $organizer = $DB->get_record('organizer', array('id' => $n), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $organizer->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    } else {
        print_error('You must specify a course_module ID or an instance ID');
    }

    $context = get_context_instance(CONTEXT_MODULE, $cm->id, MUST_EXIST);

    return array($cm, $course, $organizer, $context);
}

function organizer_get_course_module_data_new() {
    global $DB;

    $id = optional_param('id', 0, PARAM_INT); // course_module ID, or
    $n = optional_param('o', 0, PARAM_INT); // organizer instance ID - it should be named as the first character of the module

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
        print_error('You must specify a course_module ID or an instance ID');
    }

    $instance->context = get_context_instance(CONTEXT_MODULE, $instance->cm->id, MUST_EXIST);

    return $instance;
}

function organizer_is_group_mode() {
    global $DB;
    $id = optional_param('id', 0, PARAM_INT);
    $cm = get_coursemodule_from_id('organizer', $id, 0, false, MUST_EXIST);
    $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
    return $organizer->isgrouporganizer;
}

function organizer_fetch_my_group() {
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
}

function organizer_log_action($action, $logurl, $instance) {
    add_to_log($instance->course->id, 'organizer', $action, "{$logurl}", $instance->organizer->name, $instance->cm->id);
}
