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

defined('MOODLE_INTERNAL') || die();

define('ORGANIZER_SPACING', '&nbsp;&nbsp;'); // TODO Remove this.

// Required for the form rendering.

require_once("$CFG->libdir/formslib.php");

class organizer_edit_slots_form extends moodleform
{

    protected function definition() {
        global $CFG, $PAGE;

        $params = new \stdClass();
        $params->imagepaths = array(
            'warning' => "{$CFG->wwwroot}/mod/organizer/pix/warning.png",
            'changed' => "{$CFG->wwwroot}/mod/organizer/pix/warning2.png");
        $params->warningtext1 = get_string("warningtext1", "organizer");
        $params->warningtext2 = get_string("warningtext2", "organizer");

        $PAGE->requires->js_call_amd('mod_organizer/editform', 'init', array($params));

        $defaults = $this->_get_defaults();
        $this->_sethiddenfields();
        $this->_addfields($defaults);
        $this->_addbuttons();
        $this->set_data($defaults);
    }

    private function _get_defaults() {
        global $DB;
        $defaults = array();
        $defset = array('visible' => false,  'trainerids' => false, 'visibility' => false,  'comments' => false,
                        'location' => false, 'locationlink' => false,
                        'maxparticipants' => false, 'availablefrom' => false, 'teachervisible' => false,
                        'notificationtime' => false);

        $slotids = $this->_customdata['slots'];

        $defaults['now'] = 0;

        foreach ($slotids as $slotid) {
            $slot = $DB->get_record('organizer_slots', array('id' => $slotid));

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
                $timeunit = $this->_organizer_figure_out_unit($slot->notificationtime);
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

    private function _addbuttons() {
        $mform = $this->_form;

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'editsubmit', get_string('edit_submit', 'organizer'));
        $buttonarray[] = &$mform->createElement('reset', 'editreset', get_string('revert'), array('class' => 'btn btn-secondary'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    private function _sethiddenfields() {

        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        // TODO: might cause crashes!
        $mform->addElement('hidden', 'action', 'edit');
        $mform->setType('action', PARAM_ACTION);

        $mform->addElement('hidden', 'warningtext1', get_string('warningtext1', 'organizer'));
        $mform->setType('warningtext1', PARAM_TEXT);
        $mform->addElement('hidden', 'warningtext2', get_string('warningtext2', 'organizer'));
        $mform->setType('warningtext2', PARAM_TEXT);

        for ($i = 0; $i < count($data['slots']); $i++) {
            $mform->addElement('hidden', "slots[$i]", $data['slots'][$i]);
            $mform->setType("slots[$i]", PARAM_INT);
        }
    }

    private function _addfields($defaults) {
        $mform = $this->_form;
        $data = $this->_customdata;

        $mform->addElement('header', 'slotdetails', get_string('slotdetails', 'organizer'));

        $slots = array();
        for ($i = 0; $i < count($data['slots']); $i++) {
            $slots[] = $data['slots'][$i];
        }
        $mform->addElement('hidden', "apps", organizer_count_slotappointments($slots));
        $mform->setType('apps', PARAM_INT);

        $group = array();
        $group[] = $mform->createElement(
                'advcheckbox', 'visible', get_string('visible', 'organizer'), null, null, array(0, 1)
        );
        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('visible', isset($defaults['visible']))
        );
        $mform->addGroup($group, '', get_string('visible', 'organizer'), ORGANIZER_SPACING, false);
        $mform->setDefault('visible', 1);
        $mform->disabledIf('visible', 'apps', 'neq', 0);
        $mform->addElement('hidden', 'mod_visible', 0);
        $mform->setType('mod_visible', PARAM_BOOL);

        $trainers = $this->_load_trainers($defaults);
        $group = array();
        $group[] = $mform->createElement('select', 'trainerid', get_string('teacher', 'organizer'),
            $trainers, array('multiple' => 'true'));
        $group[] = $mform->createElement('static', '', '', $this->_warning_icon('teacherid', isset($defaults['trainerids'])));
        $mform->setType('trainerid', PARAM_INT);
        $mform->addGroup($group, 'teachergrp', get_string('teacher', 'organizer'), ORGANIZER_SPACING, false);
        if (isset($defaults['trainerids'])) {
            $mform->setDefault('trainerid', $defaults['trainerids']);
        }
        $mform->addElement('hidden', 'mod_trainerid', 0);
        $mform->setType('mod_trainerid', PARAM_BOOL);

        $group = array();
        $group[] = $mform->createElement('advcheckbox', 'teachervisible', get_string('teachervisible', 'organizer'),
            null, null, array(0, 1)
        );

        $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('teachervisible', isset($defaults['teachervisible']))
        );

        $mform->setDefault('teachervisible', 1);
        $mform->addGroup($group, '', get_string('teachervisible', 'organizer'), ORGANIZER_SPACING, false);
        $mform->addElement('hidden', 'mod_teachervisible', 0);
        $mform->setType('mod_teachervisible', PARAM_BOOL);

        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->_get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->addElement('hidden', 'mod_visibility', 0);
        $mform->setType('mod_visibility', PARAM_BOOL);

        $locations = get_config('mod_organizer', 'locations');
        if (!$locations) {
            $locations = [];
        } else {
            $locations = explode("\n", $locations);
        }
        $options = array(
                'multiple' => false,
                'tags' => true,
        );

        $group = array();
        $group[] = $mform->createElement('autocomplete', 'location', null,
                array_combine($locations, $locations), $options);
        $group[] = $mform->createElement('static', '', '', $this->_warning_icon('location', isset($defaults['location'])));

        $mform->addGroup($group, 'locationgroup', get_string('location', 'organizer'), ORGANIZER_SPACING, false);
        $mform->addElement('hidden', 'mod_location', 0);
        $mform->setType('mod_location', PARAM_BOOL);

        $group = array();
        $group[] = $mform->createElement('text', 'locationlink', get_string('locationlink', 'organizer'),
            array('size' => '64', 'group' => null)
        );
        $group[] = $mform->createElement(
            'static', '', '',
            $this->_warning_icon('locationlink', isset($defaults['locationlink']))
        );
        $mform->setType('locationlink', PARAM_URL);

        $mform->addGroup($group, 'locationlinkgroup', get_string('locationlink', 'organizer'), ORGANIZER_SPACING, false);
        $mform->addElement('hidden', 'mod_locationlink', 0);
        $mform->setType('mod_locationlink', PARAM_BOOL);

        if (!organizer_is_group_mode()) {
            $group = array();
            $group[] = $mform->createElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'),
                array('size' => '3', 'group' => null)
            );
            $group[] = $mform->createElement('static', '', '',
                $this->_warning_icon('maxparticipants', isset($defaults['maxparticipants']))
            );

            $mform->addGroup($group, 'maxparticipantsgroup', get_string('maxparticipants', 'organizer'), ORGANIZER_SPACING, false);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('mod_maxparticipants', PARAM_BOOL);
            $mform->setType('maxparticipants', PARAM_INT);
        } else {
            $mform->addElement('hidden', 'maxparticipants', 1);
            $mform->addElement('hidden', 'mod_maxparticipants', 0);
            $mform->setType('maxparticipants', PARAM_INT);
            $mform->setType('mod_maxparticipants', PARAM_BOOL);
        }

        $group = array();
        if ($defaults['now']) {
            $group[] = $mform->createElement(
                'duration', 'availablefrom', null,
                null, array('group' => null, 'disabled' => true)
            );
        } else {
            $group[] = $mform->createElement('duration', 'availablefrom', null);
        }
        $group[] = $mform->createElement(
            'static', '', '',
            get_string('relative_deadline_before', 'organizer') . '&nbsp;&nbsp;&nbsp;'
            . get_string('relative_deadline_now', 'organizer')
        );
        $group[] = $mform->createElement('checkbox', 'availablefrom[now]', get_string('relative_deadline_now', 'organizer'));
        $group[] = $mform->createElement(
            'static', '', '',
            $this->_warning_icon('availablefrom', isset($defaults['availablefrom']))
        );

        $mform->setDefault('availablefrom', '');
        $mform->setDefault('availablefrom[now]', $defaults['now']);

        $mform->addGroup($group, 'availablefromgroup', get_string('availablefrom', 'organizer'), ORGANIZER_SPACING, false);

        $availablefromgroup = $mform->getElement('availablefromgroup')->getElements();
        $availablefrom = $availablefromgroup[0]->getElements();
        $availablefrom[1]->removeOption(1);

        $mform->addElement('hidden', 'mod_availablefrom', 0);
        $mform->setType('mod_availablefrom', PARAM_BOOL);

        $group = array();
        $group[] = $mform->createElement(
            'duration', 'notificationtime', get_string('notificationtime', 'organizer'),
            null, null, array(0, 1)
        );
        $group[] = $mform->createElement('static', '', '',
            $this->_warning_icon('notificationtime', isset($defaults['notificationtime']))
        );

        $mform->setDefault('notificationtime', '');
        $mform->addGroup($group, 'notificationtimegroup', get_string('notificationtime', 'organizer'), ORGANIZER_SPACING, false);
        $mform->addElement('hidden', 'mod_notificationtime', 0);
        $mform->setType('mod_notificationtime', PARAM_BOOL);

        $notificationtimegroup = $mform->getElement('notificationtimegroup')->getElements();
        $notificationtime = $notificationtimegroup[0]->getElements();
        $notificationtime[1]->removeOption(1);

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        $group = array();
        $group[] = $mform->createElement(
            'textarea', 'comments', get_string('appointmentcomments', 'organizer'),
            array('wrap' => 'virtual', 'rows' => '10', 'cols' => '60')
        );
        $group[] = $mform->createElement(
            'static', '', '',
            $this->_warning_icon('comments', isset($defaults['comments']))
        );

        $mform->setDefault('comments', '');
        $mform->addGroup($group, '', get_string('appointmentcomments', 'organizer'), ORGANIZER_SPACING, false);
        $mform->addElement('hidden', 'mod_comments', 0);
        $mform->setType('mod_comments', PARAM_BOOL);
    }

    private function _converts_to_int($value) {
        if (is_numeric($value)) {
            if (intval($value) == floatval($value)) {
                return true;
            }
        }
        return false;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['mod_maxparticipants'] != 0
            && (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0)
        ) {
            $errors['maxparticipantsgroup'] = get_string('err_posint', 'organizer');
        }

        if ($data['mod_notificationtime'] != 0
            && (!$this->_converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0)
        ) {
            $errors['notificationtimegroup'] = get_string('err_posint', 'organizer');
        }

        if ($data['mod_location'] != 0 && (!isset($data['location']) || $data['location'] === '')) {
            $errors['locationgroup'] = get_string('err_location', 'organizer');
        }

        return $errors;
    }

    private function _load_trainers() {
        $context = organizer_get_context();

        $trainersraw = get_users_by_capability($context, 'mod/organizer:leadslots');

        $trainers = array();
        foreach ($trainersraw as $trainer) {
            $a = new stdClass();
            $a->firstname = $trainer->firstname;
            $a->lastname = $trainer->lastname;
            $name = get_string('fullname_template', 'organizer', $a) . " ({$trainer->email})";
            $trainers[$trainer->id] = $name;
        }

        return $trainers;
    }

    private function _warning_icon($name, $noshow = false) {
        if (!$noshow) {
            $warningname = $name . '_warning';
            $text = get_string('warningtext1', 'organizer');
            $columnicon = organizer_get_icon('warning', $text, "small", $warningname);
            return $columnicon;
        } else {
            return '';
        }
    }

    private function _organizer_figure_out_unit($time) {
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

    private function _get_visibilities() {

        $visibilities = array();
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('slot_visible', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('slot_anonymous', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('slot_slotvisible', 'organizer');

        return $visibilities;
    }

    private function _get_instance_visibility() {

        $organizer = organizer_get_organizer();

        return    $organizer->visibility;
    }

}
