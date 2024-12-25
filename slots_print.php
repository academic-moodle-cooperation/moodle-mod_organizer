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
 * slots_print.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_organizer\MTablePDF;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_print.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = organizer_get_param_slots();
$tsort = optional_param('tsort', null, PARAM_ALPHA);

[$cm, $course, $organizer, $context, $redirecturl] = organizer_slotpages_header();

$params['limitedwidth'] = organizer_get_limitedwidth();

require_login($course, false, $cm);

$slots = organizer_sortout_hiddenslots($slots);

if (count($slots) == 0) {
    $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_warning_no_visible_slots_selected',
        'organizer'), 'error');
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

$s = $slots == null ? [] : $slots;

$mform = new organizer_print_slots_form(null, ['id' => $cm->id, 'mode' => $mode, 'slots' => $s], 'post', '_blank');

if ($data = $mform->get_data()) {
    // Create pdf.

    $slots = $_SESSION['organizer_slots'];

    if (count($slots) == 0) {
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_warning_no_slots_selected',
            'organizer'), 'error');
        redirect($redirecturl);
    }

    $ppp = organizer_print_setuserprefs_and_triggerevent($data, $cm, $context);

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
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_warning_no_slots_selected',
            'organizer'), 'error');
        redirect($redirecturl);
    }

    organizer_display_form($mform, get_string('title_print', 'organizer'));
}

die;

/**
 * Displays a printable table for the organizer module.
 *
 * @param int $registrationsfromdate The date from which registrations are allowed.
 * @param int $timedue The due date for the organizer.
 * @param array $columns An array of column names to be displayed in the table.
 * @param array $slots An array of slot data to be displayed.
 * @param bool|int $entriesperpage The number of entries per page. Default is false.
 * @param string $textsize The text size to use for the printable table. Default is '10'.
 * @param string $orientation The page orientation ('L' for landscape, 'P' for portrait). Default is 'L'.
 * @param bool $headerfooter Whether to include header and footer in the printable table. Default is true.
 *
 * @return void
 */
