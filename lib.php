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
 * lib.php
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

defined('MOODLE_INTERNAL') || die();

define('ORGANIZER_MESSAGES_NONE', 0);
define('ORGANIZER_MESSAGES_RE_UNREG', 1);
define('ORGANIZER_MESSAGES_ALL', 2);
define('ORGANIZER_DELETE_EVENTS', 1);

define('ORGANIZER_VISIBILITY_ALL', 0);
define('ORGANIZER_VISIBILITY_ANONYMOUS', 1);
define('ORGANIZER_VISIBILITY_SLOT', 2);

define('ORGANIZER_GROUPMODE_NOGROUPS', 0);
define('ORGANIZER_GROUPMODE_EXISTINGGROUPS', 1);
define('ORGANIZER_GROUPMODE_NEWGROUPSLOT', 2);
define('ORGANIZER_GROUPMODE_NEWGROUPBOOKING', 3);

define('ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT', 'Appointment');
define('ORGANIZER_CALENDAR_EVENTTYPE_SLOT', 'Slot');
define('ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE', 'Instance');

define('EIGHTDAYS', 691200);
define('ORGANIZER_PRINTSLOTUSERFIELDS', 9);

require_once(dirname(__FILE__) . '/slotlib.php');

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param  object $organizer An object from the form in mod_form.php
 * @return int The id of the newly inserted organizer record
 */
