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
 * db/migrate.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_CHECK_USERS', 1);
define('ORGANIZER_LIMIT_EXECUTION', 0); // All.
define('ORGANIZER_COURSE_START', 0);
define('ORGANIZER_COURSE_COUNT_LIMIT', 9999999999);

define('ORGANIZER_TABLE_ORGANIZER_OLD', 'scheduler');
define('ORGANIZER_TABLE_ORGANIZER_NEW', 'organizer');
define('ORGANIZER_TABLE_ORGANIZER_SLOTS_OLD', 'scheduler_slots');
define('ORGANIZER_TABLE_ORGANIZER_SLOTS_NEW', 'organizer_slots');
define('ORGANIZER_TABLE_ORGANIZER_SLOT_APPOINTMENTS_NEW', 'organizer_slot_appointments');

define('ORGANIZER_DEFAULT_ORGANIZER_INTROFORMAT', 1);
define('ORGANIZER_DEFAULT_ORGANIZER_ISGROUPORGANIZER', 0);
define('ORGANIZER_DEFAULT_ORGANIZER_GRADE', 0);
define('ORGANIZER_DEFAULT_ORGANIZER_RELATIVEDEADLINE', 86400);

define('ORGANIZER_DEFAULT_SLOT_LOCATIONLINK', '');
define('ORGANIZER_DEFAULT_SLOT_TEACHERVISIBLE', 1);
define('ORGANIZER_DEFAULT_SLOT_NOTIFIED', 1);

define('ORGANIZER_DEFAULT_APPOINTMENT_GROUPID', 0);
define('ORGANIZER_DEFAULT_APPOINTMENT_NOTIFIED', 1);
define('ORGANIZER_DEFAULT_APPOINTMENT_ALLOW_NEW_APPOINTMENTS', 0);
define('ORGANIZER_DEFAULT_APPOINTMENT_GRADE', 0);
define('ORGANIZER_DEFAULT_ALLOW_NEW_APPOINTMENTS', 0);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once(dirname(__FILE__) . '/../../../lib/moodlelib.php');
require_once(dirname(__FILE__) . '/../locallib.php');

require_login();
require_sesskey();
if (!is_siteadmin()) {
    header('Location: index.php');
    exit;
}

global $DB;

echo "<pre>";
$courses = $DB->get_records_sql("SELECT DISTINCT c.* FROM {course} c INNER JOIN {scheduler} s ON c.id = s.course");

$count = 0;
foreach ($courses as $course) {
    if ($count < ORGANIZER_COURSE_START) {
        $count++;
        continue;
    }

    try {
        $transaction = $DB->start_delegated_transaction();

        echo "Processing COURSE id = {$course->id}, name = \"{$course->fullname}\"...\n";

        $schedulers = $DB->get_records("scheduler", ['course' => $course->id]);

        foreach ($schedulers as $scheduler) {
            echo "Processing scheduler id = {$scheduler->id}, name = \"{$scheduler->name}\"...\n";
            echo "\tReading organizer data... ";
            $organizer = new stdClass();
            $organizer->course = $scheduler->course;
            $organizer->name = $scheduler->name;
            $organizer->intro = $scheduler->description;
            $organizer->introformat = ORGANIZER_DEFAULT_ORGANIZER_INTROFORMAT;
            $organizer->timemodified = $scheduler->timemodified > 0 ? $scheduler->timemodified : 0;
            $organizer->isgrouporganizer = ORGANIZER_DEFAULT_ORGANIZER_ISGROUPORGANIZER;
            $organizer->emailteachers = $scheduler->emailteachers;
            $organizer->allowregistrationsfromdate = null;
            $organizer->duedate = $scheduler->changeuntil > 0 ? $scheduler->changeuntil : null;
            $deadline = ORGANIZER_DEFAULT_ORGANIZER_RELATIVEDEADLINE;
            $organizer->relativedeadline = $scheduler->changebefore > 0 ? $scheduler->changebefore : $deadline;
            $organizer->grade = ORGANIZER_DEFAULT_ORGANIZER_GRADE;

            echo "done.\n";

            echo "\tInserting new organizer record... ";
            $organizer->id = $DB->insert_record('organizer', $organizer);
            $cmold = get_coursemodule_from_instance("scheduler", $scheduler->id);
            $cm = add_course_module_x($cmold, $organizer);
            echo "done. New organizer id = {$organizer->id}\n";

            $section = $DB->get_record_sql(
                "SELECT *
            FROM {course_sections} cs
            WHERE cs.sequence LIKE CONCAT('%', :cmid, '%') AND cs.course = :courseid",
                ['cmid' => $cmold->id, 'courseid' => $cm->course]
            );

            if (!$section) {
                echo "WARNING! This organizer has already been migrated! Reverting and skipping...\n";
                $DB->delete_records("organizer", ['id' => $organizer->id]);
                $DB->delete_records("course_modules", ['id' => $cm->id]);
                continue;
            }

            $section->sequence = str_replace($cmold->id, $cm->id, $section->sequence);

            $DB->update_record('course_sections', $section);
            $DB->set_field('course_modules', 'section', $section->id, ['id' => $cm->id]);

            migrate_slots($cm, $scheduler, $organizer);
        }

        $transaction->allow_commit();
        echo "This course has been migrated successfully.\n\n";
    } catch (Exception $e) {
        $transaction->rollback($e);
        echo "Migration failed for this course!\n\n";
    }

    rebuild_course_cache($course->id);
    $count++;

    if (ORGANIZER_LIMIT_EXECUTION && $count == ORGANIZER_COURSE_START + ORGANIZER_COURSE_COUNT_LIMIT) {
        break;
    }
}

