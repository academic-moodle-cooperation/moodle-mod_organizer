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
 * mod_form.php
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

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(dirname(__FILE__) . '/locallib.php');
use core_grades\component_gradeitems;

class mod_organizer_mod_form extends moodleform_mod {

    public function definition_after_data() {
        global $PAGE, $DB;
        $mform = &$this->_form;

        $instance = $mform->getElementValue('instance');
        if ($instance) {
            $activateduedatecheckbox = false;
            // If mode of coursegroup creation is changed to 'slot creation' create groups from existing slots.
            $isgrouporganizerdb = $DB->get_field('organizer', 'isgrouporganizer', array('id' => $instance));
            $isgrouporganizerdata = $mform->getElementValue('isgrouporganizer');
            $isgrouporganizerdata = reset($isgrouporganizerdata);
            if ($isgrouporganizerdata[0] != $isgrouporganizerdb &&
                $isgrouporganizerdata[0] == ORGANIZER_GROUPMODE_NEWGROUPSLOT) {
                if ($slots = organizer_fetch_allslots($instance)) {
                    foreach ($slots as $slot) {
                        if ($participants = organizer_fetch_slotparticipants($slot->id)) {
                            foreach ($participants as $participantid) {
                                organizer_groupsynchronization($slot->id, $participantid, 'add');
                            }
                        } else {
                            organizer_create_coursegroup($slot);
                        }
                    }
                }
            }
        } else {
            $organizerconfig = get_config('organizer');
            $activateduedatecheckbox = $organizerconfig->absolutedeadline != 'never';
        }
        $params = new \stdClass();
        $params->activatecheckbox = $activateduedatecheckbox;
        $PAGE->requires->js_call_amd('mod_organizer/modform', 'init', array($params));

        parent::definition_after_data();

    }

