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
require_once("slotlib.php");

class mod_organizer_slots_delete_form extends moodleform {

    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'delete');
        $mform->setType('action', PARAM_ACTION);

        $deletableslots = false;

        if (isset($data['slots'])) {
            list($sql, $params) = $DB->get_in_or_equal($data['slots']);

            $slots = $DB->get_records_sql('SELECT * FROM {organizer_slots} WHERE {organizer_slots}.id ' . $sql, $params);

            $mform->addElement('static', '', '', '<b>' . get_string('deleteheader', 'organizer') . '</b>');

            foreach ($slots as $slot) {
                $slot = new organizer_slot($slot);
                if (!$slot->has_participants()) {
                    $deletableslots = true;
                    $mform->addElement('hidden', 'slots[]', $slot->id);
                    $date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
                    $stime = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
                    $etime = userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
                    $mform->addElement('static', '', '',
                            "$date " . get_string('slotfrom', 'organizer') . " $stime "
                                    . get_string('slotto', 'organizer') . " $etime "
                                    . get_string('atlocation', 'organizer') . " $slot->location");
                }
            }
            if (!$deletableslots) {
                $mform->addElement('static', '', '', get_string('deletenoslots', 'organizer'));
            }

            $exceptions = false;
            foreach ($slots as $slot) {
                $slot = new organizer_slot($slot);
                if ($slot->has_participants()) {
                    $exceptions = true;
                    break;
                }
            }

            if ($exceptions) {
                $mform->addElement('static', '', '', '<br/><b>' . get_string('deletekeep', 'organizer') . '</b>');
                foreach ($slots as $slot) {
                    $slot = new organizer_slot($slot);
                    if ($slot->has_participants()) {
                        $date = userdate($slot->starttime, get_string('datetemplate', 'organizer'));
                        $stime = userdate($slot->starttime, get_string('timetemplate', 'organizer'));
                        $etime = userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
                        $mform->addElement('static', '', '',
                                "$date " . get_string('slotfrom', 'organizer') . " $stime "
                                        . get_string('slotto', 'organizer') . " $etime "
                                        . get_string('atlocation', 'organizer') . " $slot->location");
                    }
                }
            }
        }

        $buttonarray = array();
        if ($deletableslots) {
            $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_delete', 'organizer'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_delete', 'organizer'),
                    array('disabled'));
        }
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}