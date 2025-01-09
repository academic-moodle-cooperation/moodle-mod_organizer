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
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use core_calendar\action_factory;
use core_calendar\local\event\entities\action_interface;

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

define('USERSLOTS_MIN_NOT_REACHED', 0);
define('USERSLOTS_MIN_REACHED', 1);
define('USERSLOTS_MAX_REACHED', 2);

define('GRADEAGGREGATIONMETHOD_AVERAGE', 1);
define('GRADEAGGREGATIONMETHOD_SUM', 2);
define('GRADEAGGREGATIONMETHOD_BEST', 3);
define('GRADEAGGREGATIONMETHOD_WORST', 4);

require_once(dirname(__FILE__) . '/slotlib.php');

/**
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param object $organizer An object from the form in mod_form.php
 * @return int The id of the newly inserted organizer record
 * @throws dml_exception
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
 * @param object $organizer An object from the form in mod_form.php
 * @return boolean Success/Fail
 * @throws dml_exception
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
    $organizerold = $DB->get_record('organizer', ['id' => $organizer->id], 'name, gradeaggregationmethod');

    organizer_grade_item_update($organizer);

    $DB->update_record('organizer', $organizer);

    if (isset($organizer->queue) && $organizer->queue == 0) {
        organizer_remove_waitingqueueentries($organizer);
    }

    // If grade aggregation method has changed regrade all grades.
    if ($organizerold->gradeaggregationmethod != $organizer->gradeaggregationmethod) {
        organizer_update_all_grades($organizer->id);
    }

    // Update event entries.
    $params = ['modulename' => 'organizer', 'instance' => $organizer->id,
        'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE];

    $query = 'SELECT id
              FROM {event}
              WHERE modulename = :modulename
              AND instance = :instance
              AND eventtype = :eventtype';

    $eventids = $DB->get_fieldset_sql($query, $params);

    organizer_change_event_instance($organizer, $eventids);

    if ($organizerold->name != $newname) {
        organizer_change_eventnames($organizer->id, $organizerold->name, $newname);
    }

    return true;
}

/**
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 * @throws dml_exception
 */
function organizer_delete_instance($id) {
    global $DB;

    if (!$organizer = $DB->get_record('organizer', ['id' => $id])) {
        return false;
    }

    include_once(__DIR__ . '/locallib.php');

    $slots = $DB->get_records('organizer_slots', ['organizerid' => $id]);
    foreach ($slots as $slot) {
        if ($apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slot->id])) {
            $slotx = new organizer_slot($slot);
            $notify = $slotx->is_upcoming();
            foreach ($apps as $app) {
                organizer_delete_appointment($app->id, $notify);
            } // Foreach app.
        }

        $trainers = organizer_get_slot_trainers($slot->id);
        foreach ($trainers as $trainerid) {
            $slottrainer = $DB->get_record('organizer_slot_trainer', ['slotid' => $slot->id, 'trainerid' => $trainerid]);
            $DB->delete_records('event', ['id' => $slottrainer->eventid]);
            $DB->delete_records('organizer_slot_trainer', ['id' => $slottrainer->id]);
        }

        if (ORGANIZER_DELETE_EVENTS) {
            $DB->delete_records('event', ['uuid' => $slot->id,
                'modulename' => 'organizer', 'instance' => $organizer->id, 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_SLOT]);
        }
        $DB->delete_records('organizer_slots', ['id' => $slot->id]);
    } // Foreach slot.

    if (ORGANIZER_DELETE_EVENTS) {
        $DB->delete_records('event', [
                'modulename' => 'organizer', 'instance' => $organizer->id, 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE]);
    }

    $DB->delete_records('organizer', ['id' => $organizer->id]);

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
 */
function organizer_print_recent_activity($course, $viewfullnames, $timestart) {
    return false; // True if anything was printed, otherwise false.
}

/**
 * Add elements to reset course form for organizer module.
 *
 * @param MoodleQuickForm $mform Form to define elements on.
 */
function organizer_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'organizerheader', get_string('modulenameplural', 'organizer'));
    $mform->addElement('checkbox', 'reset_organizer_all', get_string('resetorganizerall', 'organizer'));
    $mform->addElement('checkbox', 'delete_organizer_grades', get_string('deleteorganizergrades', 'organizer'));
}

/**
 * Reset user data for the organizer module during course reset.
 *
 * @param stdClass $data Data submitted by the reset course form.
 * @return array Status information list for the reset process.
 * @throws dml_exception
 */
