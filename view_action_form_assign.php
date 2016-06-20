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
 * view_action_form_assign.php
 *
 * @package       mod_organizer
 * @author        Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/lib.php');

class organizer_assign_slot_form extends moodleform {

    protected function definition() {
        $this->_sethiddenfields();
    }

    public function definition_after_data() {
        $this->_addslotlist();
		$this->add_action_buttons(false, get_string('assign', 'organizer'));
 //       $this->_addbuttons();
    }

    private function _sethiddenfields() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'assign');
        $mform->setType('action', PARAM_ACTION);
        $mform->addElement('hidden', 'participant', $data['participant']);
        $mform->setType('participant', PARAM_INT);
        $mform->addElement('hidden', 'group', $data['group']);
        $mform->setType('group', PARAM_INT);
        $mform->addElement('hidden', 'organizerid', $data['organizerid']);
        $mform->setType('organizerid', PARAM_INT);
    }

    private function _addslotlist() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

		if($data['group']!=0) {
	       	$mform->addElement('header', 'assignheader', get_string('availableslotsfor', 'organizer') . ' <strong>' .$data['groupname'] . '</strong>');
		} else {
        	$mform->addElement('header', 'assignheader', get_string('availableslotsfor', 'organizer') . ' <strong>' .$this->_displayparticipant($data['participant']) . '</strong>');
 		}

        $slots = $DB->get_records('organizer_slots', array('organizerid' => $data['organizerid']));
        $organizer = $DB->get_record('organizer', array('id' => $data['organizerid']));
        
		$radioarray=array();
		$mform->addElement('html', '<table class="generaltable">');
        $i = 0;
		foreach ($slots as $slot) {
			if($this->_organizer_slot_is_free($slot, $data['participant'])) {
				$i++;
				$date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
				$time = userdate($slot->starttime, get_string('timetemplate', 'organizer')) . ' - '
						. userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));;
				$str = ''.$date.' '.$time;
				$mform->addElement('html', '<tr class="rcs-course"><td class="cell">');
				$mform->addElement('radio', 'selectedslot', '', $str, $slot->id);
				$mform->addElement('html', '</td></tr>');
				if($i==1) $defaultid = $slot->id;
			}
        }
		
		if($i==0) {
			$mform->addElement('html', '<tr class="rcs-course"><td class="cell">'. get_string('nofreeslots', 'organizer') . '</td></tr>');		
		} else {
			$mform->setDefault('selectedslot', $defaultid);
		}
		$mform->addElement('html', '</table>');
		
    }

    private function _organizer_slot_is_free($slot, $userid) {

		$slotx = new organizer_slot($slot);
		if (!$slotx->is_past_due() && !$slotx->is_full() && $slotx->is_available() ) {

			$apps = organizer_get_all_user_appointments($slotx->organizerid, $userid);
			foreach ($apps as $app) {
				if ($app->slotid == $slotx->id) {
					return false;
				}
			}
			return true;
		}
			
		return false;
    }

    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'assignsubmit', get_string('assign', 'organizer'));

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    private function _displayparticipant($userid) {
		
		return organizer_get_name_link($userid);
		
    }

}