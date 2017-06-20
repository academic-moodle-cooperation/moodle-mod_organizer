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
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ORGANIZER_MESSAGES_NONE', 0);
define('ORGANIZER_MESSAGES_RE_UNREG', 1);
define('ORGANIZER_MESSAGES_ALL', 2);
define('ORGANIZER_DELETE_EVENTS', 1);

define('ORGANIZER_VISIBILITY_ALL', 0);
define('ORGANIZER_VISIBILITY_ANONYMOUS', 1);
define('ORGANIZER_VISIBILITY_SLOT', 2);

define('ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT', 'Appointment');
define('ORGANIZER_CALENDAR_EVENTTYPE_SLOT', 'Slot');

define('ONEWEEK', 604800);

require_once(dirname(__FILE__) . '/slotlib.php');

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $organizer An object from the form in mod_form.php
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

    $_SESSION["organizer_new_instance"] = $organizer->id;

    return $organizer->id;
}

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param object $organizer An object from the form in mod_form.php
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

    // waiting list
    if (isset($organizer->queue) && $organizer->queue == 0) {
        organizer_remove_waitingqueueentries($organizer);
    }

    organizer_grade_item_update($organizer);

    return $DB->update_record('organizer', $organizer);
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function organizer_delete_instance($id) {
    global $DB;

    if (!$organizer = $DB->get_record('organizer', array('id' => $id))) {
        return false;
    }

    $slots = $DB->get_records('organizer_slots', array('organizerid' => $id));

    foreach ($slots as $slot) {
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));

        foreach ($apps as $app) {
            if (ORGANIZER_DELETE_EVENTS) {
                $DB->delete_records('event', array('id' => $app->eventid));
            }
            $DB->delete_records('organizer_slot_appointments', array('id' => $app->id));
        }

        if (ORGANIZER_DELETE_EVENTS) {
            $DB->delete_records('event', array('id' => $slot->eventid));
        }
        $DB->delete_records('organizer_slots', array('id' => $slot->id));
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
 * @todo Finish documenting this function
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
 * @todo Finish documenting this function
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
 * @todo Finish documenting this function
 */
function organizer_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

function organizer_get_overview_link($organizer) {
    global $CFG;

    $class = $organizer->visible == 0 ? "dimmed" : "";

    return '<div class="name">' . get_string('modulename', 'organizer') . ': <a class="' . $class . '" title="' . $organizer->name
            . '" href="' . $CFG->wwwroot . '/mod/organizer/view.php?id=' . $organizer->coursemodule . '">'
            . $organizer->name . '</a> </div>';
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
        $ok = shift_course_mod_dates('organizer',
                array('allowregistrationsfromdate', 'duedate'), $data->timeshift, $data->courseid);
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
        return array($result->userid => $result);
    } else {
        // Tscpr: Why keep this query and this whole else-branch if we don't use it?
        $query = 'SELECT
                a.id AS id,
                a.userid AS userid,
                a.grade AS rawgrade,
                s.starttime AS dategraded,
                s.starttime AS datesubmitted,
                a.feedback AS feedback
            FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid
            ORDER BY id DESC';
        return array(); // Unused.
    }
}

function organizer_update_grades($organizer, $userid = 0) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    if ($organizer->grade == 0) {
        return organizer_grade_item_update($organizer);
    } else if ($grades = organizer_get_user_grade($organizer, $userid)) {
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

function organizer_grade_item_update($organizer, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

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
            if($finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $userid)) {
                      return organizer_display_finalgrade($finalgrade);
            } else {
                      return $nograde;
            }
        } else {
            $returnstr = organizer_clean_num($grade) . '/' . organizer_clean_num($organizer->grade);
            if($finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $userid)) {
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
        if (isset($scalegrades[$organizer->id][intval($grade)])) {
            if ($grade == 0 || $grade == null) {
                return $nograde;
            } else {
                return $scalegrades[$organizer->id][intval($grade)];
            }
        }
        return $nograde;
    }
}