echo "</pre>";
die;

/**
 * Migrate each slot
 * @param $cm
 * @param $scheduler
 * @param $organizer
 * @return void
 * @throws dml_exception
 */
function migrate_slots($cm, $scheduler, $organizer) {
    global $DB;

    $query = "SELECT CONCAT(scheduler, starttime, teacher) AS tempid, scheduler, starttime, duration, teacher,
        appointmentlocation, timemodified, hideuntil, emaildate, maxstudents, notes, appointmentnote, exclusive
        FROM {scheduler_slots} s
        WHERE scheduler = :scheduler AND teacher IS NOT NULL AND teacher > 0 AND starttime > 0 AND duration > 0
        GROUP BY scheduler, starttime, duration, teacher, appointmentlocation";

    echo "\tReading associated slots from scheduler... ";
    $oldslots = $DB->get_records_sql($query, ['scheduler' => $scheduler->id]);
    echo "done.\n";

    $slotcount = 0;
    foreach ($oldslots as $oldslot) {
        echo "\tProcessing slot tempid = {$oldslot->tempid}...\n";
        echo "\t\tReading slot data... ";
        if (ORGANIZER_CHECK_USERS) {
            if (!$DB->get_record('user', ['id' => $oldslot->teacher])) {
                echo "\t\tTeacher with user id = {$oldslot->teacher} does not exist in the database! Skipping slot entry.";
                continue;
            }
        }

        $newslot = new stdClass();
        $newslot->organizerid = $organizer->id;
        $newslot->starttime = $oldslot->starttime;
        $newslot->duration = $oldslot->duration == 0 ? 5 * 60 : $oldslot->duration * 60;
        $newslot->location = $oldslot->appointmentlocation ? $oldslot->appointmentlocation : '';
        $newslot->locationlink = ORGANIZER_DEFAULT_SLOT_LOCATIONLINK;
        $newslot->maxparticipants = $oldslot->exclusive != 1 ? $oldslot->maxstudents : 1;
        $newslot->teacherid = $oldslot->teacher;
        $newslot->isanonymous = ($oldslot->exclusive == 2) ? 1 : 0;
        $newslot->availablefrom = $oldslot->hideuntil > 0 ? $oldslot->hideuntil : 0;
        $newslot->timemodified = $oldslot->timemodified > 0 ? $oldslot->timemodified : 0;
        $newslot->notificationtime = $oldslot->emaildate > 0 ? $oldslot->starttime - $oldslot->emaildate : null;
        $newslot->comments = isset($oldslot->appointmentnote) ? $oldslot->appointmentnote : '';
        $newslot->teachervisible = ORGANIZER_DEFAULT_SLOT_TEACHERVISIBLE;
        $newslot->notified = ORGANIZER_DEFAULT_SLOT_NOTIFIED;
        echo "done.\n";

        echo "\t\tInserting new slot record... ";
        $newslot->id = $DB->insert_record("organizer_slots", $newslot);
        $newslot->eventid = organizer_add_event_slot($cm->id, $newslot->id);
        echo "\t\tEvent id = {$newslot->eventid}... ";
        $DB->update_record("organizer_slots", $newslot);
        echo "done. New slot id = {$newslot->id}\n";

        echo "\t\tReading appointment data from scheduler_slots...\n";

        $query = "SELECT s.*
            FROM mdl_scheduler_slots s
            WHERE s.scheduler = :schedulerid AND s.starttime = :starttime AND
            s.teacher = :teacherid AND student IS NOT NULL AND student > 0";

        $oldapps = $DB->get_records_sql(
            $query,
            ['schedulerid' => $oldslot->scheduler, 'starttime' => $oldslot->starttime,
            'teacherid' => $oldslot->teacher]
        );

        $appcount = 0;
        foreach ($oldapps as $oldapp) {
            if (ORGANIZER_CHECK_USERS) {
                if (!$DB->get_record('user', ['id' => $oldapp->student])) {
                    echo "\t\tStudent with user id = {$oldapp->student} does not exist in the database!
                        Skipping appointment entry.";
                    continue;
                }
            }

            $newapp = new stdClass();
            $newapp->slotid = $newslot->id;
            $newapp->userid = $oldapp->student;
            $newapp->groupid = ORGANIZER_DEFAULT_APPOINTMENT_GROUPID;
            $newapp->applicantid = $oldapp->student;
            $newapp->attended = $oldapp->attended;
            $newapp->grade = ORGANIZER_DEFAULT_APPOINTMENT_GRADE;
            $newapp->feedback = '';
            $newapp->comments = isset($oldapp->notes) ? $oldapp->notes : '';
            $newapp->notified = ORGANIZER_DEFAULT_APPOINTMENT_NOTIFIED;
            $newapp->allownewappointments = ORGANIZER_DEFAULT_ALLOW_NEW_APPOINTMENTS;

            echo "\t\t\tInserting new appointment record for slot id = {$newslot->id} ...";
            $newapp->id = $DB->insert_record('organizer_slot_appointments', $newapp);
            $newapp->eventid = organizer_add_event_appointment($cm->id, $newapp->id);
            echo "\t\t\tEvent id = {$newapp->eventid}... ";
            $DB->update_record('organizer_slot_appointments', $newapp);
            echo "done. Appointment id = {$newapp->id}\n";

            $appcount++;
        }
        echo "\t\tDone. $appcount appointments migrated.\n";

        $slotcount++;
    }

    echo "Organizer migrated successfully. $slotcount slots migrated.\n";
}

