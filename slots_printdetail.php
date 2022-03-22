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
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
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


require_capability('mod/organizer:printslots', $context);

if (!$slot) {
    $redirecturl->param('messages[]', 'message_warning_no_visible_slots_selected');
    redirect($redirecturl);
} else {
    $_SESSION['organizer_slot'] = $slot;
}

$mform = new organizer_print_slotdetail_form(null, array('id' => $cm->id, 'mode' => $mode, 'slot' => $slot), 'post', '_blank');

if ($data = $mform->get_data()) {
    // Create pdf.

    if (!$slot) {
        $slot = $_SESSION['organizer_slot'];
    }

    if (!$slot) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    $ppp = organizer_print_setuserprefs_and_triggerevent($data, $cm, $context);

    if (!isset($data->cols)) {
        redirect($redirecturl, get_string('nosingleslotprintfields', 'organizer'), null, \core\output\notification::NOTIFY_ERROR);
    } else {
        organizer_display_printable_slotdetail_table($data->cols, $data->slot, $ppp, $data->textsize,
            $data->pageorientation, $data->headerfooter
        );
        redirect($redirecturl);
    }

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

    $organizerconfig = get_config('organizer');

    organizer_display_form($mform, get_string('title_print', 'organizer'));
}

die;

function organizer_display_printable_slotdetail_table($columns, $slotid, $entriesperpage = false, $textsize = '10',
        $orientation = 'L', $headerfooter = true) {
    global $DB;

    list(, $course, $organizer, ) = organizer_get_course_module_data();

    $coursename = $course->idnumber ? $course->idnumber . " " . $course->fullname : $course->fullname;
    $coursename = organizer_filter_text($coursename);
    $courseshortname = organizer_filter_text($course->shortname);
    $organizername = organizer_filter_text($organizer->name);

    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    $trainers = organizer_get_slot_trainers($slotid, true);
    $filename = $coursename . "-" . $organizername;
    $columnwitdh = array();
    $titles = array();
    $columnformats = array();

    foreach ($columns as $column) {

        switch ($column) {
            case 'lastname':
                $titles[] = organizer_filter_text(get_string('lastname'));
                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'firstname':
                $titles[] = organizer_filter_text(get_string('firstname'));
                $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'email':
                $titles[] = organizer_filter_text(get_string('email'));
                $columnwitdh[] = array('value' => 64, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'idnumber':
                $titles[] = organizer_filter_text(get_string('idnumber'));
                $columnwitdh[] = array('value' => 24, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'attended':
                $titles[] = organizer_filter_text(get_string('attended', 'organizer'));
                $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'grade':
                $titles[] = organizer_filter_text(get_string('grade'));
                $columnwitdh[] = array('value' => 12, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'feedback':
                $titles[] = organizer_filter_text(get_string('feedback'));
                $columnwitdh[] = array('value' => 32, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            case 'signature':
                $titles[] = organizer_filter_text(get_string('signature', 'organizer'));
                $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                $columnformats[] = array('fill' => 0, 'align' => 'C');
            break;
            default:
                if (is_numeric($column)) { // Custom user field.
                    $userinfofield = $DB->get_record_select('user_info_field', 'id = :id', array('id' => $column));
                    $userinfofields[$userinfofield->id] = $userinfofield->datatype;
                    $name = $userinfofield->name ? $userinfofield->name : $userinfofield->shortname;
                    $titles[] = organizer_filter_text($name);
                    $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                    $columnformats[] = array('fill' => 0, 'align' => 'C');
                } else {  // Field of moodle user table.
                    switch ($column) {
                        case 'fullnameuser':
                            $titles[] = organizer_filter_text(get_string('fullnameuser', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'icq':
                            $titles[] = organizer_filter_text(get_string('icqnumber', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'skype':
                            $titles[] = organizer_filter_text(get_string('skypeid', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'yahoo':
                            $titles[] = organizer_filter_text(get_string('yahooid', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'aim':
                            $titles[] = organizer_filter_text(get_string('aimid', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'msn':
                            $titles[] = organizer_filter_text(get_string('msnid', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'phone1':
                            $titles[] = organizer_filter_text(get_string('phone1', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'phone2':
                            $titles[] = organizer_filter_text(get_string('phone2', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'institution':
                            $titles[] = organizer_filter_text(get_string('institution', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'department':
                            $titles[] = organizer_filter_text(get_string('department', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'address':
                            $titles[] = organizer_filter_text(get_string('address', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'city':
                            $titles[] = organizer_filter_text(get_string('city', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'country':
                            $titles[] = organizer_filter_text(get_string('country', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'lang':
                            $titles[] = organizer_filter_text(get_string('language', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'timezone':
                            $titles[] = organizer_filter_text(get_string('timezone', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                        case 'description':
                            $titles[] = organizer_filter_text(get_string('userdescription', 'moodle'));
                            $columnwitdh[] = array('value' => 48, 'mode' => 'Relativ');
                            $columnformats[] = array('fill' => 0, 'align' => 'C');
                            break;
                    }
                }
        }
    }

    $slotdatetime = organizer_date_time($slot);
    $slotdatetime = str_replace("<br />", " ", $slotdatetime);
    $trainerstr = "";
    $conn = "";
    foreach ($trainers as $trainer) {
        $trainerstr .= $conn . $trainer->firstname . " " . $trainer->lastname;
        $conn = ", ";
    }

    $mpdftable = new \mod_organizer\MTablePDF($orientation, $columnwitdh);
    $mpdftable->SetTitle(
        get_string('modulename', 'organizer') . " " . $organizername . " - " . get_string('printout', 'organizer')
    );
    $mpdftable->setRowsperPage($entriesperpage);
    $mpdftable->ShowHeaderFooter($headerfooter);
    $mpdftable->SetFontSize($textsize);
    $mpdftable->setheadertext(
            get_string('course')                  . ':', $coursename,
            get_string('shortnamecourse')         . ':', $courseshortname,
            get_string('modulename', 'organizer') . ':', $organizername,
            get_string('slot', 'organizer')       . ':', $slotdatetime,
            get_string('trainer', 'organizer')    . ':', $trainerstr,
            get_string('created', 'organizer')    . ':', userdate(time())
    );
    $mpdftable->setTitles($titles);
    $mpdftable->setColumnFormat($columnformats);
    $entries = organizer_fetch_printdetail_entries($slotid);
    foreach ($entries as $entry) {
        $row = array();
        foreach ($columns as $column) {

            switch ($column) {
                case 'fullnameuser':
                    $content = fullusername($entry->id);
                    $row[] = array('data' => $content);
                    break;
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
                    $grade = isset($entry->grade) && $entry->grade >= 0 ? sprintf("%01.2f", $entry->grade) : '';
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
                    } else {  // Field of moodle user table.
                        switch ($column) {
                            case 'icq':
                            case 'skype':
                            case 'yahoo':
                            case 'aim':
                            case 'msn':
                            case 'phone1':
                            case 'phone2':
                            case 'institution':
                            case 'department':
                            case 'address':
                            case 'city':
                            case 'country':
                            case 'lang':
                            case 'timezone':
                            case 'description':
                                $content = (isset($entry->{$column}) && $entry->{$column} !== '') ? $entry->{$column} : '';
                                $row[] = array('data' => $content);
                        }
                    }
            }

        }
        $mpdftable->addRow($row);
    }

    organizer_format_and_print($mpdftable, $filename);
}