function organizer_add_instance($organizer) {
    global $DB;

    $organizer->timemodified = time();
    if (isset($organizer->allowregistrationsfromdate) && $organizer->allowregistrationsfromdate == 0) {
        $organizer->allowregistrationsfromdate = null;
    }

    if (isset($organizer->duedate) && $organizer->duedate == 0) {
        $organizer->duedate = null;
    }

    $organizer->id = $DB->insert_record('organizer', $organizer);

    organizer_grade_item_update($organizer);

    organizer_change_event_instance($organizer);

    $_SESSION["organizer_new_instance"] = $organizer->id;

    return $organizer->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param  object $organizer An object from the form in mod_form.php
 * @return boolean Success/Fail
 */
function organizer_update_instance($organizer) {
    global $DB;

    $organizer->id = $organizer->instance;
    $organizer->timemodified = time();

    if (isset($organizer->allowregistrationsfromdate) && $organizer->allowregistrationsfromdate == 0) {
        $organizer->allowregistrationsfromdate = null;
    }

    if (isset($organizer->duedate) && $organizer->duedate == 0) {
        $organizer->duedate = null;
    }

    $newname = $organizer->name;
    $oldname = $DB->get_field('organizer', 'name', array('id' => $organizer->id));

    organizer_grade_item_update($organizer);

    $DB->update_record('organizer', $organizer);

    if (isset($organizer->queue) && $organizer->queue == 0) {
        organizer_remove_waitingqueueentries($organizer);
    }

    $params = array('modulename' => 'organizer', 'instance' => $organizer->id,
        'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE);

    $query = 'SELECT id
              FROM {event}
              WHERE modulename = :modulename
              AND instance = :instance
              AND eventtype = :eventtype';

    $eventids = $DB->get_fieldset_sql($query, $params);

    organizer_change_event_instance($organizer, $eventids);

    if ($oldname != $newname) {
        organizer_change_eventnames($organizer->id, $oldname, $newname);
    }

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param  int $id Id of the module instance
 * @return boolean Success/Failure
 */
function organizer_delete_instance($id) {
    global $DB;

    if (!$organizer = $DB->get_record('organizer', array('id' => $id))) {
        return false;
    }

    $slots = $DB->get_records('organizer_slots', array('organizerid' => $id));
    foreach ($slots as $slot) {
        $DB->delete_records('organizer_slot_trainer', array('slotid' => $slot->id));
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
        foreach ($apps as $app) {
            if (ORGANIZER_DELETE_EVENTS) {
                $DB->delete_records('event', array('id' => $app->eventid));
                $DB->delete_records('event', array('uuid' => $app->id,
                    'modulename' => 'organizer', 'instance' => $organizer->id,
                    'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT));
            }
            $DB->delete_records('organizer_slot_appointments', array('id' => $app->id));
        } // Foreach app.

        if (ORGANIZER_DELETE_EVENTS) {
            $DB->delete_records('event', array('uuid' => $slot->id,
                'modulename' => 'organizer', 'instance' => $organizer->id, 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_SLOT));
        }
        $DB->delete_records('organizer_slots', array('id' => $slot->id));
    } // Foreach slot.

    if (ORGANIZER_DELETE_EVENTS) {
        $DB->delete_records('event', array(
                'modulename' => 'organizer', 'instance' => $organizer->id, 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE));
    }

    $DB->delete_records('organizer', array('id' => $organizer->id));

    organizer_grade_item_update($organizer);

    return true;
}

/**
 * Return a small object with summary information about what a
 * user has done with a given particular instance of this module
 * Used for user activity reports.
 * $return->time = the time they did it
 * $return->info = a short text description
 *
 * @return null
 * @todo   Finish documenting this function
 */
function organizer_user_outline($course, $user, $mod, $organizer) {
    // Tscpr: do we need this function if it's returning just nothing?
    $return = new stdClass;
    $return->time = time();
    $return->info = '';
    return $return;
}

/**
 * Print a detailed representation of what a user has done with
 * a given particular instance of this module, for user activity reports.
 *
 * @return boolean
 * @todo   Finish documenting this function
 */
function organizer_user_complete($course, $user, $mod, $organizer) {
    // Tscpr: do we need this function if we don't support completions?
    return false;
}

/**
 * Given a course and a time, this module should find recent activity
 * that has occurred in organizer activities and print it out.
 * Return true if there was output, or false is there was none.
 *
 * @return boolean
 * @todo   Finish documenting this function
 */
function organizer_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

function organizer_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'organizerheader', get_string('modulenameplural', 'organizer'));
    $mform->addElement('checkbox', 'reset_organizer_all', get_string('resetorganizerall', 'organizer'));
    $mform->addElement('checkbox', 'delete_organizer_grades', get_string('deleteorganizergrades', 'organizer'));
}

function organizer_reset_userdata($data) {
    global $DB;

    if (!$DB->count_records('organizer', array('course' => $data->courseid))) {
        return array();
    }

    $componentstr = get_string('modulenameplural', 'organizer');
    $status = array();

    if (isset($data->reset_organizer_all)) {
        $params = array('courseid' => $data->courseid);

        $slotquery = 'SELECT s.*
                    FROM {organizer_slots} s
                    INNER JOIN {organizer} m ON s.organizerid = m.id
                    WHERE m.course = :courseid';

        $appquery = 'SELECT a.*
                    FROM {organizer_slot_appointments} a
                    INNER JOIN {organizer_slots} s ON a.slotid = s.id
                    INNER JOIN {organizer} m ON s.organizerid = m.id
                    WHERE m.course = :courseid';

        $slots = $DB->get_records_sql($slotquery, $params);
        $appointments = $DB->get_records_sql($appquery, $params);

        $ok = true;

        foreach ($slots as $slot) {
            $DB->delete_records('event', array('id' => $slot->eventid));
            // Tscpr: Petr Skoda told us that $DB->delete_records will throw an exeption if it fails, otherwise it always succeeds.
            $ok &= $DB->delete_records('organizer_slots', array('id' => $slot->id));
        }

        foreach ($appointments as $appointment) {
            $DB->delete_records('event', array('id' => $appointment->eventid));
            // Tscpr: Petr Skoda told us that $DB->delete_records will throw an exeption if it fails, otherwise it always succeeds.
            $ok &= $DB->delete_records('organizer_slot_appointments', array('id' => $appointment->id));
        }

        $status[] = array('component' => $componentstr, 'item' => get_string('reset_organizer_all', 'organizer'),
                'error' => !$ok);
    }

    if (isset($data->delete_organizer_grades)) {
        $ok = organizer_reset_gradebook($data->courseid);
        $status[] = array('component' => $componentstr, 'item' => get_string('delete_organizer_grades', 'organizer'),
                'error' => !$ok);
    }

    if ($data->timeshift) {
        $ok = shift_course_mod_dates(
            'organizer',
            array('allowregistrationsfromdate', 'duedate'), $data->timeshift, $data->courseid
        );
        $status[] = array('component' => $componentstr, 'item' => get_string('timeshift', 'organizer'),
                'error' => !$ok);
    }

    return $status;
}

function organizer_reset_gradebook($courseid) {
    global $CFG, $DB;

    $params = array('courseid' => $courseid);

    $sql = 'SELECT a.*, cm.idnumber as cmidnumber, a.course as courseid
              FROM {organizer} a, {course_modules} cm, {modules} m
             WHERE m.name=\'organizer\' AND m.id=cm.module AND cm.instance=a.id AND a.course=:courseid';

    $ok = true;
    if ($assignments = $DB->get_records_sql($sql, $params)) {
        foreach ($assignments as $assignment) {
            $status = organizer_grade_item_update($assignment, 'reset');
            $ok &= $status == GRADE_UPDATE_OK;
        }
    }

    return $ok;
}

function organizer_get_user_grade($organizer, $userid = 0) {
    global $DB;

    $params = array('organizerid' => $organizer->id, 'userid' => $userid);
    if ($userid) {
        $query = 'SELECT
                    a.id AS id,
                    a.userid AS userid,
                    a.grade AS rawgrade,
                    s.starttime AS dategraded,
                    s.starttime AS datesubmitted,
                    a.feedback AS feedback
                FROM {organizer_slot_appointments} a
                INNER JOIN {organizer_slots} s ON a.slotid = s.id
                WHERE s.organizerid = :organizerid AND a.userid = :userid
                ORDER BY id DESC';
        $arr = $DB->get_records_sql($query, $params);
        $result = reset($arr);
        if ($result) {
            return array($userid => $result);
        } else {
            return array();
        }
    } else {
        return array();
    }
}

function organizer_update_grades($organizer, $userid = 0) {
    global $CFG;
    include_once($CFG->libdir . '/gradelib.php');

    if ($organizer->grade == 0) {
        return organizer_grade_item_update($organizer);
    } else {
        if ($grades = organizer_get_user_grade($organizer, $userid)) {
            foreach ($grades as $key => $value) {
                if ($value->rawgrade == -1) {
                    $grades[$key]->rawgrade = null;
                }
            }
            return organizer_grade_item_update($organizer, $grades);
        } else {
            return organizer_grade_item_update($organizer);
        }
    }
}

function organizer_grade_item_update($organizer, $grades = null) {
    global $CFG;
    include_once($CFG->libdir . '/gradelib.php');

    if (!isset($organizer->courseid)) {
        $organizer->courseid = $organizer->course;
    }

    if (isset($organizer->cmidnumber)) {
        $params = array('itemname' => $organizer->name, 'idnumber' => $organizer->cmidnumber);
    } else {
        $params = array('itemname' => $organizer->name);
    }

    if (isset($organizer->grade) && $organizer->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $organizer->grade;
        $params['grademin'] = 0;
    } else if (isset($organizer->grade) && $organizer->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid']   = -$organizer->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('mod/organizer', $organizer->courseid, 'mod', 'organizer', $organizer->id, 0, $grades, $params);
}

function organizer_display_grade($organizer, $grade, $userid) {
    global $DB;
    $nograde = get_string('nograde');
    static $scalegrades = array();   // Cache scales for each organizer - they might have different scales!!

    if ($organizer->grade >= 0) {    // Normal number.
        if ($grade == -1 || $grade == null) {
            $finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $userid);
            if ($finalgrade !== false) {
                      return organizer_display_finalgrade($finalgrade);
            } else {
                      return $nograde;
            }
        } else {
            $returnstr = organizer_clean_num($grade) . '/' . organizer_clean_num($organizer->grade);
            $finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $userid);
            if ($finalgrade !== false) {
                      $returnstr .= organizer_display_finalgrade($finalgrade);
            }
            return $returnstr;
        }
    } else {    // Scale.
        if (empty($scalegrades[$organizer->id])) {
            if ($scale = $DB->get_record('scale', array('id' => -($organizer->grade)))) {
                $scalegrades[$organizer->id] = make_menu_from_list($scale->scale);
            } else {
                return $nograde;
            }
        }
        $finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $userid);
        if ($finalgrade !== false) {
            if (isset($scalegrades[$organizer->id][intval($finalgrade)])) {
                return organizer_display_finalgrade($scalegrades[$organizer->id][intval($finalgrade)]);
            } else {
                return $nograde;
            }
        }
        if (isset($scalegrades[$organizer->id][intval($grade)])) {
            return $scalegrades[$organizer->id][intval($grade)];
        } else {
            return $nograde;
        }
    }
}

function organizer_display_finalgrade($finalgrade) {
    $nograde = get_string('nograde');

    if ($finalgrade !== false) {
        return html_writer::span('(' . $finalgrade . ')', 'finalgrade', array('title' => get_string('finalgrade', 'organizer')));
    } else {
        return $nograde;
    }
}

function organizer_get_finalgrade_overwritten($organizerid, $userid) {
    global $DB;

    $params = array('organizerid' => $organizerid, 'userid' => $userid);
    $query = "SELECT gg.rawgrade, gg.finalgrade FROM {grade_items} gi
			inner join {grade_grades} gg on gg.itemid = gi.id
			where gi.itemtype = 'mod' and gi.itemmodule = 'organizer'
			and gi.iteminstance = :organizerid and gg.userid = :userid";
    if ($grades = $DB->get_record_sql($query, $params)) {
        if (is_null($grades->rawgrade)) {
            $grades->rawgrade = 0;
        }
        if (is_null($grades->finalgrade)) {
            $grades->finalgrade = 0;
        }
        if ($grades->rawgrade !== $grades->finalgrade) {
            return organizer_clean_num($grades->finalgrade);
        } else {
            return false;
        }
    } else {
        return false;
    }
}

// Tscpr: we can strip the trailing _organizer in this function name...
function organizer_make_grades_menu_organizer($gradingtype) {
    global $DB;

    $grades = array();
    if ($gradingtype < 0) {
        if ($scale = $DB->get_record('scale', array('id' => (-$gradingtype)))) {
            $menu = make_menu_from_list($scale->scale);
            $menu['0'] = get_string('nograde');
            return $menu;
        }
    } else if ($gradingtype > 0) {
        $grades['-1'] = get_string('nograde');
        for ($i = $gradingtype; $i >= 0; $i--) {
            $grades[$i] = organizer_clean_num($i) . ' / ' . organizer_clean_num($gradingtype);
        }
        return $grades;
    }
    return $grades;
}

function organizer_clean_num($num) {
    $pos = strpos($num, '.');
    if ($pos === false) { // It is integer number.
        return $num;
    } else { // It is decimal number.
        return rtrim(rtrim($num, '0'), '.');
    }
}

function organizer_get_last_group_appointment($organizer, $groupid) {
    global $DB;
    $params = array('groupid' => $groupid, 'organizerid' => $organizer->id);
    $groupapps = $DB->get_records_sql(
        'SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE a.groupid = :groupid AND s.organizerid = :organizerid
            ORDER BY a.id DESC', $params
    );

    $app = null;

    $appcount = 0;
    $someoneattended = 0;
    foreach ($groupapps as $groupapp) {
        if ($groupapp->groupid == $groupid) {
            $app = $groupapp;
        }
        if (isset($groupapp->attended)) {
            $appcount++;
            if ($groupapp->attended == 1) {
                $someoneattended = 1;
            }
        }
    }

    if ($app) {
        $app->attended = ($appcount == count($groupapps)) ? $someoneattended : null;
    }

    return $app;
}

function organizer_get_counters($organizer) {
    global $DB;

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
        $params = array('groupingid' => $cm->groupingid);
        $query = 'SELECT {groups}.* FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid
                ORDER BY {groups}.name ASC';
        $groups = $DB->get_records_sql($query, $params);

        $attended = 0;
        $registered = 0;
        foreach ($groups as $group) {
            $app = organizer_get_last_group_appointment($organizer, $group->id);
            if ($app && $app->attended == 1) {
                $attended++;
            } else if ($app && !isset($app->attended)) {
                $registered++;
            }
        }
        $total = count($groups);

        $a = new stdClass();
        $a->registered = $registered;
        $a->attended = $attended;
        $a->total = $total;
    } else {
        $course = $DB->get_record('course', array('id' => $organizer->course), '*', MUST_EXIST);
        $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
        $context = context_module::instance($cm->id, MUST_EXIST);

        $students = get_enrolled_users($context, 'mod/organizer:register');

        $attended = 0;
        $registered = 0;
        foreach ($students as $student) {
            $app = organizer_get_last_user_appointment($organizer, $student->id);
            if ($app && $app->attended == 1) {
                $attended++;
            } else if ($app && !isset($app->attended)) {
                $registered++;
            }
        }
        $total = count($students);

        $a = new stdClass();
        $a->registered = $registered;
        $a->attended = $attended;
        $a->total = $total;
    }

    return $a;
}