function organizer_reset_userdata($data) {
    global $DB;

    if (!$DB->count_records('organizer', ['course' => $data->courseid])) {
        return [];
    }

    $componentstr = get_string('modulenameplural', 'organizer');
    $status = [];

    if (isset($data->reset_organizer_all)) {
        $params = ['courseid' => $data->courseid];

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
            $DB->delete_records('event', ['id' => $slot->eventid]);
            // Tscpr: Petr Skoda told us that $DB->delete_records will throw an exeption if it fails, otherwise it always succeeds.
            $ok &= $DB->delete_records('organizer_slots', ['id' => $slot->id]);
        }

        foreach ($appointments as $appointment) {
            $DB->delete_records('event', ['id' => $appointment->eventid]);
            // Tscpr: Petr Skoda told us that $DB->delete_records will throw an exeption if it fails, otherwise it always succeeds.
            $ok &= $DB->delete_records('organizer_slot_appointments', ['id' => $appointment->id]);
        }

        $status[] = ['component' => $componentstr, 'item' => get_string('reset_organizer_all', 'organizer'),
                'error' => !$ok];
    }

    if (isset($data->delete_organizer_grades)) {
        $ok = organizer_reset_gradebook($data->courseid);
        $status[] = ['component' => $componentstr, 'item' => get_string('delete_organizer_grades', 'organizer'),
                'error' => !$ok];
    }

    if ($data->timeshift) {
        $ok = shift_course_mod_dates(
            'organizer',
            ['allowregistrationsfromdate', 'duedate'], $data->timeshift, $data->courseid
        );
        $status[] = ['component' => $componentstr, 'item' => get_string('timeshift', 'organizer'),
                'error' => !$ok];
    }

    return $status;
}

/**
 * Reset organizer grades for a given course ID.
 *
 * @param int $courseid ID of the course being reset.
 * @return bool Gradebook reset status.
 * @throws dml_exception
 */
function organizer_reset_gradebook($courseid) {
    global $DB;

    $params = ['courseid' => $courseid];

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

/**
 * Retrieve grades for a user in an organizer instance.
 *
 * @param stdClass $organizer Organizer instance object.
 * @param int $userid User ID to fetch grades for, or 0 for all users.
 * @return array List of grade records.
 * @throws dml_exception
 */
function organizer_get_user_grade($organizer, $userid = 0) {
    global $DB;

    $params = ['organizerid' => $organizer->id, 'userid' => $userid];
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
        return $DB->get_records_sql($query, $params);
    } else {
        return [];
    }
}

/**
 * Update activity grades.
 *
 * @param object $organizer
 * @param int $userid specific user only, 0 means all
 */
function organizer_update_grades($organizer, $userid = 0) {
    global $CFG;
    include_once($CFG->libdir . '/gradelib.php');

    if ($organizer->grade == 0) {
        return organizer_grade_item_update($organizer);
    } else {
        if ($grades = organizer_get_user_grade($organizer, $userid)) {
            $grade = reset($grades);
            if ($organizer->grade > 0) { // Numerical.
                switch ($organizer->gradeaggregationmethod) {
                    case GRADEAGGREGATIONMETHOD_AVERAGE:
                        $sum = 0;
                        $i = 0;
                        foreach ($grades as $value) {
                            if ($value->rawgrade) {
                                $i++;
                                $sum += $value->rawgrade;
                            }
                        }
                        $grade->rawgrade = $i ? $sum / $i : 0;
                        break;
                    case GRADEAGGREGATIONMETHOD_SUM:
                        $sum = 0;
                        foreach ($grades as $value) {
                            if ($value->rawgrade) {
                                $sum += $value->rawgrade;
                            }
                        }
                        $grade->rawgrade = $sum;
                        break;
                    case GRADEAGGREGATIONMETHOD_BEST:
                        $max = 0;
                        foreach ($grades as $value) {
                            if (is_numeric($value->rawgrade)) {
                                if ((float) $value->rawgrade > (float) $max) {
                                    $max = $value->rawgrade;
                                }
                            }
                        }
                        $grade->rawgrade = $max;
                        break;
                    case GRADEAGGREGATIONMETHOD_WORST:
                        $min = INF;
                        foreach ($grades as $value) {
                            if (is_numeric($value->rawgrade)) {
                                if ((float) $value->rawgrade < (float) $min) {
                                    $min = $value->rawgrade;
                                }
                            }
                        }
                        $grade->rawgrade = $min;
                        break;
                    default:
                        // If no grade method is selected take average method.
                        $sum = 0;
                        $i = 0;
                        foreach ($grades as $value) {
                            if ($value->rawgrade) {
                                $i++;
                                $sum += $value->rawgrade;
                            }
                        }
                        $grade->rawgrade = $sum / $i;
                }
            }
            return organizer_grade_item_update($organizer, $grade);
        } else {
            return organizer_grade_item_update($organizer);
        }
    }
}

