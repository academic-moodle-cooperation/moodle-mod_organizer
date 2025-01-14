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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/organizer/backup/moodle2/restore_organizer_stepslib.php');


/**
 * organizer restore task that provides all the settings and steps to perform one complete restore of the activity
 * backup/moodle2/restore_organizer_activity_task.class.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class restore_organizer_activity_task extends restore_activity_task {

    /**
     * Define (add) particular settings this activity can have
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have
     */
    protected function define_my_steps() {
        // Organizer only has one structure step.
        $this->add_step(new restore_organizer_activity_structure_step('organizer_structure', 'organizer.xml'));
    }

    /**
     * Define the contents in the activity that must be
     * processed by the link decoder
     */
    public static function define_decode_contents() {
        $contents = [];

        $contents[] = new restore_decode_content('organizer', ['intro'], 'organizer');

        return $contents;
    }

    /**
     * Define the decoding rules for links belonging
     * to the activity to be executed by the link decoder
     */
    public static function define_decode_rules() {
        $rules = [];

        $rules[] = new restore_decode_rule('ORGANIZERVIEWBYID', '/mod/organizer/view.php?id=$1', 'course_module');
        $rules[] = new restore_decode_rule('ORGANIZERINDEX', '/mod/organizer/index.php?id=$1', 'course');

        return $rules;

    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * organizer logs. It must return one array
     * of {@link restore_log_rule} objects
     */
    public static function define_restore_log_rules() {
        $rules = [];

        $rules[] = new restore_log_rule('organizer', 'myview', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'allview', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'studview', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'statusview', 'view.php?id={course_module}', '{organizer}');

        $rules[] = new restore_log_rule('organizer', 'add', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'edit', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'delete', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'eval', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'register', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'reregister', 'view.php?id={course_module}', '{organizer}');
        $rules[] = new restore_log_rule('organizer', 'unregister', 'view.php?id={course_module}', '{organizer}');

        return $rules;
    }

    /**
     * Define the restore log rules that will be applied
     * by the {@link restore_logs_processor} when restoring
     * course logs. It must return one array
     * of {@link restore_log_rule} objects
     *
     * Note these rules are applied when restoring course logs
     * by the restore final task, but are defined here at
     * activity level. All them are rules not linked to any module instance (cmid = 0)
     */
    public static function define_restore_log_rules_for_course() {
        $rules = [];

        // No particular settings for this activity

        return $rules;
    }

}
