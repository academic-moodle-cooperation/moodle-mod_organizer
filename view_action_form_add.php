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
define('ORGANIZER_NUM_DAYS', '10');
define('ORGANIZER_NUM_DAYS_ADD', '5');
define('ORGANIZER_NO_DAY', '-1');
define('ORGANIZER_DAY_IN_SECS', '86400');
define('ORGANIZER_USE_SCROLL_FIX', '1');

require_once(dirname(__FILE__) . '/../../lib/formslib.php');
require_once(dirname(__FILE__) . '/locallib.php');
/**
 * view_action_form_add.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_add_slots_form extends moodleform
{

    private $pickeroptions;

    private $weekdays;
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        global $USER, $PAGE, $DB;

        $this->_init_arrays();
        $this->_add_scroll_fix();

        $mform = &$this->_form;
        $data = &$this->_customdata;

        $cm = get_coursemodule_from_id('organizer', $data['id'], 0, false, MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
        $organizerconfig = get_config('organizer');

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);
        $mform->addElement('hidden', 'action', 'add');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        $mform->addElement('header', 'slotdetails', get_string('slotdetails', 'organizer'));

        $menu = $this->_get_trainer_list();
        $select = $mform->addElement('select', 'trainerid', get_string('trainerid', 'organizer'), $menu);
        $select->setMultiple(true);
        $mform->setType('trainerid', PARAM_INT);
        $mform->addHelpButton('trainerid', 'trainerid', 'organizer');

        $mform->addElement('checkbox', 'teachervisible', get_string('teachervisible', 'organizer'));
        $mform->setType('teachervisible', PARAM_BOOL);
        $mform->setDefault('teachervisible', 1);
        $mform->addHelpButton('teachervisible', 'teachervisible', 'organizer');

        $mform->addElement('select', 'visibility', get_string('visibility', 'organizer'), $this->_get_visibilities());
        $mform->setType('visibility', PARAM_INT);
        $mform->setDefault('visibility', $this->_get_instance_visibility());
        $mform->addHelpButton('visibility', 'visibility', 'organizer');

        $locations = get_config('mod_organizer', 'locations');
        if (!$locations) {
            $mform->addElement('text', 'location', get_string('location', 'organizer'), array('size' => '64'));
            $mform->setType('location', PARAM_TEXT);
        } else {
            $locations = explode("\n", $locations);
            $locations = array_combine($locations, $locations);
            $firstitem = array(null => get_string("choose"));
            $locations = $firstitem + $locations;
            $options = array(
                'multiple' => false,
                'tags' => true,
                'noselectionstring' => get_string('choose'),
            );
            $mform->addElement('autocomplete', 'location', get_string('location', 'organizer'),
                $locations, $options);
            $mform->setType('location', PARAM_RAW);
            $mform->setDefault('location', null);
        }
        $mform->addHelpButton('location', 'location', 'organizer');
        if ($locationmandatory = get_config('organizer', 'locationmandatory')) {
            $mform->addRule('location', null, 'required');
        }

        $mform->addElement('text', 'locationlink', get_string('locationlink', 'organizer'), array('size' => '64'));
        $mform->setType('locationlink', PARAM_URL);
        $mform->addHelpButton('locationlink', 'locationlink', 'organizer');

        $mform->addElement(
            'duration', 'duration', get_string('duration', 'organizer'),
            array('optional' => false, 'defaultunit' => 60)
        );
        $mform->setType('duration', PARAM_INT);
        $mform->setDefault('duration', 900);
        $duration = $mform->getElement('duration')->getElements();
        $duration[1]->removeOption(1);
        $duration[1]->removeOption(604800);
        $mform->addHelpButton('duration', 'duration', 'organizer');

        $mform->addElement(
            'duration', 'gap', get_string('gap', 'organizer'),
            array('optional' => false, 'defaultunit' => 60)
        );
        $mform->setType('gap', PARAM_INT);
        $mform->setDefault('gap', 0);
        $duration = $mform->getElement('gap')->getElements();
        $duration[1]->removeOption(1);
        $duration[1]->removeOption(604800);
        $mform->addHelpButton('gap', 'gap', 'organizer');

        $mform->addElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'), array('size' => '3'));
        $mform->setType('maxparticipants', PARAM_INT);
        $mform->setDefault('maxparticipants', 1);
        $mform->addHelpButton('maxparticipants', 'maxparticipants', 'organizer');

        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $mform->addElement('hidden', 'isgrouporganizer', '1');
            $mform->setType('isgrouporganizer', PARAM_BOOL);

            $mform->freeze('maxparticipants');
            $mform->disabledif ('maxparticipants', 'isgrouporganizer');
        }

        $mform->addElement(
            'duration', 'notificationtime', get_string('notificationtime', 'organizer'),
            array('optional' => false, 'defaultunit' => 86400)
        );
        $mform->setType('notificationtime', PARAM_INT);
        $mform->setDefault('notificationtime', 86400);
        $notificationtime = $mform->getElement('notificationtime')->getElements();
        $notificationtime[1]->removeOption(1);
        $mform->addHelpButton('notificationtime', 'notificationtime', 'organizer');

        $group = array();
        $group[] = $mform->createElement(
            'duration', 'availablefrom', null, array('optional' => false, 'defaultunit' => 86400)
        );
        $group[] = $mform->createElement(
            'static', '', '', get_string('relative_deadline_before', 'organizer') . '&nbsp;&nbsp;&nbsp;'
        );
        $group[] = $mform->createElement('checkbox', 'now', '', get_string('relative_deadline_now', 'organizer'));

        $mform->setDefault('availablefrom', 86400 * 7);
        $mform->setDefault('now', 1);
        $mform->insertElementBefore(
            $mform->createElement(
                'group', 'availablefromgroup', get_string('availablefrom', 'organizer'), $group,
                ORGANIZER_SPACING, false
            ), 'notificationtime'
        );
        $mform->addHelpButton('availablefromgroup', 'availablefrom', 'organizer');
        $mform->disabledif ('availablefrom', 'now', 'checked');

        $mform->addElement('header', 'slotperiod', get_string('slotperiodheader', 'organizer'));
        $mform->addHelpButton('slotperiod', 'slotperiodheader', 'organizer');

        $mform->addElement('date_selector', 'startdate', get_string('slotperiodstarttime', 'organizer'));
        $mform->setType('startdate', PARAM_INT);
        $mform->setDefault('startdate', time());

        $mform->addElement('date_selector', 'enddate', get_string('slotperiodendtime', 'organizer'));
        $mform->setType('enddate', PARAM_INT);
        $mform->setDefault('enddate', mktime(null, null, null, date("m"), date("d") + 6, date("Y")));

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        if (isset($_POST['newslots'])) { // Submitted form data.
            $totalslots = count($_POST['newslots']);
        } else {
            $totalslots = ORGANIZER_NUM_DAYS;
        }
        for ($newslotindex = 0; $newslotindex < $totalslots; $newslotindex++) {
            $slotgroup = $this->_create_day_slot_group($newslotindex);
            $grouplabel = get_string("weekdaylabel", "organizer") . " " . ($newslotindex + 1);
            $mform->insertElementBefore(
                $mform->createElement('group', "slotgroup{$newslotindex}", $grouplabel, $slotgroup, ORGANIZER_SPACING, false
                ), 'other'
            );
        }

        if (isset($_POST['addday'])) {
            $totalslots = $this->_add_slot_fields($newslotindex);
            $displayallslots = 1;
        } else {
            $displayallslots = 0;
        }
        $forecasttotalgroup[] = $mform->createElement("html",
                "<div name='organizer_newslots_forecasttotal' class='col-md-9 form-inline felement'></div>");
        $mform->insertElementBefore(
                $mform->createElement('group', "forecasttotalgroup", "&nbsp;", $forecasttotalgroup, ORGANIZER_SPACING, false
                ), 'other'
        );

        $mform->insertElementBefore(
            $mform->createElement('submit', "addday", get_string('newslot', 'organizer')), 'other'
        );

        $mform->addElement(
            'textarea', 'comments', get_string('appointmentcomments', 'organizer'),
            array('wrap' => 'virtual', 'rows' => '10', 'cols' => '60')
        );
        $mform->setType('comments', PARAM_RAW);
        $mform->addHelpButton('comments', 'appointmentcomments', 'organizer');

        $mform->setExpanded('slotperiod');

        $this->add_action_buttons();

        $params = new \stdClass();
        $params->totalslots = $totalslots;
        $params->displayallslots = $displayallslots;
        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $params->totaltotal = get_string("totaltotal_groups", "organizer");
            $params->totalday = get_string("totalday_groups", "organizer");
        } else {
            $params->totaltotal = get_string("totaltotal", "organizer");
            $params->totalday = get_string("totalday", "organizer");
        }
        $params->relativedeadline = $organizer->relativedeadline;
        $params->relativedeadlinestring = get_string('infobox_deadline_passed_slot', 'organizer');
        $params->allowcreationofpasttimeslots = $organizerconfig->allowcreationofpasttimeslots;
        $params->pasttimeslotsstring = get_string('pasttimeslotstring', 'organizer');

        $PAGE->requires->js_call_amd('mod_organizer/adddayslot', 'init', array($params));
    }
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition_after_data()
     */
    public function definition_after_data() {
        $mform = &$this->_form;

        if (isset($mform->_submitValues['newslots'])) {  // Newslotsform submitted.
            // Check for errors.
            $data['noerrors'] = $this->_validation_step1($mform->_submitValues);
        }
    }
    /**
     *
     * {@inheritDoc}
     * @see moodleform::validation()
     */
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

        // Notification time full number and positive?
        if (!$this->_converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0) {
            $errors['notificationtime'] = get_string('err_posint', 'organizer');
        }

        // Availablefrom int?
        if (!isset($data['now']) && (!$this->_converts_to_int($data['availablefrom'])
            || $data['availablefrom'] <= 0)
        ) {
            $errors['availablefromgroup'] = get_string('err_posint', 'organizer');
        }

        // Startdate in the past?
        // Startdate after the enddate?
        if ($data['startdate'] != 0 && $data['enddate'] != 0) {
            $today = mktime(0, 0, 0, date("m", time()), date("d", time()), date("Y", time()));
            if ($data['startdate'] < $today) {
                $organizerconfig = get_config('organizer');
                if (isset($organizerconfig->allowcreationofpasttimeslots) && $organizerconfig->allowcreationofpasttimeslots != 1) {
                    $a = new stdClass();
                    $a->now = userdate($data['startdate'], get_string('datetemplate', 'organizer'));
                    $errors['startdate'] = get_string('err_startdate', 'organizer', $a) . ' (' . get_string("today") . ": "
                            . userdate($today, get_string('datetemplate', 'organizer')) . ')';
                }
            }
            if ($data['startdate'] > $data['enddate']) {
                $errors['enddate'] = get_string('err_enddate', 'organizer');
            }
        }

        // Get the gap.
        if ($data['gap'] == 0) {
            $gap = 0;
        } else {
            $gap = $data['gap']['number'] * $data['gap']['timeunit'];
        }

        // For new slots.
        if (isset($data['newslots'])) {
            $slots = $data['newslots'];

            $slotcount = 0;

            // Count used slots.
            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['day'] != -1) {
                    $slotcount++;
                }
            }

            // Are there overlapping slots? Is the gap considered?
            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                $message = ' ';
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if ($currentslot['day'] == $otherslot['day']
                        && ($this->_between($currentslot['from'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                        || $this->_between($currentslot['to'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                        || $this->_between($otherslot['from'], $currentslot['from'] - $gap, $currentslot['to'] + $gap)
                        || $this->_between($otherslot['to'], $currentslot['from'] - $gap, $currentslot['to'] + $gap))
                    ) {

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
    /**
     *
     * @param array $data
     * @return boolean
     */
    private function _validation_step1($data) {
        // Checks form to submit.

        // Maxparticipants not int or negative?
        if (isset($data['isgrouporganizer']) && $data['isgrouporganizer'] == 0
            && (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0)
        ) {
            return false;
        }

        // Location empty?
        // Duration not negative and full minute?
        // Notificationtime not int or negative?
        if ($data['location'] == ''
            || !($data['duration']['number'] * $data['duration']['timeunit'] % 60 == 0)
            || $data['duration']['number'] <= 0
            || !($data['gap']['number'] * $data['gap']['timeunit'] % 60 == 0)
            || $data['gap']['number'] < 0
            || !$this->_converts_to_int($data['notificationtime']['number'])
            || $data['notificationtime']['number'] <= 0
        ) {
            return false;
        }

        // Availablefrom not int or negative?
        if (isset($data['availablefrom']) && is_array($data['availablefrom']['number'])) {
            if (!$this->_converts_to_int($data['availablefrom']['number']) || $data['availablefrom']['number'] <= 0) {
                return false;
            }
        }

        // Get the gap.
        $gap = $data['gap']['number'] * $data['gap']['timeunit'];

        // For new slots.
        if (isset($data['newslots'])) {
            $slots = $data['newslots'];

            // Is "from" before "to"?
            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['day'] != -1 && ($slot['from'] >= $slot['to'])) {
                    return false;
                }
            }

            // Are there overlapping slots? Is the gap considered?
            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if ($currentslot['day'] == $otherslot['day']
                        && ($currentslot['day'] != -1 && $otherslot['day'] != -1)
                        && ($this->_between($currentslot['from'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                        || $this->_between($currentslot['to'], $otherslot['from'] - $gap, $otherslot['to'] + $gap)
                        || $this->_between($otherslot['from'], $currentslot['from'] - $gap, $currentslot['to'] + $gap)
                        || $this->_between($otherslot['to'], $currentslot['from'] - $gap, $currentslot['to'] + $gap))
                    ) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
    /**
     *
     * @param mixed $value
     * @return boolean
     */
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
    /**
     *
     * @param number $newslotnext
     * @return number
     */
    private function _add_slot_fields($newslotnext) {
        $mform = &$this->_form;

        $totalslots = $newslotnext + ORGANIZER_NUM_DAYS_ADD;
        for ($slot = $newslotnext; $slot < $totalslots; $slot++) {
                $slotgroup = $this->_create_day_slot_group($slot);
                $grouplabel = get_string("weekdaylabel", "organizer") . " " . ($slot + 1);
                $mform->insertElementBefore(
                    $mform->createElement(
                        'group', 'organizer_slotgroup{$day}',
                        $grouplabel, $slotgroup, ORGANIZER_SPACING, false
                    ), 'other'
                );
        }

        return $totalslots;
    }
    /**
     *
     * @param int $newslotindex
     * @return NULL[]|object[]|object[]
     */
    private function _create_day_slot_group($newslotindex) {
        $mform = &$this->_form;
        $name = "newslots[$newslotindex]";
        $slotgroup = array();

        $slotgroup[] = $mform->createElement('select', "{$name}[day]", '', $this->weekdays);
        $mform->setType("{$name}[day]", PARAM_INT);
        $mform->setDefault("{$name}[day]", -1);

        $slotgroup[] = $mform->createElement('static', '', '', get_string('slotfrom', 'organizer'));
        $slotgroup[] = $mform->createElement('select', "{$name}[from]", '', $this->pickeroptions);
        $mform->setType("{$name}[from]", PARAM_INT);
        $mform->setDefault("{$name}[from]", 8 * 3600);

        $slotgroup[] = $mform->createElement('select', "{$name}[dayto]", '', $this->weekdays);
        $mform->setType("{$name}[dayto]", PARAM_INT);
        $mform->setDefault("{$name}[dayto]", -1);

        $slotgroup[] = $mform->createElement('static', '', '', get_string('slotto', 'organizer'));
        $slotgroup[] = $mform->createElement('select', "{$name}[to]", '', $this->pickeroptions);
        $mform->setType("{$name}[to]", PARAM_INT);
        $mform->setDefault("{$name}[to]", 8 * 3600);
        $slotgroup[] = $mform->createElement('advcheckbox', "{$name}[visible]", '', get_string('visible', 'organizer'),
                null, array(0, 1));
        $mform->setType("{$name}[visible]", PARAM_INT);
        $mform->setDefault("{$name}[visible]", 1);
        $slotgroup[] = $mform->createElement("html", "<span name='forecastday_{$newslotindex}'></span>");
        $slotgroup[] = $mform->createElement("html", "<span name='newslots_{$newslotindex}' style='display:none'>0</span>");
        $slotgroup[] = $mform->createElement("html", "<span name='newpax_{$newslotindex}' style='display:none'>0</span>");

        return $slotgroup;
    }


    /**
     *
     * @return string[]
     */
    private function _get_visibilities() {

        $visibilities = array();
        $visibilities[ORGANIZER_VISIBILITY_ALL] = get_string('visibility_all', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_ANONYMOUS] = get_string('visibility_anonymous', 'organizer');
        $visibilities[ORGANIZER_VISIBILITY_SLOT] = get_string('visibility_slot', 'organizer');

        return $visibilities;
    }
    /**
     *
     * @return string[]
     */
    private function _get_trainer_list() {
        $context = organizer_get_context();

        $trainerraw = get_users_by_capability($context, 'mod/organizer:leadslots');

        $trainers = array();
        foreach ($trainerraw as $trainer) {
            $a = new stdClass();
            $a->firstname = $trainer->firstname;
            $a->lastname = $trainer->lastname;
            $name = get_string('fullname_template', 'organizer', $a) . " ({$trainer->email})";
            $trainers[$trainer->id] = $name;
        }

        return $trainers;
    }
    /**
     *
     * @param number $num
     * @param number $lower
     * @param number $upper
     * @return boolean
     */
    private function _between($num, $lower, $upper) {
        return $num > $lower && $num < $upper;
    }
    /**
     * initialize arrays
     */
    private function _init_arrays() {
        $this->pickeroptions = array();
        for ($i = 0; $i < 24; $i++) {
            for ($j = 0; $j < 60; $j += 5) {
                $this->pickeroptions[($i * 3600) + ($j * 60)] = gmdate('H:i', ($i * 3600) + ($j * 60));
            }
        }
        $this->weekdays = array();
        $this->weekdays[-1] = get_string("choose");
        $this->weekdays[0] = get_string('day_0', 'organizer');
        $this->weekdays[1] = get_string('day_1', 'organizer');
        $this->weekdays[2] = get_string('day_2', 'organizer');
        $this->weekdays[3] = get_string('day_3', 'organizer');
        $this->weekdays[4] = get_string('day_4', 'organizer');
        $this->weekdays[5] = get_string('day_5', 'organizer');
        $this->weekdays[6] = get_string('day_6', 'organizer');
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

        $PAGE->requires->js_call_amd('mod_organizer/addform', 'init', array(false));

        $mform = &$this->_form;

        $data = $this->_customdata;
        $mform->addElement('hidden', 'scrollx', isset($data->scrollx) ? $data->scrollx : 0);
        $mform->setType('scrollx', PARAM_BOOL);
        $mform->addElement('hidden', 'scrolly', isset($data->scrolly) ? $data->scrolly : 0);
        $mform->setType('scrolly', PARAM_BOOL);
    }
    /**
     *
     * @return mixed
     */
    private function _get_instance_visibility() {

          $organizer = organizer_get_organizer();

        return    $organizer->visibility;
    }

}
