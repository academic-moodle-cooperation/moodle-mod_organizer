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
 * view_action_form_remind_all.php
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

defined('MOODLE_INTERNAL') || die();

// Required for the form rendering.
require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/locallib.php');

/**
 * Class organizer_remind_all_form
 *
 * This class defines the form for the "Remind All" action in the Moodle Organizer module.
 * It generates a custom form to send reminder notifications to students or groups depending on the organizer settings.
 *
 * @package   mod_organizer
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_remind_all_form extends moodleform {

    /**
     * Defines the "Remind All" form.
     *
     * This method generates the form for sending reminder notifications
     * to students or groups, depending on the organizer settings. It sets
     * up hidden form elements for necessary data, counts recipients, and
     * allows adding a custom message to the reminder. If no recipients
     * are available, it disables the confirmation button and displays a
     * relevant message.
     *
     * @return void
     */
    protected function definition() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'remindall');
        $mform->setType('action', PARAM_ALPHANUMEXT);
        $mform->addElement('hidden', 'recipient', $data['recipient']);
        $mform->setType('recipient', PARAM_INT);
        $mform->addElement('hidden', 'recipientsstr', $data['recipients']);
        $mform->setType('recipientsstr', PARAM_TEXT);

        [, $course, $organizer, $context] = organizer_get_course_module_data();

        $recipientscount = 0;
        if ($data['recipients']) {
            $recipientsarr = $data['recipients'] ? explode(",", $data['recipients']) : [];
            $recipientscount = count($recipientsarr);
        } else if ($data['recipient']) {
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                $recipientsarr = array_keys(get_enrolled_users($context, 'mod/organizer:register', $data['recipient'],
                    'u.id', 'lastname,firstname', null, null, true));
                $recipientscount = count($recipientsarr);
                $groupname = organizer_fetch_groupname($data['recipient']);
            } else {
                $recipientsarr = [$data['recipient']];
                $recipientscount = 1;
            }
        }

        $buttonarray = [];
        if ($recipientscount > 0) {
            $a = new stdClass();
            $a->count = $recipientscount;
            if ($recipientscount == 1) {
                $mform->addElement('static', '', '', get_string('organizer_remind_all_recepients_sg', 'organizer', $a));
            } else {
                $mform->addElement('static', '', '', get_string('organizer_remind_all_recepients_pl', 'organizer', $a));
            }
            $groupnameswitch = true;
            $recipientslist = [];
            foreach ($recipientsarr as $recepient) {
                $identity = organizer_get_user_identity($recepient);
                $identity = $identity != "" ? "({$identity})" : "";
                $recipientslist[] = organizer_get_name_link($recepient) . $identity;
            }
            $mform->addElement('static', '', '', html_writer::alist($recipientslist,
                ['class' => 'generalbox'], 'ul'));
            $buttonarray[] = &$mform->createElement('submit', 'confirm',
                get_string('confirm_organizer_remind_all', 'organizer'));
            $strautomessage = "register_reminder_student";
            $strautomessage .= ($organizer->isgrouporganizer == 0) ? "" : ":group";
            $strautomessage .= ":fullmessage";

            $a = new stdClass();
            $a->receivername = get_string('recipientname', 'organizer');
            $a->courseid = ($course->idnumber == "") ? "" : $course->idnumber . ' ';
            $a->coursefullname = $course->fullname;
            $a->custommessage = "";
            $a->groupname = isset($groupname) ? $groupname : "";
            $mform->addElement(
                'static', 'message_autogenerated', get_string('message_autogenerated2', 'organizer'),
                nl2br(str_replace("\n\n\n", "\n", get_string($strautomessage, 'organizer', $a)))
            );

            $mform->addElement('editor', 'message_custommessage', get_string('message_custommessage', 'organizer'));
            $mform->addHelpButton('message_custommessage', 'message_custommessage', 'organizer');
        } else { // No recipients.
            $mform->addElement('static', '', '', get_string('organizer_remind_all_no_recepients', 'organizer'));
            $buttonarray[] = &$mform->createElement(
                'submit', 'confirm', get_string('confirm_organizer_remind_all', 'organizer'),
                ['disabled']
            );
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);
        $mform->closeHeaderBefore('buttonar');
    }
}
