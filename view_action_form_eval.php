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

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/lib.php');
/**
 *
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_evaluate_slots_form extends moodleform
{
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $PAGE;

        $PAGE->requires->js_call_amd('mod_organizer/evalform', 'init', array(false));

        $this->_sethiddenfields();
        $this->_form->addElement('header', 'slots', get_string('eval_header', 'organizer'));
        $this->_addbuttons();
    }
    /**
     * {@inheritDoc}
     * @see moodleform::definition_after_data()
     */
    public function definition_after_data() {
        $this->_addevalfields();
    }
    /**
     * set the hidden fields of the form
     */
    private function _sethiddenfields() {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'eval');
        $mform->setType('action', PARAM_ALPHANUMEXT);
    }
    /**
     * add eval fields to the form
     */
    private function _addevalfields() {
        global $DB;

        $mform = $this->_form;
        $data = $this->_customdata;

        $now = time();

        if ($slotid = reset($data['slots'])) {
               $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
               $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
        }

        $i = 0;
        foreach ($data['slots'] as $slotid) {

            $mform->addElement('hidden', 'slots[' . $i++ . ']', $slotid);

            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));

            // Build Slot datetime string.
            $date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
            $time = userdate($slot->starttime, get_string('timetemplate', 'organizer')) . ' - '
                    . userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
            $slotdatetime = " <strong>$date, $time</strong>";
            if ($slot->starttime > $now) {
                $slotdatetime .= ' <em>(' . get_string('eval_not_occured', 'organizer') . ')</em>';
            }
            $appgroup = array();
            // Slot checkbox.
            $checkboxname = "slotenable[{$slotid}]";
            $box = $appgroup[] = $mform->createElement('checkbox', $checkboxname, '', $slotdatetime);
            $mform->setDefault($checkboxname, true);
            // Insert formgroup.
            $mform->insertElementBefore($mform->createElement('group', '', '', $appgroup, '', false), 'buttonar');

            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                $query = "SELECT DISTINCT a.*
                        FROM {organizer_slot_appointments} a
                        INNER JOIN {groups} g ON g.id = a.groupid
                        INNER JOIN {groups_members} gm ON gm.groupid = g.id
                        INNER JOIN {user} u ON gm.userid = u.id
                        WHERE a.slotid = :slotid
                        ORDER BY g.name ASC, u.lastname ASC, u.firstname ASC";
            } else {
                $query = "SELECT DISTINCT a.*
                        FROM {organizer_slot_appointments} a
                        INNER JOIN {user} u ON a.userid = u.id
                        WHERE a.slotid = :slotid
                        ORDER BY u.lastname ASC, u.firstname ASC";
            }
            $param = array('slotid' => $slot->id);
            $apps = $DB->get_records_sql($query, $param);

            // If groupmode write groupname and checkbox allownewappointments here.
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && count($apps) != 0) {
                $app = reset($apps);
                $groupname = $DB->get_field('groups', 'name', array('id' => $app->groupid));
                $mform->insertElementBefore(
                    $mform->createElement(
                        'advcheckbox', "allownewappointments{$slotid}", $groupname, get_string('eval_allow_new_appointments', 'organizer'),
                        array('group' => 0, 'class' => "allow{$slotid}"), array(0, 1)
                    ), 'buttonar'
                );
                $mform->setType("allownewappointments{$slotid}", PARAM_INT);
                $mform->setDefault("allownewappointments{$slotid}", $app->allownewappointments);
            }

            // If no apps write it and deactivate slot.
            if (count($apps) == 0) {
                $appgroup = array();
                $appgroup[] = $mform->createElement('static', '', '', get_string('eval_no_participants', 'organizer'));
                $mform->insertElementBefore($mform->createElement('group', '', '', $appgroup, '', false), 'buttonar');
                $mform->setDefault($checkboxname, false);
                $box->freeze();
            }

            // For each appointment of this slot.
            foreach ($apps as $app) {
                $user = $DB->get_record('user', array('id' => $app->userid));
                $name = "apps[{$app->id}]";

                $finalgrade = organizer_get_finalgrade_overwritten($organizer->id, $user->id);

                $lastapp = organizer_get_last_user_appointment($organizer, $app->userid);

                if ($lastapp->id != $app->id) { // If not lastapp - no evaluation of this app.

                    $link = new moodle_url(
                        '/mod/organizer/view_action.php', array('id' => $data['id'], 'mode' => $data['mode'], 'action' => 'eval',
                        'slots[]' => $lastapp->slotid, 'sesskey' => sesskey())
                    );

                    $title = $this->_organizer_get_name_link($user->id) . '<br/><em>' . get_string('cannot_eval', 'organizer')
                            . '</em> ' . html_writer::link($link, get_string('eval_link', 'organizer')) . '<br/>';
                    $appgroup = array();
                    $appgroup[] = $mform->createElement('static', '', '', $title);

                } else { // If lastapp - evaluation of this app.

                    $title = $this->_organizer_get_name_link($user->id) . '<br/>';

                    $appgroup = array();
                    $appgroup[] = $mform->createElement('static', '', '', $title);
                    $appgroup[] = $mform->createElement('advcheckbox', 'attended', get_string('eval_attended', 'organizer'), '',
                            null, array(0, 1));

                    $maxgrade = $organizer->grade;
                    if ($maxgrade != 0) {

                        $grademenu = organizer_make_grades_menu_organizer($maxgrade);
                        if ($finalgrade !== false) {
                            $appgrade = $app->grade == "0" ? "-1" : $app->grade;
                            $appgroup[] = $mform->createElement(
                                'hidden', 'grade', $appgrade, array('class' => "allow{$slotid}")
                            );
                            $appgroup[] = $select = $mform->createElement(
                                'select', 'gradenothing', '', $grademenu, array('disabled' => 'disabled')
                            );
                            $appgroup[] = $mform->createElement('static', '', '', organizer_display_finalgrade($finalgrade));
                        } else {
                            $appgroup[] = $mform->createElement('select', 'grade', '', $grademenu);
                        }
                    }

                    $appgroup[] = $mform->createElement('static', '', '', get_string('eval_feedback', 'organizer') . ':&nbsp;');
                    $appgroup[] = $mform->createElement('text', 'feedback', null, array('size' => 32));

                    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                        $appgroup[] = $mform->createElement(
                            'hidden', 'allownewappointments', 0, array('class' => "allow{$slotid}")
                        );
                    } else {
                        $appgroup[] = $mform->createElement(
                            'static', '', '', '&nbsp;'.get_string('eval_allow_new_appointments', 'organizer').':&nbsp;'
                        );
                        $appgroup[] = $mform->createElement(
                            'advcheckbox', 'allownewappointments', '', '', null, array(0, 1)
                        );
                    }

                    $mform->disabledif ("{$name}[attended]", $checkboxname);
                    $mform->disabledif ("{$name}[grade]", $checkboxname);
                    $mform->disabledif ("{$name}[feedback]", $checkboxname);
                    $mform->disabledif ("{$name}[allownewappointments]", $checkboxname);

                    $mform->setType("{$name}[attended]", PARAM_INT);
                    $mform->setDefault("{$name}[attended]", $app->attended);

                    if ($maxgrade != 0) {
                        $mform->setType("{$name}[grade]", PARAM_INT);
                        if ($finalgrade) {
                            $mform->setType("{$name}[gradenothing]", PARAM_INT);
                            $mform->setDefault("{$name}[gradenothing]", $app->grade);
                        } else {
                            $mform->setDefault("{$name}[grade]", $app->grade);
                        }
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
    /**
     * @param int $id
     * @return string
     */
    private function _organizer_get_name_link($id) {
        global $DB;
        $profileurl = new moodle_url('/user/profile.php', array('id' => $id));
        $user = $DB->get_record('user', array('id' => $id));
        $a = new stdClass();
        $a->firstname = $user->firstname;
        $a->lastname = $user->lastname;
        $name = get_string('fullname_template', 'organizer', $a);
        $identity = organizer_get_user_identity($user);
        $identity = $identity ? "&nbsp;(" . $identity . ")" : "";
        return html_writer::link($profileurl, $name) . $identity;
    }
    /**
     * add buttons to the form
     */
    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'evalsubmit', get_string('evaluate', 'organizer'));

        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }


}