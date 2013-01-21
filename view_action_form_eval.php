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
 * The main organizer configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package   mod_organizer
 * @copyright 2010 Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/formslib.php");
require_once("lib.php");

class organizer_evaluate_slots_form extends moodleform {

    protected function definition() {
        global $PAGE;
        $jsmodule = array(
                'name' => 'mod_organizer',
                'fullpath' => '/mod/organizer/module.js',
                'requires' => array('node-base'),
        );
        
        $PAGE->requires->js_init_call('M.mod_organizer.init_eval_form', null, false, $jsmodule);
        
        $this->_sethiddenfields();
        $this->_form->addElement('header', 'slots', get_string('eval_header', 'organizer'));
        $this->_addbuttons();
    }

    public function definition_after_data() {
        $this->_addevalfields();
    }

    private function _sethiddenfields() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'eval');
        $mform->setType('action', PARAM_ACTION);
    }

    private function _addevalfields() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $now = time();

        $i = 0;
        foreach ($data['slots'] as $slotid) {

            $mform->addElement('hidden', 'slots[' . $i++ . ']', $slotid);

            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));

            $appgroup = array();
            $checkboxname = "slotenable[{$slotid}]";
            $appgroup[] = $mform->createElement('static', '', '', '<p>');
            $box = $appgroup[] = $mform->createElement('checkbox', $checkboxname);
            $mform->setDefault($checkboxname, true);

            $date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $time = userdate($slot->starttime, get_string('timetemplate', 'organizer')) . ' - '
                    . userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));

            $appgroup[] = $mform->createElement('static', '', '', " <strong>$date, $time</strong>");

            if ($slot->starttime > $now) {
                $appgroup[] = $mform->createElement('static', '', '',
                        ' <em>(' . get_string('eval_not_occured', 'organizer') . ')</em>');
            }

            $appgroup[] = $mform->createElement('static', '', '', '</p>');

            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid));

            if ($organizer->isgrouporganizer && count($apps) != 0) {
                $app = reset($apps);
                $groupname = $DB->get_field('groups', 'name', array('id' => $app->groupid));
                $appgroup[] = $mform->createElement('static', '', '',
                        "<br/><strong>$groupname</strong> " . get_string('eval_allow_new_appointments', 'organizer'));
                $appgroup[] = $mform->createElement('advcheckbox', "allownewappointments", '', '',
                        array('group' => 0, 'class' => "allow{$slotid}"), array(0, 1));
                $mform->setType("allownewappointments", PARAM_INT);
                $mform->setDefault("allownewappointments", $app->allownewappointments);
            }

            $mform->insertElementBefore($mform->createElement('group', '', '', $appgroup, '', false), 'buttonar');

            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid));
            if (count($apps) == 0) {
                $appgroup = array();
                $appgroup[] = $mform->createElement('static', '', '', get_string('eval_no_participants', 'organizer'));
                $mform->insertElementBefore($mform->createElement('group', '', '', $appgroup, '', false), 'buttonar');
                $mform->setDefault($checkboxname, false);
                $box->freeze();
            }

            foreach ($apps as $app) {
                $user = $DB->get_record('user', array('id' => $app->userid));
                $name = "apps[{$app->id}]";

                $lastapp = organizer_get_last_user_appointment($slot->organizerid, $app->userid);
                if ($lastapp->id != $app->id) {
                    $link = new moodle_url('/mod/organizer/view_action.php',
                            array('id' => $data['id'], 'mode' => $data['mode'], 'action' => 'eval',
                                    'slots[]' => $lastapp->slotid, 'sesskey' => sesskey()));

                    $title = $this->_organizer_get_name_link($user->id) . '<br/><em>' . get_string('cannot_eval', 'organizer')
                            . '</em> ' . html_writer::link($link, get_string('eval_link', 'organizer')) . '<br/>';

                    $appgroup = array();
                    $appgroup[] = $mform->createElement('static', '', '', $title);
                } else {
                    $title = $this->_organizer_get_name_link($user->id) . '<br/>';

                    $appgroup = array();
                    $appgroup[] = $mform->createElement('static', '', '', $title);
                    $appgroup[] = $mform->createElement('static', '', '', get_string('eval_attended', 'organizer'));
                    $appgroup[] = $mform->createElement('advcheckbox', "attended", '', '', null, array(0, 1));

                    $maxgrade = $organizer->grade;
                    if ($maxgrade != 0) {
                        $grademenu = organizer_make_grades_menu_organizer($maxgrade);
                        $appgroup[] = $mform->createElement('static', '', '', get_string('eval_grade', 'organizer'));
                        $appgroup[] = $mform->createElement('select', "grade", '', $grademenu);
                    }

                    $appgroup[] = $mform->createElement('static', '', '', '<span class="nobreak">');
                    $appgroup[] = $mform->createElement('static', '', '', get_string('eval_feedback', 'organizer'));
                    $appgroup[] = $mform->createElement('text', "feedback", '', array('size' => 24));
                    $appgroup[] = $mform->createElement('static', '', '', "</span>");

                    if ($organizer->isgrouporganizer) {
                        $appgroup[] = $mform->createElement('hidden', "allownewappointments", 0,
                                array('class' => "allow{$slotid}"));
                    } else {
                        $appgroup[] = $mform->createElement('static', '', '', '<span class="nobreak">');
                        $appgroup[] = $mform->createElement('static', '', '',
                                get_string('eval_allow_new_appointments', 'organizer'));
                        $appgroup[] = $mform->createElement('advcheckbox', "allownewappointments", '', '', null,
                                array(0, 1));
                        $appgroup[] = $mform->createElement('static', '', '', "</span>");
                    }

                    $mform->disabledIf("{$name}[attended]", $checkboxname);
                    $mform->disabledIf("{$name}[grade]", $checkboxname);
                    $mform->disabledIf("{$name}[feedback]", $checkboxname);
                    $mform->disabledIf("{$name}[allownewappointments]", $checkboxname);

                    $mform->setType("{$name}[attended]", PARAM_INT);
                    $mform->setDefault("{$name}[attended]", $app->attended);

                    if ($maxgrade != 0) {
                        $mform->setType("{$name}[grade]", PARAM_INT);
                        $mform->setDefault("{$name}[grade]", $app->grade);
                    }

                    $mform->setType("{$name}[feedback]", PARAM_TEXT);
                    $mform->setDefault("{$name}[feedback]", $app->feedback);

                    $mform->setType("{$name}[allownewappointments]", PARAM_INT);
                    $mform->setDefault("{$name}[allownewappointments]", $app->allownewappointments);
                }

                $mform->insertElementBefore($mform->createElement('group', $name, '', $appgroup, ' ', true), 'buttonar');
            }

            $mform->insertElementBefore($mform->createElement('html', '<hr />'), 'buttonar');
        }
    }

    private function _organizer_get_name_link($id) {
        global $DB;
        $profileurl = new moodle_url('/user/profile.php', array('id' => $id));
        $user = $DB->get_record('user', array('id' => $id));
        $a = new stdClass();
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        $name = get_string('fullname_template', 'organizer', $a);
        if (isset($user->idnumber) && $user->idnumber !== '') {
            return html_writer::link($profileurl, $name) . " ({$user->idnumber})";
        } else {
            return html_writer::link($profileurl, $name);
        }

    }

    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'evalsubmit', get_string('evaluate', 'organizer'));

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }


}