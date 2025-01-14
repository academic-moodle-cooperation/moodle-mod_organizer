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

/**
 * Populates additional data and initializes the module form after it has been assembled.
 *
 * This method is executed after the form has been populated with data, to further
 * initialize and manage the module's form behavior based on available data or settings,
 * such as synchronizing groups or enabling specific form elements.
 *
 * @return void
 */
class mod_organizer_mod_form extends moodleform_mod {

    /**
     * This method is executed after the main form data has been populated.
     * It performs additional initialization and synchronization tasks,
     * such as updating group organization settings or enabling specific UI elements,
     * based on the provided form and database values.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition_after_data() {
        global $PAGE, $DB;
        $mform = &$this->_form;

        $instance = $mform->getElementValue('instance');
        if ($instance) {
            $activateduedatecheckbox = false;
            // If mode of coursegroup creation is changed to 'slot creation' create groups from existing slots.
            $isgrouporganizerdb = $DB->get_field('organizer', 'isgrouporganizer', ['id' => $instance]);
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
            $activateduedatecheckbox = $organizerconfig->absolutedeadline ?? "" != 'never';
        }
        $params = new stdClass();
        $params->activatecheckbox = $activateduedatecheckbox;
        $PAGE->requires->js_call_amd('mod_organizer/modform', 'init', [$params]);

        parent::definition_after_data();

    }

    /**
     * Defines the structure of the mod_organizer form.
     *
     * This function is responsible for setting up and adding all necessary
     * form elements to handle the configuration of the mod_organizer module.
     * These include general settings like name, intro, registration dates,
     * and organizer-specific configuration options.
     *
     * The form elements are initialized with proper default values, input rules,
     * and help buttons. This ensures that the module's configuration is user-friendly
     * and adheres to required parameters.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     */
    public function definition() {
        global $PAGE, $CFG, $DB;

        $organizerconfig = get_config('organizer');
        $mform = &$this->_form;

        // Header.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Name.
        $mform->addElement('text', 'name', get_string('organizername', 'organizer'), ['size' => '64']);
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
            ['optional' => true]
        );
        $mform->setDefault('allowregistrationsfromdate', mktime(0, 0, 0, date('m'), date('d'), date('y')));
        $mform->setType('allowregistrationsfromdate', PARAM_INT);
        $mform->addHelpButton('allowregistrationsfromdate', 'allowsubmissionsfromdate', 'organizer');
        $mform->addElement(
            'date_time_selector', 'duedate', get_string('absolutedeadline', 'organizer'),
            ['optional' => true]
        );
        $mform->setDefault('duedate', mktime(0, 0, 0, date('m'), date('d'), date('y') + 1) - (5 * 60));
        $mform->setType('duedate', PARAM_INT);
        $mform->addHelpButton('duedate', 'absolutedeadline', 'organizer');
        if (isset($organizerconfig->absolutedeadline) && $organizerconfig->absolutedeadline != 'never') {
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
            ['size' => '2', 'class' => 'text-center']);
        $mform->setType('userslotsmin', PARAM_INT);
        $mform->addElement('text', 'userslotsmax', get_string('userslotsmax', 'organizer'),
            ['size' => '2', 'class' => 'text-center']);
        $mform->setType('userslotsmax', PARAM_INT);
        $mform->addHelpButton('userslotsmin', 'userslotsmin', 'organizer');
        $mform->addHelpButton('userslotsmax', 'userslotsmax', 'organizer');
        $mform->setDefault('userslotsmin', 1);
        $mform->addRule('userslotsmin', null, 'numeric', null, 'client');
        $mform->addRule('userslotsmin', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addRule('userslotsmin', null, 'required', null, 'client');
        $mform->setDefault('userslotsmax', 1);
        $mform->addRule('userslotsmax', null, 'numeric', null, 'client');
        $mform->addRule('userslotsmax', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');
        $mform->addRule('userslotsmax', null, 'required', null, 'client');

        // Allowed bookings per user/group and day.
        $mform->addElement('text', 'userslotsdailymax', get_string('userslotsdailymax', 'organizer'),
            ['size' => '2', 'class' => 'text-center']);
        $mform->setType('userslotsdailymax', PARAM_INT);
        $mform->addHelpButton('userslotsdailymax', 'userslotsdailymax', 'organizer');
        $mform->setDefault('userslotsdailymax', 0);
        $mform->addRule('userslotsdailymax', null, 'numeric', null, 'client');
        $mform->addRule('userslotsdailymax', get_string('maximumchars', '', 2), 'maxlength', 2, 'client');

        // Relative Deadline.
        $mform->addElement('duration', 'relativedeadline', get_string('relativedeadline', 'organizer'));
        $mform->setType('relativedeadline', PARAM_INT);
        if (isset($organizerconfig->relativedeadline)) {
            $mform->setDefault('relativedeadline', $organizerconfig->relativedeadline);
        }
        $mform->addHelpButton('relativedeadline', 'relativedeadline', 'organizer');

        // Grouporganizer.
        $mform->addElement('select', 'isgrouporganizer', get_string('isgrouporganizer', 'organizer'), $this->get_groupmodes());
        $mform->setDefault('isgrouporganizer', 0);
        $mform->addHelpButton('isgrouporganizer', 'isgrouporganizer', 'organizer');
        // Include trainers.
        $mform->addElement(
                'advcheckbox', 'includetraineringroups', get_string('includetraineringroups', 'organizer'), '',
                null, [0, 1]
        );
        $mform->addHelpButton('includetraineringroups', 'includetraineringroups', 'organizer');
        $mform->setType('includetraineringroups', PARAM_INT);
        $mform->setDefault('includetraineringroups', 0);
        $mform->disabledif ('includetraineringroups', 'isgrouporganizer', 'eq', 0);
        $mform->disabledif ('includetraineringroups', 'isgrouporganizer', 'eq', 1);

        // Synchronize Moodle group members.
        $mform->addElement(
            'advcheckbox', 'synchronizegroupmembers', get_string('synchronizegroupmembers', 'organizer'), null,
            null, [0, 1]
        );
        $mform->addHelpButton('synchronizegroupmembers', 'synchronizegroupmembers', 'organizer');
        $mform->setType('synchronizegroupmembers', PARAM_INT);
        $mform->setDefault('synchronizegroupmembers', $organizerconfig->synchronizegroupmembers ?? 0);
        $mform->disabledif ('synchronizegroupmembers', 'isgrouporganizer', 'neq', 1);

        // Member visibility.
        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->setDefault('visibility', ORGANIZER_VISIBILITY_SLOT);
        $mform->addHelpButton('visibility', 'visibility', 'organizer');

        // Teacher notifications.
        $pickeroptions = [];
        $pickeroptions[0] = get_string('messages_none', 'organizer');
        $pickeroptions[1] = get_string('messages_re_unreg', 'organizer');
        $pickeroptions[2] = get_string('messages_all', 'organizer');
        $mform->addElement('select', 'emailteachers', get_string('emailteachers', 'organizer'), $pickeroptions);
        if (isset($organizerconfig->emailteachers)) {
            $mform->setDefault('emailteachers', $organizerconfig->emailteachers);
        }
        $mform->addHelpButton('emailteachers', 'emailteachers', 'organizer');

        // Queing.
        $mform->addElement('advcheckbox', 'queue', get_string('queue', 'organizer'), null, null, [0, 1]);
        $mform->setType('queue', PARAM_INT);
        $mform->addHelpButton('queue', 'queue', 'organizer');

        // Hidecalendar.
        $mform->addElement(
                'advcheckbox', 'hidecalendar', get_string('hidecalendar', 'organizer'), null, null, [0, 1]
        );
        $mform->setType('hidecalendar', PARAM_INT);
        $mform->setDefault('hidecalendar', 1);
        $mform->addHelpButton('hidecalendar', 'hidecalendar', 'organizer');

        // Calendar events.
        $mform->addElement(
                'advcheckbox', 'nocalendareventslotcreation',
                get_string('nocalendareventslotcreation', 'organizer'), null, null, [0, 1]
        );
        $mform->setType('nocalendareventslotcreation', PARAM_INT);
        $mform->setDefault('nocalendareventslotcreation', 1);
        $mform->addHelpButton('nocalendareventslotcreation', 'nocalendareventslotcreation', 'organizer');
        if ($this->_instance) {
            $mform->disabledIf('nocalendareventslotcreation', 'sesskey', 'neq', '');
        }

        // No reregistrations after deadline.
        $mform->addElement(
            'advcheckbox', 'noreregistrations', get_string('noreregistrations', 'organizer'), null, null, [0, 1]
        );
        $mform->setType('noreregistrations', PARAM_INT);
        $mform->setDefault('noreregistrations', 0);
        $mform->addHelpButton('noreregistrations', 'noreregistrations', 'organizer');

        // Grading.
        $this->standard_grading_coursemodule_elements();
        $itemnumber = 0;
        $component = "mod_organizer";
        $gradefieldname = component_gradeitems::get_field_name_for_itemnumber($component, $itemnumber, 'grade');
        if (isset($organizerconfig->maximumgrade)) {
            $mform->setDefault($gradefieldname, $organizerconfig->maximumgrade);
        }

        // Print slot userfields.
        // Header.
        $mform->addElement('header', 'printslotuserfields', get_string('singleslotprintfields', 'organizer'));
        $mform->addHelpButton('printslotuserfields', 'singleslotprintfields', 'organizer');

        // Select print slot userfields.
        $selectableprofilefields = organizer_printslotuserfields();
        $allowedprofilefields = organizer_get_allowed_printslotuserfields();
        $allowslotprofilefieldchange = isset($organizerconfig->enableprintslotuserfields) &&
            $organizerconfig->enableprintslotuserfields;
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
        $aggregationmethods = $mform->createElement('select', 'gradeaggregationmethod',
            get_string('gradeaggregationmethod', 'organizer'), $this->get_gradeaggregationmethods());
        $mform->insertElementBefore($aggregationmethods, 'gradecat');
        $mform->setDefault('gradeaggregationmethod', 0);
        $mform->addHelpButton('gradeaggregationmethod', 'gradeaggregationmethod', 'organizer');
        $mform->disabledIf('gradeaggregationmethod', 'grade[modgrade_type]', 'neq', 'point');
        $mform->hideIf('gradeaggregationmethod', 'grade[modgrade_type]', 'neq', 'point');

        // Add warning popup/noscript tag, if grades are changed by user.
        $hasgrade = false;
        if (!empty($this->_instance)) {
            $hasgrade = $DB->record_exists_sql(
                'SELECT *
                    FROM {organizer_slots} slots
                    JOIN {organizer_slot_appointments} apps
                    ON slots.id = apps.slotid
                    WHERE slots.organizerid=? and NOT grade IS NULL',
                [$this->_instance]
            );
        }
        if ($mform->elementExists('grade') && $hasgrade) {
            $param = new stdClass();
            $param->changegradewarning = get_string('changegradewarning', 'organizer');
            $PAGE->requires->js_call_amd('mod_organizer/modform', 'init_gradechange', [$param]);
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

    /**
     * Validate the submitted form values on the server.
     *
     * @param $data
     * @param $files
     * @return array An associative array where the keys are user IDs and the values are arrays of groups
     *               the user belongs to within the course or the specified grouping.
     * @throws coding_exception
     * @throws dml_exception
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['allowregistrationsfromdate'] && $data['duedate']) {
            if ($data['allowregistrationsfromdate'] > $data['duedate']) {
                $errors['duedate'] = get_string('duedateerror', 'organizer');
            }
        }

        if ($data['isgrouporganizer'] == 1 && $data['groupmode'] == 0) {
            $errors['groupmode'] = get_string('warninggroupmode', 'organizer');
        }

        if ($data['groupmode'] != 0) {
            global $DB;
            $errormsg = '';
            $error = false;
            $memberships = $this->get_memberships($data['course'], $data['groupingid']);
            foreach ($memberships as $userid => $groups) {
                if (count($groups) > 1) {
                    if (!has_capability("mod/organizer:editslots", context_course::instance($data['course']),
                        $userid)) {
                        $error = true;
                        $a = new stdClass();
                        $user = $DB->get_record('user', ['id' => $userid]);
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
            }

            if ($error) {
                $errors['groupmode'] = $errormsg . get_string('multimember', 'organizer');
            }
        }

        if ((int)$data['userslotsmin'] > (int)$data['userslotsmax']) {
            $errors['userslotsmin'] = get_string('userslots_mingreatermax', 'organizer');
        }

        return $errors;
    }

    /**
     * Retrieve the group memberships for a specific course or grouping.
     *
     * This method fetches all the groups within the specified course or grouping and identifies
     * the membership of each user. If a user belongs to multiple groups, their memberships
     * will be represented as an array of groups.
     *
     * @param int $courseid The ID of the course.
     * @param int $groupingid The ID of the grouping.
     * @return array An associative array where the keys are user IDs and the values are arrays of group objects
     *               the user belongs to within the specified grouping.
     */
    private function get_memberships($courseid, $groupingid = null) {

        $groups = groups_get_all_groups($courseid, 0, $groupingid, 'g.*', true);
        $memberships = [];
        foreach ($groups as $group) {
            foreach ($group->members as $member) {
                if (!isset($memberships[$member])) {
                    $memberships[$member] = [$group];
                } else {
                    $memberships[$member][] = $group;
                }
            }
        }

        return $memberships;
    }

    /**
     * Retrieves visibility options for the organizer.
     *
     * This function returns an associative array of available visibility settings
     * for the organizer. The keys of the array are the visibility constants defined
     * elsewhere, and the values are the respective language strings for those options.
     *
     * @return array An associative array where the keys are visibility constants
     *               and the values are localized strings for the visibility modes.
     * @throws coding_exception
     */
    private function get_visibilities() {

        $visibilities = [];
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('visibility_all', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('visibility_anonymous', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('visibility_slot', 'organizer');

        return $visibilities;
    }

    /**
     * Retrieves the available group modes for the organizer.
     *
     * This method provides an associative array of group modes that
     * can be used within the organizer. Each key in the array is a
     * constant representing a group mode, and the corresponding value
     * is the localized string describing the group mode.
     *
     * @return array An associative array where keys are group mode constants
     *               and values are localized strings for the group modes.
     * @throws coding_exception
     */
    private function get_groupmodes() {

        $groupmodes = [];
        $groupmodes[ORGANIZER_GROUPMODE_NOGROUPS] = get_string('groupmodenogroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_EXISTINGGROUPS] = get_string('groupmodeexistingcoursegroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_NEWGROUPSLOT] = get_string('groupmodeslotgroups', 'organizer');
        $groupmodes[ORGANIZER_GROUPMODE_NEWGROUPBOOKING] = get_string('groupmodeslotgroupsappointment', 'organizer');

        return $groupmodes;
    }

    /**
     * Retrieves the available grade aggregation methods for the organizer.
     *
     * This method provides an associative array of grade aggregation methods
     * that can be used within the organizer. The keys in the array represent
     * different aggregation methods, and the corresponding values are localized
     * strings describing those methods.
     *
     * @return array An associative array where keys are grade aggregation method constants
     *               and values are localized strings for the grade aggregation methods.
     * @throws coding_exception
     */
    private function get_gradeaggregationmethods() {

        $gradeaggregationmethods = [];
        $gradeaggregationmethods[0] = get_string('choose').'...';
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_AVERAGE] = get_string('aggregatemean', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_SUM] = get_string('aggregatesum', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_BEST] = get_string('aggregatemax', 'grades');
        $gradeaggregationmethods[GRADEAGGREGATIONMETHOD_WORST] = get_string('aggregatemin', 'grades');

        return $gradeaggregationmethods;
    }
}
