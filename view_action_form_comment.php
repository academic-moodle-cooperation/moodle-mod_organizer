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

class organizer_comment_slot_form extends moodleform {
    protected function definition() {
        global $DB, $USER;

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'comment');
        $mform->setType('action', PARAM_ACTION);

        $mform->addElement('hidden', 'slot', $data['slot']);
        $mform->setType('comment', PARAM_INT);

        $mform->addElement('textarea', 'comments', get_string('appointmentcomments', 'organizer'),
                array('wrap' => 'virtual', 'rows' => '10', 'cols' => '80'));
        $mform->setType('comments', PARAM_RAW);

        $comments = $DB->get_field('organizer_slot_appointments', 'comments',
                array('slotid' => $data['slot'], 'userid' => $USER->id));

        $mform->setDefault('comments', $comments);

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'reviewslots', get_string('btn_save', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}