function organizer_get_eventaction_instance_trainer($organizer) {
    $a = organizer_get_counters($organizer);
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        if ($a->attended == 0) {
            return get_string('mymoodle_registered_group_short', 'organizer', $a);
        } else {
            return get_string('mymoodle_attended_group_short', 'organizer', $a);
        }
    } else {
        if ($a->attended == 0) {
            return get_string('mymoodle_registered_short', 'organizer', $a);
        } else {
            return get_string('mymoodle_attended_short', 'organizer', $a);
        }
    }
}

function organizer_get_eventaction_instance_student($organizer) {

    $app = organizer_get_next_user_appointment($organizer);
    if ($app) {
        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $group = organizer_fetch_group($organizer);
            $a = new stdClass();
            $a->groupname = $group->name;
            $str = get_string('mymoodle_reg_slot_group', 'organizer', $a);
        } else {
            $str = get_string('mymoodle_reg_slot', 'organizer');
        }
    } else {
        if (!$app = organizer_get_last_user_appointment($organizer)) {
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                $group = organizer_fetch_group($organizer);
                $a = new stdClass();
                $a->groupname = $group->name;
                $str = get_string('mymoodle_no_reg_slot_group', 'organizer', $a);
            } else {
                $str = get_string('mymoodle_no_reg_slot', 'organizer');
            }
        } else {
            $regslot = get_string('mymoodle_reg_slot', 'organizer');
            $str = " " . $regslot;
            if (isset($organizer->duedate)) {
                $a = new stdClass();
                $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
                $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
                if ($organizer->duedate > time()) {
                    $orgexpires = get_string('mymoodle_organizer_expires', 'organizer', $a);
                } else {
                    $orgexpires = get_string('mymoodle_organizer_expired', 'organizer', $a);
                }
                $str .= " " . $orgexpires;
            }
        }
    }

    return $str;
}

