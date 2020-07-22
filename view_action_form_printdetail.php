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

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

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
class organizer_print_slotdetail_form extends moodleform
{
    /**
     * @var string select of the colums
     */
    private $_selcols;
    /**
     *
     * {@inheritDoc}
     * @see moodleform::definition()
     */
    protected function definition() {
        $this->_add_slot_info();
        $this->_add_column_select();
    }
    /**
     * adds the info of the slot to the form
     */
    private function _add_slot_info() {
        $mform = &$this->_form;
        $data = &$this->_customdata;

        $mform->addElement('hidden', 'id', $data['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'mode', $data['mode']);
        $mform->setType('mode', PARAM_INT);

        // TODO: might cause crashes!
        $mform->addElement('hidden', 'action', 'print');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        if (isset($data['slot'])) {
            $mform->addElement('hidden', 'slot', $data['slot']);
            $mform->setType("slot", PARAM_INT);
        } else {
            print_error('This should not happen!');
        }
    }
    /**
     * adds information which colums to select for printing
     */
    private function _add_column_select() {
        global $DB, $CFG;

        $mform = $this->_form;
        $data = &$this->_customdata;
        $organizerconfig = get_config('organizer');

        $params = array('slotid' => $data['slot']);
        $organizer = $DB->get_records_sql(
                'SELECT o.*
                 FROM {organizer} o
                 INNER JOIN {organizer_slots} s ON o.id = s.organizerid
                 WHERE s.id = :slotid', $params);

        $organizer = reset($organizer);

        if (isset($organizerconfig->enableprintslotuserfields) && $organizerconfig->enableprintslotuserfields) {
            $selcols = array();
            for ($i = 0; $i <= ORGANIZER_PRINTSLOTUSERFIELDS; $i++) {
                if ($organizer->{'singleslotprintfield'.$i}) {
                    $selcols[] = $organizer->{'singleslotprintfield'.$i};
                }
            }
        } else {
            for ($i = 0; $i <= ORGANIZER_PRINTSLOTUSERFIELDS; $i++) {
                if ($organizerconfig->{'singleslotprintfield'.$i}) {
                    $selcols[] = $organizerconfig->{'singleslotprintfield'.$i};
                }
            }
        }

        $this->_selcols = $selcols;

        $mform->addElement('header', 'export_settings_header', get_string('exportsettings', 'organizer'));

        $exportformats = array(
                'pdf' => get_string('format_pdf', 'organizer'),
                'xlsx' => get_string('format_xlsx', 'organizer'));

        $mform = organizer_build_printsettingsform($mform, $exportformats);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'downloadfile', get_string('downloadfile', 'organizer'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('print_return', 'organizer'));

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

        foreach ($selcols as $key => $selcol) {
            $mform->addElement('hidden', "cols[$key]", $selcol, array('id' => "col_{$selcol}"));
            $mform->setType("cols[$key]", PARAM_ALPHANUMEXT);
        }
    }
    /**
     *
     * {@inheritDoc}
     * @see moodleform::display()
     */
    public function display() {

        // Finalize the form definition if not yet done.
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        $this->_form->getValidationScript();
        $output = $this->_form->toHtml();

        $output = organizer_printtablepreview_icons($output);

        $output .= '<div class="forced_scroll">';
        $output .= '<div style="float: left">';
        $output .= $this->_create_preview_table($this->_selcols);
        $output .= '</div><div style="width: 1em; float: left;"> </div></div>';

        print $output;
    }

    /**
     *
     * @param array $columns the columns of the table
     * @return string the html of the table
     */
    private function _create_preview_table($columns) {
        global $OUTPUT, $CFG, $DB;

        $table = new html_table();
        $table->id = 'print_preview';
        $table->attributes['class'] = 'flexible generaltable generalbox';
        $table->attributes['width'] = '100%';

        if (!$columns) {
            return "<strong> ==> " . get_string('nosingleslotprintfields', 'organizer') . "</strong>";
        }

        $header = array();
        $data = &$this->_customdata;

        $userinfofields = array();
        foreach ($columns as $column) {

            switch($column) {
                case 'lastname':
                    $cell = new html_table_cell(organizer_filter_text(get_string('lastname')));
                    break;
                case 'firstname':
                    $cell = new html_table_cell(organizer_filter_text(get_string('firstname')));
                    break;
                case 'email':
                    $cell = new html_table_cell(organizer_filter_text(get_string('email')));
                    break;
                case 'idnumber':
                    $cell = new html_table_cell(organizer_filter_text(get_string('idnumber')));
                    break;
                case 'attended':
                    $cell = new html_table_cell(organizer_filter_text(get_string('attended', 'organizer')));
                    break;
                case 'grade':
                    $cell = new html_table_cell(organizer_filter_text(get_string('grade')));
                    break;
                case 'feedback':
                    $cell = new html_table_cell(organizer_filter_text(get_string('feedback')));
                    break;
                case 'signature':
                    $cell = new html_table_cell(organizer_filter_text(get_string('signature', 'organizer')));
                    break;
                default:
                    if (is_numeric($column)) { // Custom user field.
                        if ($userinfofield = $DB->get_record_select('user_info_field', 'id = :id', array('id' => $column))) {
                            $userinfofields[$userinfofield->id] = $userinfofield->datatype;
                            $name = $userinfofield->name ? organizer_filter_text($userinfofield->name) :
                                organizer_filter_text($userinfofield->shortname);
                        } else {
                            $name = "???";
                        }
                        $cell = new html_table_cell($name);
                    } else {  // Field of moodle user table.
                        switch ($column) {
                            case 'fullnameuser':
                                $name = organizer_filter_text(get_string('fullnameuser', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'icq':
                                $name = organizer_filter_text(get_string('icqnumber', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'skype':
                                $name = organizer_filter_text(get_string('skypeid', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'yahoo':
                                $name = organizer_filter_text(get_string('yahooid', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'aim':
                                $name = organizer_filter_text(get_string('aimid', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'msn':
                                $name = organizer_filter_text(get_string('msnid', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'phone1':
                                $name = organizer_filter_text(get_string('phone1', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'phone2':
                                $name = organizer_filter_text(get_string('phone2', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'institution':
                                $name = organizer_filter_text(get_string('institution', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'department':
                                $name = organizer_filter_text(get_string('department', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'address':
                                $name = organizer_filter_text(get_string('address', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'city':
                                $name = organizer_filter_text(get_string('city', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'country':
                                $name = organizer_filter_text(get_string('country', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'lang':
                                $name = organizer_filter_text(get_string('language', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'timezone':
                                $name = organizer_filter_text(get_string('timezone', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                            case 'description':
                                $name = organizer_filter_text(get_string('userdescription', 'moodle'));
                                $cell = new html_table_cell($name);
                                break;
                        }
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
                    case 'fullnameuser':
                        $content = "<span name='{$column}_cell'>" . fullname($entry->id) . '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                        break;
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
                        $grade = isset($entry->grade) && $entry->grade >= 0 ? sprintf("%01.2f", $entry->grade) : '';
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
                        $content = "<span name='{$column}_cell'>" . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' .
                            '</span>';
                        $cell = new html_table_cell($content);
                        $row->cells[] = $cell;
                    break;
                    default:
                        if (is_numeric($column)) {
                            if ($userinfodata = $DB->get_field(
                                    'user_info_data', 'data', array('fieldid' => $column, 'userid' => $entry->id))) {
                                if (isset($userinfofields[$column]) && ($userinfofields[$column] == 'text' ||
                                        $userinfofields[$column] == 'textarea')) {
                                    $cell = new html_table_cell($userinfodata);
                                } else {
                                    $cell = new html_table_cell("Unsupported column type: $column");
                                }
                                $row->cells[] = $cell;
                            } else {
                                $row->cells[] = new html_table_cell("");
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
                                    $content = "<span name='{$column}_cell'>" .  $entry->{$column} . '</span>';
                                    $cell = new html_table_cell($content);
                                    $row->cells[] = $cell;
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