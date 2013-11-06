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
 * @copyright 2011 Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/lib.php');

class mod_organizer_mod_form extends moodleform_mod {

    public function definition_after_data() {
        global $DB, $PAGE;
        $mform = &$this->_form;
        
        $jsmodule = array(
                'name' => 'mod_organizer',
                'fullpath' => '/mod/organizer/module.js',
                'requires' => array('node-base', 'node-event-simulate'),
        );
        
        $organizerconfig = get_config('organizer');
        $togglecheckbox = $organizerconfig->absolutedeadline == 'never';

        $instance = $mform->getElementValue('instance');
        if ($instance) {
            $PAGE->requires->js_init_call('M.mod_organizer.init_mod_form', array(false), false, $jsmodule);
        } else {
            $organizerconfig = get_config('organizer');
            $activatecheckbox = $organizerconfig->absolutedeadline == 'never';
            $PAGE->requires->js_init_call('M.mod_organizer.init_mod_form', array($togglecheckbox), false, $jsmodule);
        }
    }

    public function definition() {
        global $PAGE, $CFG;

        $organizerconfig = get_config('organizer');

        $mform = &$this->_form;
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('organizername', 'organizer'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->add_intro_editor($organizerconfig->requiremodintro);

        //------ MY ELEMENTS -----------------------------------------------------------

        $mform->addElement('date_time_selector', 'enablefrom', get_string('timeavailable', 'organizer'),
                array('optional' => true));
        $mform->setDefault('enablefrom', mktime(0, 0, 0, date('m'), date('d'), date('y')));
        $mform->setType('enablefrom', PARAM_INT);
        $mform->addHelpButton('enablefrom', 'timeavailable', 'organizer');

        $mform->addElement('date_time_selector', 'enableuntil', get_string('absolutedeadline', 'organizer'),
                array('optional' => true));
        $mform->setDefault('enableuntil', mktime(0, 0, 0, date('m'), date('d'), date('y') + 1) - (5 * 60));
        $mform->setType('enableuntil', PARAM_INT);
        $mform->addHelpButton('enableuntil', 'absolutedeadline', 'organizer');

        $this->standard_grading_coursemodule_elements();
        $mform->setDefault('grade', $organizerconfig->maximumgrade);

        $mform->addElement('header', 'organizercommon', get_string('organizercommon', 'organizer'));

        $mform->addElement('duration', 'relativedeadline', get_string('relativedeadline', 'organizer'));
        $mform->setType('relativedeadline', PARAM_INT);
        $mform->setDefault('relativedeadline', $organizerconfig->relativedeadline);
        $mform->addHelpButton('relativedeadline', 'relativedeadline', 'organizer');

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'isgrouporganizer',
                get_string('isgrouporganizer', 'organizer'), null, null, array(0, 1));
        $mform->setType('isgrouporganizer', PARAM_INT);
        $mform->addGroup($group, 'isgrouporganizergroup', get_string('isgrouporganizer', 'organizer'), null, false);
        $mform->addHelpButton('isgrouporganizergroup', 'isgrouporganizer', 'organizer');

        $pickeroptions = array();
        $pickeroptions[0] = get_string('messages_none', 'organizer');
        $pickeroptions[1] = get_string('messages_re_unreg', 'organizer');
        $pickeroptions[2] = get_string('messages_all', 'organizer');
        
        $mform->addElement('select', 'emailteachers', get_string('emailteachers', 'organizer'), $pickeroptions);
        $mform->setDefault('emailteachers', $organizerconfig->emailteachers);
        $mform->addHelpButton('emailteachers', 'emailteachers', 'organizer');

        if ($organizerconfig->absolutedeadline != 'never') {
            $absdefault = strtotime($organizerconfig->absolutedeadline);
            $mform->setDefault('enableuntil', $absdefault);
        }
        $this->standard_coursemodule_elements();
        $warning = $mform->createElement('static', '', '', '<span id="groupingid_warning">' . get_string('warning_groupingid', 'organizer') . '</span>');
        $mform->insertElementBefore($warning, 'groupingid');
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['enablefrom'] && $data['enableuntil']) {
            if ($data['enablefrom'] > $data['enableuntil']) {
                $errors['enableuntil'] = get_string('enableuntilerror', 'organizer');
            }
        }

        if ($data['instance'] != 0) { // skip the group check for old organizer compatibility
            return $errors;
        }

        if ($data['isgrouporganizer'] == 1 && $data['groupmode'] == 0) {
            $errors['isgrouporganizer'] = get_string('groupwarning', 'organizer');
            $errors['groupmode'] = get_string('warninggroupmode', 'organizer');
        }

        if ($data['groupmode'] != 0 && $data['groupingid'] == 0) {
            $errormsg = get_string('invalidgrouping', 'organizer');
            $errors['groupingid'] = $errormsg;
        } else if ($data['groupmode'] != 0 && $data['groupingid'] != 0) {
            global $DB;
            $errormsg = '';
            $error = false;
            $memberships = $this->_get_memberships($data['groupingid']);
            foreach ($memberships as $userid => $groups) {
                if (count($groups) > 1) {
                    $error = true;
                    $a = new stdClass();
                    $user = $DB->get_record('user', array('id' => $userid));
                    $a->username = fullname($user);
                    $a->idnumber = $user->idnumber != "" ? "({$user->idnumber})" : "";
                    $grouplist = "";
                    $first = true;
                    foreach ($groups as $group) {
                        if ($first) {
                            $grouplist .= "$group->name";
                            $first = false;
                        } else {
                            $grouplist .= ", $group->name";
                        }
                    }
                    $a->groups = $grouplist;
                    $errormsg .= get_string('multimemberspecific', 'organizer', $a) . "<br />";
                }
            }

            if ($error) {
                $errors['groupingid'] = $errormsg . get_string('multimember', 'organizer');
            }
        }

        return $errors;
    }

    private function _get_memberships($groupingid) {
        global $DB;

        $querygroups = "SELECT {groups}.* FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid";
        $groups = $DB->get_records_sql($querygroups, array('groupingid' => $groupingid));

        $memberships = array();
        foreach ($groups as $group) {
            $queryusers = "SELECT {user}.* FROM {user}
                INNER JOIN {groups_members} ON {user}.id = {groups_members}.userid
                WHERE {groups_members}.groupid = :groupid";
            $users = $DB->get_records_sql($queryusers, array('groupid' => $group->id));

            foreach ($users as $user) {
                if (!isset($memberships[$user->id])) {
                    $memberships[$user->id] = array($group);
                } else {
                    $memberships[$user->id][] = $group;
                }
            }
        }

        return $memberships;
    }
}