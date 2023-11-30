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
 * view_action_form_delete.php
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
require_once(dirname(__FILE__) . '/slotlib.php');
require_once(dirname(__FILE__) . '/locallib.php');

class organizer_delete_slots_form extends moodleform {

    protected function definition() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'delete');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $deletableslots = false;

        if (isset($data['slots'])) {
            if (empty($data['slots'])) {
                $slots = array(0);
            } else {
                $slots = $data['slots'];
            }
            list($sql, $params) = $DB->get_in_or_equal($slots);

            $slots = $DB->get_records_sql('SELECT * FROM {organizer_slots} WHERE {organizer_slots}.id ' . $sql, $params);

            $mform->addElement('static', '', '', '<b>' . get_string('deleteheader', 'organizer') . '</b>');

            foreach ($slots as $slot) {
                $slot = new organizer_slot($slot);
                $deletableslots = true;
                $mform->addElement('hidden', 'slots[]', $slot->id);
                $mform->setType('slots[]', PARAM_INT);
                if (!$slot->has_participants()) {
                    $out = html_writer::start_div();
                    $out .= html_writer::span(organizer_date_time($slot, true), '');
                    $out .= html_writer::span($slot->location, 'ml-2');
                    $out .= html_writer::end_div();
                    $mform->addElement('static', '', '', $out);
                }
            }
            if (!$deletableslots) {
                $mform->addElement('static', '', '',
                    html_writer::span(get_string('deletenoslots', 'organizer'), 'text-danger'));
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
                        $out = html_writer::start_div();
                        $out .= html_writer::span(organizer_date_time($slot, true), '');
                        $out .= html_writer::span($slot->location, 'ml-2');
                        $out .= html_writer::end_div();
                        $mform->addElement('static', '', '', $out);
                    }
                }
            }
        }

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'confirm', get_string('confirm_delete', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}
