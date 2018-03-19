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
require_once(dirname(__FILE__) . '/view_action_form_printdetail.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');
require_once(dirname(__FILE__) . '/slotlib.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$app = optional_param('app', null, PARAM_INT);

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


if (!$slot) {
    $redirecturl->param('messages[]', 'message_warning_no_visible_slots_selected');
    redirect($redirecturl);
} else {
    $_SESSION['organizer_slot'] = $slot;
}

$mform = new organizer_print_slotdetail_form(null, array('id' => $cm->id, 'mode' => $mode, 'slot' => $slot));

if ($data = $mform->get_data()) {
    // Create pdf.

    if (!$slot) {
        $slot = $_SESSION['organizer_slot'];
    }

    if (!$slot) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots(array($slot))) {
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

    organizer_display_printable_slotdetail_table($data->cols, $data->slot, $ppp, $data->textsize,
        $data->pageorientation, $data->headerfooter
    );
    redirect($redirecturl);

} else if ($mform->is_cancelled()) {
    // Form canceled.
    unset($_SESSION['organizer_slot']);
    redirect($redirecturl);

} else {
    // Display printpreview.
    if (!$slot) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots(array($slot))) {
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

function organizer_display_printable_slotdetail_table($columns, $slotid, $entriesperpage = false, $textsize = '10',
        $orientation = 'L', $headerfooter = true)
{
    global $DB;

    list(, $course, $organizer, ) = organizer_get_course_module_data();

    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    $trainers = organizer_get_slot_trainers($slotid, true);
    $filename = $course->shortname . " - " . $organizer->name;
    $columnwitdh = array();
    $titles = array();
    $columnformats = array();

    $i = 0;
    foreach ($columns as $column) {

        switch ($column) {
            case 'lastname':
                $titles[] = get_string('lastname');
                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'firstname':
                $titles[] = get_string('firstname');
                $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'email':
                $titles[] = get_string('email');
                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'idnumber':
                $titles[] = get_string('idnumber');
                $columnwitdh[] = array('value' => 24, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'attended':
                $titles[] = get_string('attended', 'organizer');
                $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'grade':
                $titles[] = get_string('grade');
                $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'feedback':
                $titles[] = get_string('feedback');
                $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'signature':
                $titles[] = get_string('signature', 'organizer');
                $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            default:
                if (is_numeric($column)){ // custom user field
                    $userinfofield = $DB->get_record_select('user_info_field', 'id = :id', array('id' => $column));
                    $userinfofields[$userinfofield->id] = $userinfofield->datatype;
                    $name = $userinfofield->name ? $userinfofield->name : $userinfofield->shortname;
                    $titles[] = $name;
                    $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                }
        }
    }

    $slotdatetime = organizer_date_time($slot);
    $slotdatetime = str_replace("<br />", " ", $slotdatetime);
    $trainerstr = "";
    $conn = "";
    foreach($trainers as $trainer) {
        $trainerstr .= $conn . $trainer->firstname . " " . $trainer->lastname;
        $conn = ", ";
    }
    $coursename = $course->idnumber ? $course->idnumber . " " . $course->fullname : $course->fullname;

    $mpdftable = new \mod_organizer\MTablePDF($orientation, $columnwitdh);
    $mpdftable->SetTitle(
        get_string('modulename', 'organizer') . " " .
        $organizer->name . " - " . get_string('printout', 'organizer')
    );
    $mpdftable->setRowsperPage($entriesperpage);
    $mpdftable->ShowHeaderFooter($headerfooter);
    $mpdftable->SetFontSize($textsize);
    $mpdftable->setheadertext(
            get_string('course')                  . ':', $coursename,
            get_string('modulename', 'organizer') . ':', $organizer->name,
            get_string('slot', 'organizer')       . ':', $slotdatetime,
            get_string('trainer', 'organizer')    . ':', $trainerstr,
            '', get_string('created', 'organizer') . " " . userdate(time()),
            '', ''
    );
    $mpdftable->setTitles($titles);
    $mpdftable->setColumnFormat($columnformats);
    $entries = organizer_fetch_printdetail_entries($slotid);
    foreach ($entries as $entry) {
        $row = array();
        foreach ($columns as $column) {

            switch ($column) {
                case 'lastname':
                    $content = $entry->lastname;
                    $row[] = array('data' => $content);
                    break;
                case 'firstname':
                    $content = $entry->firstname;
                    $row[] = array('data' => $content);
                    break;
                case 'email':
                    $content = $entry->email;
                    $row[] = array('data' => $content);
                    break;
                case 'idnumber':
                    $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                    $content = $idnumber;
                    $row[] = array('data' => $content);
                    break;
                case 'attended':
                    $attended = isset($entry->attended) ? ($entry->attended == 1 ? get_string('yes') : get_string('no')) : '';
                    $content = $attended;
                    $row[] = array('data' => $content);
                    break;
                case 'grade':
                    $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                    $content = $grade;
                    $row[] = array('data' => $content);
                    break;
                case 'feedback':
                    $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                    $content = $feedback;
                    $row[] = array('data' => $content);
                    break;
                case 'groupname':
                    $content = $entry->groupname;
                    $row[] = array('data' => $content);
                    break;
                case 'signature':
                    $content = ' ';
                    $row[] = array('data' => $content);
                    break;
                default:
                    if (is_numeric($column)) {
                        if ($userinfodata = $DB->get_field(
                                'user_info_data', 'data', array('fieldid' => $column, 'userid' => $entry->id))
                        ) {
                            if (isset($userinfofields[$column]) &&
                                    ($userinfofields[$column] == 'text' || $userinfofields[$column] == 'textarea')
                            ) {
                                $row[] = array('data' => $userinfodata);
                            } else {
                                $row[] = array("data" => "Unsupported column type: $column");
                            }
                        } else {
                            $content = '';
                            $row[] = array('data' => $content);
                        }
                    }
            }

        }
        $mpdftable->addRow($row);
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