    public function definition() {
        global $PAGE, $CFG, $DB;

        $organizerconfig = get_config('organizer');
        $mform = &$this->_form;

        // Header.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('organizername', 'organizer'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        // Intro.
        $this->standard_intro_elements();

        // Header reg dates.
        $mform->addElement('header', 'availability', get_string('availability', 'organizer'));

        // Registration dates.
        $mform->addElement(
            'date_time_selector', 'allowregistrationsfromdate', get_string('allowsubmissionsfromdate', 'organizer'),
            array('optional' => true)
        );
        $mform->setDefault('allowregistrationsfromdate', mktime(0, 0, 0, date('m'), date('d'), date('y')));
        $mform->setType('allowregistrationsfromdate', PARAM_INT);
        $mform->addHelpButton('allowregistrationsfromdate', 'allowsubmissionsfromdate', 'organizer');
        $mform->addElement(
            'date_time_selector', 'duedate', get_string('absolutedeadline', 'organizer'),
            array('optional' => true)
        );
        $mform->setDefault('duedate', mktime(0, 0, 0, date('m'), date('d'), date('y') + 1) - (5 * 60));
        $mform->setType('duedate', PARAM_INT);
        $mform->addHelpButton('duedate', 'absolutedeadline', 'organizer');
        if ($organizerconfig->absolutedeadline != 'never') {
            $absdefault = strtotime($organizerconfig->absolutedeadline);
            $mform->setDefault('duedate', $absdefault);
        }

        // Show description.
        $mform->addElement('advcheckbox', 'alwaysshowdescription', get_string('alwaysshowdescription', 'organizer'), '');
        $mform->addHelpButton('alwaysshowdescription', 'alwaysshowdescription', 'organizer');
        $mform->setDefault('alwaysshowdescription', 1);
        $mform->disabledif ('alwaysshowdescription', 'allowregistrationsfromdate[enabled]', 'notchecked');

        $mform->setExpanded('availability');

        // Header organizer settings.
        $mform->addElement('header', 'organizercommon', get_string('organizercommon', 'organizer'));

        // Allowed/Required bookings per user.
        $mform->addElement('text', 'userslotsmin', get_string('userslotsmin', 'organizer'),
            array('size' => '2', 'class' => 'text-center'));
        $mform->setType('userslotsmin', PARAM_INT);
        $mform->addElement('text', 'userslotsmax', get_string('userslotsmax', 'organizer'),
            array('size' => '2', 'class' => 'text-center'));
        $mform->setType('userslotsmax', PARAM_INT);
        $mform->addHelpButton('userslotsmin', 'userslotsmin', 'organizer');
        $mform->addHelpButton('userslotsmax', 'userslotsmax', 'organizer');
        $mform->setDefault('userslotsmin', 1);
        $mform->addHelpButton('userslotsmin', 'userslotsmin', 'organizer');
        $mform->addRule('userslotsmin', null, 'numeric', null, 'client');
        $mform->addRule('userslotsmin', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addRule('userslotsmin', null, 'required', null, 'client');
        $mform->setDefault('userslotsmax', 1);
        $mform->addRule('userslotsmax', null, 'numeric', null, 'client');
        $mform->addRule('userslotsmax', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addRule('userslotsmax', null, 'required', null, 'client');

        // Relative Deadline.
        $mform->addElement('duration', 'relativedeadline', get_string('relativedeadline', 'organizer'));
        $mform->setType('relativedeadline', PARAM_INT);
        $mform->setDefault('relativedeadline', $organizerconfig->relativedeadline);
        $mform->addHelpButton('relativedeadline', 'relativedeadline', 'organizer');

        // Grouporganizer.
        $mform->addElement('select', 'isgrouporganizer', get_string('isgrouporganizer', 'organizer'), $this->_get_groupmodes());
        $mform->setDefault('isgrouporganizer', 0);
        $mform->addHelpButton('isgrouporganizer', 'isgrouporganizer', 'organizer');
        $mform->addElement(
                'advcheckbox', 'includetraineringroups', get_string('includetraineringroups', 'organizer'), '',
                null, array(0, 1)
        );
        // Include trainers.
        $mform->addHelpButton('includetraineringroups', 'includetraineringroups', 'organizer');
        $mform->setType('includetraineringroups', PARAM_INT);
        $mform->setDefault('includetraineringroups', 0);
        $mform->disabledif ('includetraineringroups', 'isgrouporganizer', 'eq', 0);
        $mform->disabledif ('includetraineringroups', 'isgrouporganizer', 'eq', 1);

        // Member visibility.
        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->_get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->setDefault('visibility', ORGANIZER_VISIBILITY_SLOT);
        $mform->addHelpButton('visibility', 'visibility', 'organizer');

        // Teacher notifications.
        $pickeroptions = array();
        $pickeroptions[0] = get_string('messages_none', 'organizer');
        $pickeroptions[1] = get_string('messages_re_unreg', 'organizer');
        $pickeroptions[2] = get_string('messages_all', 'organizer');
        $mform->addElement('select', 'emailteachers', get_string('emailteachers', 'organizer'), $pickeroptions);
        $mform->setDefault('emailteachers', $organizerconfig->emailteachers);
        $mform->addHelpButton('emailteachers', 'emailteachers', 'organizer');

        // Queing.
        $mform->addElement('advcheckbox', 'queue', get_string('queue', 'organizer'), null, null, array(0, 1));
        $mform->setType('queue', PARAM_INT);
        $mform->addHelpButton('queue', 'queue', 'organizer');

        // Hidecalendar.
        $mform->addElement(
                'advcheckbox', 'hidecalendar', get_string('hidecalendar', 'organizer'), null, null, array(0, 1)
        );
        $mform->setType('hidecalendar', PARAM_INT);
        $mform->setDefault('hidecalendar', 1);
        $mform->addHelpButton('hidecalendar', 'hidecalendar', 'organizer');

        // Calendar events.
        $mform->addElement(
                'advcheckbox', 'nocalendareventslotcreation',
                get_string('nocalendareventslotcreation', 'organizer'), null, null, array(0, 1)
        );
        $mform->setType('nocalendareventslotcreation', PARAM_INT);
        $mform->setDefault('nocalendareventslotcreation', 1);
        $mform->addHelpButton('nocalendareventslotcreation', 'nocalendareventslotcreation', 'organizer');
        if ($this->_instance) {
            $mform->disabledIf('nocalendareventslotcreation', 'sesskey', 'neq', '');
        }

        // Grading.
        $this->standard_grading_coursemodule_elements();
        $itemnumber = 0;
        $component = "mod_organizer";
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        $mform->setDefault($gradefieldname, $organizerconfig->maximumgrade);

        // Print slot userfields.
        // Header.
        $mform->addElement('header', 'printslotuserfields', get_string('singleslotprintfields', 'organizer'));
        $mform->addHelpButton('printslotuserfields', 'singleslotprintfields', 'organizer');

        // Select print slot userfields.
        $selectableprofilefields = organizer_printslotuserfields();
        $allowedprofilefields = organizer_get_allowed_printslotuserfields();
        $allowslotprofilefieldchange = isset($organizerconfig->enableprintslotuserfields) && $organizerconfig->enableprintslotuserfields;
        $selectableprofilefields = ['' => '--'] + $selectableprofilefields;
        for ($i = 0; $i <= ORGANIZER_PRINTSLOTUSERFIELDS; $i++) {
            $fieldname = 'singleslotprintfield' . $i;
            $fieldlabel = $i + 1 . '. ' . get_string('singleslotprintfield', 'organizer');
            if (isset($organizerconfig->{'singleslotprintfield' . $i})) {
                $default = $organizerconfig->{'singleslotprintfield' . $i};
            } else {
                $default = "";
            }
            if ($allowslotprofilefieldchange) {
                $mform->addElement('select', $fieldname, $fieldlabel, $allowedprofilefields);
                $mform->setType($fieldname, PARAM_TEXT);
                $mform->setDefault($fieldname, $default);
            } else {
                $mform->addElement('static', $fieldname . 'static', $fieldlabel, $selectableprofilefields[$default]);
            }
        }
        if ($allowslotprofilefieldchange) {
            $mform->addHelpButton('singleslotprintfield0', 'singleslotprintfield0', 'organizer');
        }

        // Standard course module fields.
        $this->standard_coursemodule_elements();

        // Action buttons.
        $this->add_action_buttons();

        // Grading Aggragation Methods.
        $aggregationmethods = $mform->createElement('select', 'gradeaggregationmethod', get_string('gradeaggregationmethod', 'organizer'), $this->_get_gradeaggregationmethods());
        $mform->insertElementBefore($aggregationmethods, 'gradecat');
        $mform->setDefault('gradeaggregationmethod', 0);
        $mform->addHelpButton('gradeaggregationmethod', 'gradeaggregationmethod', 'organizer');
        $mform->disabledIf('gradeaggregationmethod', 'grade[modgrade_type]', 'neq', 'point');
        $mform->hideIf('gradeaggregationmethod', 'grade[modgrade_type]', 'neq', 'point');

        // Insert grouping-ID warning.
        $warning = $mform->createElement(
            'static', '', '',
            '<span id="groupingid_warning">' . get_string('warning_groupingid', 'organizer') . '</span>'
        );
        $mform->insertElementBefore($warning, 'groupingid');

        // Add warning popup/noscript tag, if grades are changed by user.
        $hasgrade = false;
        if (!empty($this->_instance)) {
            $hasgrade = $DB->record_exists_sql(
                'SELECT *
                    FROM {organizer_slots} slots
                    JOIN {organizer_slot_appointments} apps
                    ON slots.id = apps.slotid
                    WHERE slots.organizerid=? and NOT grade IS NULL',
                array($this->_instance)
            );
        }
        if ($mform->elementExists('grade') && $hasgrade) {
            $param = new \stdClass();
            $param->changegradewarning = get_string('changegradewarning', 'organizer');
            $PAGE->requires->js_call_amd('mod_organizer/modform', 'init_gradechange', array($param));
            // Add noscript tag in case.
            $noscriptwarning = $mform->createElement(
                'static',
                'warning',
                null,
                html_writer::tag(
                    'noscript',
                    get_string('changegradewarning', 'mod_organizer')
                )
            );
            $mform->insertElementBefore($noscriptwarning, 'grade');
        }

        $mform->setExpanded('organizercommon');
        $mform->setExpanded('modstandardelshdr');

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['allowregistrationsfromdate'] && $data['duedate']) {
            if ($data['allowregistrationsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedateerror', 'organizer');
            }
        }

        if ($data['isgrouporganizer'] == 1 && $data['groupmode'] == 0) {
            $errors['groupmode'] = get_string('warninggroupmode', 'organizer');
        } else if ($data['isgrouporganizer'] == 1 && $data['groupingid'] == 0) {
            $errors['isgrouporganizer'] = get_string('invalidgrouping', 'organizer');
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
                    $identity = $DB->get_field_select('user', 'idnumber', "id = {$userid}");
                    $a->idnumber = $identity != "" ? "({$identity})" : "";
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

        if ((int)$data['userslotsmin'] > (int)$data['userslotsmax']) {
            $errors['userslotsmin'] = get_string('userslots_mingreatermax', 'organizer');
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

    private function _get_visibilities() {

        $visibilities = array();
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('visibility_all', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('visibility_anonymous', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('visibility_slot', 'organizer');

        return $visibilities;
    }

    private function _get_groupmodes() {

        $groupmodes = array();
        $groupmodes[ORGANIZER_GROUPMODE_NOGROUPS] = get_string('groupmodenogroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_NOGROUPS] = get_string('groupmodenogroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_EXISTINGGROUPS] = get_string('groupmodeexistingcoursegroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_NEWGROUPSLOT] = get_string('groupmodeslotgroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_NEWGROUPBOOKING] = get_string('groupmodeslotgroupsappointment', 'organizer');

        return $groupmodes;
    }

    private function _get_gradeaggregationmethods() {

        $gradeaggregationmethods = array();
        $gradeaggregationmethods[0] = get_string('choose').'...';
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_AVERAGE] = get_string('aggregatemean', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_SUM] = get_string('aggregatesum', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_BEST] = get_string('aggregatemax', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_WORST] = get_string('aggregatemin', 'grades');

        return $gradeaggregationmethods;
    }
}