/**
 * Create/update grade items for given organizer.
 *
 * @param stdClass $organizer Organizer instance object
 * @param mixed $grades Optional array/object of grade(s); 'reset' means reset grades in gradebook
 */
function organizer_grade_item_update($organizer, $grades = null) {
    global $CFG;
    include_once($CFG->libdir . '/gradelib.php');

    if (!isset($organizer->courseid)) {
        $organizer->courseid = $organizer->course;
    }

    if (isset($organizer->cmidnumber)) {
        $params = ['itemname' => $organizer->name, 'idnumber' => $organizer->cmidnumber];
    } else {
        $params = ['itemname' => $organizer->name];
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

/**
 * Display a formatted grade for an organizer activity.
 *
 * @param stdClass $organizer Organizer instance object.
 * @param float|int $grade Raw grade from the gradebook.
 * @param int $userid User ID for scale grade processing.
 * @return string Formatted grade string or no-grade string.
 */
function organizer_display_grade($organizer, $grade, $userid) {
    global $DB;
    $nograde = get_string('nograde');
    static $scalegrades = [];   // Cache scales for each organizer - they might have different scales!!

    if ($organizer->grade > 0) {    // Normal number.
        if ($grade == -1 || $grade == null) {
            return $nograde;
        } else {
            $returnstr = organizer_clean_num($grade) . '/' . organizer_clean_num($organizer->grade);
            return $returnstr;
        }
    } else {    // Scale.
        if (empty($scalegrades[$organizer->id])) {
            if ($scale = $DB->get_record('scale', ['id' => -($organizer->scale)])) {
                $scalegrades[$organizer->id] = make_menu_from_list($scale->scale);
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

/**
 * Update all grades for the specified organizer.
 *
 * This function retrieves all students of the course or grouping enrolled in the specified organizer,
 * calculates their grades, and updates them accordingly in the gradebook.
 *
 * @param int $organizerid The ID of the organizer whose grades need to be updated.
 * @return int The number of student grades updated.
 * @throws dml_exception If any database operation fails.
 */
function organizer_update_all_grades($organizerid) {
    global $DB;

    [$cm, , $organizer, $context] = organizer_get_course_module_data(null, $organizerid);

    if ($cm->groupingid != 0) {
        $query = "SELECT u.id FROM {user} u
        INNER JOIN {groups_members} gm ON u.id = gm.userid
        INNER JOIN {groups} g ON gm.groupid = g.id
        INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
        WHERE gg.groupingid = :grouping";
        $par = ['grouping' => $cm->groupingid];
        $studentids = $DB->get_fieldset_sql($query, $par);
    } else {
        $studentids = array_keys(get_enrolled_users($context, 'mod/organizer:register'));
    }

    foreach ($studentids as $studentid) {
        organizer_update_grades($organizer, $studentid);
    }

    return count($studentids);
}

/**
 * Creates a grades menu for an organizer based on the grading type.
 *
 * This function generates a grade menu for the organizer activity. If the grading type
 * is a scale, it fetches the corresponding scale items from the database and adds a "No grade" option.
 * For numeric grading, it creates a menu with grades ranging from 0 to the maximum grade allowed.
 *
 * @param int $gradingtype The grading type of the organizer:
 *                         - Negative values indicate a scale ID.
 *                         - Positive values specify a maximum numeric grade.
 * @return array An associative array representing the grades menu.
 *               Keys are grades or scale indices, and values are their string representations.
 *               Includes a "No grade" option.
 * @throws dml_exception If there's an error fetching scale data from the database.
 */
function organizer_make_grades_menu_organizer($gradingtype) {
    global $DB;

    $grades = [];
    if ($gradingtype < 0) {
        if ($scale = $DB->get_record('scale', ['id' => (-$gradingtype)])) {
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

/**
 * Cleans up a numeric value by removing unnecessary trailing zeros from decimal numbers.
 *
 * This function checks whether the given number is an integer or a decimal.
 * If it is a decimal, it removes trailing zeros and the decimal point if there are no significant decimals left.
 *
 * @param string|float|int $num The numeric value to clean. It can be a string, float, or integer.
 * @return string The cleaned numeric value as a string.
 */
function organizer_clean_num($num) {
    $pos = strpos($num, '.');
    if ($pos === false) { // It is integer number.
        return $num;
    } else { // It is decimal number.
        return rtrim(rtrim($num, '0'), '.');
    }
}

/**
 * Retrieves the event action instance for a trainer in an organizer module.
 *
 * @param object $organizer The organizer module instance.
 * @return string A localized string representing the event action instance.
 */
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

/**
 * Retrieves the event action instance for a student in an organizer module.
 *
 * @param object $organizer The organizer module instance.
 * @return string A localized string representing the event action instance.
 */
function organizer_get_eventaction_instance_student($organizer) {

    $a = new stdClass();
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $group = organizer_fetch_group($organizer);
        $a->booked = organizer_count_bookedslots($organizer->id, null, $group->id);
        $a->groupname = $group->name;
        $a->slotsmin = $organizer->userslotsmin;
        if (organizer_multiplebookings_status($a->booked, $organizer) != USERSLOTS_MIN_NOT_REACHED) {
            $str = get_string('mymoodle_reg_slot_group', 'organizer', $a);
        } else {
            $str = get_string('mymoodle_no_reg_slot_group', 'organizer');
        }
    } else {
        $a->booked = organizer_count_bookedslots($organizer->id);
        $a->slotsmin = $organizer->userslotsmin;
        if (organizer_multiplebookings_status($a->booked, $organizer) != USERSLOTS_MIN_NOT_REACHED) {
            $str = get_string('mymoodle_reg_slot', 'organizer', $a);
        } else {
            $str = get_string('mymoodle_no_reg_slot', 'organizer');
        }
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

    return $str;
}


/**
 * Fetches the group associated with a user in the specified organizer module.
 *
 * This function determines the group to which a user belongs in the given course and
 * organizer module. If no user ID is provided, it defaults to the ID of the currently logged-in user.
 *
 * If the organizer parameter is passed as an integer ID, the corresponding organizer record
 * will be fetched from the database.
 *
 * @param object|int $organizer The organizer module instance or its ID.
 * @param int|null $userid The user ID for whom the group is to be fetched. Defaults to the current user.
 * @return object The group object that the user belongs to.
 * @throws dml_exception If there is a problem with retrieving records from the database.
 */
function organizer_fetch_group($organizer, $userid = null) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', ['id' => $organizer]);
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
 * @throws moodle_exception
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

    $params = ['now' => $now, 'now2' => $now];
    $appsquery = "SELECT a.*, s.location, s.starttime, s.organizerid, s.teachervisible FROM {organizer_slot_appointments} a
        INNER JOIN {organizer_slots} s ON a.slotid = s.id WHERE
        s.starttime - s.notificationtime < :now AND s.starttime > :now2 AND
        a.notified = 0";

    $apps = $DB->get_records_sql($appsquery, $params);
    foreach ($apps as $app) {
        $customdata = ['showsendername' => intval($app->teachervisible == 1)];
        $success &= organizer_send_message_from_trainer(intval($app->userid), $app,
            'appointment_reminder_student', null, $customdata);
    }

    if (empty($apps)) {
        $ids = [0];
    } else {
        $ids = array_keys($apps);
    }
    [$insql, $inparams] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
    $DB->execute("UPDATE {organizer_slot_appointments} SET notified = 1 WHERE id $insql", $inparams);

    $organizerconfig = get_config('organizer');
    if (isset($organizerconfig->digest) && $organizerconfig->digest == 'never') {
        return $success;
    } else if (isset($organizerconfig->digest)) {
        $time = $organizerconfig->digest + mktime(0, 0, 0, date("m"), date("d"), date("Y"));
        if (abs(time() - $time) >= 300) {
            return $success;
        }
    }

    $params['tomorrowstart'] = mktime(0, 0, 0, date("m"), date("d") + 1, date("Y"));
    $params['tomorrowend'] = mktime(0, 0, 0, date("m"), date("d") + 2, date("Y"));

    $slotsquery = "SELECT DISTINCT t.trainerid FROM {organizer_slots} s INNER JOIN {organizer_slot_trainer} t ON s.id = t.slotid
            WHERE s.starttime >= :tomorrowstart AND
            s.starttime < :tomorrowend AND
            s.notified = 0";

    $trainerids = $DB->get_fieldset_sql($slotsquery, $params);

    if (empty($trainerids)) {
        $trainerids = [0];
    }

    [$insql, $inparams] = $DB->get_in_or_equal($trainerids, SQL_PARAMS_NAMED);

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
                $time = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
                $digest .= "$time @ $slot->location\n";
                $found = true;
            }
        }

        if (empty($slots)) {
            $ids = [0];
        } else {
            $ids = array_keys($slots);
        }

        if ($found) {
            // Reminder for trainer in cron job.
            $success &= $thissuccess = organizer_send_message(
                intval($trainerid), intval($trainerid), reset($slots),
                'appointment_reminder_teacher', $digest
            );

            if ($thissuccess) {
                [$insql, ] = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
                $DB->execute("UPDATE {organizer_slots} SET notified = 1 WHERE id $insql");
            }
        }
    }
    return $success;
}

/**
 * Creates and sends a digest message for an organizer trainer.
 *
 * This function generates a digest of upcoming appointment slots that
 * meet the criteria for notification. The digest includes the date, time,
 * and location of each slot. It updates the notification status of the slots
 * to prevent duplicate notifications and sends the digest message to the
 * trainer.
 *
 * @param int $trainerid The ID of the trainer for whom the digest is created.
 * @return bool True if the digest message is successfully sent, false otherwise.
 */
function organizer_create_digest($trainerid) {
    include_once(dirname(__FILE__) . '/messaging.php');
    global $DB;
    $now = time();

    $params = ['now' => $now, 'trainerid' => $trainerid];

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

    // Send digest from trainer to itself. Is this used??
    $success = organizer_send_message($trainerid, $trainerid, $slot, 'appointment_reminder_teacher:digest', $digest);

    return $success;
}

/**
 * Must return an array of users who are participants for a given instance
 * of organizer. Must include every user involved in the instance,
 * independent of his role (student, teacher, admin...). The returned
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

    if ($organizerid && $scaleid && $DB->record_exists('organizer', ['id' => $organizerid, 'grade' => -$scaleid])) {
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

    if ($scaleid && $DB->record_exists('organizer', ['grade' => -$scaleid])) {
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

/**
 * Defines support for certain features within the organizer module.
 *
 * This function takes a feature constant as input and returns whether
 * the feature is supported by the organizer module. It uses a switch
 * statement to check various predefined feature cases.
 *
 * Additional cases could be added as needed to extend support for new features.
 *
 * @param string $feature The feature constant to check support for.
 * @return mixed True if the feature is supported, null otherwise.
 *               Returns a string for specific custom cases like 'mod_purpose'.
 */
function organizer_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
        case FEATURE_GROUPINGS:
        case FEATURE_MOD_INTRO:
        case FEATURE_COMPLETION_TRACKS_VIEWS:
        case FEATURE_GRADE_HAS_GRADE:
        case FEATURE_GRADE_OUTCOMES:
        case FEATURE_BACKUP_MOODLE2:
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case 'mod_purpose':
            return 'administration';
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

    $dbparams = ['id' => $coursemodule->instance];
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

/**
 * Removes all waiting queue entries for an organizer.
 *
 * This function deletes all records from the organizer_slot_queues table
 * that are associated with a specific organizer's slots.
 *
 * @param stdClass $organizer The organizer object containing the ID of the organizer.
 * @return bool True if the entries were successfully removed, false otherwise.
 */
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
 * @param  action_factory $factory
 * @return action_interface|null
 */
function mod_organizer_core_calendar_provide_event_action(calendar_event $event,
    action_factory $factory
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
    global $USER, $DB, $CFG;
    $props = $event->properties();

    $organizer = $DB->get_record('organizer', ['id' => $props->instance], '*');

    if ($organizer == false) {
        return false;
    }

    if ($props->eventtype == ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT) {
        $courseisvisible = $DB->get_field('course', 'visible', ['id' => $props->courseid]);
        if (instance_is_visible('organizer', $organizer) && $courseisvisible) {
            if (empty($userid)) {
                $userid = $USER->id;
            }
            if (!$userid) {
                $isvisible = false;
            } else {
                $useridevent = $DB->get_field('event', 'userid', ['id' => $props->id]);
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
                include_once(dirname(__FILE__) . '/locallib.php');
                $a = organizer_get_counters($organizer, $cm);
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
                $courseisvisible = $DB->get_field('course', 'visible', ['id' => $props->courseid]);
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

/**
 * Updates or creates calendar events for an organizer instance.
 *
 * Depending on if event IDs are provided, this function either updates existing calendar events
 * or creates new ones for the organizer instance in the calendar.
 *
 * @param object $organizer The organizer object containing details about the instance.
 * @param array $eventids (optional) Array of event IDs to be updated, if any.
 * @return bool|array False on failure or an array of created/updated event objects on success.
 */
function organizer_change_event_instance($organizer, $eventids = []) {
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

/**
 * Creates a calendar event for an organizer instance or its appointments/slots.
 *
 * Based on the event type, this function creates an event in the Moodle calendar
 * for an organizer instance, appointments, or slots. It sets the event properties
 * like type, description, duration, and more. If a UUID is provided for an
 * appointment, it removes already existing slot events linked to that UUID.
 *
 * @param object $organizer The organizer object containing instance details.
 * @param string $eventtitle The title of the event.
 * @param string $eventdescription The description text for the event.
 * @param int $eventtype The type of the event (e.g., instance or appointment).
 * @param int $userid The ID of the user associated with the event.
 * @param int $timestart The start time of the event in timestamp format.
 * @param int $duration The duration of the event in seconds.
 * @param int $group The group ID associated with the event (optional).
 * @param string $uuid The unique identifier for the event/slot (optional).
 *
 * @return int|false Returns the event ID if created or false on failure.
 */
function organizer_create_calendarevent($organizer, $eventtitle, $eventdescription, $eventtype, $userid,
    $timestart, $duration, $group, $uuid
) {
    global $CFG, $DB;

    include_once($CFG->dirroot.'/calendar/lib.php');

    $event = new stdClass();
    $event->eventtype = $eventtype;
    $intro = strip_pluginfile_content($eventdescription);
    $event->description = [
            'text' => $intro,
            'format' => $organizer->introformat,
    ];
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
        $DB->delete_records('event', ['modulename' => 'organizer', 'eventtype' => 'Slot', 'uuid' => $uuid]);
    }

    return $event->id;
}

/**
 * Updates the details of one or more calendar events related to the organizer.
 *
 * @param array $eventids An array of event IDs to update.
 * @param object $organizer The organizer object associated with the events.
 * @param string $eventtitle The new title for the events.
 * @param string $eventdescription The new description for the events.
 * @param int $eventtype The type of the event (e.g. instance, appointment, slot).
 * @param int $userid The ID of the user associated with the events.
 * @param int $timestart The start time of the event in timestamp format.
 * @param int $duration The duration of the event in seconds.
 * @param int|null $group The group ID associated with the event (optional).
 * @param string|null $uuid A unique identifier for the event/slot (optional).
 *
 * @return bool Returns true on successful update.
 */
function organizer_change_calendarevent($eventids, $organizer, $eventtitle, $eventdescription, $eventtype, $userid,
    $timestart, $duration, $group, $uuid
) {
    global $CFG;

    include_once($CFG->dirroot.'/calendar/lib.php');

    $data = new stdClass();
    $data->eventtype = $eventtype;
    $intro = strip_pluginfile_content($eventdescription);
    $data->description = [
            'text' => $intro,
            'format' => $organizer->introformat,
    ];
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

/**
 * Updates the event names of calendar events associated with a specific organizer.
 *
 * @param int $organizerid The ID of the organizer whose events are to be updated.
 * @param string $oldname The old name to search for in event names and descriptions.
 * @param string $newname The new name to replace the old name with in event names and descriptions.
 *
 * @return void
 */
function organizer_change_eventnames($organizerid, $oldname, $newname) {
    global $DB;

    $record = new stdClass();
    $params = ['organizerid' => $organizerid, 'modulename' => 'organizer',
        'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE];
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

/**
 * Get icon mapping for font-awesome.
 */
function mod_organizer_get_fontawesome_icon_map() {
    return [
        'mod_organizer:message_error' => 'fa-times-circle',
        'mod_organizer:message_info' => 'fa-info-circle',
        'mod_organizer:message_warning' => 'fa-exclamation-circle',
    ];
}
