<?php
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
 * This file contains forms needed to create new appointments for organizer
 *
 * @package    mod
 * @subpackage organizer
 * @copyright  2011 Ivan Šakić <ivan.sakic3@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// required for the form rendering

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/slotlib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_lib.php');

class organizer_remind_all_form extends moodleform {

    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'remindall');
        $mform->setType('action', PARAM_ACTION);

        list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

        $entries = organizer_organizer_get_status_table_entries(array('sort' => ''));

        $counter = 0;
        $recepients = array();
        foreach ($entries as $entry) {
            if ($entry->status == ORGANIZER_APP_STATUS_NOT_REGISTERED || $entry->status == ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP) {
                $counter++;
                $recepients[] = $entry;
            }
        }

        $buttonarray = array();
        if ($counter > 0) {
            $a = new stdClass();
            $a->count = $counter;
            if ($counter == 1) {
                $mform->addElement('static', '', '', get_string('organizer_remind_all_recepients_sg', 'organizer', $a));
            } else {
                $mform->addElement('static', '', '', get_string('organizer_remind_all_recepients_pl', 'organizer', $a));
            }
            foreach ($recepients as $recepient) {
                $mform->addElement('static', '', '',
                        organizer_get_name_link($recepient->id) . ($recepient->idnumber ? " ({$recepient->idnumber})" : ''));
            }
            $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_organizer_remind_all', 'organizer'));
        } else {
            $mform->addElement('static', '', '', get_string('organizer_remind_all_no_recepients', 'organizer'));
            $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_organizer_remind_all', 'organizer'),
                    array('disabled'));
        }
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}