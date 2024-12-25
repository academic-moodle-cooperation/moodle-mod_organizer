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
 * view_action_form_print.php
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

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

/**
 * Class organizer_print_slots_form
 *
 * This class represents a custom print slots form for the Moodle Organizer module.
 * It extends the Moodle core `moodleform` class to manage the definition and display
 * of the form, which includes slot information and customizable column selection
 * for printing or exporting.
 *
 * @package   mod_organizer
 * @author    Andreas Hruska
 * @author    Katarzyna Potocka
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_print_slots_form extends moodleform {

    /**
     * Selected columns for the export or printing.
     *
     * This variable stores an array of column identifiers
     * that are selected for display and used in export or print functionalities.
     *
     * @var string[]
     */
    private $_selcols;

    /**
     * Class constructor to initialize the organizer_print_slots_form.
     *
     * This constructor is inherited from the `moodleform` class and allows
     * initialization of the form with any required custom data.
     *
     * @param mixed $caller The caller object that initializes this form.
     * @param array $customdata An array of data containing additional parameters required by the form.
     * @throws coding_exception
     */
    protected function definition() {
        $this->add_slot_info();
        $this->add_column_select();
    }

    /**
     * Adds hidden elements for slot information to the form.
     *
     * This method populates the form with hidden fields required for proper handling
     * of slot-related data based on the provided custom data.
     *
     * @throws coding_exception If the required slots data is missing in the custom data.
     */
    private function add_slot_info() {
        $mform = &$this->_form;
        $data = &$this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);

        $mform->addElement('hidden', 'action', 'print');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        if (isset($data['slots'])) {
            foreach ($data['slots'] as $key => $slotid) {
                $mform->addElement('hidden', "slots[$key]", $slotid);
                $mform->setType("slots[$key]", PARAM_INT);
            }
        } else {
            throw new coding_exception('This should not happen!');
        }
    }

    /**
     * Adds column selection and export format fields to the form.
     *
     * This method populates the form with elements used to customize the export functionality.
     * It adds fields for selecting the columns to be included in the export, defines various
     * output formats (e.g. PDF, XLSX, CSV), and specifies additional settings like group visibility
     * or identity field configurations. It also prepares a button group for submitting or canceling the form.
     *
     * The generated form elements account for whether the organizer is a group organizer or not,
     * and dynamically adapt the column list based on identity fields enabled in the site configuration.
     *
     * @throws coding_exception If there's an error building form elements.
     */
    private function add_column_select() {
        global $DB, $CFG;

        $mform = $this->_form;
        $data = &$this->_customdata;

        $params = ['slotid' => reset($data['slots'])];
        $isgrouporganizer = $DB->get_field_sql(
            "SELECT o.isgrouporganizer
                FROM {organizer} o
                INNER JOIN {organizer_slots} s ON o.id = s.organizerid
                WHERE s.id = :slotid", $params
        );

        $identityfields = explode(',', $CFG->showuseridentity);
        $selcols = ['datetime', 'location', 'teacher', 'teachercomments', 'participant'];
        if (in_array('idnumber', $identityfields)) {
            $selcols[] = 'idnumber';
        }
        if (in_array('email', $identityfields)) {
            $selcols[] = 'email';
        }
        $selcols[] = 'comments';
        $selcols[] = 'attended';
        $selcols[] = 'grade';
        $selcols[] = 'feedback';

        if ($isgrouporganizer) {
            array_splice($selcols, 3, 0, 'groupname');
        }

        $this->_selcols = $selcols;

        $mform->addElement('header', 'export_settings_header', get_string('exportsettings', 'organizer'));

        $exportformats = [
                'pdf' => get_string('format_pdf', 'organizer'),
                'xlsx' => get_string('format_xlsx', 'organizer'),
                'xls' => get_string('format_xls', 'organizer'),
                'ods' => get_string('format_ods', 'organizer'),
                'csv_tab' => get_string('format_csv_tab', 'organizer'),
                'csv_comma' => get_string('format_csv_comma', 'organizer')];

        $mform = organizer_build_printsettingsform($mform, $exportformats);
        $mform->disabledif ('headerfooter', 'format', 'neq', 'pdf');

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('submit', 'downloadfile', get_string('downloadfile', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('print_return', 'organizer'));

        $mform->addGroup($buttonarray, 'buttonar', '', [' '], false);

        foreach ($selcols as $key => $selcol) {
            $mform->addElement('hidden', "cols[$key]", $selcol, ['id' => "col_{$selcol}"]);
            $mform->setType("cols[$key]", PARAM_ALPHANUMEXT);
        }
    }

    /**
     * Displays the form and preview table for the selected columns.
     *
     * This method ensures the form is fully defined and finalized before rendering.
     * It generates and outputs the HTML for the form, processes the selected columns
     * for preview, and handles the visibility of identity fields based on site
     * configuration.
     *
     * The method dynamically builds a column preview table, which can be modified
     * based on user preferences, such as toggling specific columns' visibility
     * within the preview.
     *
     * @throws coding_exception If there is an error during form or preview table initialization.
     *
     * @return void
     */
    public function display() {
        global $CFG;

        // Finalize the form definition if not yet done.
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        $this->_form->getValidationScript();
        $output = $this->_form->toHtml();

        $output = organizer_printtablepreview_icons($output);

        $printcols = $this->_selcols;
        $identityfields = explode(',', $CFG->showuseridentity);
        if (in_array('idnumber', $identityfields) == false) {
            if ($key = array_search('idnumber', $printcols)) {
                unset($printcols[$key]);
            }
        }
        if (in_array('email', $identityfields) == false) {
            if ($key = array_search('email', $printcols)) {
                unset($printcols[$key]);
            }
        }

        $output .= '<div class="forced_scroll">';
        $output .= '<div style="float: left">';
        $output .= $this->create_preview_table($printcols);
        $output .= '</div><div style="width: 1em; float: left;"> </div></div>';

        echo $output;
    }

    /**
     * Creates a preview table for selected columns.
     *
     * This method dynamically generates a table to preview column content, allowing users
     * to toggle column visibility, sort columns, and customize the table's appearance
     * based on their preferences.
     *
     * The function integrates sorting and user preferences for hiding/displaying specific
     * columns. It also ensures compatibility with AJAX updates by initializing
     * relevant parameters and JS scripts.
     *
     * @param array $columns An array of selected columns to be displayed.
     *
     * @return html_table The table object containing the preview of column data with customizable options.
     * @throws dml_exception If there is an issue fetching user preferences.
     */
    private function create_preview_table($columns) {
        global $PAGE, $OUTPUT, $cm, $CFG, $USER;

        $USER->ajax_updatable_user_prefs['mod_organizer_noprintfields'] = PARAM_TEXT;

        $param = new stdClass();
        $param->iconminus = $OUTPUT->image_icon('t/switch_minus', get_string('hide'), 'moodle', [
            'id' => 'xxx', 'col' => 'yyy', 'style' => 'cursor:pointer;margin-left:3px;']);
        $param->iconplus = $OUTPUT->image_icon('t/switch_plus', get_string('show'), 'moodle', [
            'id' => 'xxx', 'col' => 'yyy', 'style' => 'cursor:pointer;margin-left:3px;']);
        $PAGE->requires->js_call_amd('mod_organizer/printform', 'init', [$param]);

        $isgrouporganizer = organizer_is_group_mode();

        $table = new html_table();
        $table->id = 'print_preview';
        $table->attributes['class'] = 'boxaligncenter';

        $tsort = isset($_SESSION['organizer_tsort']) ? $_SESSION['organizer_tsort'] : "";
        if ($tsort != "") {
            $order = "ASC";
            if (substr($tsort, strlen($tsort) - strlen("DESC")) == "DESC") {
                $tsort = substr($tsort, 0, strlen($tsort) - strlen("DESC"));
                $order = "DESC";
            }
        }

        $iconup = $OUTPUT->image_icon('t/up', get_string('up'), 'moodle',
            ['style' => 'cursor:pointer;margin-left:3px;']);
        $icondown = $OUTPUT->image_icon('t/down', get_string('down'), 'moodle',
            ['style' => 'cursor:pointer;margin-left:3px;']);

        $header = [];
        $noprintfieldsarray = [];
        if ($noprintfields = get_user_preferences("mod_organizer_noprintfields")) {
            $noprintfieldsarray = explode(",", $noprintfields);
        }

        $data = &$this->_customdata;

        $urlinit  = $CFG->wwwroot . '/mod/organizer/slots_print.php?';
        $urlinit .= 'id=' . $cm->id;
        $urlinit .= '&sesskey=' . sesskey();
        $urlinit .= '&action=print';

        foreach ($columns as $column) {

            $url = $urlinit;

            $icon = "";
            if ($column == $tsort) {
                if ($order == "ASC") {
                    $url .= '&tsort=' . $column . "DESC";
                    $icon = $iconup;
                } else {
                    $url .= '&tsort=' . $column;
                    $icon = $icondown;
                }
            } else {
                $url .= '&tsort=' . $column;
            }

            $slotids = implode(',', array_values($data['slots']));
            $url .= '&slots=' . $slotids;

            $linkarray = ['name' => $column . '_cell'];

            if ($noprintfieldsarray) {
                if (in_array($column, $noprintfieldsarray)) {
                    $linkarray['noprint'] = "1";
                }
            }

            $cell = new html_table_cell(html_writer::link($url, get_string("th_{$column}", 'organizer') . $icon, $linkarray));
            $cell->header = true;
            $cell->attributes['class'] = 'text-nowrap p-2';
            $header[] = $cell;
        }
        $table->head = $header;

        switch($tsort) {
            case "datetime":
                $sort = "starttime";
            break;
            case "location":
                $sort = "s.location";
            break;
            case "teacher":
                $sort = null;
                $order = null;
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
                $order = null;
        }

        if (!isset($order)) {
            $order = "";
        } else if ($order != "DESC" && $order != "ASC") {
            $order = "";
        }

        $entries = organizer_fetch_table_entries($data['slots'], $sort . ' ' . $order);

        $rows = [];
        $rowspan = 0;
        foreach ($entries as $entry) {
            $row = $rows[] = new html_table_row();
            foreach ($columns as $column) {
                // Rows with rowspan = slot rows.
                if ($rowspan == 0) {
                    switch ($column) {
                        case 'datetime':
                            $datetime = userdate($entry->starttime, get_string('fulldatetimetemplate', 'organizer'))
                            . ' - '
                            . userdate(
                                $entry->starttime + $entry->duration,
                                get_string('timetemplate', 'organizer')
                                );
                                $content = "<span name='{$column}_cell'>" . $datetime . '</span>';
                                $cell = new html_table_cell($content);
                                $cell->rowspan = $entry->rowspan;
                                $cell->attributes['class'] = 'align-middle p-2';
                                $row->cells[] = $cell;
                        break;
                        case 'location':
                            $location = $entry->location;
                            $content = "<span name='{$column}_cell'>" . $location . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->attributes['class'] = 'align-middle p-2';
                            $row->cells[] = $cell;
                        break;
                        case 'teacher':
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
                            $content = "<span name='{$column}_cell'>" . $name . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->attributes['class'] = 'align-middle p-2';
                            $row->cells[] = $cell;
                        break;
                        case 'teachercomments':
                            $content = "<span name='{$column}_cell'>" . organizer_filter_text($entry->teachercomments) . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->attributes['class'] = 'align-middle p-2';
                            $row->cells[] = $cell;
                            break;
                        case 'groupname':
                            $groupname = $entry->groupname;
                            $content = "<span name='{$column}_cell'>" . $groupname . '</span>';
                            if ($isgrouporganizer) {
                                $content .= organizer_get_teacherapplicant_output($entry->teacherapplicantid, null);
                            }
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->attributes['class'] = 'align-middle p-2';
                            $row->cells[] = $cell;
                        break;
                        default:
                        break;
                    }
                }

                // Columns without rowspan = participant's rows.
                switch ($column) {
                    case 'participant':
                        $a = new stdClass();
                        $a->firstname = $entry->firstname;
                        $a->lastname = $entry->lastname;
                        $name = get_string('fullname_template', 'organizer', $a);
                        $content = html_writer::start_span('', ['name' => $column.'_cell']);
                        $content .= $name;
                        if (!$isgrouporganizer) {
                            $content .= organizer_get_teacherapplicant_output($entry->teacherapplicantid, null);
                        }
                        $content .= html_writer::end_span();
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'email':
                        $content = "<span name='{$column}_cell'>" . $entry->email . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'idnumber':
                        $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                        $content = "<span name='{$column}_cell'>" . $idnumber . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'attended':
                        $attended = isset($entry->attended) ? ($entry->attended == 1 ? 'Yes' : 'No') : '';
                        $content = "<span name='{$column}_cell'>" . $attended . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'grade':
                        $grade = isset($entry->grade) && $entry->grade >= 0 ? sprintf("%01.2f", $entry->grade) : '';
                        $content = "<span name='{$column}_cell'>" . $grade . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'feedback':
                        $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                        $content = "<span name='{$column}_cell'>" . $feedback . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                    break;
                    case 'comments':
                        $comments = isset($entry->comments) && $entry->comments !== '' ? $entry->comments : '';
                        $content = "<span name='{$column}_cell'>" . organizer_filter_text($comments) . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->attributes['class'] = 'align-middle p-2';
                        $row->cells[] = $cell;
                        break;
                    case 'datetime':
                    case 'location':
                    case 'teacher':
                    case 'groupname':
                    case 'teachercomments':
                    break;
                    default:
                        throw new coding_exception("Unsupported column type: $column");
                }
            } // Each column.
            $rowspan = ($rowspan + 1) % $entry->rowspan;
        }

        $table->data = $rows;

        return organizer_render_table_with_footer($table, false, true);
    }
}