function organizer_display_printable_table($registrationsfromdate, $timedue, $columns,
    $slots, $entriesperpage = false, $textsize = '10', $orientation = 'L',
    $headerfooter = true
) {

    [, $course, $organizer, ] = organizer_get_course_module_data();

    $coursename = $course->idnumber ? $course->idnumber . " " . $course->fullname : $course->fullname;
    $coursename .= $course->shortname ? " (" . $course->shortname . ")" : "";
    $coursename = format_text($coursename, FORMAT_MOODLE, ["filter" => true, "trusted" => true]);
    $organizername = format_text($organizer->name, FORMAT_MOODLE, ["filter" => true, "trusted" => true]);
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

    $columnwidth = [];
    $titles = [];
    $columnformats = [];

    $tsort = isset($_SESSION['organizer_tsort']) ? $_SESSION['organizer_tsort'] : "";
    if ($tsort != "") {
        $order = "ASC";

        if (substr($tsort, strlen($tsort) - strlen("DESC")) == "DESC") {
            $tsort = substr($tsort, 0, strlen($tsort) - strlen("DESC"));
            $order = "DESC";
        }
    }
    $colorder = [];
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
                    $columnwidth[] = ['value' => 64, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'location':
                    $columnwidth[] = ['value' => 48, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'teacher':
                    $columnwidth[] = ['value' => 32, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'groupname':
                    $columnwidth[] = ['value' => 32, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'participant':
                    $columnwidth[] = ['value' => 32, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'email':
                    $columnwidth[] = ['value' => 32, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'idnumber':
                    $columnwidth[] = ['value' => 24, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'attended':
                    $columnwidth[] = ['value' => 12, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'grade':
                    $columnwidth[] = ['value' => 18, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 0, 'align' => 'C'];
                break;
                case 'feedback':
                    $columnwidth[] = ['value' => 64, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 1, 'align' => 'L'];
                break;
                case 'comments':
                    $columnwidth[] = ['value' => 64, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 1, 'align' => 'L'];
                    break;
                case 'teachercomments':
                    $columnwidth[] = ['value' => 64, 'mode' => 'Relativ'];
                    $columnformats[] = ['fill' => 1, 'align' => 'L'];
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
        case "comments":
            $sort = "a.commentsk";
            break;
        case "teachercomments":
            $sort = "teachercomments";
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

    $registrationsfromdate = $registrationsfromdate ? userdate($registrationsfromdate) : get_string('pdf_notactive',
        'organizer');
    $timedue = $timedue ? userdate($timedue) : get_string('pdf_notactive', 'organizer');

    $mpdftable = new MTablePDF($orientation, $columnwidth);
    $mpdftable->SetTitle(
        get_string('modulename', 'organizer') . " " .
        $organizername . "-" . get_string('printout', 'organizer')
    );
    $mpdftable->setRowsperPage($entriesperpage);
    $mpdftable->ShowHeaderFooter($headerfooter);
    $mpdftable->SetFontSize($textsize);

    $format = optional_param('format', 'pdf', PARAM_TEXT);
    if ($format != "csv_comma") {
        $mpdftable->setHeaderText(
            get_string('course') . ':', $coursename,
            get_string('modulename', 'organizer') . ':', $organizername,
            get_string('availablefrom', 'organizer').':', $registrationsfromdate,
            get_string('duedate', 'organizer').':', $timedue,
            '', get_string('created', 'organizer') . " " . userdate(time()),
            '', ''
        );
    }

    $mpdftable->setTitles($titles);
    $mpdftable->setColumnFormat($columnformats);
    $entries = organizer_fetch_table_entries($slots, $dosort);
    $rowspan = 0;
    foreach ($entries as $entry) {
        $row = [];
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
                        $row[] = ['data' => $datetime, 'rowspan' => $rowspan - 1];
                    }
                break;
                case 'location':
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $row[] = ['data' => $entry->location, 'rowspan' => $rowspan - 1];
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
                        $row[] = ['data' => $name, 'rowspan' => $rowspan - 1, 'name' => 'teacher'];
                    }
                break;
                case 'teachercomments':
                    $teachercomments = !empty($entry->teachercomments) ? $entry->teachercomments : '';
                    if ($rowspan != $entry->rowspan) {
                        $row[] = null;
                    } else {
                        $row[] = ['data' => $teachercomments, 'rowspan' => $rowspan - 1];
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
                        $row[] = ['data' => $groupname, 'rowspan' => $rowspan - 1];
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
                    $row[] = ['data' => $name, 'rowspan' => 0, 'name' => 'participant'];
                break;
                case 'email':
                    $row[] = ['data' => $entry->email, 'rowspan' => 0, 'name' => 'email'];
                break;
                case 'idnumber':
                    $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                    $row[] = ['data' => $idnumber, 'rowspan' => 0];
                break;
                case 'attended':
                    $attended = $entry->attended ?? -1;
                    switch ($attended) {
                        case -1:
                            $content = '';
                            break;
                        case 0:
                            $content = get_string('no');
                            break;
                        case 1:
                            $content = get_string('yes');
                    }
                    $row[] = ['data' => $content, 'rowspan' => 0];
                break;
                case 'grade':
                    $grade = isset($entry->grade) && $entry->grade >= 0 ? sprintf("%01.2f", $entry->grade) : '';
                    $row[] = ['data' => $grade, 'rowspan' => 0];
                break;
                case 'feedback':
                    $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                    $row[] = ['data' => $feedback, 'rowspan' => 0];
                break;
                case 'comments':
                    $comments = isset($entry->comments) && $entry->comments !== '' ? $entry->comments : '';
                    $row[] = ['data' => $comments, 'rowspan' => 0];
                    break;
            }
        }

        $mpdftable->addRow($row);
        $rowspan--;
    }

    organizer_format_and_print($mpdftable, $filename);
}
