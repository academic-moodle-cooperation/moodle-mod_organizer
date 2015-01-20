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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * view_action_form_print.php
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

require_once("$CFG->libdir/formslib.php");
require_once(dirname(__FILE__) . '/custom_table_renderer.php');
require_once(dirname(__FILE__) . '/print.php');

class organizer_print_slots_form extends moodleform {

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

        if (isset($data['slots'])) {
            foreach ($data['slots'] as $key => $slotid) {
                $mform->addElement('hidden', "slots[$key]", $slotid);
                $mform->setType("slots[$key]", PARAM_INT);
            }
        } else {
            print_error('This should not happen!');
        }
    }

    private function _add_column_select() {
        global $DB, $CFG;

        $mform = $this->_form;
        $data = &$this->_customdata;

        $params = array('slotid' => reset($data['slots']));
        $isgrouporganizer = $DB->get_field_sql(
                "SELECT o.isgrouporganizer
                FROM {organizer} o
                INNER JOIN {organizer_slots} s ON o.id = s.organizerid
                WHERE s.id = :slotid", $params);

        $identityfields = explode(',', $CFG->showuseridentity);
        if (array_search('idnumber', $identityfields) !== false) {
            $selcols = array('datetime', 'location', 'teacher', 'participant', 'idnumber', 'attended', 'grade', 'feedback');
        } else {
            $selcols = array('datetime', 'location', 'teacher', 'participant', 'attended', 'grade', 'feedback');
        }
        $this->_selcols = $selcols;
        
        if ($isgrouporganizer) {
            array_splice($selcols, 3, 0, 'groupname');
        }
       

        
        $mform->addElement('header', 'export_settings_header', get_string('exportsettings', 'organizer'));        
        
        $exportformats = array(
        		'pdf'=>get_string('format_pdf','organizer'),
        		'xlsx'=>get_string('format_xlsx','organizer'),
        		'xls'=>get_string('format_xls','organizer'),
        		'ods'=>get_string('format_ods','organizer'),
        		'csv_tab'=>get_string('format_csv_tab','organizer'),
        		'csv_comma'=>get_string('format_csv_comma','organizer'));
        $mform->addElement('select','format',get_string('format','organizer'),$exportformats);
        
        $mform->addElement('static','pdf_settings',get_string('pdfsettings','organizer'));
        
    	$entriesperpage = get_user_preferences('organizer_printperpage', 20);
    	$printperpage_optimal = get_user_preferences('organizer_printperpage_optimal', 0);
    	$textsize = get_user_preferences('organizer_textsize',10);
    	$pageorientation = get_user_preferences('organizer_pageorientation', 'P');
    	$headerfooter = get_user_preferences('organizer_headerfooter', 1);
        
        
        
        // submissions per page        
        $pppgroup = array();
        $pppgroup[] = &$mform->createElement('text', 'entriesperpage', get_string('numentries', 'organizer'), array('size' => '2'));
        $pppgroup[] = &$mform->createElement('advcheckbox','printperpage_optimal', get_string('stroptimal','organizer'),get_string('stroptimal','organizer'), array("group"=> 1));        
        
        $mform->addGroup($pppgroup,'printperpagegrp',get_string('numentries', 'organizer'), array(' '), false);
        $mform->setType('entriesperpage', PARAM_INT);
        
        $mform->setDefault('entriesperpage', $entriesperpage);
        $mform->setDefault('printperpage_optimal', $printperpage_optimal);
        
        $mform->addHelpButton('printperpagegrp','numentries','organizer');
        
        $mform->disabledIf('entriesperpage','printperpage_optimal','checked');
        $mform->disabledIf('printperpagegrp', 'format','neq','pdf');

        $mform->addElement('select', 'textsize', get_string('textsize', 'organizer'),
                array('8' => get_string('font_small', 'organizer'), '10' => get_string('font_medium', 'organizer'),
                        '12' => get_string('font_large', 'organizer')));
        
        $mform->setDefault('textsize', $textsize);
        $mform->disabledIf('textsize', 'format','neq','pdf');

        $mform->addElement('select', 'pageorientation', get_string('pageorientation', 'organizer'),
                array('P' => get_string('orientationportrait', 'organizer'),
                        'L' => get_string('orientationlandscape', 'organizer')));
        
        $mform->setDefault('pageorientation', $pageorientation);
        $mform->disabledIf('pageorientation', 'format','neq','pdf');


        $mform->addElement('advcheckbox', 'headerfooter', get_string('headerfooter', 'organizer'), null, null,
                array(0, 1));
        $mform->setType('headerfooter', PARAM_BOOL);
        $mform->setDefault('headerfooter', $headerfooter);
        $mform->addHelpButton('headerfooter','headerfooter','organizer');
        $mform->disabledIf('headerfooter', 'format','neq','pdf');

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
    	global $OUTPUT;
    	
        //finalize the form definition if not yet done
        if (!$this->_definition_finalized) {
            $this->_definition_finalized = true;
            $this->definition_after_data();
        }
        $this->_form->getValidationScript();
        $output = $this->_form->toHtml();
        
        $help_icon = new help_icon('datapreviewtitle', 'organizer');
        $output .= html_writer::tag('div',
        		get_string('datapreviewtitle', 'organizer') . $OUTPUT->render($help_icon),
        		array('class'=>'datapreviewtitle'));
        
        
        $output .= '<div class="forced_scroll">';
        		
        $output .= '<div style="float: left">';
        $output .= $this->_create_preview_table($this->_selcols);
        $output .= '</div><div style="width: 1em; float: left;"> </div></div>';
        
        print $output;
    }

    private function _create_preview_table($columns) {
        global $PAGE, $OUTPUT, $cm;
        
        $jsmodule = array(
                'name' => 'mod_organizer',
                'fullpath' => '/mod/organizer/module.js',
                'requires' => array('node', 'node-event-delegate'),
        );
        
        $PAGE->requires->js_init_call('M.mod_organizer.init_organizer_print_slots_form', null, false, $jsmodule);

        $table = new html_table();
        $table->id = 'print_preview';
        $table->attributes['class'] = 'boxaligncenter';
        
        $tsort = isset($_SESSION['organizer_tsort']) ? $_SESSION['organizer_tsort'] : "";
        
        if($tsort != ""){
        	$order = "ASC";
        	        	
        	if(substr($tsort, strlen($tsort) - strlen("DESC")) == "DESC"){
        		$tsort = substr($tsort,0, strlen($tsort) - strlen("DESC"));        		
        		$order = "DESC";
        	}
        }
        
        
        $icon_up = ' <img src="' . $OUTPUT->pix_url('t/up') . '" class="iconsmall" alt="up" />';
        $icon_down = ' <img src=' . $OUTPUT->pix_url('t/down') . '" class="iconsmall" alt="down" />';

        $header = array();
        foreach ($columns as $column) {       	
        	
            $content = '<a href="?id=' . $cm->id . '&sesskey=' . sesskey() . '&action=print&tsort=' . $column;

            
            
            $icon = "";
            if($column == $tsort){
            	if($order == "ASC"){
            		$content .= "DESC";
            		$icon = $icon_up;
            	}else{
            		$icon = $icon_down;
            	}
            }
            
            $content .= '" name="' . $column . '_cell">' . get_string("th_{$column}", 'organizer') . $icon;
            $content .= '</a>';
            
            $imgattr = array(
                    'src' => $OUTPUT->pix_url('t/switch_minus'),
                    'alt' => get_string('hide'), 
                    'id' => "toggle_{$column}",
                    'style' => 'cursor: pointer',
                    'title' => get_string("th_{$column}", 'organizer'));
            
            $content .= ' ' . html_writer::empty_tag('img', $imgattr);

            $cell = new html_table_cell($content);
            $cell->header = true;
            $header[] = $cell;
        }
        $table->head = $header;

        switch($tsort){
        	case "datetime";		$sort = "starttime"; break;     		
        	case "location":		$sort = "s.location"; break;
        	case "teacher":			$sort = "teacherfirstname"; break;
        	case "participant":		$sort = "u.lastname"; break;
        	case "idnumber":		$sort = "u.idnumber"; break;
        	case "attended":		$sort = "a.attended"; break;
        	case "grade":			$sort = "a.grade"; break;
        	case "feedback":		$sort = "a.feedback"; break; 				
        	default:				$sort = NULL;
        }
        
        if(!isset($order)){
        	$order = "";
        }else if($order != "DESC" && $order != "ASC"){
        	$order = "";
        }
        
        
        $data = &$this->_customdata;
        $entries = organizer_fetch_table_entries($data['slots'],$sort . ' ' . $order);
        
        $rows = array();
        $rowspan = 0;
        $numcols = 0;
        $evenodd = 0;
        foreach ($entries as $entry) {
            if ($numcols == 10) {
                break;
            }
            $row = $rows[] = new html_table_row();
            foreach ($columns as $column) {
                if ($rowspan == 0) {
                    switch ($column) {
                        case 'datetime':
                            $datetime = userdate($entry->starttime, get_string('fulldatetimetemplate', 'organizer'))
                                    . ' - '
                                    . userdate($entry->starttime + $entry->duration,
                                            get_string('timetemplate', 'organizer'));
                            $content = "<span name='{$column}_cell'>" . $datetime . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->style = 'vertical-align: middle;';
                            $row->cells[] = $cell;
                            break;
                        case 'location':
                            $location = $entry->location;
                            $content = "<span name='{$column}_cell'>" . $location . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->style = 'vertical-align: middle;';
                            $row->cells[] = $cell;
                            break;
                        case 'teacher':
                            $a = new stdClass();
                            $a->firstname = $entry->teacherfirstname;
                            $a->lastname = $entry->teacherlastname;
                            $name = get_string('fullname_template', 'organizer', $a);
                            $content = "<span name='{$column}_cell'>" . $name . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->style = 'vertical-align: middle;';
                            $row->cells[] = $cell;
                            break;
                        case 'groupname':
                            $groupname = $entry->groupname;
                            $content = "<span name='{$column}_cell'>" . $groupname . '</span>';
                            $cell = new html_table_cell($content);
                            $cell->rowspan = $entry->rowspan;
                            $cell->style = 'vertical-align: middle;';
                            $row->cells[] = $cell;
                            break;
                        default:
                            break;
                    }
                }

                switch ($column) {
                    case 'participant':
                        $a = new stdClass();
                        $a->firstname = $entry->firstname;
                        $a->lastname = $entry->lastname;
                        $name = get_string('fullname_template', 'organizer', $a);
                        $content = "<span name='{$column}_cell'>" . $name . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->style = 'vertical-align: middle;';
                        $row->cells[] = $cell;
                        break;
                    case 'idnumber':
                        $idnumber = (isset($entry->idnumber) && $entry->idnumber !== '') ? $entry->idnumber : '';
                        $content = "<span name='{$column}_cell'>" . $idnumber . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->style = 'vertical-align: middle;';
                        $row->cells[] = $cell;
                        break;
                    case 'attended':
                        $attended = isset($entry->attended) ? ($entry->attended == 1 ? 'Yes' : 'No') : '';
                        $content = "<span name='{$column}_cell'>" . $attended . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->style = 'vertical-align: middle;';
                        $row->cells[] = $cell;
                        break;
                    case 'grade':
                        $grade = isset($entry->grade) ? sprintf("%01.2f", $entry->grade) : '';
                        $content = "<span name='{$column}_cell'>" . $grade . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->style = 'vertical-align: middle;';
                        $row->cells[] = $cell;
                        break;
                    case 'feedback':
                        $feedback = isset($entry->feedback) && $entry->feedback !== '' ? $entry->feedback : '';
                        $content = "<span name='{$column}_cell'>" . $feedback . '</span>';
                        $cell = new html_table_cell($content);
                        $cell->style = 'vertical-align: middle;';
                        $row->cells[] = $cell;
                        break;
                    case 'datetime':
                    case 'location':
                    case 'teacher':
                    case 'groupname':
                        break;
                    default:
                        print_error("Unsupported column type: $column");
                }
            }
            $numcols++;
            $row->attributes['class'] = " r{$evenodd}";
            $rowspan = ($rowspan + 1) % $entry->rowspan;

            if ($rowspan == 0) {
                $evenodd = $evenodd ? 0 : 1;
            }
        }

        $table->data = $rows;

        return organizer_render_table_with_footer($table, false, true);
    }
}