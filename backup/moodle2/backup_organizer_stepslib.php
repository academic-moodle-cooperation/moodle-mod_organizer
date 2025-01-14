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
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_organizer_activity_task
 */

/**
 * Define the complete organizer structure for backup, with file and id annotations
 */
class backup_organizer_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define structure of class
     * @return backup_nested_element
     * @throws base_element_struct_exception
     * @throws base_step_exception
     */
    protected function define_structure() {
        // Define each element separated.
        $organizer = new backup_nested_element(
            'organizer', ['id'],
            [
                'course',
                'name',
                'intro',
                'introformat',
                'timemodified',
                'isgrouporganizer',
                'emailteachers',
                'allowregistrationsfromdate',
                'duedate',
                'alwaysshowdescription',
                'relativedeadline',
                'grade',
                'gradeaggregationmethod',
                'scale',
                'queue',
                'visibility',
                'hidecalendar',
                'nocalendareventslotcreation',
                'includetraineringroups',
                'singleslotprintfield0',
                'singleslotprintfield1',
                'singleslotprintfield2',
                'singleslotprintfield3',
                'singleslotprintfield4',
                'singleslotprintfield5',
                'singleslotprintfield6',
                'singleslotprintfield7',
                'singleslotprintfield8',
                'singleslotprintfield9',
                'userslotsmin',
                'userslotsmax',
                'synchronizegroupmembers',
                'userslotsdailymax',
                'noreregistrations']
        );

        $slots = new backup_nested_element('slots');
        $slot = new backup_nested_element(
            'slot', ['id'],
            ['organizerid', 'starttime', 'duration', 'gap', 'location', 'locationlink', 'maxparticipants',
                        'visibility', 'availablefrom', 'timemodified', 'notificationtime', 'comments',
            'teachervisible', 'eventid', 'notified', 'visible', 'coursegroup']
        );

        $appointments = new backup_nested_element('appointments');
        $appointment = new backup_nested_element(
            'appointment', ['id'],
            ['slotid', 'userid', 'groupid', 'applicantid', 'attended', 'grade',
            'feedback', 'comments', 'eventid', 'notified', 'allownewappointments', 'teacherapplicantid',
                    'teacherapplicanttimemodified']
        );

        $queues = new backup_nested_element('queues');
        $queue = new backup_nested_element(
            'queue', ['id'],
            ['slotid', 'userid', 'groupid', 'applicantid', 'eventid', 'notified']
        );

        $trainers = new backup_nested_element('trainers');
        $trainer = new backup_nested_element(
                'trainer', ['id'],
                ['slotid', 'trainerid', 'eventid']
        );

        // Build the tree.
        $organizer->add_child($slots);
        $slots->add_child($slot);

        $slot->add_child($appointments);
        $appointments->add_child($appointment);

        $slot->add_child($queues);
        $queues->add_child($queue);

        $slot->add_child($trainers);
        $trainers->add_child($trainer);

        // Define sources.
        $organizer->set_source_table('organizer', ['id' => backup::VAR_ACTIVITYID]);
        $slot->set_source_table('organizer_slots', ['organizerid' => backup::VAR_PARENTID]);

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $appointment->set_source_table('organizer_slot_appointments', ['slotid' => backup::VAR_PARENTID]);
            $queue->set_source_table('organizer_slot_queues', ['slotid' => backup::VAR_PARENTID]);
            $trainer->set_source_table('organizer_slot_trainer', ['slotid' => backup::VAR_PARENTID]);
        }

        // Annotate the user ids where required.
        $slot->annotate_ids('event', 'eventid');
        $appointment->annotate_ids('user', 'userid');
        $appointment->annotate_ids('user', 'applicantid');
        $appointment->annotate_ids('group', 'groupid');
        $queue->annotate_ids('user', 'userid');
        $queue->annotate_ids('user', 'applicantid');
        $queue->annotate_ids('group', 'groupid');
        $trainer->annotate_ids('user', 'trainerid');

        // Return the root element (organizer), wrapped into standard activity structure.
        return $this->prepare_activity_structure($organizer);
    }
}
