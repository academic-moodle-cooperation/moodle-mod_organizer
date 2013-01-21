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
 * This file contains forms needed to create new appointments for organizer
 *
 * @package    mod
 * @subpackage organizer
 * @copyright  2011 Ivan �aki� <ivan.sakic3@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('SPACING', '&nbsp;&nbsp;');
define('NUM_DAYS', '7');
define('NO_DAY', '-1');
define('DAY_IN_SECS', '86400');
define('USE_SCROLL_FIX', '1');

require_once('../../lib/formslib.php');
require_once('locallib.php');

class organizer_add_slots_form extends moodleform {

    private $pickeroptions;

    public function definition_after_data() {
        $mform = &$this->_form;

        if (isset($mform->_submitValues['reviewslots'])) {
            $noerrors = $this->_my_validation($mform->_submitValues);

            if (!$noerrors) {
                $this->_add_slot_fields();

                $buttons = &$mform->getElement('buttonar')->getElements();
                $this->_hide_button($buttons[1]);
            } else {
                $this->_add_review_slots();

                $this->_freeze_fields();

                $buttons = &$mform->getElement('buttonar')->getElements();

                $this->_hide_button($buttons[0]);

                if (isset($mform->_submitValues['comments']) && $mform->_submitValues['comments'] != '') {
                    $mform->getElement('comments')->freeze();
                } else {
                    $mform->removeElement('comments');
                    $mform->removeElement('other');
                }
            }
        } else if (isset($mform->_submitValues['createslots'])) {
            $this->_add_dummy_fields();
        } else {
            $this->_add_slot_fields();

            $buttons = &$mform->getElement('buttonar')->getElements();
            $this->_hide_button($buttons[1]);
        }
    }

    private function _freeze_fields() {
        $mform = &$this->_form;
        $mform->getElement('teacherid')->freeze();
        $mform->getElement('notificationtime')->freeze();
        $mform->getElement('teachervisible')->freeze();
        $mform->getElement('isanonymous')->freeze();
        $mform->getElement('location')->freeze();
        $mform->getElement('locationlink')->freeze();
        $mform->getElement('comments')->freeze();
        $mform->getElement('startdate')->freeze();
        $mform->getElement('enddate')->freeze();
        $mform->getElement('duration')->freeze();
        $mform->getElement('availablefrom')->freeze();
        $mform->getElement('maxparticipants')->freeze();
    }

    private function _converts_to_int($value) {
        if (is_numeric($value)) {
            if (intval($value) == floatval($value)) {
                return true;
            }
        }
        return false;
    }

