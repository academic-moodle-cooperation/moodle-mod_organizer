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
 * index.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

// Replace organizer with the name of your module and remove this line.

use mod_organizer\event\course_module_instance_list_viewed;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$id = required_param('id', PARAM_INT);   // Course.

$course = $DB->get_record('course', ['id' => $id]);
require_course_login($course);

$PAGE->set_pagelayout('incourse');

$event = course_module_instance_list_viewed::create(
    ['context' => context_course::instance($course->id)]);
$event->trigger();

// Print the header.

$PAGE->set_url('/mod/organizer/index.php', ['id' => $course->id]);
$PAGE->navbar->add(get_string("modulenameplural", "organizer"));
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->shortname);

$params['limitedwidth'] = organizer_get_limitedwidth();

echo $OUTPUT->header();

// Get all the appropriate data.

if (! $organizers = get_all_instances_in_course('organizer', $course)) {
    echo $OUTPUT->heading(get_string('noorganizers', 'organizer'), 2);
    echo $OUTPUT->continue_button("view.php?id=$course->id");
    echo $OUTPUT->footer();
    die();
}

$table = new html_table();

$table->head  = [];
$table->align = [];

if ($course->format == 'weeks') {
    $table->head[] = get_string('week');
    $table->align[] = 'center';
} else if ($course->format == 'topics') {
    $table->head[] = get_string('topic');
    $table->align[] = 'center';
}

$table->head[] = get_string('name');
$table->align[] = 'left';
$table->head[] = get_string('description');
$table->align[] = 'left';
$table->head[] = get_string('reg_status', 'organizer');
$table->align[] = 'left';
$table->head[] = get_string('gradenoun');
$table->align[] = 'center';


foreach ($organizers as $organizer) {
    if (!$organizer->visible) {
        // Show dimmed if the mod is hidden.
        $link = '<a class="dimmed" href="view.php?id='.$organizer->coursemodule.'">'.format_string($organizer->name).'</a>';
    } else {
        // Show normal if the mod is visible.
        $link = '<a href="view.php?id='.$organizer->coursemodule.'">'.format_string($organizer->name).'</a>';
    }

    $row = [];
    if ($course->format == 'weeks' || $course->format == 'topics') {
        $row[] = $organizer->section;
    }

    $row[] = $link;

    $cm = get_coursemodule_from_instance('organizer', $organizer->id, $course->id, false, MUST_EXIST);
    $context = context_module::instance($cm->id, MUST_EXIST);

    $row[] = format_module_intro('organizer', $organizer, $cm->id);
    if (has_capability('mod/organizer:viewregistrations', $context)) {
        $a = organizer_get_counters($organizer, $cm);
        $a->slotsmin = $organizer->userslotsmin;
        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $reg = get_string('mymoodle_registered_group_short', 'organizer', $a);
            $att = get_string('mymoodle_attended_group_short', 'organizer', $a);
            $str = '<p>'.$reg.'</p><p>'.$att.'</p>';
        } else {
            $reg = get_string('mymoodle_registered_short', 'organizer', $a);
            $att = get_string('mymoodle_attended_short', 'organizer', $a);
            $str = '<p>'.$reg.'</p><p>'.$att.'</p>';
        }
        $row[] = $str;
        $row[] = '-';
    } else {
        $row[] = organizer_get_eventaction_instance_student($organizer);
        $apps = organizer_get_all_user_appointments($organizer, $USER->id, false);
        foreach ($apps as $app) {
            $row[] = organizer_display_grade($organizer, $app->grade, $app->userid);
        }
    }

    $table->data[] = $row;
}

echo $OUTPUT->heading(get_string('modulenameplural', 'organizer'), 2);
echo html_writer::table($table);
echo $OUTPUT->footer();

die;
