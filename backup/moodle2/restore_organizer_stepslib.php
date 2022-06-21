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
/**
 * backup/moodle2/restore_organizer_stepslib.php
 * Structure step to restore one organizer activity
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 *
 */
class restore_organizer_activity_structure_step extends restore_activity_structure_step
{
    /**
     * {@inheritDoc}
     * @see restore_structure_step::define_structure()
     */
    protected function define_structure() {
        $paths = array();
        $paths[] = new restore_path_element('organizer', '/activity/organizer');
        $paths[] = new restore_path_element('slot', '/activity/organizer/slots/slot');

        $userinfo = $this->get_setting_value('userinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('appointment',
                '/activity/organizer/slots/slot/appointments/appointment'
            );
        }

        return $this->prepare_activity_structure($paths);
    }
    /**
     * process organizer data for the stepslib
     * @param mixed $data
     */
    protected function process_organizer($data) {
        global $DB;

        $data = (object) $data;
        $data->course = $this->get_courseid();

        $data->allowregistrationsfromdate = $this->apply_date_offset($data->allowregistrationsfromdate);
        $data->duedate = $this->apply_date_offset($data->duedate);
        $data->timemodified = time();

        if ($data->grade < 0) { // Scale found, get mapping.
            $data->grade = -($this->get_mappingid('scale', abs($data->grade)));
        }

        $newitemid = $DB->insert_record('organizer', $data);
        $this->apply_activity_instance($newitemid);
    }
    /**
     * process slotdata for restore
     * @param  $data
     */
    protected function process_slot($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->organizerid = $this->get_new_parentid('organizer');

        $data->availablefrom = $this->apply_date_offset($data->availablefrom);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->notificationtime = $this->apply_date_offset($data->notificationtime);

        $newitemid = $DB->insert_record('organizer_slots', $data);
        $this->set_mapping('slot', $oldid, $newitemid);
    }
    /**
     * process appointment data for restore
     * @param mixed $data
     */
    protected function process_appointment($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->slotid = $this->get_new_parentid('slot');

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->applicantid = $this->get_mappingid('user', $data->applicantid);
        $data->groupid = $this->get_mappingid('group', $data->groupid);

        $newitemid = $DB->insert_record('organizer_slot_appointments', $data);

        $this->set_mapping('appointment', $oldid, $newitemid);
    }
    /**
     * process trainer data for restore
     * @param mixed $data
     */
    protected function process_trainer($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->slotid = $this->get_new_parentid('slot');

        $data->trainerid = $this->get_mappingid('user', $data->trainerid);
        $data->eventid = $this->get_mappingid('event', $data->eventid);

        $newitemid = $DB->insert_record('organizer_slot_trainer', $data);

        $this->set_mapping('trainer', $oldid, $newitemid);
    }
    /**
     * process queue data for restore
     * @param mixed $data
     */
    protected function process_queue($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;

        $data->slotid = $this->get_new_parentid('slot');

        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->groupid = $this->get_mappingid('groups', $data->groupid);
        $data->applicantid = $this->get_mappingid('user', $data->applicantid);
        $data->eventid = $this->get_mappingid('event', $data->eventid);

        $newitemid = $DB->insert_record('organizer_slot_queues', $data);

        $this->set_mapping('trainer', $oldid, $newitemid);
    }
    /**
     * {@inheritDoc}
     * @see restore_structure_step::after_execute()
     */
    protected function after_execute() {
        // Nothing yet.
    }
}