function organizer_fetch_group($organizer, $userid = null) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizer));
    }

    $usergrouparrays = groups_get_user_groups($organizer->course, $userid);
    $usergroups = reset($usergrouparrays);
    $usergroup = reset($usergroups);
    $group = groups_get_group($usergroup);

    return $group;
}
/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return bool|mixed
 * @throws coding_exception
 * @throws dml_exception
 **/
function organizer_cron() {
    global $DB;

    include_once(__DIR__ . '/messaging.php');
    include_once(__DIR__ . '/locallib.php');
    $now = time();

    /* Handle deleted users in slots */
    $sql = <<<SQL
SELECT
    s.id,
    u.id AS userid,
    s.id AS slotid,
    s.organizerid,
    o.isgrouporganizer,
    o.queue
FROM
    {organizer_slots} s
JOIN
    {organizer_slot_appointments} sa ON sa.slotid = s.id
JOIN
    {organizer} o ON o.id = s.organizerid
JOIN
    {user} u ON u.id = sa.userid
WHERE
    (u.deleted = 1 OR u.suspended = 1) AND
    s.starttime >= :now
SQL;

    $deletedusers = $DB->get_records_sql($sql, ['now' => $now]);
    foreach ($deletedusers as $du) {
        $org = new stdClass;
        $org->id = $du->organizerid;
        $org->isgrouporganizer = $du->isgrouporganizer;
        organizer_unregister_single_appointment($du->slotid, $du->userid, $org);
        if ($du->isgrouporganizer != ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            if ($du->queue) {
                $slotx = new organizer_slot($du->slotid);
                if ($next = $slotx->get_next_in_queue()) {
                    organizer_register_appointment($du->slotid, 0, $next->userid, true);
                    organizer_delete_from_queue($du->slotid, $next->userid);
                }
            }
        }
    }
    // Handle queued deleted or suspended users!
    $sql = <<<SQL
SELECT
    s.id,
    u.id AS userid,
    s.id AS slotid,
    s.organizerid,
    o.isgrouporganizer,
    o.queue
FROM
    {organizer_slots} s
JOIN
    {organizer_slot_queues} sa ON sa.slotid = s.id
JOIN
    {organizer} o ON o.id = s.organizerid
JOIN
    {user} u ON u.id = sa.userid
WHERE
    (u.deleted = 1 OR u.suspended = 1) AND
    s.starttime >= :now
SQL;

    $deletedusers = $DB->get_records_sql($sql, ['now' => $now]);
    foreach ($deletedusers as $du) {
        organizer_delete_from_queue($du->slotid, $du->userid);
    }

    $success = true;

    $params = array('now' => $now, 'now2' => $now);
    $appsquery = "SELECT a.*, s.location, s.starttime, s.organizerid, s.teachervisible FROM {organizer_slot_appointments} a
        INNER JOIN {organizer_slots} s ON a.slotid = s.id WHERE
        s.starttime - s.notificationtime < :now AND s.starttime > :now2 AND
        a.notified = 0";

    $apps = $DB->get_records_sql($appsquery, $params);
    foreach ($apps as $app) {
        $customdata = ['showsendername' => intval($app->teachervisible == 1)];
        $success &= organizer_send_message_from_trainer(intval($app->userid), $app, 'appointment_reminder_student', null, $customdata);
    }

    if (empty($apps)) {
        $ids = array(0);
    } else {
        $ids = array_keys($apps);
    }
    list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
    $DB->execute("UPDATE {organizer_slot_appointments} SET notified = 1 WHERE id $insql", $inparams);

    $organizerconfig = get_config('organizer');
    if ($organizerconfig->digest == 'never') {
        return $success;
    }
    $time = $organizerconfig->digest + mktime(0, 0, 0, date("m"), date("d"), date("Y"));
    if (abs(time() - $time) >= 300) {
        return $success;
    }

    $params['tomorrowstart'] = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
    $params['tomorrowend'] = mktime(0, 0, 0, date("m"), date("d") + 2, date("Y"));

    $slotsquery = "SELECT DISTINCT t.teacherid FROM {organizer_slots} s INNER JOIN {organizer_slot_trainer} t ON s.id = t.slotid
            WHERE s.starttime >= :tomorrowstart AND
            s.starttime < :tomorrowend AND
            s.notified = 0";

    $trainerids = $DB->get_fieldset_sql($slotsquery, $params);

    if (empty($trainerids)) {
        $trainerids = array(0);
    }

    list($insql, $inparams) = $DB->get_in_or_equal($trainerids, SQL_PARAMS_NAMED);

    $slotsquery = "SELECT s.*, t.trainerid
        FROM {organizer_slots} s INNER JOIN {organizer_slot_trainer} t ON s.id = t.slotid
        WHERE s.starttime >= :tomorrowstart AND
        s.starttime < :tomorrowend AND
        s.notified = 0 AND
        t.trainerid $insql";

    $params = array_merge($params, $inparams);

    $slots = $DB->get_records_sql($slotsquery, $params);

    foreach ($trainerids as $trainerid) {
        $digest = '';

        $found = false;
        foreach ($slots as $slot) {
            if ($slot->trainerid == $trainerid) {
                $date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
                $time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
                $digest .= "$time @ $slot->location\n";
                $found = true;
            }
        }

        if (empty($slots)) {
            $ids = array(0);
        } else {
            $ids = array_keys($slots);
        }

        if ($found) {
            $success &= $thissuccess = organizer_send_message(
                intval($trainerid), intval($trainerid), reset($slots),
                'appointment_reminder_teacher', $digest
            );

            if ($thissuccess) {
                list($insql, ) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
                $DB->execute("UPDATE {organizer_slots} SET notified = 1 WHERE id $insql");
            }
        }
    }
    return $success;
}


