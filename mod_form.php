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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * mod_form.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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
        global $PAGE, $CFG, $DB;

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
        
        $mform->addElement('header','availability',get_string('availability','organizer'));

        $mform->addElement('date_time_selector', 'allowregistrationsfromdate', get_string('allowsubmissionsfromdate', 'organizer'),
                array('optional' => true));
        $mform->setDefault('allowregistrationsfromdate', mktime(0, 0, 0, date('m'), date('d'), date('y')));
        $mform->setType('allowregistrationsfromdate', PARAM_INT);
        $mform->addHelpButton('allowregistrationsfromdate', 'allowsubmissionsfromdate', 'organizer');

        $mform->addElement('date_time_selector', 'duedate', get_string('absolutedeadline', 'organizer'),
                array('optional' => true));
        $mform->setDefault('duedate', mktime(0, 0, 0, date('m'), date('d'), date('y') + 1) - (5 * 60));
        $mform->setType('duedate', PARAM_INT);
        $mform->addHelpButton('duedate', 'absolutedeadline', 'organizer');
        
        $mform->addElement('advcheckbox', 'alwaysshowdescription', get_string('alwaysshowdescription', 'organizer'), '');
        $mform->addHelpButton('alwaysshowdescription', 'alwaysshowdescription','organizer');
        $mform->setDefault('alwaysshowdescription', 1);
        $mform->disabledIf('alwaysshowdescription', 'allowregistrationsfromdate[enabled]', 'notchecked');

        $mform->setExpanded('availability');
        
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
            $mform->setDefault('duedate', $absdefault);
        }
        $this->standard_coursemodule_elements();
        $warning = $mform->createElement('static', '', '', '<span id="groupingid_warning">' . get_string('warning_groupingid', 'organizer') . '</span>');
        $mform->insertElementBefore($warning, 'groupingid');
        $this->add_action_buttons();
        
        // Add warning popup/noscript tag, if grades are changed by user.
        $hasgrade = false;
        if (!empty($this->_instance)) {
        	$hasgrade = $DB->record_exists_sql('SELECT *
					FROM {organizer_slots} slots
					JOIN {organizer_slot_appointments} apps
					ON slots.id = apps.slotid
					WHERE slots.organizerid=? and NOT grade IS NULL',
        			array($this->_instance));
        }
        
        if ($mform->elementExists('grade') && $hasgrade) {
        	$module = array(
        			'name' => 'mod_organizer',
        			'fullpath' => '/mod/organizer/module.js',
        			'requires' => array('node', 'event'),
        			'strings' => array(array('changegradewarning', 'mod_organizer'))
        	);
        	$PAGE->requires->js_init_call('M.mod_organizer.init_grade_change', null, false, $module);
        
        	// Add noscript tag in case.
        	$noscriptwarning = $mform->createElement('static',
        			'warning',
        			null,
        			html_writer::tag('noscript',
        					get_string('changegradewarning', 'mod_organizer')));
        	$mform->insertElementBefore($noscriptwarning, 'grade');
        }
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if ($data['allowregistrationsfromdate'] && $data['duedate']) {
            if ($data['allowregistrationsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedateerror', 'organizer');
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