function organizer_display_finalgrade($finalgrade) {
    $nograde = get_string('nograde');

    if($finalgrade) {
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
    if($grades = $DB->get_record_sql($query, $params)) {
        if(is_null($grades->rawgrade)) { $grades->rawgrade = 0;
        }
        if(is_null($grades->finalgrade)) { $grades->finalgrade = 0;
        }
        if($grades->rawgrade != $grades->finalgrade) {
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
    $groupapps = $DB->get_records_sql('SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE a.groupid = :groupid AND s.organizerid = :organizerid
            ORDER BY a.id DESC', $params);

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
    if ($organizer->isgrouporganizer) {
        $params = array('groupingid' => $organizer->groupingid);
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

function organizer_get_eventaction_slot_teacher($eventid) {
    global $DB;

    $slotid = $DB->get_field("event", "uuid", array("id" => $eventid));

    $now = time();
    $displayeventto = $now + ONEWEEK;

    $slot = $DB->get_records_sql('SELECT * FROM {organizer_slots} INNER JOIN {organizer_slot_appointments} ON 
        {organizer_slots}.id = {organizer_slot_appointments}.slotid 
        WHERE {organizer_slot_appointments}.slotid = :slotid AND 
        {organizer_slots}.starttime > :now AND {organizer_slots}.starttime < :displayeventto',
            array('slotid' => $slotid, 'now' => $now, 'displayeventto' => $displayeventto));

    $appslot = reset($slot);

    $slotstr = "";

    if ($appslot) {
        $a = new stdClass();
        $a->date = userdate($appslot->starttime, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($appslot->starttime, get_string('timetemplate', 'organizer'));
        $slotstr = get_string('mymoodle_app_slot', 'organizer', $a);
    }


    return $slotstr;
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

function organizer_get_eventaction_student($organizer, $forindex = false) {
    global $DB;

    if (!$forindex) {
        $str = '<div class="assignment overview">';
        $str .= organizer_get_overview_link($organizer);
        $class = "class=\"info organizerinfo\"";
        $element = "div";
    } else {
        $str = '';
        $class = "";
        $element = "p";
        $eventstr = "";
    }

    if ($organizer->isgrouporganizer) {
        $group = organizer_fetch_group($organizer);
        $app = organizer_get_last_user_appointment($organizer);

        if ($app && isset($app->attended) && (int) $app->attended === 1) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
            $a->groupname = $group->name;
            $completedapp = get_string('mymoodle_completed_app_group', 'organizer', $a) .
                    ($forindex ? '' : "<br />(" . get_string('grade') . ": " .
                            organizer_display_grade($organizer, $app->grade, $app->userid) . ")");
            if ($app->allownewappointments) {
                $completedapp .= "<br />" . get_string('can_reregister', 'organizer');
            }

            $str .= "<{$element} {$class}>$completedapp</{$element}>";
            $eventstr .= " " . $completedapp;
        } else if ($app && isset($app->attended) && (int) $app->attended === 0) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));

            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
            $a->groupname = $group->name;

            $missedapp = get_string('mymoodle_missed_app_group', 'organizer', $a) .
                    ($forindex ? '' : "<br />(" . get_string('grade') . ": " .
                            organizer_display_grade($organizer, $app->grade, $app->userid) . ")");
            if ($app->allownewappointments) {
                $missedapp .= "<br />" . get_string('can_reregister', 'organizer');
            }

            $str .= "<{$element} {$class}>$missedapp</{$element}>";
            $eventstr .= " " . $missedapp;

            if (isset($organizer->duedate)) {
                $a = new stdClass();
                $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
                $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
                if ($organizer->duedate > time()) {
                    $orgexpires = get_string('mymoodle_organizer_expires', 'organizer', $a);
                } else {
                    $orgexpires = get_string('mymoodle_organizer_expired', 'organizer', $a);
                }
                $str .= "<{$element} {$class}>$orgexpires</{$element}>";
                $eventstr .= " " . $orgexpires;
            }
        } else if ($app && !isset($app->attended)) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));

            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
            $a->groupname = $group->name;

            if (isset($slot->locationlink) && $slot->locationlink != '') {
                $a->location = html_writer::link($slot->locationlink, $slot->location, array('target' => '_blank'));
            } else {
                $a->location = $slot->location;
            }

            if ($slot->starttime > time()) {
                $upcomingapp = get_string('mymoodle_upcoming_app_group', 'organizer', $a);
                $str .= "<{$element} {$class}>$upcomingapp</{$element}>";
                $eventstr .= " " . $upcomingapp;
            } else {
                $pending = get_string('mymoodle_pending_app_group', 'organizer', $a);
                $str .= "<{$element} {$class}>$pending</{$element}>";
                $eventstr .= " " . $pending;
            }
        } else {
            $noregslot = get_string('mymoodle_no_reg_slot', 'organizer');
            $str .= "<{$element} {$class}>$noregslot</{$element}>";
            $eventstr .= " " . $noregslot;

            if (isset($organizer->duedate)) {
                $a = new stdClass();
                $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
                $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
                if ($organizer->duedate > time()) {
                    $orgexpires = get_string('mymoodle_organizer_expires', 'organizer', $a);
                } else {
                    $orgexpires = get_string('mymoodle_organizer_expired', 'organizer', $a);
                }
                $str .= "<{$element} {$class}>$orgexpires</{$element}>";
                $eventstr .= " " . $orgexpires;
            }
        }
    } else {
        $app = organizer_get_last_user_appointment($organizer);
        if ($app && isset($app->attended) && (int) $app->attended === 1) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
            $completedapp = get_string('mymoodle_completed_app', 'organizer', $a) . ($forindex ? '' : "<br />(" . get_string('grade') . ": " . organizer_display_grade($organizer, $app->grade, $app->userid) . ")");
            if ($app->allownewappointments) {
                $completedapp .= "<br />" . get_string('can_reregister', 'organizer');
            }

            $str .= "<{$element} {$class}>$completedapp</{$element}>";
            $eventstr .= " " . $completedapp;
        } else if ($app && isset($app->attended) && (int) $app->attended === 0) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
            $missedapp = get_string('mymoodle_missed_app', 'organizer', $a) . ($forindex ? '' : "<br />(" . get_string('grade') . ": " . organizer_display_grade($organizer, $app->grade, $app->userid) . ")");
            if ($app->allownewappointments) {
                $missedapp .= "<br />" . get_string('can_reregister', 'organizer');
            }

            $str .= "<{$element} {$class}>$missedapp</{$element}>";
            $eventstr .= " " . $missedapp;
            if (isset($organizer->duedate)) {
                $a = new stdClass();
                $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
                $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
                if ($organizer->duedate > time()) {
                    $orgexpires = get_string('mymoodle_organizer_expires', 'organizer', $a);
                } else {
                    $orgexpires = get_string('mymoodle_organizer_expired', 'organizer', $a);
                }
                $str .= "<{$element} {$class}>$orgexpires</{$element}>";
                $eventstr .= " " . $orgexpires;
            }
        } else if ($app && !isset($app->attended)) {
            $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));

            $a = new stdClass();
            $a->date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $a->time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));

            if (isset($slot->locationlink) && $slot->locationlink != '') {
                $a->location = html_writer::link($slot->locationlink, $slot->location, array('target' => '_blank'));
            } else {
                $a->location = $slot->location;
            }

            if ($slot->starttime > time()) {
                $upcomingapp = get_string('mymoodle_upcoming_app', 'organizer', $a);
                $str .= "<{$element} {$class}>$upcomingapp</{$element}>";
                $eventstr .= " " . $upcomingapp;
            } else {
                $pending = get_string('mymoodle_pending_app', 'organizer', $a);
                $str .= "<{$element} {$class}>$pending</{$element}>";
                $eventstr .= " " . $pending;
            }
        } else {
            $noregslot = get_string('mymoodle_no_reg_slot', 'organizer');
            $str .= "<{$element} {$class}>$noregslot</{$element}>";
            $eventstr .= " " . $noregslot;

            if (isset($organizer->duedate)) {
                $a = new stdClass();
                $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
                $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
                if ($organizer->duedate > time()) {
                    $orgexpires = get_string('mymoodle_organizer_expires', 'organizer', $a);
                } else {
                    $orgexpires = get_string('mymoodle_organizer_expired', 'organizer', $a);
                }
                $str .= '<'.$element.' '.$class.'>'.$orgexpires.'</'.$element.'>';
                $eventstr .= " " . $orgexpires;
            }
        }
    }

    if (!$forindex) {
        // $str .= '</div>';
        return $eventstr;
    } else {
        return $str;
    }

}

