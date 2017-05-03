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
 * view_action_form_add.php
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

define('ORGANIZER_SPACING', '&nbsp;&nbsp;');
define('ORGANIZER_NUM_DAYS', '7');
define('ORGANIZER_NUM_DAYS_ADD', '3');
define('ORGANIZER_NO_DAY', '-1');
define('ORGANIZER_DAY_IN_SECS', '86400');
define('ORGANIZER_USE_SCROLL_FIX', '1');

require_once(dirname(__FILE__) . '/../../lib/formslib.php');
require_once(dirname(__FILE__) . '/locallib.php');

class organizer_add_slots_form extends moodleform {

    private $pickeroptions;

    private $weekdays;

    protected function definition() {
        global $USER, $PAGE;

        $this->_init_arrays();
        $this->_add_scroll_fix();

        $mform = &$this->_form;
        $data = &$this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_ACTION);

        $mform->addElement('header', 'slotdetails', get_string('slotdetails', 'organizer'));

        $mform->addElement('select', 'teacherid', get_string('teacher', 'organizer'), $this->_get_teacher_list());
        $mform->setType('teacherid', PARAM_INT);
        $mform->setDefault('teacherid', $USER->id);
        $mform->addHelpButton('teacherid', 'teacherid', 'organizer');

        $mform->addElement('checkbox', 'teachervisible', get_string('teachervisible', 'organizer'));
        $mform->setType('teachervisible', PARAM_BOOL);
        $mform->setDefault('teachervisible', 1);
        $mform->addHelpButton('teachervisible', 'teachervisible', 'organizer');

        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->_get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->setDefault('visibility', $this->_get_instance_visibility());
        $mform->addHelpButton('visibility', 'visibility', 'organizer');

        $mform->addElement('text', 'location', get_string('location', 'organizer'), array('size' => '64'));
        $mform->setType('location', PARAM_TEXT);
        $mform->addRule('location', get_string('err_location', 'organizer'), 'required');
        $mform->addHelpButton('location', 'location', 'organizer');

        $mform->addElement('text', 'locationlink', get_string('locationlink', 'organizer'), array('size' => '64'));
        $mform->setType('locationlink', PARAM_URL);
        $mform->addHelpButton('locationlink', 'locationlink', 'organizer');

        $mform->addElement('duration', 'duration', get_string('duration', 'organizer'),
                array('optional' => false, 'defaultunit' => 60));
        $mform->setType('duration', PARAM_INT);
        $mform->setDefault('duration', 900);
        $duration = $mform->getElement('duration')->getElements();
        $duration[1]->removeOption(1);
        $duration[1]->removeOption(86400);
        $duration[1]->removeOption(604800);
        $mform->addHelpButton('duration', 'duration', 'organizer');

        $mform->addElement('duration', 'gap', get_string('gap', 'organizer'),
                array('optional' => false, 'defaultunit' => 60));
        $mform->setType('gap', PARAM_INT);
        $mform->setDefault('gap', 0);
        $duration = $mform->getElement('gap')->getElements();
        $duration[1]->removeOption(1);
        $duration[1]->removeOption(86400);
        $duration[1]->removeOption(604800);
        $mform->addHelpButton('gap', 'gap', 'organizer');

        $mform->addElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'), array('size' => '3'));
        $mform->setType('maxparticipants', PARAM_INT);
        $mform->setDefault('maxparticipants', 1);
        $mform->addHelpButton('maxparticipants', 'maxparticipants', 'organizer');

        global $DB;
        $cm = get_coursemodule_from_id('organizer', $data['id'], 0, false, MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
        if ($organizer->isgrouporganizer) {
            $mform->addElement('hidden', 'isgrouporganizer', '1');
            $mform->setType('isgrouporganizer', PARAM_BOOL);

            $mform->freeze('maxparticipants');
            $mform->disabledIf('maxparticipants', 'isgrouporganizer');
        }

        $mform->addElement('duration', 'notificationtime', get_string('notificationtime', 'organizer'),
                array('optional' => false, 'defaultunit' => 86400));
        $mform->setType('notificationtime', PARAM_INT);
        $mform->setDefault('notificationtime', 86400);
        $notificationtime = $mform->getElement('notificationtime')->getElements();
        $notificationtime[1]->removeOption(1);
        $mform->addHelpButton('notificationtime', 'notificationtime', 'organizer');

        $group = array();
        $group[] = $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer'),
            array('optional' => false, 'defaultunit' => 86400));
        $group[] = $mform->createElement('static', '', '', get_string('relative_deadline_before', 'organizer')
            . '&nbsp;&nbsp;&nbsp;' . get_string('relative_deadline_now', 'organizer'));
        $group[] = $mform->createElement('checkbox', 'now', '', null);

