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
 * backup/moodle2/backup_organizer_stepslib.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_organizer_activity_task
 */

/**
 * Define the complete organizer structure for backup, with file and id annotations
 */
class backup_organizer_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {
        // Define each element separated.
        $organizer = new backup_nested_element('organizer', array('id'),
                array('course', 'name', 'intro', 'introformat', 'timemodified', 'isgrouporganizer', 'emailteachers',
                        'allowregistrationsfromdate', 'duedate', 'relativedeadline', 'grade', 'queue'));

        $slots = new backup_nested_element('slots');
        $slot = new backup_nested_element('slot', array('id'),
                array('organizerid', 'starttime', 'duration', 'location', 'locationlink', 'maxparticipants',
                        'teacherid', 'isanonymous', 'availablefrom', 'timemodified', 'notificationtime', 'comments',
                        'teachervisible', 'eventid', 'notified'));

        $appointments = new backup_nested_element('appointments');
        $appointment = new backup_nested_element('appointment', array('id'),
                array('slotid', 'userid', 'groupid', 'applicantid', 'registrationtime', 'attended', 'grade',
                        'feedback', 'comments', 'eventid', 'notified', 'allownewappointments'));

        $queues = new backup_nested_element('queues');
        $queue = new backup_nested_element('queue', array('id'),
                array('slotid', 'userid', 'groupid', 'applicantid', 'eventid', 'notified'));

        // Build the tree.
        $organizer->add_child($slots);
        $slots->add_child($slot);

        $slot->add_child($appointments);
        $appointments->add_child($appointment);

        $slot->add_child($queues);
        $queues->add_child($queue);

        // Define sources.
        $organizer->set_source_table('organizer', array('id' => backup::VAR_ACTIVITYID));

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $slot->set_source_table('organizer_slots', array('organizerid' => backup::VAR_PARENTID));
            $appointment->set_source_table('organizer_slot_appointments', array('slotid' => backup::VAR_PARENTID));
            $appointment->set_source_table('organizer_slot_queues', array('slotid' => backup::VAR_PARENTID));
        }

        // Annotate the user id's where required.
        $slot->annotate_ids('user', 'teacherid');
        $appointment->annotate_ids('user', 'userid');
        $appointment->annotate_ids('user', 'applicantid');
        $appointment->annotate_ids('group', 'groupid');
        $queue->annotate_ids('user', 'userid');
        $queue->annotate_ids('user', 'applicantid');
        $queue->annotate_ids('group', 'groupid');

        // Return the root element (organizer), wrapped into standard activity structure.
        return $this->prepare_activity_structure($organizer);
    }
}