    private function _my_validation($data) {
        if ($data['location'] == '' || !$this->_converts_to_int($data['maxparticipants'])
                || $data['maxparticipants'] <= 0 || !$this->_converts_to_int($data['duration']['number'])
                || $data['duration']['number'] <= 0 || !$this->_converts_to_int($data['notificationtime']['number'])
                || $data['notificationtime']['number'] <= 0) {
            return false;
        }

        if (isset($data['availablefrom']) && is_array($data['availablefrom'])) {
            if (!$this->_converts_to_int($data['availablefrom']['number']) || $data['availablefrom']['number'] <= 0) {
                return false;
            }
        }

        if (isset($data['newslots'])) {
            $slots = $data['newslots'];

            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['selected'] && ($slot['from'] >= $slot['to'])) {
                    return false;
                }
            }

            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if ($currentslot['day'] == $otherslot['day']
                            && ($this->_between($currentslot['from'], $otherslot['from'], $otherslot['to'])
                                    || $this->_between($currentslot['to'], $otherslot['from'], $otherslot['to'])
                                    || $this->_between($otherslot['from'], $currentslot['from'], $currentslot['to'])
                                    || $this->_between($otherslot['to'], $currentslot['from'], $currentslot['to']))) {

                        return false;
                    }
                }
            }
        }
        return true;
    }

    private function _hide_button(&$button) {
        $attr = $button->getAttributes();
        $attr['style'] = 'display: none;';
        $button->setAttributes($attr);
    }

    protected function definition() {
        global $USER;

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

        /*
        $mform->addElement('advcheckbox', 'teachervisible', get_string('teachervisible', 'organizer'), null, null,
                array(0, 1));
                */
        $mform->addElement('checkbox', 'teachervisible', get_string('teachervisible', 'organizer'));
        $mform->setType('teachervisible', PARAM_BOOL);
        $mform->setDefault('teachervisible', 1);
        $mform->addHelpButton('teachervisible', 'teachervisible', 'organizer');

        /*
        $mform->addElement('advcheckbox', 'isanonymous', get_string('isanonymous', 'organizer'), null, null,
                array(0, 1));
                */
        $mform->addElement('checkbox', 'isanonymous', get_string('isanonymous', 'organizer'));
        $mform->setType('isanonymous', PARAM_BOOL);
        $mform->addHelpButton('isanonymous', 'isanonymous', 'organizer');

        $mform->addElement('text', 'location', get_string('location', 'organizer'), array('size' => '64'));
        $mform->setType('location', PARAM_TEXT);
        $mform->addRule('location', get_string('err_location', 'organizer'), 'required');
        $mform->addHelpButton('location', 'location', 'organizer');

        $mform->addElement('text', 'locationlink', get_string('locationlink', 'organizer'), array('size' => '64'));
        $mform->setType('locationlink', PARAM_TEXT);
        $mform->addHelpButton('locationlink', 'locationlink', 'organizer');

        $mform->addElement('duration', 'duration', get_string('duration', 'organizer'),
                array('optional' => false, 'defaultunit' => 60));
        $mform->setType('duration', PARAM_INT);
        $mform->setDefault('duration', 900);
        $duration = $mform->getElement('duration')->getElements();
        $duration[1]->removeOption(1);
        $duration[1]->removeOption(86400);
        $mform->addHelpButton('duration', 'duration', 'organizer');

        $mform->addElement('text', 'maxparticipants', get_string('maxparticipants', 'organizer'), array('size' => '3'));
        $mform->setType('maxparticipants', PARAM_INT);
        $mform->setDefault('maxparticipants', 1);
        $mform->addHelpButton('maxparticipants', 'maxparticipants', 'organizer');

        global $DB;
        $cm = get_coursemodule_from_id('organizer', $data['id'], 0, false, MUST_EXIST);
        $organizer = $DB->get_record('organizer', array('id' => $cm->instance), '*', MUST_EXIST);
        if ($organizer->isgrouporganizer) {
            $mform->freeze('maxparticipants');
        }

        $mform->addElement('duration', 'notificationtime', get_string('notificationtime', 'organizer'),
                array('optional' => false, 'defaultunit' => 86400));
        $mform->setType('notificationtime', PARAM_INT);
        $mform->setDefault('notificationtime', 86400);
        $notificationtime = $mform->getElement('notificationtime')->getElements();
        $notificationtime[1]->removeOption(1);
        $mform->addHelpButton('notificationtime', 'notificationtime', 'organizer');

        $mform->addElement('header', 'slotperiod', get_string('slotperiodheader', 'organizer'));
        $mform->addHelpButton('slotperiod', 'slotperiodheader', 'organizer');

        $mform->addElement('date_selector', 'startdate', get_string('slotperiodstarttime', 'organizer'));
        $mform->setType('startdate', PARAM_INT);
        $mform->setDefault('startdate', time());

        $mform->addElement('date_selector', 'enddate', get_string('slotperiodendtime', 'organizer'));
        $mform->setType('enddate', PARAM_INT);
        $mform->setDefault('enddate', mktime(null, null, null, date("m"), date("d") + 6, date("Y")));

        // HERE GO THE SLOT FIELDS

        $mform->addElement('header', 'other', get_string('otherheader', 'organizer'));

        $mform->addElement('textarea', 'comments', get_string('appointmentcomments', 'organizer'),
                array('wrap' => 'virtual', 'rows' => '10', 'cols' => '60'));
        $mform->setType('comments', PARAM_RAW);
        $mform->addHelpButton('comments', 'appointmentcomments', 'organizer');

        $buttonarray = array();

        $buttonarray[] = &$mform->createElement('submit', 'reviewslots', get_string('reviewsubmit', 'organizer'));
        $buttonarray[] = &$mform->createElement('submit', 'createslots', get_string('createsubmit', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel');

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    private function _add_slot_fields() {
        $mform = &$this->_form;

        if (!isset($mform->_submitValues['reviewslots'])) {
            $now = true;
        } else {
            if (isset($mform->_submitValues['now'])) {
                $now = true;
            } else {
                $now = false;
            }
        }

        $group = array();

        if ($now) {
            $group[] = $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer'),
                    array('optional' => false, 'defaultunit' => 86400), array('disabled' => 1));
        } else {
            $group[] = $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer'),
                    array('optional' => false, 'defaultunit' => 86400));
        }
        $group[] = $mform->createElement('static', '', '',
                get_string('relative_deadline_before', 'organizer') . '&nbsp;&nbsp;&nbsp;'
                        . get_string('relative_deadline_now', 'organizer'));
        $group[] = $mform->createElement('checkbox', 'now', '', null,
                array('onchange' => 'toggleAvailableFrom(this);', 'group' => null));

        $mform->setDefault('availablefrom', 86400 * 7);
        $mform->setDefault('now', $now);
        $mform->insertElementBefore(
                $mform->createElement('group', 'availablefromgroup', get_string('availablefrom', 'organizer'), $group,
                        SPACING, false), 'notificationtime');
        $availablefromgroup = $mform->getElement('availablefromgroup')->getElements();
        $availablefrom = $availablefromgroup[0]->getElements();
        $availablefrom[1]->removeOption(1);

        $mform->insertElementBefore(
                $mform->createElement('header', 'slottimeframes', get_string('slottimeframesheader', 'organizer')),
                'other');
        $mform->addHelpButton('slottimeframes', 'slottimeframesheader', 'organizer');

        $totalslots = isset($mform->_submitValues['totalslots']) ? $mform->_submitValues['totalslots'] : 0;

        if (isset($mform->_submitValues['addday'])) {
            $adddayarray = array_keys($mform->_submitValues['addday']);
            $addday = reset($adddayarray);
        } else {
            $addday = -1;
        }

        $slots = isset($mform->_submitValues['newslots']) ? $mform->_submitValues['newslots'] : array();

        for ($day = 0; $day < NUM_DAYS; $day++) {
            $dayset = false;
            $first = true;
            foreach ($slots as $id => $slot) {
                if ($slot['day'] == $day) {
                    $dayslot = $this->_create_day_slot_group($day, $id, $first);
                    $mform->insertElementBefore(
                            $mform->createElement('group', "slotgroup{$id}", '', $dayslot, SPACING, false), 'other');
                    $dayset = true;
                    $first = false;
                }
            }

            if ($addday == $day || !$dayset) {
                $dayslot = $this->_create_day_slot_group($day, $totalslots, $first);
                $mform->insertElementBefore(
                        $mform->createElement('group', "slotgroup{$totalslots}", '', $dayslot, SPACING, false),
                        'other');
                $totalslots++;
            }
            if ($day != NUM_DAYS - 1) {
                $mform->insertElementBefore($mform->createElement('html', '<hr />'), 'other');
            }
        }

        $mform->_submitValues['totalslots'] = $totalslots;
        $mform->addElement('hidden', 'totalslots', $totalslots);
    }

    private function _create_day_slot_group($day, $id, $isfirst) {
        $mform = &$this->_form;
        $name = "newslots[$id]";
        $dayslot = array();
        $dayslot[] = $mform->createElement('advcheckbox', "{$name}[selected]", '', SPACING, null, array(0, 1));
        $mform->setDefault("{$name}[selected]", 0);
        $dayslot[] = $mform->createElement('static', '', '',
                get_string("day_$day", 'organizer') . get_string('slotfrom', 'organizer'));
        $dayslot[] = $mform->createElement('hidden', "{$name}[day]", $day);
        $dayslot[] = $mform->createElement('select', "{$name}[from]", '', $this->pickeroptions);
        $mform->setDefault("{$name}[from]", 8 * 3600);
        $dayslot[] = $mform->createElement('static', '', '', get_string('slotto', 'organizer'));
        $dayslot[] = $mform->createElement('select', "{$name}[to]", '', $this->pickeroptions);
        $mform->setDefault("{$name}[to]", 8 * 3600);

        if ($isfirst) {
            $dayslot[] = $mform->createElement('submit', "addday[{$day}]", get_string('newslot', 'organizer'));
        }
        return $dayslot;
    }

    private function _add_review_slots() {
        $mform = &$this->_form;

        if (isset($mform->_submitValues['now'])) {
            $mform->insertElementBefore(
                    $mform->createElement('static', 'availablefromdummy', get_string('availablefrom', 'organizer'),
                            'Starting now'), 'notificationtime');
            $mform->addElement('hidden', 'availablefrom', 0);
        } else {
            $mform->insertElementBefore(
                    $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer')),
                    'notificationtime');
        }

        $mform->insertElementBefore(
                $mform->createElement('header', 'slottimeframes', get_string('rewievslotsheader', 'organizer')),
                'other');

        $slots = isset($mform->_submitValues['newslots']) ? $mform->_submitValues['newslots'] : array();

        $startdate = mktime(0, 0, 0, $mform->_submitValues['startdate']['month'],
                $mform->_submitValues['startdate']['day'], $mform->_submitValues['startdate']['year']);
        $enddate = mktime(0, 0, 0, $mform->_submitValues['enddate']['month'], $mform->_submitValues['enddate']['day'],
                $mform->_submitValues['enddate']['year']);

        $teacherid = $mform->_submitValues['teacherid'];

        $events = $this->_organizer_load_events($teacherid, $startdate, $enddate + DAY_IN_SECS);

        $id = 0;
        $totalslots = 0;

        $collcount = 0;

        for ($day = 0; $day < NUM_DAYS; $day++) {
            $dayempty = true;
            for ($date = $startdate; $date < $enddate + DAY_IN_SECS; $date += DAY_IN_SECS) {

                $datedata = getdate($date);
                $dayofweek = ($datedata['wday'] + 6) % 7;
                if ($day == $dayofweek) {
                    $heading = true;
                    foreach ($slots as $slot) {
                        if ($slot['selected'] && $slot['day'] == $day) {
                            if ($heading) {
                                $mform->insertElementBefore(
                                        $mform->createElement('html',
                                                '<strong>'
                                                        . userdate($date,
                                                                get_string('fulldatelongtemplate', 'organizer'))
                                                        . '</strong><br />'), 'other');
                                $heading = false;
                            }
                            $duration = $mform->_submitValues['duration']['number'];
                            $unit = $mform->_submitValues['duration']['timeunit'];

                            $collisions = $this->_check_collision($slot, $date, $events);
                            $collcount += count($collisions);
                            $disabled = count($collisions) ? true : false;

                            $dayslot = $this->_create_slot_review_group($date, $id, $slot['from'], $slot['to'],
                                    $duration, $unit, $disabled);
                            $mform->insertElementBefore(
                                    $mform->createElement('group', "reviewgroup{$id}", '', $dayslot, SPACING, false),
                                    'other');

                            $head = true;
                            foreach ($collisions as $event) {
                                if ($head) {
                                    $mform->insertElementBefore(
                                            $mform->createElement('html',
                                                    '<span class="error">' . get_string('collision', 'organizer')
                                                            . '</span><br />'), 'other');
                                    $head = false;
                                }
                                $mform->insertElementBefore(
                                        $mform->createElement('html',
                                                '&nbsp;&nbsp;- <strong>' . $event->name . '</strong> from '
                                                        . userdate($event->timestart,
                                                                get_string('timetemplate', 'organizer')) . ' to '
                                                        . userdate($event->timestart + $event->timeduration,
                                                                get_string('timetemplate', 'organizer')) . '<br />'),
                                        'other');
                                $disabled = true;
                            }
                            if (!$disabled) {
                                $totalslots += intval(($slot['to'] - $slot['from']) / ($duration * $unit));
                            }

                            $dayempty = false;

                            $id++;
                        }
                    }
                }
            }

            if ($dayempty) {
                $mform->insertElementBefore(
                        $mform->createElement('html',
                                get_string('noslots', 'organizer') . get_string("day_$day", 'organizer')), 'other');
            }
            $mform->insertElementBefore($mform->createElement('html', '<hr />'), 'other');
        }

        global $DB;

        if (organizer_is_group_mode()) {
            list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
            $slots = $DB->get_records('organizer_slots', array('organizerid' => $organizer->id));

            $a = new stdClass();
            $a->numplaces = $totalslots;
            $a->totalplaces = count($slots) + $a->numplaces;
            $groups = groups_get_all_groups($course->id, 0, $cm->groupingid);
            $a->numgroups = count($groups);

            $html = get_string('addslots_placesinfo_group', 'organizer', $a);

        } else {
            list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
            $slots = $DB->get_records('organizer_slots', array('organizerid' => $organizer->id));
            $placecount = 0;
            foreach ($slots as $slot) {
                $placecount += $slot->maxparticipants;
            }

            $a = new stdClass();
            $a->numplaces = $totalslots * $mform->_submitValues['maxparticipants'];
            $a->totalplaces = $placecount + $a->numplaces;
            $a->numstudents = count(get_enrolled_users($context, 'mod/organizer:register'));

            $html = get_string('addslots_placesinfo', 'organizer', $a);
        }

        $mform->insertElementBefore($mform->createElement('html', $html), 'other');

        return count($collcount) == 0;
    }

    private function _create_slot_review_group($date, $id, $from, $to, $duration, $unit, $disabled = false) {
        $mform = &$this->_form;
        $name = "finalslots[{$id}]";

        if ($unit == 1) {
            $unitname = ($duration == 1) ? get_string('sec', 'organizer') : get_string('sec_pl', 'organizer');
        } else if ($unit == 60) {
            $unitname = ($duration == 1) ? get_string('min', 'organizer') : get_string('min_pl', 'organizer');
        } else if ($unit == 3600) {
            $unitname = ($duration == 1) ? get_string('hour', 'organizer') : get_string('hour_pl', 'organizer');
        } else {
            $unitname = ($duration == 1) ? get_string('day', 'organizer') : get_string('day_pl', 'organizer');
        }

        $a = new stdClass();

        $a->starttime = gmdate('H:i', $from);
        $a->endtime = gmdate('H:i', $to);
        $a->duration = $duration;
        $a->unit = $unitname;
        $a->totalslots = intval(($to - $from) / ($duration * $unit));

        $dayslot = array();
        $dayslot[] = $mform->createElement('advcheckbox', "{$name}[selected]", '',
                SPACING . get_string('totalslots', 'organizer', $a),
                $disabled ? array('disabled' => 'disabled', 'group' => 0) : null, array(0, 1));

        $dayslot[] = $mform->createElement('hidden', "{$name}[date]", $date);
        $dayslot[] = $mform->createElement('hidden', "{$name}[from]", $from);
        $dayslot[] = $mform->createElement('hidden', "{$name}[to]", $to);

        $dayslot[0]->setValue($disabled ? 0 : 1);

        return $dayslot;
    }

    /**
     * This is necessary to facilitate validation and proper handling of incoming
     * data by QuickForm. These fields are never actually rendered.
     * @param type $button 
     */
    private function _add_dummy_fields() {
        $mform = &$this->_form;

        if (isset($mform->_submitValues['now'])) {
            $mform->insertElementBefore(
                    $mform->createElement('static', 'availablefromdummy', get_string('availablefrom', 'organizer'),
                            'Starting now'), 'notificationtime');
            $mform->addElement('hidden', 'availablefrom', 0);
        } else {
            $mform->insertElementBefore(
                    $mform->createElement('duration', 'availablefrom', get_string('availablefrom', 'organizer')),
                    'notificationtime');
        }

        if (isset($mform->_submitValues['finalslots'])) {
            foreach ($mform->_submitValues['finalslots'] as $id => $slot) {
                $name = "finalslots[{$id}]";
                $mform->addElement('hidden', "{$name}[selected]", $slot['selected']);
                $mform->addElement('hidden', "{$name}[date]", $slot['date']);
                $mform->addElement('hidden', "{$name}[from]", $slot['from']);
                $mform->addElement('hidden', "{$name}[to]", $slot['to']);
            }
        }
    }

    /**
     * Checks whether there any events in selected teacher's calendar that collide with the
     * selected time frames for a given date.
     * @param unknown_type $frame time frame to be tested for collision
     * @param int $date particular date on which to make the check
     * @param array $events an array of all events of a teacher that are relevant
     * @return array an array of events in collision
     */
    private function _check_collision($frame, $date, $events) {
        $collidingevents = array();
        foreach ($events as $event) {
            $framefrom = $frame['from'] + $date;
            $frameto = $frame['to'] + $date;
            $eventfrom = $event->timestart;
            $eventto = $eventfrom + $event->timeduration;

            if ($this->_between($framefrom, $eventfrom, $eventto) || $this->_between($frameto, $eventfrom, $eventto)
                    || $this->_between($eventfrom, $framefrom, $frameto)
                    || $this->_between($eventto, $framefrom, $frameto) || $framefrom == $eventfrom
                    || $eventfrom == $eventto) {
                $collidingevents[] = $event;
            }
        }
        return $collidingevents;
    }

    /**
     * Loads the events from the database in the period described in the POST header
     * @return array of event objects
     */
    private function _organizer_load_events($teacherid, $startdate, $enddate) {
        global $DB;

        $params = array('teacherid' => $teacherid, 'startdate1' => $startdate, 'enddate1' => $enddate,
                'startdate2' => $startdate, 'enddate2' => $enddate);

        $query = "SELECT e.id, e.name, e.timestart, e.timeduration FROM {event} e
                INNER JOIN {user} u ON u.id = e.userid
                WHERE u.id = :teacherid AND (e.timestart >= :startdate1
                AND e.timestart < :enddate1 OR (e.timestart + e.timeduration) >= :startdate2
                AND (e.timestart + e.timeduration) < :enddate2) AND e.timeduration > 0";

        return $DB->get_records_sql($query, $params);
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (!$this->_converts_to_int($data['maxparticipants']) || $data['maxparticipants'] <= 0) {
            $errors['maxparticipants'] = get_string('err_posint', 'organizer');
        }

        if (!$this->_converts_to_int($data['duration']) || $data['duration'] <= 0) {
            $errors['duration'] = get_string('err_posint', 'organizer');
        }

        if (!$this->_converts_to_int($data['notificationtime']) || $data['notificationtime'] <= 0) {
            $errors['notificationtime'] = get_string('err_posint', 'organizer');
        }

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

        if (isset($data['newslots'])) {
            $invalidslots = array();
            $slots = $data['newslots'];

            $slotcount = 0;

            for ($i = 0; $i < count($slots); $i++) {
                $slot = $slots[$i];
                if ($slot['selected'] && ($slot['from'] >= $slot['to'])) {
                    $errors['slotgroup' . $i] = get_string('err_fromto', 'organizer');
                    $invalidslots[] = $i;
                }
                if ($slot['selected']) {
                    $slotcount++;
                }
            }

            for ($i = 0; $i < count($slots); $i++) {
                $currentslot = $slots[$i];
                if (in_array($i, $invalidslots)) {
                    continue;
                }
                $message = ' ';
                for ($j = 0; $j < $i; $j++) {
                    $otherslot = $slots[$j];
                    if (!in_array($j, $invalidslots) && $currentslot['day'] == $otherslot['day']
                            && ($this->_between($currentslot['from'], $otherslot['from'], $otherslot['to'])
                                    || $this->_between($currentslot['to'], $otherslot['from'], $otherslot['to'])
                                    || $this->_between($otherslot['from'], $currentslot['from'], $currentslot['to'])
                                    || $this->_between($otherslot['to'], $currentslot['from'], $currentslot['to']))) {

                        $message .= '(' . $this->pickeroptions[$otherslot['from']] . '-'
                                . $this->pickeroptions[$otherslot['to']] . '), ';
                    }
                }
                if ($message != ' ' && $currentslot['selected']) {
                    $message = substr($message, 0, strlen($message) - 2);
                    $errors['slotgroup' . $i] = get_string('err_collision', 'organizer') . $message;
                }
            }
        }

        return $errors;
    }

    private function _get_teacher_list() {
        list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

        $teachersraw = get_users_by_capability($context, 'mod/organizer:addslots');

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
    }

    /**
     * This function applies a javascript fix that scrolls the page back to the position before
     * the submission. It preserves the scroll coordinates within hidden form fields and restores
     * the scroll position from them when the page reloads. Uses JavaScript.
     *
     * The fix can be overriden by setting USE_SCROLL_FIX constant to 0.
     */
    private function _add_scroll_fix() {
        global $PAGE;

        if (!USE_SCROLL_FIX) {
            return;
        }

        $jsmodule = array(
                'name' => 'mod_organizer',
                'fullpath' => '/mod/organizer/module.js',
                'requires' => array('node', 'node-scroll-info', 'scrollview-base'),
        );
        
        $PAGE->requires->js_init_call('M.mod_organizer.init_add_form', null, false, $jsmodule);

        $mform = &$this->_form;

        $data = $this->_customdata;
        $mform->addElement('hidden', 'scrollx', isset($data->scrollx) ? $data->scrollx : 0);
        $mform->addElement('hidden', 'scrolly', isset($data->scrolly) ? $data->scrolly : 0);
    }

}