        $mform->setDefault('availablefrom', 86400 * 7);
        $mform->setDefault('now', 1);
        $mform->insertElementBefore(
            $mform->createElement('group', 'availablefromgroup', get_string('availablefrom', 'organizer'), $group,
                ORGANIZER_SPACING, false), 'notificationtime');
        $mform->addHelpButton('availablefromgroup', 'availablefrom', 'organizer');
        $mform->disabledIf('availablefrom', 'now', 'checked');

        $mform->addElement('header', 'slotperiod', get_string('slotperiodheader', 'organizer'));
        $mform->addHelpButton('slotperiod', 'slotperiodheader', 'organizer');

        $mform->addElement('date_selector', 'startdate', get_string('slotperiodstarttime', 'organizer'));
        $mform->setType('startdate', PARAM_INT);
        $mform->setDefault('startdate', time());

        $mform->addElement('date_selector', 'enddate', get_string('slotperiodendtime', 'organizer'));
        $mform->setType('enddate', PARAM_INT);
        $mform->setDefault('enddate', mktime(null, null, null, date("m"), date("d") + 6, date("Y")));

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        if(isset($_POST['newslots'])) { // submitted form data
            $totalslots = count($_POST['newslots']);
        } else {
            $totalslots = ORGANIZER_NUM_DAYS;
        }
        for ($newslot_index = 0; $newslot_index < $totalslots; $newslot_index++) {
            $slotgroup = $this->_create_day_slot_group($newslot_index);
            $grouplabel = get_string("weekdaylabel", "organizer") . " " . ($newslot_index + 1);
            $mform->insertElementBefore($mform->createElement('group', "slotgroup{$newslot_index}",
                                        $grouplabel, $slotgroup, ORGANIZER_SPACING, false), 'other');
        }
        if(isset($_POST['addday'])) {
            $totalslots = $this->_add_slot_fields($newslot_index);
            $displayallslots = 1;
        }  else {  // must be errors from formdata-checking
            $displayallslots = 0;
        }
        $mform->insertElementBefore(
                $mform->createElement('submit', "addday", get_string('newslot', 'organizer')), 'other');

        $mform->addElement('textarea', 'comments', get_string('appointmentcomments', 'organizer'),
                array('wrap' => 'virtual', 'rows' => '10', 'cols' => '60'));
        $mform->setType('comments', PARAM_RAW);
        $mform->addHelpButton('comments', 'appointmentcomments', 'organizer');

        $mform->setExpanded('slotperiod');

        $this->add_action_buttons();

