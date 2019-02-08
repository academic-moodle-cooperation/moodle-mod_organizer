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
 * view_action.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_print.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = optional_param_array('slots', array(), PARAM_INT);
$app = optional_param('app', null, PARAM_INT);
$tsort = optional_param('tsort', null, PARAM_ALPHA);

$url = new moodle_url('/mod/organizer/view_action.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('action', $action);
$url->param('sesskey', sesskey());

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$slots = organizer_sortout_hiddenslots($slots);

if (count($slots) == 0) {
    $redirecturl->param('messages[]', 'message_warning_no_visible_slots_selected');
    redirect($redirecturl);
}

if ($tsort != null) {
    if (isset($_SESSION['organizer_slots'])) {
        $slots = $_SESSION['organizer_slots'];
        $_SESSION['organizer_tsort'] = $tsort;
    } else {
        redirect($redirecturl);
    }
} else {
    $_SESSION['organizer_slots'] = $slots;
}

$s = $slots == null ? array() : $slots;

$mform = new organizer_print_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $s));

if ($data = $mform->get_data()) {
    // Create pdf.

    $slots = $_SESSION['organizer_slots'];

    if (count($slots) == 0) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    set_user_preference('organizer_printperpage', $data->entriesperpage);
    set_user_preference('organizer_printperpage_optimal', $data->printperpage_optimal);
    set_user_preference('organizer_textsize', $data->textsize);
    set_user_preference('organizer_pageorientation', $data->pageorientation);
    set_user_preference('organizer_headerfooter', $data->headerfooter);

    if ($data->printperpage_optimal == 1) {
        $ppp = false;
    } else {
        $ppp = $data->entriesperpage;
    }

    $organizer = $DB->get_record('organizer', array('id' => $cm->instance));

    require_capability('mod/organizer:printslots', $context);

    $event = \mod_organizer\event\appointment_list_printed::create(
        array(
            'objectid' => $PAGE->cm->id,
            'context' => $PAGE->context
        )
    );
    $event->trigger();

    organizer_display_printable_table(
        $organizer->allowregistrationsfromdate,
        $organizer->duedate, $data->cols, $data->slots, $ppp, $data->textsize,
        $data->pageorientation, $data->headerfooter
    );
    redirect($redirecturl);

} else if ($mform->is_cancelled()) {
    // Form canceled.
    unset($_SESSION['organizer_slots']);
    redirect($redirecturl);

} else {
    // Display printpreview.
    if ($slots == null || count($slots) == 0) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    organizer_display_form($mform, get_string('title_print', 'organizer'));
}

die;

function organizer_organizer_student_action_allowed($action, $slot) {
    global $DB;

    if (!$DB->record_exists('organizer_slots', array('id' => $slot))) {
        return false;
    }

    $slotx = new organizer_slot($slot);

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    $canregister = has_capability('mod/organizer:register', $context, null, false);
    $canunregister = has_capability('mod/organizer:unregister', $context, null, false);
    $canreregister = $canregister && $canunregister;

    $myapp = organizer_get_last_user_appointment($organizer);
    if ($myapp) {
        $regslot = $DB->get_record('organizer_slots', array('id' => $myapp->slotid));
        if (isset($regslot)) {
            $regslotx = new organizer_slot($regslot);
        }
    }

    $myslotexists = isset($regslot);
    $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotdisabled = $slotx->is_past_due() || $slotx->is_past_deadline();
    $myslotpending = $myslotexists && $regslotx->is_past_deadline() && !$regslotx->is_evaluated();
    $ismyslot = $myslotexists && ($slotx->id == $regslot->id);
    $slotfull = $slotx->is_full();

    $disabled = $myslotpending || $organizerdisabled || $slotdisabled
        || !$slotx->organizer_user_has_access() || $slotx->is_evaluated();

    if ($myslotexists) {
        if (!$slotdisabled) {
            if ($ismyslot) {
                $disabled |= !$canunregister
                || (isset($regslotx) && $regslotx->is_evaluated() && !$myapp->allownewappointments);
            } else {
                $disabled |= $slotfull || !$canreregister
                || (isset($regslotx) && $regslotx->is_evaluated() && !$myapp->allownewappointments);
            }
        }
        $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REREGISTER;
    } else {
        $disabled |= $slotfull || !$canregister || $ismyslot;
        $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REGISTER;
    }

    return !$disabled && ($action == $allowedaction);
}