function organizer_print_overview($courses, &$htmlarray) {
    global $USER;

    if (empty($courses) || !is_array($courses) || count($courses) == 0) {
        return array();
    }

    if (!$organizers = get_all_instances_in_courses('organizer', $courses)) {
        return;
    }

    foreach ($organizers as $organizer) {
        if (organizer_is_student_in_course($organizer->course, $USER->id)) {
            $str = organizer_get_eventaction_student($organizer);
        } else {
            $str = organizer_get_eventaction_slot_teacher($organizer);
        }

        if (empty($htmlarray[$organizer->course]['organizer'])) {
            $htmlarray[$organizer->course]['organizer'] = $str;
        } else {
            $htmlarray[$organizer->course]['organizer'] .= $str;
        }
    }
}

// FIXME replace this one with an alternative over capabilities.
function organizer_is_student_in_course($courseid, $userid) {
    global $DB;

    $stud = $DB->get_records_sql('SELECT * FROM {role_assignments}
            INNER JOIN {context} ON {role_assignments}.contextid = {context}.id
            WHERE {role_assignments}.roleid = 5
                AND {context}.instanceid = :courseid
                AND {role_assignments}.userid = :userid', array('courseid' => $courseid,
                                                                'userid'   => $userid));
    return count($stud) > 0;
}

