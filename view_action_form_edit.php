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

define('ORGANIZER_SPACING', '&nbsp;&nbsp;');

// Required for the form rendering.

require_once("$CFG->libdir/formslib.php");

/**
 * view_action_form_edit.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_edit_slots_form extends moodleform {

    /**
     * Spacing used for rendering form elements, depending on page layout.
     *
     * @var string
     */
    private $spacing;

    /**
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $CFG, $PAGE;

        $pagebodyclasses = $PAGE->bodyclasses;
        if (strpos($pagebodyclasses, 'limitedwidth')) {
            $this->spacing = '';
        } else {
            $this->spacing = ORGANIZER_SPACING;
        }

        $params = new stdClass();
        $params->imagepaths = [
            'warning' => "{$CFG->wwwroot}/mod/organizer/pix/warning.png",
            'changed' => "{$CFG->wwwroot}/mod/organizer/pix/warning2.png"];
        $params->warningtext1 = get_string("warningtext1", "organizer");
        $params->warningtext2 = get_string("warningtext2", "organizer");

        $PAGE->requires->js_call_amd('mod_organizer/editform', 'init', [$params]);

        $defaults = $this->get_defaults();
        $this->sethiddenfields();
        $this->addfields($defaults);
        $this->addbuttons();
        $this->set_data($defaults);
    }

    /**
     * Retrieves the default values for the organizer edit slots form.
     *
     * This method fetches the default settings and values for various fields based
     * on the provided slot IDs. It ensures defaults are only set if all selected slots
     * have consistent values for a particular field. If any discrepancies are detected
     * between the slot values for a field, that field is excluded from the defaults.
     *
     * @return array An associative array of default values for the form fields.
     * @throws coding_exception
     * @throws dml_exception
     */
    private function get_defaults() {
        global $DB;

        $defaults = [];
        $defset = ['visible' => false,  'trainerids' => false, 'visibility' => false,  'comments' => false,
                        'location' => false, 'locationlink' => false,
                        'maxparticipants' => false, 'availablefrom' => false, 'teachervisible' => false,
                        'notificationtime' => false];

        $slotids = $this->_customdata['slots'];

        $defaults['now'] = 0;

        foreach ($slotids as $slotid) {
            $slot = $DB->get_record('organizer_slots', ['id' => $slotid]);

            // Only if all values of the selected slots are equal defaults[] will be set.

            if (!isset($defaults['visible']) && !$defset['visible']) {
                $defaults['visible'] = $slot->visible;
                $defset['visible'] = true;
            } else {
                if (isset($defaults['visible']) && $defaults['visible'] != $slot->visible) {
                    unset($defaults['visible']);
                }
            }
            $trainerids = organizer_get_slot_trainers($slotid);
            if (!isset($defaults['trainerids']) && !$defset['trainerids']) {
                $defaults['trainerids'] = $trainerids;
                $defset['trainerids'] = true;
            } else {
                if (isset($defaults['trainerids']) && $defaults['trainerids'] != $trainerids) {
                    unset($defaults['trainerids']);
                }
            }
            if (!isset($defaults['visibility']) && !$defset['visibility']) {
                $defaults['visibility'] = $slot->visibility;
                $defset['visibility'] = true;
            } else {
                if (isset($defaults['visibility']) && $defaults['visibility'] != $slot->visibility) {
                    unset($defaults['visibility']);
                }
            }
            if (!isset($defaults['comments']) && !$defset['comments']) {
                $defaults['comments'] = $slot->comments;
                $defset['comments'] = true;
            } else {
                if (isset($defaults['comments']) && $defaults['comments'] != $slot->comments) {
                    unset($defaults['comments']);
                }
            }
            if (!isset($defaults['location']) && !$defset['location']) {
                $defaults['location'] = $slot->location;
                $defset['location'] = true;
            } else {
                if (isset($defaults['location']) && $defaults['location'] != $slot->location) {
                    unset($defaults['location']);
                }
            }
            if (!isset($defaults['locationlink']) && !$defset['locationlink']) {
                $defaults['locationlink'] = $slot->locationlink;
                $defset['locationlink'] = true;
            } else {
                if (isset($defaults['locationlink']) && $defaults['locationlink'] != $slot->locationlink) {
                    unset($defaults['locationlink']);
                }
            }
            if (!isset($defaults['maxparticipants']) && !$defset['maxparticipants']) {
                $defaults['maxparticipants'] = $slot->maxparticipants;
                $defset['maxparticipants'] = true;
            } else {
                if (isset($defaults['maxparticipants']) && $defaults['maxparticipants'] != $slot->maxparticipants) {
                    unset($defaults['maxparticipants']);
                }
            }
            if (!isset($defaults['availablefrom']) && !$defset['availablefrom']) {
                $defaults['availablefrom'] = $slot->availablefrom;
                $defset['availablefrom'] = true;
                if ($slot->availablefrom == 0) {
                    $defaults['now'] = 1;
                }
            } else {
                if (isset($defaults['availablefrom']) && $defaults['availablefrom'] != $slot->availablefrom) {
                    unset($defaults['availablefrom']);
                }
            }
            if (!isset($defaults['teachervisible']) && !$defset['teachervisible']) {
                $defaults['teachervisible'] = $slot->teachervisible;
                $defset['teachervisible'] = true;
            } else {
                if (isset($defaults['teachervisible']) && $defaults['teachervisible'] != $slot->teachervisible) {
                    unset($defaults['teachervisible']);
                }
            }
            if (!isset($defaults['notificationtime']) && !$defset['notificationtime']) {
                $defset['notificationtime'] = true;
                $timeunit = $this->organizer_figure_out_unit($slot->notificationtime);
                $defaults['notificationtime']['number'] = $slot->notificationtime / $timeunit;
                $defaults['notificationtime']['timeunit'] = $timeunit;
            } else {
                if (isset($defaults['notificationtime'])
                    && $defaults['notificationtime']['number'] != $slot->notificationtime / $timeunit
                ) {
                    unset($defaults['notificationtime']);
                }
            }
        }

        if (!isset($defaults['visibility']) && !$defset['visibility']) {
               $instance = organizer_get_course_module_data_new();
               $defaults['visibility'] = $instance->organizer->visibility;
        }

        return $defaults;
    }
    /**
     * add submit, reset and cancel buttons
     */
    private function addbuttons() {
        $mform = $this->_form;

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'editsubmit', get_string('edit_submit', 'organizer'));
        $buttonarray[] = &$mform->createElement('reset', 'editreset', get_string('revert'), ['class' => 'btn btn-secondary']);
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', ['&nbsp;'], false);
        $mform->closeHeaderBefore('buttonar');
    }
    /**
     * set hidden fields in form
     */
    private function sethiddenfields() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('hidden', 'warningtext1', get_string('warningtext1', 'organizer'));
        $mform->setType('warningtext1', PARAM_TEXT);
        $mform->addElement('hidden', 'warningtext2', get_string('warningtext2', 'organizer'));
        $mform->setType('warningtext2', PARAM_TEXT);

        for ($i = 0; $i < count($data['slots']); $i++) {
            $mform->addElement('hidden', "slots[$i]", $data['slots'][$i]);
            $mform->setType("slots[$i]", PARAM_INT);
        }
    }

    /**
     * Adds form fields based on the provided defaults and custom data.
     *
     * This method is responsible for rendering the form fields required to
     * allow users to configure slot and visibility details, trainer assignments,
     * location details, and more. It automatically handles default values and
     * ensures proper initialization of each form element.
     *
     * @param array $defaults An array of default values for form fields. Includes keys like:
     *                        - 'visible': Slot visibility.
     *                        - 'trainerids': IDs of trainers.
     *                        - 'location': Slot location.
     *                        - 'teachervisible': Whether teachers can view the slot.
     *                        - 'visibility': Slot visibility level.
     *
     * @throws coding_exception If required settings or defaults are missing.
     */
    private function addfields($defaults) {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('header', 'slotdetails', get_string('slotdetails', 'organizer'));

        $slots = [];
        for ($i = 0; $i < count($data['slots']); $i++) {
            $slots[] = $data['slots'][$i];
        }
        $mform->addElement('hidden', "apps", organizer_count_slotappointments($slots));
        $mform->setType('apps', PARAM_INT);

        $group = [];
        $group[] = $mform->createElement(
                'advcheckbox', 'visible', get_string('visible', 'organizer'), null, null, [0, 1]
        );
        $group[] = $mform->createElement('static', '', '',
                $this->warning_icon('visible', isset($defaults['visible']))
        );
        $mform->addGroup($group, '', get_string('visible', 'organizer'), $this->spacing, false);
        $mform->setDefault('visible', 1);
        $mform->disabledIf('visible', 'apps', 'neq', 0);
        $mform->addElement('hidden', 'mod_visible', 0);
        $mform->setType('mod_visible', PARAM_BOOL);

        $trainers = $this->load_trainers($defaults);
        $group = [];
        $group[] = $mform->createElement('select', 'trainerid', get_string('teacher', 'organizer'),
            $trainers, ['multiple' => 'true']);
        $group[] = $mform->createElement('static', '', '', $this->warning_icon('teacherid', isset($defaults['trainerids'])));
        $mform->setType('trainerid', PARAM_INT);
        $mform->addGroup($group, 'teachergrp', get_string('teacher', 'organizer'), $this->spacing, false);
        if (isset($defaults['trainerids'])) {
            $mform->setDefault('trainerid', $defaults['trainerids']);
        }
        $mform->addElement('hidden', 'mod_trainerid', 0);
        $mform->setType('mod_trainerid', PARAM_BOOL);

        $group = [];
        $group[] = $mform->createElement('advcheckbox', 'teachervisible', get_string('teachervisible', 'organizer'),
            null, null, [0, 1]
        );

        $group[] = $mform->createElement('static', '', '',
                $this->warning_icon('teachervisible', isset($defaults['teachervisible']))
        );

        $mform->setDefault('teachervisible', 1);
        $mform->addGroup($group, '', get_string('teachervisible', 'organizer'), $this->spacing, false);
        $mform->addElement('hidden', 'mod_teachervisible', 0);
        $mform->setType('mod_teachervisible', PARAM_BOOL);

        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->addElement('hidden', 'mod_visibility', 0);
        $mform->setType('mod_visibility', PARAM_BOOL);

        $locations = get_config('mod_organizer', 'locations');
        if (!$locations) {
            $group = [];
            $group[] = $mform->createElement('text', 'location', get_string('location', 'organizer'), ['size' => '64']);
            $mform->setType('location', PARAM_TEXT);
            $group[] = $mform->createElement('static', '', '',
                $this->warning_icon('location', isset($defaults['location'])));
            $mform->addGroup($group, 'locationgroup', get_string('location', 'organizer'), $this->spacing, false);
            if ($locationmandatory = get_config('organizer', 'locationmandatory')) {
                $mform->addRule('locationgroup', null, 'required');
            }
        } else {
            $locations = explode("\n", $locations);
            $locations = array_combine($locations, $locations);
            $group = [];
            $group[] = $mform->createElement('autocomplete', 'location', null, $locations, ['tags' => true]);
            $group[] = $mform->createElement('static', '', '', $this->warning_icon('location', isset($defaults['location'])));

            $mform->addGroup($group, 'locationgroup', get_string('location', 'organizer'), $this->spacing, false);
            if ($locationmandatory = get_config('organizer', 'locationmandatory')) {
                $mform->addRule('locationgroup', null, 'required');
            }
        }

        $mform->addElement('hidden', 'mod_location', 0);
        $mform->setType('mod_location', PARAM_BOOL);

        $group = [];
        $group[] = $mform->createElement('text', 'locationlink', get_string('locationlink', 'organizer'),
            ['size' => '64', 'group' => null]
        );
        $group[] = $mform->createElement(
            'static', '', '',
            $this->warning_icon('locationlink', isset($defaults['locationlink']))
        );
        $mform->setType('locationlink', PARAM_URL);

        $mform->addGroup($group, 'locationlinkgroup', get_string('locationlink', 'organizer'), $this->spacing, false);
        $mform->addElement('hidden', 'mod_locationlink', 0);
        $mform->setType('mod_locationlink', PARAM_BOOL);

        if (!organizer_is_group_mode()) {
            $group = [];
            $group[] = $mform->createElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'),
                ['size' => '3', 'group' => null]
            );
            $group[] = $mform->createElement('static', '', '',
                $this->warning_icon('maxparticipants', isset($defaults['maxparticipants']))
            );

            $mform->addGroup($group, 'maxparticipantsgroup', get_string('maxparticipants', 'organizer'), $this->spacing, false);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('mod_maxparticipants', PARAM_BOOL);
            $mform->setType('maxparticipants', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'maxparticipants', 1);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('maxparticipants', PARAM_INT);
            $mform->setType('mod_maxparticipants', PARAM_BOOL);
        }

        $group = [];
        $group[] = $mform->createElement('duration', 'availablefrom');
        $group[] = $mform->createElement(
            'static', '', '',
            get_string('relative_deadline_before', 'organizer')
        );
        $group[] = $mform->createElement('checkbox', 'availablefrom[now]', get_string('relative_deadline_now', 'organizer'));
        $group[] = $mform->createElement(
            'static', '', '',
            $this->warning_icon('availablefrom', isset($defaults['availablefrom']))
        );

        $mform->setDefault('availablefrom', isset($defaults['availablefrom']) ? $defaults['availablefrom'] : 604800);
        $mform->setDefault('availablefrom[now]', $defaults['now']);

        $mform->addGroup($group, 'availablefromgroup', get_string('availablefrom', 'organizer'), $this->spacing, false);

        $mform->disabledIf('availablefrom[number]', 'availablefrom[now]', 'checked');
        $mform->disabledIf('availablefrom[timeunit]', 'availablefrom[now]', 'checked');

        $availablefromgroup = $mform->getElement('availablefromgroup')->getElements();
        $availablefrom = $availablefromgroup[0]->getElements();
        $availablefrom[1]->removeOption(1);

        $mform->addElement('hidden', 'mod_availablefrom', 0);
        $mform->setType('mod_availablefrom', PARAM_BOOL);

        $group = [];
        $group[] = $mform->createElement('duration', 'notificationtime', null, null, null, [0, 1]);
        $group[] = $mform->createElement('static', '', '',
            $this->warning_icon('notificationtime', isset($defaults['notificationtime']))
        );

        $mform->setDefault('notificationtime', isset($defaults['notificationtime']) ? $defaults['notificationtime'] : 86400);
        $mform->addGroup($group, 'notificationtimegroup', get_string('notificationtime', 'organizer'), $this->spacing, false);
        $mform->addElement('hidden', 'mod_notificationtime', 0);
        $mform->setType('mod_notificationtime', PARAM_BOOL);

        $notificationtimegroup = $mform->getElement('notificationtimegroup')->getElements();
        $notificationtime = $notificationtimegroup[0]->getElements();
        $notificationtime[1]->removeOption(1);

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        $group = [];
        $group[] = $mform->createElement(
            'textarea', 'comments', get_string('appointmentcomments', 'organizer'),
            ['wrap' => 'virtual', 'rows' => '10', 'cols' => '60']
        );
        $group[] = $mform->createElement(
            'static', '', '',
            $this->warning_icon('comments', isset($defaults['comments']))
        );

        $mform->setDefault('comments', '');
        $mform->addGroup($group, '', get_string('appointmentcomments', 'organizer'), $this->spacing, false);
        $mform->addElement('hidden', 'mod_comments', 0);
        $mform->setType('mod_comments', PARAM_BOOL);
    }

    /**
     * Converts a value to an integer and verifies if the conversion is valid.
     *
     * This function checks whether the given value is numeric and,
     * if the value is both an integer and a float, determines that
     * the conversion is valid.
     *
     * @param mixed $value The value to be converted and validated.
     * @return bool Returns true if the value can be converted to an
     * integer and is valid, otherwise false.
     */
    private function converts_to_int($value) {
        if (is_numeric($value)) {
            if (intval($value) == floatval($value)) {
                return true;
            }
        }
        return false;
    }

    /**
     * Performs server-side validation for form data.
     *
     * This function overrides the parent validation method to add custom
     * validation logic for multiple fields such as checking integer values,
     * positive value requirements, mandatory location fields, and other specific
     * rules applicable to the organizer.
     *
     * @param array $data The submitted form data to validate.
     * @param array $files The uploaded files (if any) to validate.
     * @return array An array of validation error messages, where keys are field names
     * and values are explanatory error strings. Returns an empty array if no errors are found.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['mod_maxparticipants'] != 0
            && (!$this->converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0)
        ) {
            $errors['maxparticipantsgroup'] = get_string('err_posint', 'organizer');
        }

        if ($data['mod_notificationtime'] != 0
            && (!$this->converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0)
        ) {
            $errors['notificationtimegroup'] = get_string('err_posint', 'organizer');
        }

        $locationmandatory = get_config('organizer', 'locationmandatory');

        if ($data['mod_location'] != 0 && (!isset($data['location']) || $data['location'] === '')
            && $locationmandatory) {
            $errors['locationgroup'] = get_string('err_location', 'organizer');
        }

        return $errors;
    }

    /**
     * Retrieves the submitted form data and performs additional processing for specific fields.
     *
     * This method overrides the parent `get_data` method to provide additional handling
     * for certain fields, such as assigning the 'location' field directly from the POST
     * request data if it's set.
     *
     * @return stdClass|null The processed form data as an object, or null if no data is available.
     */
    public function get_data() {
        if ($data = parent::get_data()) {
            if (isset($_POST['location']) && $_POST['location']) {
                $data->location = $_POST['location'];
            }
            return $data;
        }
    }

    /**
     * Loads the list of trainers who have the capability to lead slots.
     *
     * Fetches users with the 'mod/organizer:leadslots' capability for the current context,
     * and formats their names and email addresses into a readable format.
     *
     * @return string[] An associative array of trainers, where keys are trainer IDs,
     * and values are formatted strings containing the trainer's full name and email address.
     */
    private function load_trainers() {
        $context = organizer_get_context();

        $trainersraw = get_users_by_capability($context, 'mod/organizer:leadslots');

        $trainers = [];
        foreach ($trainersraw as $trainer) {
            $a = new stdClass();
            $a->firstname = $trainer->firstname;
            $a->lastname = $trainer->lastname;
            $name = get_string('fullname_template', 'organizer', $a) . " ({$trainer->email})";
            $trainers[$trainer->id] = $name;
        }

        return $trainers;
    }

    /**
     * Generates a warning icon HTML element with optional visibility.
     *
     * This method creates a warning icon using a FontAwesome icon class,
     * adds a description as its tooltip, and assigns an ID for the icon.
     * If `$noshow` is set to true, an empty string is returned instead of the icon.
     *
     * @param string $name The base name for the ID attribute of the icon.
     * @param bool $noshow (Optional) Whether to suppress the icon's visibility. Defaults to false.
     * @return string The HTML markup for the warning icon, or an empty string if `$noshow` is true.
     */
    private function warning_icon($name, $noshow = false) {
        if (!$noshow) {
            $warningname = "id='".$name."_warning'";
            $text = get_string('warningtext1', 'organizer');
            $columnicon = organizer_get_fa_icon('fa fa-warning', $text, $warningname);
            return $columnicon;
        } else {
            return '';
        }
    }

    /**
     * Determines the appropriate time unit for a given time duration.
     *
     * This function analyzes a provided time duration and identifies the
     * largest unit (days, hours, minutes, or seconds) that evenly divides
     * the value. It returns the divisor representing the time unit.
     *
     * @param int $time The time duration in seconds to be evaluated.
     * @return int Returns the divisor corresponding to the largest time unit
     * (86400 for days, 3600 for hours, 60 for minutes, or 1 for seconds).
     */
    private function organizer_figure_out_unit($time) {
        if ($time % 86400 == 0) {
            return 86400;
        } else if ($time % 3600 == 0) {
            return 3600;
        } else if ($time % 60 == 0) {
            return 60;
        } else {
            return 1;
        }
    }

    /**
     * Retrieves the visibilities available for organizer slots.
     *
     * This function defines a list of visibility options that can be applied
     * to organizer slots. Each visibility mode corresponds to a specific setting
     * such as visible to all, anonymous, or visible based on the slot's configuration.
     *
     * @return array An associative array where keys are visibility constants
     * and values are their corresponding descriptions as strings.
     * @throws coding_exception
     */
    private function get_visibilities() {

        $visibilities = [];
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('slot_visible', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('slot_anonymous', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('slot_slotvisible', 'organizer');

        return $visibilities;
    }

    /**
     * Retrieves the visibility setting of the current organizer instance.
     *
     * This method fetches the organizer instance and returns the configured
     * visibility setting for that instance.
     *
     * @return int The visibility setting of the organizer instance.
     * @throws coding_exception
     */
    private function get_instance_visibility() {

        $organizer = organizer_get_organizer();

        return    $organizer->visibility;
    }

}