/**
 * Add organizer course module
 * @param $cmold
 * @param $organizer
 * @return stdClass
 * @throws dml_exception
 */
function add_course_module_x($cmold, $organizer) {
    global $DB;

    $cm = new stdClass();
    $cm->course = $organizer->course;
    $cm->module = $DB->get_field('modules', 'id', ['name' => 'organizer']);
    $cm->instance = $organizer->id;
    $cm->section = 0; // Will be changed later.
    $cm->idnumber = '';
    $cm->added = time();
    $cm->score = $cmold->score;
    $cm->indent = $cmold->indent;
    $cm->visible = $cmold->visible;
    $cm->visibleold = $cmold->visibleold;
    $cm->groupmode = $cmold->groupmode;
    $cm->groupingid = $cmold->groupingid;
    $cm->groupmembersonly = $cmold->groupmembersonly;
    $cm->completion = $cmold->completion;
    $cm->completiongradeitemnumber = $cmold->null;
    $cm->completionview = $cmold->completionview;
    $cm->completionexpected = $cmold->completionexpected;
    $cm->availablefrom = $cmold->availablefrom;
    $cm->availableuntil = $cmold->availableuntil;
    $cm->showavailability = $cmold->showavailability;
    $cm->showdescription = $cmold->showdescription;

    $cm->id = $DB->insert_record('course_modules', $cm, true, true);

    return $cm;
}