/**
 * Function to be run periodically according to the moodle cron
 * This function searches for things that need to be done, such
 * as sending out mail, toggling flags etc ...
 *
 * @return boolean
 * @todo Finish documenting this function
 **/
function organizer_cron() {
    require_once(dirname(__FILE__) . '/messaging.php');
    global $DB;
    $now = time();

    $success = true;

    $params = array('now' => $now, 'now2' => $now);
    $appsquery = "SELECT a.*, s.teacherid, s.location, s.starttime, s.organizerid FROM {organizer_slot_appointments} a
        INNER JOIN {organizer_slots} s ON a.slotid = s.id WHERE
        s.starttime - s.notificationtime < :now AND s.starttime > :now2 AND
        a.notified = 0";

    $apps = $DB->get_records_sql($appsquery, $params);
    foreach ($apps as $app) {
        $success &= organizer_send_message(intval($app->teacherid), intval($app->userid), $app,
                'appointment_reminder_student');
    }

    if (empty($apps)) {
        $ids = array(0);
    } else {
        $ids = array_keys($apps);
    }
    list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
    $DB->execute("UPDATE {organizer_slot_appointments} SET notified = 1 WHERE id $insql", $inparams);

    $organizerconfig = get_config('organizer');

    $time = $organizerconfig->digest + mktime(0, 0, 0, date("m"), date("d"), date("Y"));

    if ($organizerconfig->digest != 'never' && (abs(time() - $time) < 300)) {
        $params['tomorrowstart'] = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
        $params['tomorrowend'] = mktime(0, 0, 0, date("m"), date("d") + 2, date("Y"));

        $slotsquery = "SELECT DISTINCT s.teacherid FROM {organizer_slots} s
                WHERE s.starttime >= :tomorrowstart AND
                s.starttime < :tomorrowend AND
                s.notified = 0";

        $teacherids = $DB->get_fieldset_sql($slotsquery, $params);

        if (empty($teacherids)) {
            $teacherids = array(0);
        }

        list($insql, $inparams) = $DB->get_in_or_equal($teacherids, SQL_PARAMS_NAMED);

        $slotsquery = "SELECT *
            FROM {organizer_slots} s
            WHERE s.starttime >= :tomorrowstart AND
            s.starttime < :tomorrowend AND
            s.notified = 0 AND
            s.teacherid $insql";

        $params = array_merge($params, $inparams);

        $slots = $DB->get_records_sql($slotsquery, $params);

        foreach ($teacherids as $teacherid) {
            $digest = '';

            $found = false;
            foreach ($slots as $slot) {
                if ($slot->teacherid == $teacherid) {
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
                $success &= $thissuccess = organizer_send_message(intval($teacherid), intval($teacherid), reset($slots),
                        'appointment_reminder_teacher', $digest);

                if ($thissuccess) {
                    list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
                    $inparams['teacherid'] = $teacherid;
                    $DB->execute("UPDATE {organizer_slots} SET notified = 1 WHERE teacherid = :teacherid AND id $insql",
                                $inparams);
                }
            }

        }
    }

    return $success;
}

function organizer_create_digest($teacherid) {
    require_once(dirname(__FILE__) . '/messaging.php');
    global $DB;
    $now = time();

    $success = true;

    $params = array('now' => $now, 'teacherid' => $teacherid);

    $slotsquery = 'SELECT * FROM {organizer_slots} s
            WHERE s.starttime - s.notificationtime < :now AND
            s.notified = 0 AND s.teacherid = :teacherid';

    $digest = '';

    $slots = $DB->get_records_sql($slotsquery, $params);
    foreach ($slots as $slot) {
        if (isset($slot)) {
            $date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
            $time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
        }
        $digest .= $date.', '.$time.' @ '.$slot->location.'; ';
        $slot->notified = 1;
        $DB->update_record('organizer_slots', $slot);
    }

    $success = organizer_send_message(intval($slot->teacherid), intval($slot->teacherid), $slot,
            'appointment_reminder_teacher:digest', $digest);

    return $success;
}

/**
 * Must return an array of users who are participants for a given instance
 * of organizer. Must include every user involved in the instance,
 * independient of his role (student, teacher, admin...). The returned
 * objects must contain at least id property.
 * See other modules as example.
 *
 * @param int $organizerid ID of an instance of this module
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
 * @param int $organizerid ID of an instance of this module
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
 * @param $scaleid int
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
 * @param stdClass $coursemodule The coursemodule object (record).
 * @return cached_cm_info An object on information that the courses
 *                        will know about (most noticeably, an icon).
 */
function organizer_get_coursemodule_info($coursemodule) {
    global $CFG, $DB;

    $dbparams = array('id' => $coursemodule->instance);
    $fields = 'id, name, alwaysshowdescription, allowregistrationsfromdate, intro, introformat';
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
    return $result;
}

// waiting list
function organizer_remove_waitingqueueentries($organizer) {
    global $DB;

    $query = "slotid in (select id from {organizer_slots} where organizerid = ".$organizer->id.")";
    $ok = $DB->delete_records_select('organizer_slot_queues', $query);
    return $ok;
}


/**
 * This function receives a calendar event and returns the action associated with it, or null if there is none.
 *
 * This is used by block_myoverview in order to display the event appropriately. If null is returned then the event
 * is not displayed on the block.
 *
 * @param calendar_event $event
 * @param \core_calendar\action_factory $factory
 * @return \core_calendar\local\event\entities\action_interface|null
 */
function mod_organizer_core_calendar_provide_event_action(calendar_event $event,
                                                       \core_calendar\action_factory $factory) {
    global $CFG, $DB;

    require_once($CFG->dirroot . '/mod/organizer/locallib.php');

    $cm = get_fast_modinfo($event->courseid)->instances['organizer'][$event->instance];

    $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);

    $props = $event->properties();
    if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT) {
        $name = organizer_get_eventaction_student($organizer);
    } else {
        $name = organizer_get_eventaction_slot_teacher($props->id);
    }

    if ($name) {
        $url = new \moodle_url('/mod/organizer/view.php', [
                'id' => $cm->id,
                'action' => 'show'
        ]);
        $itemcount = 1;
        $actionable = true;

        return $factory->create_instance(
                $name,
                $url,
                $itemcount,
                $actionable
        );
    } else {
        return false;
    }
}

/**
 * Is the event visible?
 *
 * This is used to determine global visibility of an event in all places throughout Moodle.
 *
 * @param calendar_event $event
 * @return bool Returns true if the event is visible to the current user, false otherwise.
 */
function mod_organizer_core_calendar_is_event_visible(calendar_event $event) {
    global $CFG, $USER, $DB;

    require_once($CFG->dirroot . '/mod/organizer/locallib.php');

    $props = $event->properties();
    if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT) {
        $userid = $DB->get_field('event', 'userid', array('id' => $props->id));
        if ($props->groupid!=0) {
            $cm = get_coursemodule_from_instance('organizer', $props->instance, $props->courseid, false, MUST_EXIST);
            $usergroup = organizer_fetch_user_group($props->userid, $cm->id);
            $isvisible = $props->groupid == $usergroup->id ? true : false;
        } else {
            $isvisible = $userid == $USER->id ? true : false;
        }
    } else {
        $context = context_module::instance($props->instance, MUST_EXIST);
        if (has_capability('mod/organizer:viewallslots', $context)) {
            $isvisible = true;
        } else {
            $isvisible = false;
        }
    }

    return $isvisible;
}