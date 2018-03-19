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
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

class organizer_print_slotdetail_form extends moodleform
{

    private $_selcols;

    protected function definition() {
        $this->_add_slot_info();
        $this->_add_column_select();
    }

    private function _add_slot_info() {
        $mform = &$this->_form;
        $data = &$this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);

        // TODO: might cause crashes!
        $mform->addElement('hidden', 'action', 'print');
        $mform->setType('action', PARAM_ACTION);

        if (isset($data['slot'])) {
            $mform->addElement('hidden', 'slot', $data['slot']);
            $mform->setType("slot", PARAM_INT);
        } else {
            print_error('This should not happen!');
        }
    }

    private function _add_column_select() {
        global $DB, $CFG;

        $mform = $this->_form;
        $data = &$this->_customdata;

        $params = array('slotid' => $data['slot']);
        $organizer = $DB->get_records_sql(
                "SELECT o.* 
                 FROM {organizer} o 
                 INNER JOIN {organizer_slots} s ON o.id = s.organizerid 
                 WHERE s.id = :slotid", $params);

        $organizer = reset($organizer);
        $identityfields = explode(',', $CFG->showuseridentity);
        $selcols = array();
        for ($i = 0; $i <= ORGANIZER_PRINTSLOTUSERFIELDS; $i++) {
            if ($organizer->{'singleslotprintfield'.$i}) {
                $selcols[] = $organizer->{'singleslotprintfield'.$i};
            }
        }
        $this->_selcols = $selcols;

        $mform->addElement('header', 'export_settings_header', get_string('exportsettings', 'organizer'));

        $exportformats = array(
                'pdf' => get_string('format_pdf', 'organizer'),
                'xlsx' => get_string('format_xlsx', 'organizer')/*,
                'xls' => get_string('format_xls', 'organizer'),
                'ods' => get_string('format_ods', 'organizer'),
                'csv_tab' => get_string('format_csv_tab', 'organizer'),
                'csv_comma' => get_string('format_csv_comma', 'organizer')*/);
        $mform->addElement('select', 'format', get_string('format', 'organizer'), $exportformats);

        $mform->addElement('static', 'pdf_settings', get_string('pdfsettings', 'organizer'));

        $entriesperpage = get_user_preferences('organizer_printperpage', 20);
        $printperpageoptimal = get_user_preferences('organizer_printperpage_optimal', 0);
        $textsize = get_user_preferences('organizer_textsize', 10);
        $pageorientation = get_user_preferences('organizer_pageorientation', 'P');
        $headerfooter = get_user_preferences('organizer_headerfooter', 1);

        // Submissions per page.
        $pppgroup = array();
        $pppgroup[] = &$mform->createElement('text', 'entriesperpage', get_string('numentries', 'organizer'), array('size' => '2'));
        $pppgroup[] = &$mform->createElement(
            'advcheckbox', 'printperpage_optimal',
            '', get_string('stroptimal', 'organizer'), array("group" => 1)
        );

        $mform->addGroup($pppgroup, 'printperpagegrp', get_string('numentries', 'organizer'), array(' '), false);
        $mform->setType('entriesperpage', PARAM_INT);

        $mform->setDefault('entriesperpage', $entriesperpage);
        $mform->setDefault('printperpage_optimal', $printperpageoptimal);

        $mform->addHelpButton('printperpagegrp', 'numentries', 'organizer');

        $mform->disabledif ('entriesperpage', 'printperpage_optimal', 'checked');
        $mform->disabledif ('printperpagegrp', 'format', 'neq', 'pdf');

        $mform->addElement(
            'select', 'textsize', get_string('textsize', 'organizer'),
            array('8' => get_string('font_small', 'organizer'), '10' => get_string('font_medium', 'organizer'),
            '12' => get_string('font_large', 'organizer'))
        );

        $mform->setDefault('textsize', $textsize);
        $mform->disabledif ('textsize', 'format', 'neq', 'pdf');

        $mform->addElement(
            'select', 'pageorientation', get_string('pageorientation', 'organizer'),
            array('P' => get_string('orientationportrait', 'organizer'),
            'L' => get_string('orientationlandscape', 'organizer'))
        );

        $mform->setDefault('pageorientation', $pageorientation);
        $mform->disabledif ('pageorientation', 'format', 'neq', 'pdf');

        $mform->addElement(
            'advcheckbox', 'headerfooter', get_string('headerfooter', 'organizer'), null, null,
            array(0, 1)
        );
        $mform->setType('headerfooter', PARAM_BOOL);
        $mform->setDefault('headerfooter', $headerfooter);
        $mform->addHelpButton('headerfooter', 'headerfooter', 'organizer');
        $mform->disabledif ('headerfooter', 'format', 'neq', 'pdf');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'downloadfile', get_string('downloadfile', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('print_return', 'organizer'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        foreach ($selcols as $key => $selcol) {
            $mform->addElement('hidden', "cols[$key]", $selcol, array('id' => "col_{$selcol}"));
            $mform->setType("cols[$key]", PARAM_ALPHANUMEXT);
        }
    }

    public function display() {
        global $OUTPUT, $CFG;

        // Finalize the form definition if not yet done.
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        $this->_form->getValidationScript();
        $output = $this->_form->toHtml();

        $helpicon = new help_icon('datapreviewtitle', 'organizer');
        $output .= html_writer::tag(
            'div',
            get_string('datapreviewtitle', 'organizer') . $OUTPUT->render($helpicon),
            array('class' => 'datapreviewtitle')
        );

        $output .= '<div class="forced_scroll">';

        $printcols = $this->_selcols;
        $identityfields = explode(',', $CFG->showuseridentity);

        $notsortable = array();
        $output .= '<div style="float: left">';
        $output .= $this->_create_preview_table($printcols, $notsortable);
        $output .= '</div><div style="width: 1em; float: left;"> </div></div>';

        print $output;
    }

    private function _create_preview_table($columns) {
        global $cm, $CFG, $DB;


        $table = new html_table();
        $table->id = 'print_preview';
        $table->attributes['class'] = 'boxaligncenter';

        If (!$columns) {
            return "<strong> ==> " . get_string('nosingleslotprintfields', 'organizer') . "</strong>";
        }

        $header = array();
        $data = &$this->_customdata;

        $url  = $CFG->wwwroot . '/mod/organizer/slots_printdetail.php?';
        $url .= 'id=' . $cm->id;
        $url .= '&sesskey=' . sesskey();
        $url .= '&action=print';
        $url .= '&slot=' . $data['slot'];

        $userinfofields = array();
        foreach ($columns as $column) {

            switch($column) {
                case 'lastname':
                    $cell = new html_table_cell(get_string('lastname'));
                    break;
                case 'firstname':
                    $cell = new html_table_cell(get_string('firstname'));
                    break;
                case 'email':
                    $cell = new html_table_cell(get_string('email'));
                    break;
                case 'idnumber':
                    $cell = new html_table_cell(get_string('idnumber'));
                    break;
                case 'attended':
                    $cell = new html_table_cell(get_string('attended', 'organizer'));
                    break;
                case 'grade':
                    $cell = new html_table_cell(get_string('grade'));
                    break;
                case 'feedback':
                    $cell = new html_table_cell(get_string('feedback'));
                    break;
                case 'signature':
                    $cell = new html_table_cell(get_string('signature', 'organizer'));
                    break;
                default:
                    if (is_numeric($column)){ // custom user field
                        if ($userinfofield = $DB->get_record_select('user_info_field', 'id = :id', array('id' => $column))) {
                            $userinfofields[$userinfofield->id] = $userinfofield->datatype;
                            $name = $userinfofield->name ? $userinfofield->name : $userinfofield->shortname;
                        } else {
                            $name = "???";
                        }
                        $cell = new html_table_cell($name);
                    }

            }
            $cell->header = true;
            $header[] = $cell;
        }

        $table->head = $header;

        $entries = organizer_fetch_printdetail_entries($data['slot']);

        $rows = array();
        $numcols = 0;
        $evenodd = 0;
        foreach ($entries as $entry) {
            $row = $rows[] = new html_table_row();
            foreach ($columns as $column) {

                switch ($column) {
                    case 'lastname':
                        $content = "<span name='{$column}_cell'>" . $entry->lastname . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'firstname':
                        $content = "<span name='{$column}_cell'>" . $entry->firstname . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                        break;
                    case 'email':
                        $content = "<span name='{$column}_cell'>" . $entry->email . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'idnumber':
                        $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                        $content = "<span name='{$column}_cell'>" . $idnumber . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'attended':
                        $attended = isset($entry->attended) ? ($entry->attended == 1 ? get_string('yes') : get_string('no')) : '';
                        $content = "<span name='{$column}_cell'>" . $attended . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'grade':
                        $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                        $content = "<span name='{$column}_cell'>" . $grade . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'feedback':
                        $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                        $content = "<span name='{$column}_cell'>" . $feedback . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'groupname':
                        $content = "<span name='{$column}_cell'>" . $entry->groupname . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    case 'signature':
                        $content = "<span name='{$column}_cell'>" . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    default:
                        if (is_numeric($column)) {
                            if ($userinfodata = $DB->get_field(
                                    'user_info_data', 'data', array('fieldid' => $column, 'userid' => $entry->id)))
                            {
                                if (isset($userinfofields[$column]) && ($userinfofields[$column] == 'text' || $userinfofields[$column] == 'textarea')) {
                                    $cell = new html_table_cell($userinfodata);
                                } else {
                                    $cell = new html_table_cell("Unsupported column type: $column");
                                }
                                $row->cells[] = $cell;
                            } else {
                                $cell = new html_table_cell("");
                            }
                        }
                }
            }
            $numcols++;
            $row->attributes['class'] = " r{$evenodd}";
            $evenodd = $evenodd ? 0 : 1;
        }

        $table->data = $rows;

        return organizer_render_table_with_footer($table, false, true);
    }

}