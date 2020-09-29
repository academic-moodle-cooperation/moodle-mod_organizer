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
 * @package   mod_organizer
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/lib.php');

class organizer_assign_slot_form extends moodleform
{
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        $this->_sethiddenfields();
    }

    /**
     * {@inheritDoc}
     * @see moodleform::definition_after_data()
     */
    public function definition_after_data() {
        $this->_addslotlist();
        $this->add_action_buttons(false, get_string('assign', 'organizer'));
    }
    /**
     * Set the hidden fields in the function
     */
    private function _sethiddenfields() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'assign');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'participant', $data['participant']);
        $mform->setType('participant', PARAM_INT);
        $mform->addElement('hidden', 'group', $data['group']);
        $mform->setType('group', PARAM_INT);
        $mform->addElement('hidden', 'organizerid', $data['organizerid']);
        $mform->setType('organizerid', PARAM_INT);
    }

    /**
     * adds slots to the form
     */
    private function _addslotlist() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        if ($data['group'] != 0) {
               $mform->addElement('header', 'assignheader', get_string('availableslotsfor', 'organizer') .
                       ' <strong>' . $data['groupname'] . '</strong>');
        } else {
               $mform->addElement('header', 'assignheader', get_string('availableslotsfor', 'organizer') .
                       ' <strong>' . organizer_get_name_link($data['participant']) . '</strong>');
        }

        $slots = $DB->get_records('organizer_slots', array('organizerid' => $data['organizerid']));

        $mform->addElement('html', '<table class="generaltable overview">');
        $mform->addElement('html', '<tr>');
        $mform->addElement('html',
                '<th style="vertical-align: middle;white-space: nowrap; text-align:right; padding-right:50px;" nowrap>' .
                get_string('th_datetime', 'organizer') . '</td>');
        $mform->addElement('html',
                '<th style="vertical-align: middle;white-space: nowrap; text-align:center;" nowrap>' .
                get_string('th_location', 'organizer') . '</td>');
        $mform->addElement('html',
                '<th style="vertical-align: middle;white-space: nowrap; text-align:center;" nowrap>' .
                get_string('th_teacher', 'organizer') . '</td>');
        $mform->addElement('html', '<th style="vertical-align: middle;white-space: nowrap" nowrap> </td>');
        $i = 0;
        foreach ($slots as $slot) {
            if ($this->_organizer_slot_is_free($slot, $data['participant'])) {
                $i++;
                $strradio = organizer_date_time($slot);
                $mform->addElement('html', '<tr class="free_slot">');
                $mform->addElement('html', '<td style="vertical-align: middle;white-space: nowrap" nowrap>');
                $mform->addElement('radio', 'selectedslot', '', $strradio, $slot->id);
                $mform->addElement('html', '</td>');
                $mform->addElement('html',
                        '<td style="vertical-align: middle;white-space: nowrap" nowrap>' .
                        organizer_location_link($slot) . '</td>');
                $mform->addElement('html',
                        '<td style="vertical-align: middle;white-space: nowrap" nowrap>' .
                        organizer_get_name_link($slot->teacherid) . '</td>');
                $mform->addElement('html', '<td style="vertical-align: middle;width:100%;"> </td>');
                $mform->addElement('html', '</tr>');
                if ($i == 1) {
                    $defaultid = $slot->id;
                }
            }
        }

        if ($i == 0) {
               $mform->addElement('html',
                       '<tr class="rcs-course"><td class="cell">'. get_string('nofreeslots', 'organizer') . '</td></tr>');
        } else {
               $mform->setDefault('selectedslot', $defaultid);
        }
        $mform->addElement('html', '</table>');

    }
    /**
     * looks if the slot is free for the user
     * @param mixed $slot
     * @param int $userid
     * @return boolean
     */
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
    /**
     * Adds Buttons to the form
     */
    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'assignsubmit', get_string('assign', 'organizer'));

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }



}