        $params = new \stdClass();
        $params->totalslots = $totalslots;
        $params->displayallslots = $displayallslots;
        $PAGE->requires->js_call_amd('mod_organizer/adddayslot', 'init', array($params));
    }

    public function definition_after_data() {
        $mform = &$this->_form;

        if (isset($mform->_submitValues['newslots'])) {  // newslotsform submitted
            // check for errors
            $data['noerrors'] = $this->_validation_step1($mform->_submitValues);
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0) {
            $errors['maxparticipants'] = get_string('err_posint', 'organizer');
        }

        // Check if duration is a full minute.
        if ($data['duration'] % 60 != 0) {
            $errors['duration'] = get_string('err_fullminute', 'organizer');
        }

        // Check if gap is a full minute.
        if ($data['gap'] % 60 != 0) {
            $errors['gap'] = get_string('err_fullminutegap', 'organizer');
        }

        // notification time full number and positive?
        if (!$this->_converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0) {
            $errors['notificationtime'] = get_string('err_posint', 'organizer');
        }

        // "availablefrom" int?
        if (!isset($data['now']) && (!$this->_converts_to_int($data['availablefrom']) ||
                                                                    $data['availablefrom'] <= 0)) {
            $errors['availablefromgroup'] = get_string('err_posint', 'organizer');
        }

        // "startdate" in the past?
        // "startdate" after the "enddate"?
        if ($data['startdate'] != 0 && $data['enddate'] != 0) {
            $today = mktime(0, 0, 0, date("m", time()), date("d", time()), date("Y", time()));
            if ($data['startdate'] < $today) {
                $errors['startdate'] = get_string('err_startdate', 'organizer') . ' ('
                        . userdate($today, get_string('datetemplate', 'organizer')) . ')';
            }
            if ($data['startdate'] > $data['enddate']) {
                $errors['enddate'] = get_string('err_enddate', 'organizer');
            }
        }

        // get the gap
        $gap = $data['gap']['number'] * $data['gap']['timeunit'];

        // for new slots:
        if (isset($data['newslots'])) {
            $invalidslots = array();
            $slots = $data['newslots'];

            $slotcount = 0;

            // slot "from" after slot "to"?
            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['day'] != -1 && ($slot['from'] >= $slot['to'])) {
                    $errors['slotgroup' . $i] = get_string('err_fromto', 'organizer');
                    $invalidslots[] = $i;
                }
                if ($slot['day'] != -1) {
                    $slotcount++;
                }
            }

            // are there overlapping slots? Is the gap considered?
            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                if (in_array($i, $invalidslots)) {
                    continue;
                }
                $message = ' ';
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if (!in_array($j, $invalidslots) && $currentslot['day'] == $otherslot['day']
                    && ($this->_between($currentslot['from'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                            || $this->_between($currentslot['to'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                            || $this->_between($otherslot['from'], $currentslot['from'] - $gap, $currentslot['to'] + $gap)
                            || $this->_between($otherslot['to'], $currentslot['from'] - $gap, $currentslot['to'] + $gap))) {

                        $message .= '(' . $this->pickeroptions[$otherslot['from']] . '-'
                                . $this->pickeroptions[$otherslot['to']] . '), ';
                    }
                }
                if ($message != ' ' && $currentslot['day'] != -1) {
                    $message = substr($message, 0, strlen($message) - 2);
                    $errors['slotgroup' . $i] = get_string('err_collision', 'organizer') . $message;
                }
            }
        }

        return $errors;
    }

    private function _validation_step1($data) {
        // checks form to submit

        // maxparticipants not int or negative?
        if (isset($data['isgrouporganizer']) && $data['isgrouporganizer'] == 0 &&
            (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0)) {
            return false;
        }

        // location empty?
        // duration not negative and full minute?
        // notificationtime not int or negative?
        if ($data['location'] == ''
        || !($data['duration']['number'] * $data['duration']['timeunit'] % 60 == 0)
        || $data['duration']['number'] <= 0
        || !($data['gap']['number'] * $data['gap']['timeunit'] % 60 == 0)
        || $data['gap']['number'] < 0
        || !$this->_converts_to_int($data['notificationtime']['number'])
        || $data['notificationtime']['number'] <= 0) {
            return false;
        }

        // availablefrom not int or negative?
        if (isset($data['availablefrom']) && is_array($data['availablefrom']['number'])) {
            if (!$this->_converts_to_int($data['availablefrom']['number']) || $data['availablefrom']['number'] <= 0) {
                return false;
            }
        }

        // get the gap
        $gap = $data['gap']['number'] * $data['gap']['timeunit'];

        // for new slots:
        if (isset($data['newslots'])) {
            $slots = $data['newslots'];

            // is "from" before "to"?
            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['day'] != -1 && ($slot['from'] >= $slot['to'])) {
                    return false;
                }
            }

            // are there overlapping slots? Is the gap considered?
            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if ($currentslot['day'] == $otherslot['day']
                        && ($currentslot['day'] != -1 && $otherslot['day'] != -1)
                        && ($this->_between($currentslot['from'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                            || $this->_between($currentslot['to'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                            || $this->_between($otherslot['from'], $currentslot['from'] - $gap, $currentslot['to'] + $gap)
                            || $this->_between($otherslot['to'], $currentslot['from'] - $gap, $currentslot['to'] + $gap)))
                    {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function _converts_to_int($value) {
        if (is_numeric($value)) {
            if (intval($value) == floatval($value)) {
                return true;
            }
        }
        return false;
    }


    /*
     * Add additional slot fields
     * called from definition if adday-button was submitted
     */
    private function _add_slot_fields($newslot_next) {
        $mform = &$this->_form;

        $totalslots = $newslot_next + ORGANIZER_NUM_DAYS_ADD;
        for ($slot = $newslot_next; $slot < $totalslots; $slot++) {
                $slotgroup = $this->_create_day_slot_group($slot);
                $grouplabel = get_string("weekdaylabel", "organizer") . " " . ($slot + 1);
                $mform->insertElementBefore($mform->createElement('group', 'organizer_slotgroup{$day}',
                    $grouplabel, $slotgroup, ORGANIZER_SPACING, false), 'other');
        }

        return $totalslots;
    }

    private function _create_day_slot_group($newslot_index) {
        $mform = &$this->_form;
        $name = "newslots[$newslot_index]";
        $slotgroup = array();

        $slotgroup[] = $mform->createElement('select', "{$name}[day]", '', $this->weekdays);
        $mform->setType("{$name}[day]", PARAM_INT);
        $mform->setDefault("{$name}[day]", -1);

        $slotgroup[] = $mform->createElement('static', '', '', get_string('slotfrom', 'organizer'));
        $slotgroup[] = $mform->createElement('select', "{$name}[from]", '', $this->pickeroptions);
        $mform->setType("{$name}[from]", PARAM_INT);
        $mform->setDefault("{$name}[from]", 8 * 3600);
        $slotgroup[] = $mform->createElement('static', '', '', get_string('slotto', 'organizer'));
        $slotgroup[] = $mform->createElement('select', "{$name}[to]", '', $this->pickeroptions);
        $mform->setType("{$name}[to]", PARAM_INT);
        $mform->setDefault("{$name}[to]", 8 * 3600);

        return $slotgroup;
    }



    private function _get_visibilities() {

        $visibilities = array();
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('visibility_all','organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('visibility_anonymous','organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('visibility_slot','organizer');

        return $visibilities;
    }

    private function _get_teacher_list() {
        $context = organizer_get_context();

        $teachersraw = get_users_by_capability($context, 'mod/organizer:leadslots');

        $teachers = array();
        foreach ($teachersraw as $teacher) {
            $a = new stdClass();
            $a->firstname = $teacher->firstname;
            $a->lastname = $teacher->lastname;
            $name = get_string('fullname_template', 'organizer', $a) . " ({$teacher->email})";
            $teachers[$teacher->id] = $name;
        }

        return $teachers;
    }

    private function _between($num, $lower, $upper) {
        return $num > $lower && $num < $upper;
    }

    private function _init_arrays() {
        $this->pickeroptions = array();
        for ($i = 0; $i < 24; $i++) {
            for ($j = 0; $j < 60; $j += 5) {
                $this->pickeroptions[($i * 3600) + ($j * 60)] = gmdate('H:i', ($i * 3600) + ($j * 60));
            }
        }
        $date = time();
        $this->weekdays = array();
        $this->weekdays[-1] = get_string("choose");
        $this->weekdays[0] = date('l', strtotime("next Monday", $date));
        $this->weekdays[1] = date('l', strtotime("next Tuesday", $date));
        $this->weekdays[2] = date('l', strtotime("next Wednesday", $date));
        $this->weekdays[3] = date('l', strtotime("next Thursday", $date));
        $this->weekdays[4] = date('l', strtotime("next Friday", $date));
        $this->weekdays[5] = date('l', strtotime("next Saturday", $date));
        $this->weekdays[6] = date('l', strtotime("next Sunday", $date));
    }

    /**
     * This function applies a javascript fix that scrolls the page back to the position before
     * the submission. It preserves the scroll coordinates within hidden form fields and restores
     * the scroll position from them when the page reloads. Uses JavaScript.
     *
     * The fix can be overriden by setting ORGANIZER_USE_SCROLL_FIX constant to 0.
     */
    private function _add_scroll_fix() {
        global $PAGE;

        if (!ORGANIZER_USE_SCROLL_FIX) {
            return;
        }

        $jsmodule = array(
                'name' => 'mod_organizer',
                'fullpath' => '/mod/organizer/module.js',
                'requires' => array('node', 'node-scroll-info', 'scrollview-base')
        );

        $PAGE->requires->js_init_call('M.mod_organizer.init_add_form', null, false, $jsmodule);

        $PAGE->requires->strings_for_js(array('confirm_conflicts'), 'organizer');

        $mform = &$this->_form;

        $data = $this->_customdata;
        $mform->addElement('hidden', 'scrollx', isset($data->scrollx) ? $data->scrollx : 0);
        $mform->setType('scrollx', PARAM_BOOL);
        $mform->addElement('hidden', 'scrolly', isset($data->scrolly) ? $data->scrolly : 0);
        $mform->setType('scrolly', PARAM_BOOL);
    }

    private function _get_instance_visibility() {

          $organizer = organizer_get_organizer();

        return    $organizer->visibility;
    }

}