function organizer_create_digest($trainerid) {
    include_once(dirname(__FILE__) . '/messaging.php');
    global $DB;
    $now = time();

    $success = true;

    $params = array('now' => $now, 'trainerid' => $trainerid);

    $slotsquery = 'SELECT s.*, t.trainerid, t.slotid FROM {organizer_slots} s INNER JOIN {organizer_slot_trainer} t
            ON s.id = t.slotid
            WHERE s.starttime - s.notificationtime < :now AND
            s.notified = 0 AND t.trainerid = :trainerid';

    $digest = '';

    $slots = $DB->get_records_sql($slotsquery, $params);
    foreach ($slots as $slot) {
        if (isset($slot)) {
            $date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
            $time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
        }
        $digest .= $date.', '.$time.' @ '.$slot->location.'; ';
        $DB->execute("UPDATE {organizer_slots} SET notified = 1 WHERE id = $slot->slotid");
    }

    $success = organizer_send_message($trainerid, $trainerid, $slot, 'appointment_reminder_teacher:digest', $digest);

    return $success;
}

/**
 * Must return an array of users who are participants for a given instance
 * of organizer. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param  int $organizerid ID of an instance of this module
 * @return boolean|array false if no participants, array of objects otherwise
 */
function organizer_get_participants($organizerid) {
    return false;
}

