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
 * appointment_delete_form.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// Required for the form rendering.

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Form for deleting appointmentments
 */
class organizer_delete_appointment_form extends moodleform {

    /**
     * Defintion of class
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'appid', $data['appid']);
        $mform->setType('appid', PARAM_INT);

        $appointment = $DB->get_record('organizer_slot_appointments', ['id' => $data['appid']]);
        $slot = $DB->get_record('organizer_slots', ['id' => $appointment->slotid]);

        $text = organizer_date_time($slot, true);

        $list = '<span style="display: table-row;">';
        $list .= '<span style="display: table-cell;">';
        $list .= $text;
        $list .= '</span>';
        $list .= '</span>';
        $list .= '<span style="display: table-row;">';
        $list .= '<span style="display: table-cell;">';

        if (organizer_is_group_mode()) {
            $params['participantslist'] = 'notcollapsed';
            $list .= organizer_get_participant_list($params, $slot, null);
        } else {
            $identity = organizer_get_user_identity($appointment->userid);
            $identity = $identity != "" ? " ({$identity})" : "";
            $list .= organizer_get_name_link($appointment->userid) . $identity;
            if (organizer_is_group_mode()) {
                if ($appointment->userid == $appointment->applicantid) {
                    $list .= organizer_get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer'));
                }
            } else {
                $list .= organizer_get_teacherapplicant_output(
                    $appointment->teacherapplicantid,
                    $appointment->teacherapplicanttimemodified
                );
            }
            $list .= organizer_app_details($appointment);
        }
        $list .= '</span>';
        $list .= '</span>';

        $mform->addElement('static', '', '', $list);

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_delete', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
