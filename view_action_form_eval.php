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
require_once(dirname(__FILE__) . '/locallib.php');
/**
 *
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_evaluate_slots_form extends moodleform {
    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $PAGE, $DB;

        $PAGE->requires->js_call_amd('mod_organizer/evalform', 'init', array(false));

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'eval');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $this->_form->addElement('header', 'slots', get_string('eval_header', 'organizer'));

        $now = time();

        if ($slotid = reset($data['slots'])) {
            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
        }

        $i = 0;
        foreach ($data['slots'] as $slotid) {

            $mform->addElement('hidden', "slots[$i]", $slotid);
            $mform->setType("slots[$i]", PARAM_INT);
            $i++;

            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));

            // Build Slot datetime string.
            $slotdatetime = html_writer::span(organizer_date_time($slot, true), '');
            if ($slot->starttime > $now) {
                $slotdatetime .= html_writer::span(get_string('eval_not_occured', 'organizer'), 'ml-2 text-danger');
            }
            $appgroup = array();
            // Slot checkbox.
            $checkboxname = "slotenable[{$slotid}]";
            $box = $appgroup[] = $mform->createElement('checkbox', $checkboxname, '', $slotdatetime);
            $mform->setDefault($checkboxname, true);
            // Insert formgroup.
            $mform->addElement($mform->createElement('group', '', '', $appgroup, '', false));

            // Get apps (participants or groups).
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                $query = "SELECT DISTINCT a.id, a.slotid,
                        a.userid, a.groupid, a.allownewappointments, a.grade, a.attended, a.feedback,
                        u.lastname, u.firstname
                        FROM {organizer_slot_appointments} a
                        INNER JOIN {user} u ON a.userid = u.id
                        WHERE a.slotid = :slotid
                        ORDER BY u.lastname ASC, u.firstname ASC";
            } else {
                $query = "SELECT DISTINCT a.id, a.slotid,
                        a.userid, a.allownewappointments, a.grade, a.attended, a.feedback,
                        u.lastname, u.firstname
                        FROM {organizer_slot_appointments} a
                        INNER JOIN {user} u ON a.userid = u.id
                        WHERE a.slotid = :slotid
                        ORDER BY u.lastname ASC, u.firstname ASC";
            }
            $param = array('slotid' => $slot->id);
            $apps = $DB->get_records_sql($query, $param);

            // If groupmode write groupname.
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && count($apps) != 0) {
                $app = reset($apps);
                $groupname = $DB->get_field('groups', 'name', array('id' => $app->groupid));
                $mform->addElement($mform->createElement('static', 'groupname', '', $groupname));
            }

            // If no apps write it and deactivate slot.
            if (count($apps) == 0) {
                $appgroup = array();
                $appgroup[] = $mform->createElement('static', '', '', get_string('eval_no_participants', 'organizer'));
                $mform->addElement($mform->createElement('group', '', '', $appgroup, '', false));
                $mform->setDefault($checkboxname, false);
                $box->freeze();
            }

            // For each appointment of this slot.
            foreach ($apps as $app) {
                $name = "apps[{$app->id}]";
                $namelink = html_writer::div($this->_organizer_get_name_link($app->userid), 'd-block');
                $mform->addElement($mform->createElement('static', '', '', $namelink));
                // Formgroup evaluation.
                $appgroup = array();
                $appgroup[] = $mform->createElement('advcheckbox', 'attended',
                    get_string('eval_attended', 'organizer'), '', null, array(0, 1));
                $maxgrade = $organizer->grade;
                if ($maxgrade != 0) {
                    $grademenu = organizer_make_grades_menu_organizer($maxgrade);
                    $appgroup[] = $mform->createElement('select', 'grade', '', $grademenu);
                }
                $appgroup[] = $mform->createElement('static', '', '', '&nbsp;' .
                    get_string('eval_feedback', 'organizer') . ':&nbsp;');
                $appgroup[] = $mform->createElement('text', 'feedback', null, array('class' => 'w-25'));
                $mform->disabledif ("{$name}[attended]", $checkboxname);
                $mform->disabledif ("{$name}[grade]", $checkboxname);
                $mform->disabledif ("{$name}[feedback]", $checkboxname);
                $mform->setType("{$name}[attended]", PARAM_INT);
                $mform->setDefault("{$name}[attended]", $app->attended);
                if ($maxgrade != 0) {
                    $mform->setType("{$name}[grade]", PARAM_INT);
                    $mform->setDefault("{$name}[grade]", $app->grade);
                }
                $mform->setType("{$name}[feedback]", PARAM_TEXT);
                $mform->setDefault("{$name}[feedback]", $app->feedback);
                $mform->addElement($mform->createElement('group', $name, '', $appgroup, ' ', true));
            }
        }
        $this->add_action_buttons(true, get_string('evaluate', 'organizer'));
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

}
