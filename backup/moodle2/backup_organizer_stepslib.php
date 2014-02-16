<?php
//tscpr: adapt file header "This file is made for Moodle" + doctype-filecomment (with author, copyright, etc.)
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_organizer_activity_task
 */

/**
 * Define the complete organizer structure for backup, with file and id annotations
 */
class backup_organizer_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define each element separated
        $organizer = new backup_nested_element('organizer', array('id'),
                array('course', 'name', 'intro', 'introformat', 'timemodified', 'isgrouporganizer', 'emailteachers',
                        'allowregistrationsfromdate', 'duedate', 'relativedeadline', 'grade'));

        $slots = new backup_nested_element('slots');
        $slot = new backup_nested_element('slot', array('id'),
                array('organizerid', 'starttime', 'duration', 'location', 'locationlink', 'maxparticipants',
                        'teacherid', 'isanonymous', 'availablefrom', 'timemodified', 'notificationtime', 'comments',
                        'teachervisible', 'eventid', 'notified'));

        $appointments = new backup_nested_element('appointments');
        $appointment = new backup_nested_element('appointment', array('id'),
                array('slotid', 'userid', 'groupid', 'applicantid', 'registrationtime', 'attended', 'grade',
                        'feedback', 'comments', 'eventid', 'notified', 'allownewappointments'));

        // Build the tree
        $organizer->add_child($slots);
        $slots->add_child($slot);

        $slot->add_child($appointments);
        $appointments->add_child($appointment);

        // Define sources
        $organizer->set_source_table('organizer', array('id' => backup::VAR_ACTIVITYID));

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $slot->set_source_table('organizer_slots', array('organizerid' => backup::VAR_PARENTID));
            $appointment->set_source_table('organizer_slot_appointments', array('slotid' => backup::VAR_PARENTID));
        }

        // Annotate the user id's where required.
        $slot->annotate_ids('user', 'teacherid');
        $appointment->annotate_ids('user', 'userid');
        $appointment->annotate_ids('user', 'applicantid');
        $appointment->annotate_ids('group', 'groupid');

        // Return the root element (organizer), wrapped into standard activity structure
        return $this->prepare_activity_structure($organizer);
    }
}