/**
 * This function returns if a scale is being used by one organizer
 * if it has support for grading and scales. Commented code should be
 * modified if necessary. See forum, glossary or journal modules
 * as reference.
 *
 * @param  int $organizerid ID of an instance of this module
 * @return mixed
 */
function organizer_scale_used($organizerid, $scaleid) {
    global $DB;

    if ($organizerid && $scaleid && $DB->record_exists('organizer', array('id' => $organizerid, 'grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Checks if scale is being used by any instance of organizer.
 * This function was added in 1.9
 *
 * This is used to find out if scale used anywhere
 *
 * @param  $scaleid int
 * @return boolean True if the scale is used by any organizer
 */
function organizer_scale_used_anywhere($scaleid) {
    global $DB;

    if ($scaleid && $DB->record_exists('organizer', array('grade' => -$scaleid))) {
        return true;
    } else {
        return false;
    }
}

/**
 * Execute post-uninstall custom actions for the module
 * This function was added in 1.9
 *
 * @return boolean true if success, false on error
 */
function organizer_uninstall() {
    return true;
}

function organizer_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
        return true;
        case FEATURE_GROUPINGS:
        return true;
        case FEATURE_GROUPMEMBERSONLY:
        return true;
        case FEATURE_MOD_INTRO:
        return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        return true;
        case FEATURE_GRADE_HAS_GRADE:
        return true;
        case FEATURE_GRADE_OUTCOMES:
        return true;
        case FEATURE_BACKUP_MOODLE2:
        return true;
        case FEATURE_SHOW_DESCRIPTION:
        return true;
        default:
        return null;
    }
}

/**
 * Add a get_coursemodule_info function in case any organizer type wants to add 'extra' information
 * for the course (see resource).
 *
 * Given a course_module object, this function returns any "extra" information that may be needed
 * when printing this activity in a course listing.  See get_array_of_activities() in course/lib.php.
 *
 * @param  stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function organizer_get_coursemodule_info($coursemodule) {
    global $DB;

    $dbparams = array('id' => $coursemodule->instance);
    $fields = 'id, name, alwaysshowdescription, allowregistrationsfromdate, intro, introformat, duedate';
    if (! $organizer = $DB->get_record('organizer', $dbparams, $fields)) {
        return false;
    }

    $result = new cached_cm_info();
    $result->name = $organizer->name;
    if ($coursemodule->showdescription) {
        if ($organizer->alwaysshowdescription || time() > $organizer->allowregistrationsfromdate) {
            // Convert intro to html. Do not filter cached version, filters run at display time.
            $result->content = format_module_intro('organizer', $organizer, $coursemodule->id, false);
        }
    }

    // Populate some other values that can be used in calendar or on dashboard.
    if ($organizer->allowregistrationsfromdate) {
        $result->customdata['allowregistrationsfromdate'] = $organizer->allowregistrationsfromdate;
    }
    if ($organizer->duedate) {
        $result->customdata['duedate'] = $organizer->duedate;
    }

    return $result;
}

function organizer_remove_waitingqueueentries($organizer) {
    global $DB;

    $query = "slotid in (select id from {organizer_slots} where organizerid = ".$organizer->id.")";
    $ok = $DB->delete_records_select('organizer_slot_queues', $query);
    return $ok;
}


/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 * HOWEVER, due to significant performance issues, it always returns null.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param  calendar_event                $event
 * @param  \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_organizer_core_calendar_provide_event_action(calendar_event $event,
    \core_calendar\action_factory $factory
) {
    // Due to significant performance issues, it always returns null!
    return null;
}

/**
 * Is the event visible?
 *
 * This is used to determine global visibility of an event in all places throughout Moodle.
 *
 * @param  calendar_event $event
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function mod_organizer_core_calendar_is_event_visible(calendar_event $event, $userid = 0) {
    global $USER, $DB;
    $props = $event->properties();

    $organizer = $DB->get_record('organizer', array('id' => $props->instance), '*');

    if ($organizer == false) {
        return false;
    }

    if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT) {
        $courseisvisible = $DB->get_field('course', 'visible', array('id' => $props->courseid));
        if (instance_is_visible('organizer', $organizer) && $courseisvisible) {
            if (empty($userid)) {
                $userid = $USER->id;
            }
            if (!$userid) {
                $isvisible = false;
            } else {
                $useridevent = $DB->get_field('event', 'userid', array('id' => $props->id));
                if ($userid != $useridevent) {
                    $isvisible = false;
                } else {
                    $isvisible = true;
                }
            }
        } else {
            $isvisible = false;
        }
    } else if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_SLOT) {
        $cm = get_fast_modinfo($event->courseid)->instances['organizer'][$event->instance];
        $context = context_module::instance($cm->id, MUST_EXIST);
        if (has_capability('mod/organizer:viewallslots', $context)) {
            $isvisible = true;
        } else {
            $isvisible = false;
        }
    } else if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE) {
        $cm = get_fast_modinfo($event->courseid)->instances['organizer'][$event->instance];
        $context = context_module::instance($cm->id, MUST_EXIST);
        if (!is_enrolled($context)) {
            $isvisible = false;
        } else {
            if (has_capability('mod/organizer:viewallslots', $context)) {
                $a = organizer_get_counters($organizer);
                if ($a->total == 0) {
                    $isvisible = true;
                } else if ($organizer->grade != 0 && $a->attended < $a->total) { // If grading is active.
                    $isvisible = true;
                } else if ($a->registered < $a->total) {
                    $isvisible = true;
                } else {
                    $isvisible = false;
                }
            } else if (has_capability('mod/organizer:viewstudentview', $context)) {
                $courseisvisible = $DB->get_field('course', 'visible', array('id' => $props->courseid));
                if (!(instance_is_visible('organizer', $organizer) && $courseisvisible)) {
                    $isvisible = false;
                } else {
                    if (organizer_get_last_user_appointment($organizer)) {
                        $isvisible = false;
                    } else {
                        $isvisible = true;
                    }
                }
            }
        }
    } else {
        $isvisible = false;
    }

    return $isvisible;
}

function organizer_change_event_instance($organizer, $eventids = array()) {
    global $USER;

    $eventtitle = organizer_filter_text($organizer->name);
    $eventdescription = $organizer->intro;

    if ($eventids) {
        $startdate = $organizer->allowregistrationsfromdate ? $organizer->allowregistrationsfromdate : 0;
        $duration = $organizer->duedate ? $organizer->duedate - $startdate : 0;
        return organizer_change_calendarevent(
            $eventids, $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE,
            $USER->id, $startdate, $duration, 0, $organizer->id
        );
    } else {
        $startdate = $organizer->allowregistrationsfromdate ? $organizer->allowregistrationsfromdate : 0;
        $duration = $organizer->duedate ? $organizer->duedate - $startdate : 0;
        return organizer_create_calendarevent(
            $organizer, $eventtitle, $eventdescription, ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE,
            $USER->id, $startdate, $duration, 0, $organizer->id
        );
    }
}

function organizer_create_calendarevent($organizer, $eventtitle, $eventdescription, $eventtype, $userid,
    $timestart, $duration, $group, $uuid
) {
    global $CFG, $DB;

    include_once($CFG->dirroot.'/calendar/lib.php');

    $event = new stdClass();
    $event->eventtype = $eventtype;
    $intro = strip_pluginfile_content($eventdescription);
    $event->description = array(
            'text' => $intro,
            'format' => $organizer->introformat
    );
    $event->modulename = 'organizer';
    $event->instance = $organizer->id;
    $event->visible = 1;
    if ($uuid) {
        $event->uuid = $uuid;
    }

    if ($eventtype == ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE) {   // If type is instance.
        if ($timestart) {
            $event->type = CALENDAR_EVENT_TYPE_STANDARD;
            $event->timestart = $timestart;
            $event->timesort = $timestart;
            $event->timeduration = 0;
            $event->name = get_string('allowsubmissionsfromdate', 'organizer') . ": " . $eventtitle;
            $event->courseid = $organizer->course;
            calendar_event::create($event, false);
            unset($event->id);
        }
        if ($duration) {
            $event->type = CALENDAR_EVENT_TYPE_ACTION;
            $event->timestart = $timestart + $duration;
            $event->timesort = $timestart + $duration;
            $event->timeduration = 0;
            $event->name = get_string('allowsubmissionstodate', 'organizer') . ": " . $eventtitle;
            $event->courseid = $organizer->course;
            calendar_event::create($event, false);
        }
        return false;
    } else {  // Appointments or slots.
        $event->type = CALENDAR_EVENT_TYPE_ACTION;
        if ($group) {
            $event->groupid = $group;
        }
        $event->userid = $userid;
        $event->timestart = $timestart;
        $event->timesort = $timestart;
        $event->timeduration = $duration;
        $event->name = $eventtitle;
        $event->courseid = $organizer->course;
        calendar_event::create($event, false);
    }

    // If new appointment: Delete already existent slot event, if there is one.
    if ($uuid && $eventtype == ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT) {
        $DB->delete_records('event', array ('modulename' => 'organizer', 'eventtype' => 'Slot', 'uuid' => $uuid));
    }

    return $event->id;
}

function organizer_change_calendarevent($eventids, $organizer, $eventtitle, $eventdescription, $eventtype, $userid,
    $timestart, $duration, $group, $uuid
) {
    global $CFG;

    include_once($CFG->dirroot.'/calendar/lib.php');

    $data = new stdClass();
    $data->eventtype = $eventtype;
    $intro = strip_pluginfile_content($eventdescription);
    $data->description = array(
            'text' => $intro,
            'format' => $organizer->introformat
    );
    if ($uuid) {
        $data->uuid = $uuid;
    }

    if ($eventtype == ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE) {   // If event is type instance.
        $event = calendar_event::load($eventids[0]);
        if ($timestart == 0) {
            $event->delete();
        } else {
            $data->timestart = $timestart;
            $data->timesort = $timestart;
            $data->timeduration = 0;
            $data->name = get_string('allowsubmissionsfromdate', 'organizer') . ": " . $eventtitle;
            $event->update($data, false);
        }
        if (isset($eventids[1])) {
            $event2 = calendar_event::load($eventids[1]);
            if ($duration == 0) {
                $event2->delete();
            } else {
                $timedue = (int)$timestart + (int)$duration;
                $data->timestart = $timedue;
                $data->timesort = $timedue;
                $data->timeduration = 0;
                $data->name = get_string('allowsubmissionstodate', 'organizer') . ": " . $eventtitle;
                $event2->update($data, false);
            }
        }
    } else { // If eventtype appointment or slot.
        $event = calendar_event::load($eventids[0]);
        if ($group) {
            $data->groupid = $group;
        }
        $data->userid = $userid;
        $data->timestart = $timestart;
        $data->timesort = $timestart;
        $data->timeduration = $duration;
        $data->name = $eventtitle;
        $event->update($data, false);
    }

    return true;
}

function organizer_change_eventnames($organizerid, $oldname, $newname) {
    global $DB;

    $record = new stdClass();
    $params = array('organizerid' => $organizerid, 'modulename' => 'organizer',
        'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE);
    $query = 'SELECT e.id, e.name, e.description
			  FROM {event} e
			  WHERE e.instance = :organizerid AND e.modulename = :modulename
			  AND e.eventtype <> :eventtype';

    $rs = $DB->get_recordset_sql($query, $params);
    foreach ($rs as $recordset) {
        $record->id = $recordset->id;
        $name = $recordset->name;
        $name = str_replace('/ ' . $oldname . ':', '/ ' . $newname . ':', $name);
        $record->name = $name;
        $description = $recordset->description;
        $description = str_replace('/ ' . $oldname . ':', '/ ' . $newname . ':', $description);
        $record->description = $description;
        $DB->update_record('event', $record);
    }
    $rs->close();
}