function organizer_display_printable_table($registrationsfromdate, $timedue, $columns,
    $slots, $entriesperpage = false, $textsize = '10', $orientation = 'L',
    $headerfooter = true
) {

    list(, $course, $organizer, ) = organizer_get_course_module_data();

    $coursename = $course->idnumber ? $course->idnumber . " " . $course->fullname : $course->fullname;
    $coursename .= $course->shortname ? " (" . $course->shortname . ")" : "";
    $coursename = format_text($coursename, FORMAT_MOODLE, array("filter" => true, "trusted" => true));
    $organizername = format_text($organizer->name, FORMAT_MOODLE, array("filter" => true, "trusted" => true));
    $coursename = html_to_text($coursename);
    $organizername = html_to_text($organizername);
    $filename = $coursename . '-' . $organizername;

    if ($noprintfields = get_user_preferences("mod_organizer_noprintfields")) {
        $noprintfieldsarray = explode(",", $noprintfields);
        foreach ($noprintfieldsarray as $noprintfield) {
            if ($key = array_search($noprintfield, $columns)) {
                unset($columns[$key]);
            }
        }
    }

    $columnwitdh = array();
    $titles = array();
    $columnformats = array();

    $tsort = isset($_SESSION['organizer_tsort']) ? $_SESSION['organizer_tsort'] : "";
    if ($tsort != "") {
        $order = "ASC";

        if (substr($tsort, strlen($tsort) - strlen("DESC")) == "DESC") {
            $tsort = substr($tsort, 0, strlen($tsort) - strlen("DESC"));
            $order = "DESC";
        }
    }
    $colorder = array();
    $dosort = false;
    $i = 0;
    foreach ($columns as $column) {
        if ($column != "") {
            $titles[] = get_string("th_$column", 'organizer');

            if ($tsort == $column) {
                $dosort = true;
            }

            $colorder[$column] = $i++;

            switch ($column) {
                case 'datetime':
                    $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'location':
                    $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'teacher':
                    $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'groupname':
                    $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'participant':
                    $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'email':
                    $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'idnumber':
                    $columnwitdh[] = array('value' => 24, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'attended':
                    $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'grade':
                    $columnwitdh[] = array('value' => 18, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                break;
                case 'feedback':
                    $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 1, 'align' => 'L');
                break;
            }
        }
    }

    switch($tsort) {
        case "datetime";
            $sort = "starttime";
        break;
        case "location":
            $sort = "s.location";
        break;
        case "groupname":
            $sort = "groupname";
        break;
        case "participant":
            $sort = "u.lastname";
        break;
        case "email":
            $sort = "u.email";
        break;
        case "idnumber":
            $sort = "u.idnumber";
        break;
        case "attended":
            $sort = "a.attended";
        break;
        case "grade":
            $sort = "a.grade";
        break;
        case "feedback":
            $sort = "a.feedback";
        break;
        default:
            $sort = null;
    }

    if (!isset($order)) {
        $order = "";
    } else if ($order != "DESC" && $order != "ASC") {
        $order = "";
    }

    if ($dosort) {
        $dosort = $sort . ' ' . $order;
    } else {
        $dosort = "";
    }

    $registrationsfromdate = $registrationsfromdate ? userdate($registrationsfromdate) : get_string('pdf_notactive', 'organizer');
    $timedue = $timedue ? userdate($timedue) : get_string('pdf_notactive', 'organizer');

    $mpdftable = new \mod_organizer\MTablePDF($orientation, $columnwitdh);
    $mpdftable->SetTitle(
        get_string('modulename', 'organizer') . " " .
        $organizername . "-" . get_string('printout', 'organizer')
    );
    $mpdftable->setRowsperPage($entriesperpage);
    $mpdftable->ShowHeaderFooter($headerfooter);
    $mpdftable->SetFontSize($textsize);
    $mpdftable->setHeaderText(
            get_string('course') . ':', $coursename,
            get_string('modulename', 'organizer') . ':', $organizername,
            get_string('availablefrom', 'organizer').':', $registrationsfromdate,
            get_string('duedate', 'organizer').':', $timedue,
            '', get_string('created', 'organizer') . " " . userdate(time()),
            '', ''
    );
    $mpdftable->setTitles($titles);
    $mpdftable->setColumnFormat($columnformats);
    $entries = organizer_fetch_table_entries($slots, $dosort);
    $rowspan = 0;
    foreach ($entries as $entry) {
        $row = array();
        if ($rowspan == 0) {
            $rowspan = $entry->rowspan;
        }
        foreach ($columns as $column) {
            switch ($column) {
                // These columns may have rowspan.
                case 'datetime':
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $datetime = userdate($entry->starttime, get_string('fulldatetimetemplate', 'organizer')) . ' - '
                        . userdate($entry->starttime + $entry->duration, get_string('timetemplate', 'organizer'));
                        $row[] = array('data' => $datetime, 'rowspan' => $rowspan - 1);
                    }
                break;
                case 'location':
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $row[] = array('data' => $entry->location, 'rowspan' => $rowspan - 1);
                    }
                break;
                case 'teacher':
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $a = new stdClass();
                        $trainers = organizer_get_slot_trainers($entry->slotid, true);
                        $name = "";
                        $conn = "";
                        foreach ($trainers as $trainer) {
                            $a->firstname = $trainer->firstname;
                            $a->lastname = $trainer->lastname;
                            $name .= $conn . get_string('fullname_template', 'organizer', $a);
                            $conn = ", ";
                        }
                        $row[] = array('data' => $name, 'rowspan' => $rowspan - 1, 'name' => 'teacher');
                    }
                break;
                case 'groupname':
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $groupname = isset($entry->groupname) ? $entry->groupname : '';
                        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                            $groupname .= organizer_get_teacherapplicant_output($entry->teacherapplicantid, null, true);
                        }
                        $row[] = array('data' => $groupname, 'rowspan' => $rowspan - 1);
                    }
                break;
                    // These columns cannot have rowspan.
                case 'participant':
                    $a = new stdClass();
                    $a->firstname = $entry->firstname;
                    $a->lastname = $entry->lastname;
                    $name = get_string('fullname_template', 'organizer', $a);
                    if (!$organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                        $name .= organizer_get_teacherapplicant_output($entry->teacherapplicantid, null, true);
                    }
                    $row[] = array('data' => $name, 'rowspan' => 0, 'name' => 'participant');
                break;
                case 'email':
                    $row[] = array('data' => $entry->email, 'rowspan' => 0, 'name' => 'email');
                break;
                case 'idnumber':
                    $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                    $row[] = array('data' => $idnumber, 'rowspan' => 0);
                break;
                case 'attended':
                    $attended = isset($entry->attended) ? ($entry->attended == 1 ? get_string('yes') : get_string('no')) : '';
                    $row[] = array('data' => $attended, 'rowspan' => 0);
                break;
                case 'grade':
                    $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                    $row[] = array('data' => $grade, 'rowspan' => 0);
                break;
                case 'feedback':
                    $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                    $row[] = array('data' => $feedback, 'rowspan' => 0);
                break;
            }
        }

        $mpdftable->addRow($row);
        $rowspan--;
    }

    $format = optional_param('format', 'pdf', PARAM_TEXT);

    switch($format) {
        case 'xlsx':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_XLSX);
        break;
        case 'xls':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_XLS);
        break;
        case 'ods':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_ODS);
        break;
        case 'csv_comma':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_CSV_COMMA);
        break;
        case 'csv_tab':
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_CSV_TAB);
        break;
        default:
            $mpdftable->setOutputFormat(\mod_organizer\MTablePDF::OUTPUT_FORMAT_PDF);
        break;
    }

    $mpdftable->generate($filename);
    die();
}