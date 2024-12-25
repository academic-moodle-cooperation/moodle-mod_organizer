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

// Required for the form rendering.

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/slotlib.php');
/**
 * view_action_form_comment.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_comment_slot_form extends moodleform {
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $DB, $USER;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'slot', $data['slot']);
        $mform->setType('slot', PARAM_INT);
        $mform->setType('comment', PARAM_INT);

        $mform->addElement(
            'textarea', 'comments', get_string('appointmentcomments', 'organizer'),
            ['wrap' => 'virtual', 'rows' => '10', 'cols' => '80']
        );
        $mform->setType('comments', PARAM_RAW);

        $comments = $DB->get_field(
            'organizer_slot_appointments', 'comments',
            ['slotid' => $data['slot'], 'userid' => $USER->id]
        );

        $mform->setDefault('comments', $comments);

        $buttonarray = [];

        $buttonarray[] = &$mform->createElement('submit', 'reviewslots', get_string('btn_save', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
