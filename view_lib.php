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
 * @package mod_organizer
 * @copyright 2012 Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_APP_STATUS_INVALID', -1);
define('ORGANIZER_APP_STATUS_ATTENDED', 0);
define('ORGANIZER_APP_STATUS_ATTENDED_REAPP', 1);
define('ORGANIZER_APP_STATUS_PENDING', 2);
define('ORGANIZER_APP_STATUS_REGISTERED', 3);
define('ORGANIZER_APP_STATUS_NOT_ATTENDED', 4);
define('ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP', 5);
define('ORGANIZER_APP_STATUS_NOT_REGISTERED', 6);

define('ORGANIZER_ICON_STUDENT_COMMENT', 0);
define('ORGANIZER_ICON_TEACHER_COMMENT', 1);
define('ORGANIZER_ICON_TEACHER_FEEDBACK', 2);

require_once(dirname(__FILE__) . '/../../course/lib.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');
require_once(dirname(__FILE__) . '/slotlib.php');
require_once(dirname(__FILE__) . '/infobox.php');

function organizer_add_calendar() {
    global $PAGE, $DB;

    $courseid = optional_param('course', SITEID, PARAM_INT);

    if ($courseid != SITEID && !empty($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $courses = array($course->id => $course);
        $issite = false;
    } else {
        $course = get_site();
        $courses = calendar_get_default_courses();
        $issite = true;
    }

    $now = usergetdate(time());

    $calendar = new calendar_information($now['mday'], $now['mon'], $now['year']);
    $calendar->prepare_for_view($course, $courses);
    $renderer = $PAGE->get_renderer('core_calendar');
    $calendar->add_sidecalendar_blocks($renderer, true, 'month');
    $PAGE->requires->js_init_call('M.mod_organizer.fix_calendar_styles');
}

function organizer_generate_appointments_view($params, $instance) {
    global $PAGE;
    $PAGE->requires->js_init_call('M.mod_organizer.init_checkboxes');

    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);
    $output .= organizer_begin_form($params);
    $output .= organizer_generate_button_bar($params, $instance->organizer, $instance->context);

    $columns = array('select', 'datetime', 'location', 'participants', 'teacher', 'details');
    $align = array('center', 'left', 'left', 'left', 'left', 'center');
    $sortable = array('datetime', 'location', 'teacher');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_table_header($columns, $sortable, $params, true);
    $table->data = organizer_generate_table_content($columns, $params, $instance->organizer, false, false);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);
    $output .= organizer_generate_button_bar($params, $instance->organizer, $instance->context);
    $output .= organizer_end_form();

    return $output;
}

function organizer_generate_student_view($params, $instance) {
    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    if(time() > $instance->organizer->allowregistrationsfromdate ){
	    $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
	    $align = array('left', 'left', 'left', 'left', 'center', 'center');
	    $sortable = array('datetime', 'location', 'teacher');
	
	    $table = new html_table();
	    $table->id = 'slot_overview';
	    $table->attributes['class'] = 'generaltable boxaligncenter overview';
	    $table->head = organizer_generate_table_header($columns, $sortable, $params);
	    $table->data = organizer_generate_table_content($columns, $params, $instance->organizer, false, false);
	    $table->align = $align;

	    $output .= organizer_render_table_with_footer($table);
    }else{
    	
    	if($instance->organizer->alwaysshowdescription){
    		$message = get_string('allowsubmissionsfromdatesummary','organizer',userdate($instance->organizer->allowregistrationsfromdate));
    	}else{
    		$message = get_string('allowsubmissionsanddescriptionfromdatesummary','organizer',userdate($instance->organizer->allowregistrationsfromdate));
    	}
    	$output .= html_writer::div($message,'',array('id'=>'intro'));
    }

    return $output;
}

function organizer_generate_registration_status_view($params, $instance) {
    $output = organizer_generate_tab_row($params, $instance->context);
    
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    $columns = array('status');

    if ($instance->organizer->isgrouporganizer) {
        $columns[] = 'group';
        $columns[] = 'appdetails';
    } else {
        $columns[] = 'participants';
        if ($instance->organizer->grade != 0) {
            $columns[] = 'appdetails';
        }
    }

    $columns = array_merge($columns, array('datetime', 'location', 'teacher', 'actions'));

    $align = array('center', 'left', 'center', 'left', 'left', 'left', 'center');
    $sortable = array('status', 'group');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_reg_table_header($columns, $sortable, $params);
    $table->data = organizer_organizer_generate_registration_table_content($columns, $params, $instance->organizer, $instance->context);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);

    return $output;
}

//------------------------------------------------------------------------------

function organizer_begin_form($params) {
    $url = new moodle_url('/mod/organizer/view_action.php');
    $output = '<form name="viewform" action="' . $url->out() . '" method="post">';
    $output .= '<input type="hidden" name="id" value="' . $params['id'] . '" />';
    $output .= '<input type="hidden" name="mode" value="' . $params['mode'] . '" />';
    $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    return $output;
}

function organizer_end_form() {
    return '</form>';
}

function organizer_generate_tab_row($params, $context) {
    $tabrow = array();

    if (has_capability('mod/organizer:viewallslots', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => ORGANIZER_TAB_APPOINTMENTS_VIEW));
        $tabrow[] = new tabobject(ORGANIZER_TAB_APPOINTMENTS_VIEW, $targeturl, get_string('taballapp', 'organizer'));
    }

    if (has_capability('mod/organizer:viewstudentview', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php', array('id' => $params['id'], 'mode' => ORGANIZER_TAB_STUDENT_VIEW));
        $tabrow[] = new tabobject(ORGANIZER_TAB_STUDENT_VIEW, $targeturl, get_string('tabstud', 'organizer'));
    }

    if (has_capability('mod/organizer:viewregistrations', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => ORGANIZER_TAB_REGISTRATION_STATUS_VIEW));
        $tabrow[] = new tabobject(ORGANIZER_TAB_REGISTRATION_STATUS_VIEW, $targeturl, get_string('tabstatus', 'organizer'));
    }

    if (count($tabrow) > 1) {
        $tabs = array($tabrow);
        $output = print_tabs($tabs, $params['mode'], null, null, true);
        $output = preg_replace('/<div class="tabtree">/', '<div class="tabtree" style="margin-bottom: 0em;">', $output);
        return $output;
    } else {
        return ''; // if only one tab is enabled, hide the tab row altogether
    }
}

function organizer_generate_button_bar($params, $organizer, $context) {
    $actions = array('add', 'edit', 'delete', 'print', 'eval');

    $organizerexpired = isset($organizer->duedate) && $organizer->duedate - time() < 0;
    $disable = array($organizerexpired, $organizerexpired, $organizerexpired, false, false);

    $output = '<div name="button_bar" class="buttons mdl-align">';
    foreach ($actions as $id => $action) {
        if (has_capability("mod/organizer:{$action}slots", $context, null, true)) {
            $output .= '<div class="singlebutton"><button type="submit" name="action" value="' . $action . '" '
                    . ($disable[$id] ? 'disabled' : '') . '/>' . get_string("btn_$action", 'organizer')
                    . '</button></div>';
        }
    }
    $output .= '</div>';

    return $output;
}

function organizer_generate_table_header($columns, $sortable, $params, $usersort = false) {
    global $OUTPUT;

    $header = array();
    foreach ($columns as $column) {
        if (in_array($column, $sortable)) {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $columnicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
            }

            $viewurl = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir,
                            'psort' => $params['psort'], 'pdir' => $params['pdir']));
            $cell = new html_table_cell(
                    html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon);
        } else if ($column == 'select') {
            $cell = new html_table_cell(
                    html_writer::checkbox('select', null, false, '',
                            array('title' => get_string('select_all_slots', 'organizer'))));
        } else if ($column == 'participants' && $usersort) {
            if ($params['psort'] == 'name') {
                $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['psort'] == 'id') {
                $iddir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $iddir = 'ASC';
                $idicon = '';
            }

            $urln = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                            'dir' => $params['dir'], 'psort' => 'name', 'pdir' => $namedir));
            $urli = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                            'dir' => $params['dir'], 'psort' => 'id', 'pdir' => $iddir));
            $links = "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/"
                    . html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

            $cell = new html_table_cell(get_string("th_{$column}", 'organizer') . " " . $links);
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer'));
        }
        $cell->header = true;
        $header[] = $cell;
    }
    return $header;
}

function organizer_generate_reg_table_header($columns, $sortable, $params) {
    global $OUTPUT;

    $header = array();
    foreach ($columns as $column) {
        if ($column != 'group' && $column != 'participants' && in_array($column, $sortable)) {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $columnicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
            }

            $viewurl = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir));
            $cell = new html_table_cell(
                    html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon);
        } else if ($column == 'group') {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $columnicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
            }
            $viewurl = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'group', 'dir' => $columndir));
            if ($params['psort'] == 'name') {
                $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['psort'] == 'id') {
                $iddir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $iddir = 'ASC';
                $idicon = '';
            }

            $urln = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                            'dir' => $params['dir'], 'psort' => 'name', 'pdir' => $namedir));
            $urli = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                            'dir' => $params['dir'], 'psort' => 'id', 'pdir' => $iddir));
            $links = "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/"
                    . html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

            $cell = new html_table_cell(
                    html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon . " " . $links);
        } else if ($column == 'participants') {
            if ($params['sort'] == 'name') {
                $namedir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['dir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['sort'] == 'id') {
                $iddir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . organizer_get_img($OUTPUT->pix_url('t/' . ($params['dir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $iddir = 'ASC';
                $idicon = '';
            }

            $urln = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'name', 'dir' => $namedir));
            $urli = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'id', 'dir' => $iddir));
            $links = "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/"
                    . html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

            $cell = new html_table_cell(get_string("th_{$column}", 'organizer') . " " . $links);
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer'));
        }
        $cell->header = true;
        $header[] = $cell;
    }
    return $header;
}

function organizer_generate_table_content($columns, $params, $organizer, $showonlyregslot = false) {
    global $DB, $USER;

    $translate = array('datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}",
            'teacher' => "lastname {$params['dir']}, firstname {$params['dir']}");

    $order = $translate[$params['sort']];

    $app = organizer_get_last_user_appointment($organizer);

    if ($showonlyregslot) {
        if ($app) {
            $evaldapp = organizer_get_last_user_appointment($organizer, 0, false, true);
            if (!$evaldapp || $app->id == $evaldapp->id) {
                $slots = array($DB->get_record('organizer_slots', array('id' => $app->slotid)));
            } else {
                $slots = array($DB->get_record('organizer_slots', array('id' => $app->slotid)),
                        $DB->get_record('organizer_slots', array('id' => $evaldapp->slotid)));
            }
        } else {
            $slots = array();
        }
    } else {
        $sqlparams = array('organizerid' => $organizer->id);
        $query = "SELECT s.*, u.firstname, u.lastname FROM {organizer_slots} s
        INNER JOIN {user} u ON s.teacherid = u.id WHERE s.organizerid = :organizerid ORDER BY $order";
        $slots = $DB->get_records_sql($query, $sqlparams);
    }

    $showpasttimeslots = get_user_preferences('mod_organizer_showpasttimeslots', true);
    $showonlymyslots = get_user_preferences('mod_organizer_showmyslotsonly', false);
    $showonlyfreeslots = get_user_preferences('mod_organizer_showfreeslotsonly', false);

    $rows = array();
    if (count($slots) != 0) {
        $numshown = 0;
        foreach ($slots as $slot) {
            $slotx = new organizer_slot($slot);
            if (!$slotx->is_available()) {
                if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW) {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = 'unavailable';
                } else {
                    continue; // slot isn't available yet
                }
            } else {
                $row = $rows[] = new html_table_row();
                $row->attributes['class'] = '';

            }

            $slotpastdue = $slotx->is_past_due();
            $myslot = $slot->teacherid == $USER->id;

            $hidden = false;

            if ($slotpastdue) {
                $row->attributes['class'] .= ' past_due';
                if (!$showpasttimeslots && !$showonlyregslot) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
            }

            if ($myslot) {
                $row->attributes['class'] .= ' my_slot';
            } else {
                if ($showonlymyslots && $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
            }

            if (!$slotx->is_full()) {
                $row->attributes['class'] .= ' free_slot';
            } else {
                if ($showonlyfreeslots) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
            }

            if (!$hidden) {
                $numshown++;
            }

            if (array_search($slot->id, $params['slots']) !== false) {
                $row->attributes['class'] .= ' affected_slot';
            }

            if (($params['mode'] == ORGANIZER_TAB_STUDENT_VIEW) && $app && ($app->slotid == $slot->id)) {
                $row->attributes['class'] .= ' registered_slot';
            }

            foreach ($columns as $column) {
                switch ($column) {
                    case 'select':
                        $cell = $row->cells[] = new html_table_cell(
                                html_writer::checkbox('slots[]', $slot->id, false, '',
                                        array('class' => 'checkbox_slot')));
                        break;
                    case 'datetime':
                        $cell = $row->cells[] = new html_table_cell(organizer_date_time($slot));
                        break;
                    case 'location':
                        $cell = $row->cells[] = new html_table_cell(organizer_location_link($slot));
                        break;
                    case 'participants':
                        if ($showonlyregslot) {
                            $cell = $row->cells[] = new html_table_cell(organizer_organizer_get_participant_list_infobox($params, $slot));
                        } else {
                            $cell = $row->cells[] = new html_table_cell(
                                    organizer_get_participant_list($params, $slot, $app));
                        }
                        break;
                    case 'teacher':
                        $cell = $row->cells[] = new html_table_cell(organizer_teacher_data($params, $slot));
                        break;
                    case 'details':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_status($params, $slot));
                        break;
                    case 'status':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_reg_status($organizer, $slot));
                        break;
                    case 'actions':
                        $cell = $row->cells[] = new html_table_cell(organizer_student_action($params, $slot));
                        break;
                    default:
                        print_error("Unrecognized column type: $column");
                }

                $cell->style .= ' vertical-align: middle;';
            }
        }

        $inforownames = array('no_slots', 'no_due_slots', 'no_my_slots', 'no_due_my_slots');
        foreach ($inforownames as $inforowname) {
            $defaultrow = $rows[] = new html_table_row();
            $defaultrow->attributes['class'] = "info $inforowname";

            $showpastslots = get_user_preferences('mod_organizer_showpasttimeslots');
            $showmyslotsonly = get_user_preferences('mod_organizer_showmyslotsonly');

            $defaultrow->cells[] = organizer_get_span_cell(get_string($inforowname, 'organizer'), count($columns));

            $oneshown = false;

            $defaultrow->style = '';

            if ($numshown == 0 && !$oneshown) {
                switch ($inforowname) {
                    case 'no_slots':
                        $defaultrow->style = ($showpastslots && !$showmyslotsonly) ? '' : 'display: none;';
                        break;
                    case 'no_due_slots':
                        $defaultrow->style = (!$showpastslots && !$showmyslotsonly) ? '' : 'display: none;';
                        break;
                    case 'no_my_slots':
                        $defaultrow->style = ($showpastslots && $showmyslotsonly) ? '' : 'display: none;';
                        break;
                    case 'no_due_my_slots':
                        $defaultrow->style = (!$showpastslots && $showmyslotsonly) ? '' : 'display: none;';
                        break;
                    default:
                        print_error("This shouldn't happen @ generating no slot rows");
                }
            } else {
                $defaultrow->style = 'display: none;';
            }

            if ($defaultrow->style == '') {
                $oneshown = true;
            }
        }
    } else {
        $defaultrow = $rows[] = new html_table_row();
        if (!$showonlyregslot) {
            if ($params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                $url = new moodle_url('/mod/organizer/view_action.php',
                        array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'add',
                                'sesskey' => sesskey()));
                $a = new stdClass();
                $a->link = $url->out();
                $message = get_string('no_slots_defined_teacher', 'organizer', $a);
            } else {
                $message = get_string('no_slots_defined', 'organizer');
            }

            $defaultrow->cells[] = organizer_get_span_cell($message, count($columns));
            $defaultrow->attributes['class'] = "info no_slots_defined";
        }
    }
    return $rows;
}

function organizer_get_span_cell($text, $colspan) {
    $cell = $defaultrow->cells[] = new html_table_cell();
    $cell->colspan = $colspan;
    $cell->style = 'text-align: center; vertical-align: middle;';
    $cell->text = $text;

    return $cell;
}

function organizer_organizer_organizer_get_status_table_entries_group($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    $query = "SELECT g.id FROM {groups} g
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :groupingid";
    $par = array('groupingid' => $cm->groupingid);
    $groupids = $DB->get_fieldset_sql($query, $par);

    if (!$groupids || count($groupids) == 0) {
        return array();
    }

    list($insql, $inparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);

    $dir = isset($params['dir']) ? $params['dir'] : 'ASC';

    if ($params['sort'] == 'status') {
        $orderby = "ORDER BY status $dir, g.name ASC";
    } else if ($params['sort'] == 'group') {
        $orderby = "ORDER BY g.name $dir, status ASC";
    } else {
        $orderby = "ORDER BY g.name ASC, status ASC";
    }

    $par = array('now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id);
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        g.id, g.name,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 0 THEN " . ORGANIZER_APP_STATUS_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 1  THEN "
            . ORGANIZER_APP_STATUS_ATTENDED_REAPP
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN " . ORGANIZER_APP_STATUS_PENDING
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN " . ORGANIZER_APP_STATUS_REGISTERED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 0 THEN " . ORGANIZER_APP_STATUS_NOT_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 1 THEN "
            . ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP . "
            WHEN a2.id IS NULL THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID
            . "
        END AS status, a2.starttime, a2.duration, a2.location, a2.locationlink,
        a2.teacherid, a2.applicantid, a2.teachercomments, a2.comments, a2.teachervisible,
        a2.slotid, a2.allownewappointments, a2.id AS appid
        FROM {groups} g
        LEFT JOIN
        (SELECT
        a.id, a.groupid, a.allownewappointments, s.id as slotid, s.starttime, s.location,
        s.locationlink, s.teacherid, s.teachervisible,
        s.duration, a.applicantid, a.comments, s.comments AS teachercomments,
        (SELECT MAX(a3.attended) FROM {organizer_slot_appointments} a3
        WHERE a3.groupid = a.groupid GROUP BY a3.slotid ORDER BY a3.slotid DESC LIMIT 1) AS attended
        FROM {organizer_slot_appointments} a
        INNER JOIN {organizer_slots} s ON a.slotid = s.id
        WHERE s.organizerid = :organizerid ORDER BY a.id DESC) a2 ON g.id = a2.groupid
        WHERE g.id $insql
        GROUP BY g.id
        $orderby";

    return $DB->get_records_sql($query, $par);
}

function organizer_organizer_get_status_table_entries($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    if ($cm->groupingid == 0) {
        $students = get_enrolled_users($context, 'mod/organizer:register');
        $studentids = array();
        foreach ($students as $student) {
            $studentids[] = $student->id;
        }
    } else {
        $query = "SELECT u.id FROM {user} u
            INNER JOIN {groups_members} gm ON u.id = gm.userid
            INNER JOIN {groups} g ON gm.groupid = g.id
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :grouping";
        $par = array('grouping' => $cm->groupingid);
        $studentids = $DB->get_fieldset_sql($query, $par);
    }

    if (!$studentids || count($studentids) == 0) {
        return array();
    }

    list($insql, $inparams) = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);

    $dir = isset($params['dir']) ? $params['dir'] : 'ASC';

    if ($params['sort'] == 'status') {
        $orderby = "ORDER BY status $dir, u.lastname ASC, u.firstname ASC, u.idnumber ASC";
    } else if ($params['sort'] == 'name') {
        $orderby = "ORDER BY u.lastname $dir, u.firstname $dir, status ASC, u.idnumber ASC";
    } else if ($params['sort'] == 'id') {
        $orderby = "ORDER BY u.idnumber $dir, status ASC, u.lastname ASC, u.firstname ASC";
    } else {
        $orderby = "ORDER BY u.lastname ASC, u.firstname ASC, status ASC, u.idnumber ASC";
    }

    $par = array('now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id);
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        u.id, u.firstname, u.lastname, u.idnumber,
        CASE
        WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 0 THEN " . ORGANIZER_APP_STATUS_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 1  THEN "
            . ORGANIZER_APP_STATUS_ATTENDED_REAPP
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN " . ORGANIZER_APP_STATUS_PENDING
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN " . ORGANIZER_APP_STATUS_REGISTERED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 0 THEN " . ORGANIZER_APP_STATUS_NOT_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 1 THEN "
            . ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP . "
            WHEN a2.id IS NULL THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID
            . "
        END AS status,
        a2.starttime, a2.duration, a2.attended, a2.location, a2.locationlink,
        a2.grade, a2.comments, a2.teachercomments, a2.feedback, a2.teacherid,
        a2.userid, a2.teachervisible, a2.slotid, a2.allownewappointments, a2.id AS appid
        FROM {user} u
        LEFT JOIN
        (SELECT a.id, a.attended, a.grade, a.feedback, a.comments, a.userid,
        a.allownewappointments, s.starttime, s.location, s.locationlink, s.teacherid,
        s.comments AS teachercomments, s.duration, s.teachervisible, s.id AS slotid
        FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
        WHERE s.organizerid = :organizerid ORDER BY a.id DESC) a2 ON u.id = a2.userid
        WHERE u.id $insql
        GROUP BY u.id
        $orderby";

    return $DB->get_records_sql($query, $par);
}

function organizer_organizer_generate_registration_table_content($columns, $params, $organizer, $context) {
    global $DB;

    $groupmode = organizer_is_group_mode();

    if ($groupmode) {
        $entries = organizer_organizer_organizer_get_status_table_entries_group($params);
    } else {
        $entries = organizer_organizer_get_status_table_entries($params);
    }

    $rows = array();

    if (count($entries) == 0) {
        $row = new html_table_row();
        $cell = $row->cells[] = new html_table_cell(get_string('status_no_entries', 'organizer'));
        $cell->colspan = 7;
        $cell->style .= ' vertical-align: middle; text-align: center;';
        $rows[] = $row;
        return $rows;
    }

    foreach ($entries as $entry) {
        if ($entry->status == ORGANIZER_APP_STATUS_INVALID) {
            continue;
        }

        $row = new html_table_row();

        foreach ($columns as $column) {
            switch ($column) {
                case 'group':
                case 'participants':
                    if ($groupmode) {
                        if ($params['psort'] == 'id') {
                            $orderby = "ORDER BY idnumber {$params['pdir']}, lastname ASC, firstname ASC";
                        } else {
                            $orderby = "ORDER BY lastname {$params['pdir']}, firstname {$params['pdir']}, idnumber ASC";
                        }
                        $members = $DB->get_fieldset_sql(
                                'SELECT userid FROM {groups_members} gm
                                INNER JOIN {user} u ON gm.userid = u.id WHERE groupid = :groupid ' .
                                $orderby, array('groupid' => $entry->id));
                        $list = "<em>$entry->name</em><br/ >";
                        foreach ($members as $member) {
                            $idnumber = organizer_get_user_idnumber($member);

                            $list .= organizer_get_name_link($member) . ($idnumber ? " ($idnumber) " : " ")
                                    . ($entry->comments != '' ?
                                            organizer_popup_icon(ORGANIZER_ICON_STUDENT_COMMENT, $entry->comments) :
                                            organizer_get_img('pix/transparent.png', '', ''));
                            if ($member == $entry->applicantid) {
                                $list .= ' '
                                        . organizer_get_img('pix/applicant.gif', 'applicant',
                                                get_string('applicant', 'organizer')) . '<br/>';
                            } else {
                                $list .= ' ' . organizer_get_img('pix/transparent.png', '', '') . '<br />';
                            }
                        }
                        $cell = $row->cells[] = new html_table_cell($list);
                    } else {
                        $cell = $row->cells[] = new html_table_cell(
                                organizer_get_name_link($entry->id) . ($entry->idnumber ? " ($entry->idnumber)" : ""));
                    }
                    break;
                case 'status':
                    $cell = $row->cells[] = new html_table_cell(organizer_get_status_icon_new($entry->status));
                    break;
                case 'datetime':
                    $cell = $row->cells[] = new html_table_cell(organizer_date_time($entry));
                    $cell->style .= " text-align: left;";
                    break;
                case 'appdetails':
                    if ($groupmode) {
                        if ($params['psort'] == 'id') {
                            $orderby = "ORDER BY idnumber {$params['pdir']}, lastname ASC, firstname ASC";
                        } else {
                            $orderby = "ORDER BY lastname {$params['pdir']}, firstname {$params['pdir']}, idnumber ASC";
                        }
                        $members = $DB->get_fieldset_sql(
                                'SELECT userid FROM {groups_members} gm
                                INNER JOIN {user} u ON gm.userid = u.id WHERE groupid = :groupid ' .
                                $orderby, array('groupid' => $entry->id));
                        $list = '';
                        foreach ($members as $member) {
                            $list .= '<br />';
                            $list .= organizer_reg_organizer_app_details($organizer, $member);
                        }
                        $cell = $row->cells[] = new html_table_cell($list);
                    } else {
                        $cell = $row->cells[] = new html_table_cell(organizer_reg_organizer_app_details($organizer, $entry->id));
                    }

                    break;
                case 'location':
                    $cell = $row->cells[] = new html_table_cell(organizer_location_link($entry));
                    break;
                case 'teacher':
                    $cell = $row->cells[] = new html_table_cell(organizer_teacher_data($params, $entry));
                    break;
                case 'actions':
                    $cell = $row->cells[] = new html_table_cell(organizer_teacher_action_new($params, $entry, $context));
                    $cell->style .= " text-align: center;";
                    break;
            }

            $cell->style .= ' vertical-align: middle;';
        }
        $rows[] = $row;
    }

    return $rows;
}

function organizer_get_participant_entry($entry) {
    $icon = ' ' . (isset($entry->comments) && $entry->comments != '' ? 
            organizer_popup_icon(ORGANIZER_ICON_STUDENT_COMMENT, $entry->comments) :
            organizer_get_img('pix/transparent.png', '', ''));
    return organizer_get_name_link($entry->id) . " {$icon}<br/>({$entry->idnumber})";
}

function organizer_app_details($params, $appointment) {
    global $USER;
    if (!isset($appointment)) {
        return '';
    }

    $list = ' ' . ($appointment->comments != '' ?
            organizer_popup_icon(ORGANIZER_ICON_STUDENT_COMMENT, $appointment->comments) :
            organizer_get_img('pix/transparent.png', '', ''));
    $list .= ' ' . organizer_get_attended_icon($appointment);

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    if ($organizer->grade != 0) {
        $list .= ' ' . organizer_display_grade($organizer, $appointment->grade); /// OVER HERE
    }

    $list .= ' ' . ($appointment->feedback != '' ? 
            organizer_popup_icon(ORGANIZER_ICON_TEACHER_FEEDBACK, $appointment->feedback) :
            organizer_get_img('pix/transparent.png', '', ''));

    return $list;
}

function organizer_registration_allowed($organizer, $userid = null) {
    $app = organizer_get_last_user_appointment($organizer, $userid);
    if ($app) { // appointment made, check the flag
        $slot = new organizer_slot($app->slotid);
        if ($slot->is_past_deadline()) {
            return isset($app->allownewappointments) && $app->allownewappointments;
        } else {
            return !isset($app->allownewappointments) || $app->allownewappointments;
        }
    } else { // no appointment made, allowed
        return true;
    }
}

// -------------------- CONTENT GENERATING FUNCTIONS ---------------------------

function organizer_date_time($slot) {
    if (!isset($slot) || !isset($slot->starttime)) {
        return '-';
    }

    $date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
    $time = userdate($slot->starttime, get_string('timetemplate', 'organizer')) . ' - '
            . userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
    list($unitname, $value) = organizer_figure_out_unit($slot->duration);
    $duration = ($slot->duration / $value) . ' ' . $unitname;

    return "$date<br />$time ($duration)";
}

function organizer_teacher_data($params, $slot) {
    global $USER;

    if (!isset($slot) || !isset($slot->teacherid)) {
        return '-';
    }

    global $DB;
    $query = "SELECT a.*
    FROM {organizer_slot_appointments} a
    WHERE a.slotid = :slotid";
    $param = array('slotid' => $slot->id);
    $appointments = $DB->get_records_sql($query, $param);
    
    
    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    
    $canregister = has_capability('mod/organizer:register', $context, null, false);
    $canunregister = has_capability('mod/organizer:unregister', $context, null, false);
    $canreregister = $canregister && $canunregister;
    
    $slotx = new organizer_slot($slot);

    $wasownslot = false;
    foreach ($appointments as $someapp) {
    	if ($someapp->userid == $USER->id) {
    		$wasownslot = true;
    		break;
    	}
    }
    
    $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotdisabled = $slotx->is_past_due() || $slotx->is_past_deadline();
    $myslotpending = $wasownslot && $slotx->is_past_deadline() && !$slotx->is_evaluated();
    $slotfull = $slotx->is_full();
    
    $showteacher = $myslotpending || $organizerdisabled || $slotdisabled || !$slotx->organizer_user_has_access() || $slotx->is_evaluated();

   
    if ($wasownslot) {
    	if (!$slotdisabled) {
   			$showteacher |= !$canunregister || (isset($regslotx) && $regslotx->is_evaluated() && !$myapp->allownewappointments);
    	}
    }
    
    $slotx = new organizer_slot($slot);
    
    if($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $slot->teachervisible || $showteacher){
    	$output = organizer_get_name_link($slot->teacherid);
    }else{
    	$output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    if (isset($slot->teachercomments)) {
        if ($slot->teachercomments != '') {
            $output .= ' ' . organizer_popup_icon(ORGANIZER_ICON_TEACHER_COMMENT, $slot->teachercomments);
        }
    } else {
        if ($slot->comments != '') {
            $output .= ' ' . organizer_popup_icon(ORGANIZER_ICON_TEACHER_COMMENT, $slot->comments);
        }
    }

    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output .= '<br /><em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    return $output;
}

function organizer_reg_organizer_app_details($organizer, $userid) {
    $appointment = organizer_get_last_user_appointment($organizer, $userid, false);
    if ($appointment) {
        $list = '';
        if (organizer_is_group_mode()) {
            $list .= organizer_get_attended_icon($appointment) . ' ';
        }
        if ($organizer->grade != 0) {
            $list .= organizer_display_grade($organizer, $appointment->grade);
        }
        $list .= ' ' . (isset($appointment->feedback) && $appointment->feedback != '' ?
                organizer_popup_icon(ORGANIZER_ICON_TEACHER_FEEDBACK, $appointment->feedback) : '');
    } else {
        $list = '-';
    }

    return $list;
}

function organizer_teacher_action_new($params, $entry, $context) {
    global $OUTPUT;

    $evalurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'eval', 'slots[]' => $entry->slotid, 'sesskey'=>sesskey()));

    $remindurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'remindall', 'user' => $entry->id, 'sesskey'=>sesskey()));

    $buttons = array();
    
    switch ($entry->status) {
        case ORGANIZER_APP_STATUS_ATTENDED:
        	$button = new stdClass();
        	$button->text = get_string("btn_reeval", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_ATTENDED_REAPP:
        	$button = new stdClass();
        	$button->text = get_string("btn_reeval", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_PENDING:
        	$button = new stdClass();
        	$button->text = get_string("btn_eval_short", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_REGISTERED:
        	$button = new stdClass();
        	$button->text = get_string("btn_eval_short", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_NOT_ATTENDED:
        	$button = new stdClass();
        	$button->text = get_string("btn_reeval", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP:
        	$button = new stdClass();
        	$button->text = get_string("btn_remind", 'organizer');
        	$button->url = $remindurl;
        	$button->disabled = !has_capability('mod/organizer:sendreminders', $context, null, true);
        	$buttons[] = $button;
        	
        	$button = new stdClass();
        	$button->text = get_string("btn_reeval", 'organizer');
        	$button->url = $evalurl;
        	$button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true);
        	$buttons[] = $button;
        	break;

        case ORGANIZER_APP_STATUS_NOT_REGISTERED:
        	$button = new stdClass();
        	$button->text = get_string("btn_remind", 'organizer');
        	$button->url = $remindurl;
        	$button->disabled = !has_capability('mod/organizer:sendreminders', $context, null, true);
        	$buttons[] = $button;
        	break;
        default:
            print_error("Wrong status code: $entry->status");
    }
    
    $output = "";
    
    foreach($buttons as $button){
    	if($button->disabled){
    		$output .= '<a href="#" class="action disabled">' . $button->text . '</a>';
    	}else{
    		$output .= '<a href="' . $button->url . '" class="action">' . $button->text . '</a>';
    	}
    }
    
    return $output;
}

function organizer_organizer_get_participant_list_infobox($params, $slot, $userid = 0) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (organizer_is_group_mode()) {
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
        $firstapp = reset($apps);
        $groupname = $DB->get_field('groups', 'name', array('id' => $firstapp->groupid));
        $output = "<em>{$groupname}</em><br />";
        foreach($apps as $app) {
            $name = organizer_get_name_link($app->userid);
            $idnumber = organizer_get_user_idnumber($app->userid);
            if ($app->userid == $userid) {
                $output .= $name . ($idnumber ? " ($idnumber) " : " ");
                $output .= ($app->userid == $app->applicantid) ? 
                        organizer_get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                $output .= '<div style="float: right;">' . organizer_app_details($params, $app, true) .
                        '</div><div style="clear: both;"></div>';
            } else if (!$slot->isanonymous) {
                $output .= $name . ($idnumber ? " ($idnumber) " : " ");
                $output .= ($app->userid == $app->applicantid) ?
                        organizer_get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                $output .= '<div style="float: right;"></div><div style="clear: both;"></div>';
            }
        }
        
        if (count($apps) == 0) {
            $output .= "<em>" . get_string('group_slot_available', 'organizer') . "</em>";
        } else {
            $output .= "<span style=\"color: red;\"><em>" . get_string('group_slot_full', 'organizer')
            . "</em></span>";
        }
    } else {
        $app = $DB->get_record('organizer_slot_appointments', array('slotid' => $slot->id, 'userid' => $userid));
        $name = organizer_get_name_link($userid);
        $idnumber = organizer_get_user_idnumber($userid);
        $output = $name . ($idnumber ? " ($idnumber) " : " ") . '<div style="float: right;">' .
                organizer_app_details($params, $app) .
                '</div><div style="clear: both;"></div>';
        
        $count = count($DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id)));
        $a = new stdClass();
        $a->numtakenplaces = $count;
        $a->totalplaces = $slot->maxparticipants;
        
        if ($slot->maxparticipants - $count != 0) {
            if ($count == 1) {
                $output .= "<em>" . get_string('places_taken_sg', 'organizer', $a) . "</em>";
            } else {
                $output .= "<em>" . get_string('places_taken_pl', 'organizer', $a) . "</em>";
            }
        } else {
            if ($count == 1) {
                $output .= "<span style=\"color: red;\"><em>" . get_string('places_taken_sg', 'organizer', $a)
                . "</em></span>";
            } else {
                $output .= "<span style=\"color: red;\"><em>" . get_string('places_taken_pl', 'organizer', $a)
                . "</em></span>";
            }
        }
    }
    
    $output .= $slot->isanonymous ? ' ' . organizer_get_img('pix/anon.png', 'anonymous', get_string('slot_anonymous', 'organizer')) : '';
    
    return $output;
}

function organizer_get_participant_list($params, $slot, $app) {
    global $DB, $USER;

    $slotx = new organizer_slot($slot);

    $dir = isset($params['pdir']) ? $params['pdir'] : 'ASC';
    if (isset($params['psort']) && $params['psort'] == 'name') {
        $orderby = " ORDER BY u.lastname $dir, u.firstname $dir, u.idnumber ASC";
    } else if (isset($params['psort']) && $params['psort'] == 'id') {
        $orderby = " ORDER BY u.idnumber $dir, u.lastname ASC, u.firstname ASC";
    } else {
        $orderby = " ORDER BY u.lastname $dir, u.firstname $dir, u.idnumber ASC";
    }

    $query = "SELECT a.*, u.firstname, u.lastname, u.idnumber
        FROM {organizer_slot_appointments} a
        INNER JOIN {user} u ON a.userid = u.id
        WHERE a.slotid = :slotid $orderby";

    $param = array('slotid' => $slotx->id);

    $appointments = $DB->get_records_sql($query, $param);
    $count = count($appointments);
    $groupmode = organizer_is_group_mode();

    $isownslot = $app && ($app->slotid == $slotx->id);

    $wasownslot = false;
    foreach ($appointments as $someapp) {
        if ($someapp->userid == $USER->id) {
            $wasownslot = true;
            break;
        }
    }

    if (!$slotx->is_available()) {
        $when = userdate($slotx->starttime - $slotx->availablefrom, get_string('fulldatetimetemplate', 'organizer'));
        return "<em>" . get_string('unavailableslot', 'organizer') . "<br/>{$when}</em>";
    }

    $content = '';
    $studentview = $params['mode'] == ORGANIZER_TAB_STUDENT_VIEW;
    $ismyslot = $isownslot || $wasownslot;
    $groupmode = organizer_is_group_mode();
    
    if ($studentview) {
        if ($slot->isanonymous) {
            if ($ismyslot) {
                $idnumber = organizer_get_user_idnumber($app->userid);
                $content .= organizer_get_name_link($app->userid) .
                            ($idnumber ? " ($idnumber) " : " ") . '<br />';
                $content .= '<em>' . get_string('slot_anonymous', 'organizer') . '</em><br />';
            } else {
                $content .= '<em>' . get_string('slot_anonymous', 'organizer') . '</em><br />';
            }
        } else { // not anonymous
            if ($groupmode) {
                $app = reset($appointments);
                if($app === false) {
                    $content = '<em>' . get_string('nogroup', 'organizer') . '</em><br />';
                } else {
                    $groupid = $app->groupid;
                    $groupname = $DB->get_field('groups', 'name', array('id' => $groupid));
                    $content = "<em>{$groupname}</em><br />";
                }
            } else {
                $content = '';
            }
            
            foreach($appointments as $appointment) {
                $idnumber = organizer_get_user_idnumber($appointment->userid);
                $content .= organizer_get_name_link($appointment->userid) .
                            ($idnumber ? " ($idnumber) " : " ");
                if ($groupmode) {
                    $content .= ' ';
                    $content .= (organizer_is_group_mode() && $appointment->userid == $appointment->applicantid) ? organizer_get_img(
                                    'pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                }
                $content .= '<br />';
            }
        }
    } else {
        if ($count == 0) {
            $content .= ($groupmode ? '<em>' . get_string('nogroup', 'organizer') . '</em><br />'
                    : '<em>' . get_string('noparticipants', 'organizer') . '</em><br />');
        } else {
            $app = reset($appointments);

            if ($groupmode) {
                $groupid = $app->groupid;
                $groupname = $DB->get_field('groups', 'name', array('id' => $groupid));
                $list = "<em>{$groupname}</em><br />";
            } else {
                $list = '';
            }

            foreach ($appointments as $appointment) {
                $idnumber = organizer_get_user_idnumber($appointment->userid);
                $list .= '<div style="float: left;">';
                $list .= organizer_get_name_link($appointment->userid) . ($idnumber ? " ($idnumber)" : "");
                if ($groupmode) {
                    $list .= ' ';
                    $list .= (organizer_is_group_mode() && $appointment->userid == $appointment->applicantid) ? organizer_get_img(
                                    'pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                }
                $list .= '</div>';
                $list .= '<div style="float: right;">' . organizer_app_details($params, $appointment)
                        . '</div><div style="clear: both;"></div>';
            }
            $content .= $list;
        }
    }

    if (!$groupmode) {
        $a = new stdClass();
        $a->numtakenplaces = $count;
        $a->totalplaces = $slot->maxparticipants;

        if ($slot->maxparticipants - $count != 0) {
            if ($count == 1) {
                $content .= "<em>" . get_string('places_taken_sg', 'organizer', $a) . "</em>";
            } else {
                $content .= "<em>" . get_string('places_taken_pl', 'organizer', $a) . "</em>";
            }
        } else {
            if ($count == 1) {
                $content .= "<span style=\"color: red;\"><em>" . get_string('places_taken_sg', 'organizer', $a)
                        . "</em></span>";
            } else {
                $content .= "<span style=\"color: red;\"><em>" . get_string('places_taken_pl', 'organizer', $a)
                        . "</em></span>";
            }
        }
    } else {
        if ($count == 0) {
            $content .= "<em>" . get_string('group_slot_available', 'organizer') . "</em>";
        } else {
            $content .= "<span style=\"color: red;\"><em>" . get_string('group_slot_full', 'organizer')
                    . "</em></span>";
        }
    }

    $content .= ' '
            . ($slot->isanonymous ? organizer_get_img('pix/anon.png', 'anonymous', get_string('slot_anonymous', 'organizer')) : '');

    return $content;
}

function organizer_get_attended_icon($appointment) {
    return (isset($appointment->attended) ? ($appointment->attended == 1 ? ($appointment->allownewappointments ? organizer_get_img(
                                    'pix/yes_reg_small.png', '',
                                    get_string('reg_status_slot_attended_reapp', 'organizer'))
                            : organizer_get_img('pix/yes_small.png', '', get_string('reg_status_slot_attended', 'organizer')))
                    : ($appointment->allownewappointments ? organizer_get_img('pix/no_reg_small.png', '',
                                    get_string('reg_status_slot_not_attended_reapp', 'organizer'))
                            : organizer_get_img('pix/no_small.png', '', get_string('reg_status_slot_not_attended', 'organizer'))))
            : organizer_get_img('pix/slot_pending_small.png', '', get_string('reg_status_slot_pending', 'organizer')));
}

function organizer_location_link($slot) {
    if (!isset($slot) || !isset($slot->location) || $slot->location == '') {
        return '-';
    }

    if (isset($slot->locationlink)) {
        if (strpos($slot->locationlink, 'http://') === false) {
            $link = 'http://' . $slot->locationlink;
        } else {
            $link = $slot->locationlink;
        }
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return html_writer::link($link, $slot->location, array('target' => '_blank'));
        }
    }

    return $slot->location;
}

function organizer_get_img($src, $alt, $title, $id = '', $other = '') {
    return '<img src="' . $src . '" alt="' . $alt . '" title="' . $title . '" id="' . $id . '" ' . $other . ' />';
}

function organizer_slot_status($params, $slot) {
    $slotx = new organizer_slot($slot);

    $slotevaluated = $slotx->is_evaluated();
    $slotpastdue = $slotx->is_past_due();
    $slotpastdeadline = $slotx->is_past_deadline();
    $slothasparticipants = $slotx->has_participants();

    $slotnoparticipants = !$slotevaluated && ($slotpastdue || $slotpastdeadline) && !$slothasparticipants;
    $slotpending = !$slotevaluated && $slotpastdue && $slothasparticipants;
    $slotgradeable = !$slotevaluated && $slotpastdeadline && $slothasparticipants;
    $slotdueempty = !$slotpastdeadline && !$slothasparticipants;
    $slotdue = !$slotpastdeadline && $slothasparticipants;

    $actionurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'eval', 'slots[]' => $slot->id,
                    'sesskey' => sesskey()));

    if ($slotevaluated) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_img('pix/yes_24x24.png', '', get_string('img_title_evaluated', 'organizer')) . '</a>';
    } else if ($slotnoparticipants) {
        return organizer_get_img('pix/no_participants_24x24.png', '', get_string('img_title_no_participants', 'organizer'));
    } else if ($slotpending) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_img('pix/slot_pending_24x24.png', '', get_string('img_title_pending', 'organizer')) . '</a>';
    } else if ($slotgradeable) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_img('pix/student_slot_past_deadline_24x24.png', '',
                        get_string('img_title_past_deadline', 'organizer')) . '</a>';
    } else if ($slotdueempty) {
        return organizer_get_img('pix/student_slot_available_24x24.png', '', get_string('img_title_due', 'organizer'));
    } else if ($slotdue) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_img('pix/student_slot_available_24x24.png', '', get_string('img_title_due', 'organizer'))
                . '</a>';
    } else {
        print_error("This shouldn't happen.");
    }
}

function organizer_slot_reg_status($organizer, $slot) {
    global $DB;

    $slotx = new organizer_slot($slot);

    $app = organizer_get_last_user_appointment($organizer);

    if ($slotx->organizer_expired()) {
        $output = organizer_get_img('pix/organizer_expired_24x24.png', '',
                get_string('reg_status_organizer_expired', 'organizer'));
    } else if ($slotx->is_past_due()) {
        if ($app) {
            $regslot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            if ($slotx->id == $regslot->id) {
                if (!isset($app->attended)) {
                    $output = organizer_get_img('pix/slot_pending_24x24.png', '',
                            get_string('reg_status_slot_pending', 'organizer'));
                } else if ($app->attended == 0 && $app->allownewappointments == 0) {
                    $output = organizer_get_img('pix/no_24x24.png', '', get_string('reg_status_slot_not_attended', 'organizer'));
                } else if ($app->attended == 1 && $app->allownewappointments == 0) {
                    $output = organizer_get_img('pix/yes_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'));
                } else if ($app->attended == 0 && $app->allownewappointments == 1) {
                    $output = organizer_get_img('pix/no_reg_24x24.png', '',
                            get_string('reg_status_slot_not_attended_reapp', 'organizer'));
                } else if ($app->attended == 1 && $app->allownewappointments == 1) {
                    $output = organizer_get_img('pix/yes_reg_24x24.png', '',
                            get_string('reg_status_slot_attended_reapp', 'organizer'));
                }
            } else {
                $output = organizer_get_img('pix/student_slot_expired_24x24.png', '',
                        get_string('reg_status_slot_expired', 'organizer'));
            }
        } else {
            $output = organizer_get_img('pix/student_slot_expired_24x24.png', '',
                    get_string('reg_status_slot_expired', 'organizer'));
        }
    } else if ($slotx->is_past_deadline()) {
        $output = organizer_get_img('pix/student_slot_past_deadline_24x24.png', '',
                get_string('reg_status_slot_past_deadline', 'organizer'));
    } else {
        if ($slotx->is_full()) {
            $output = organizer_get_img('pix/student_slot_full_24x24.png', '', get_string('reg_status_slot_full', 'organizer'));
        } else {
            $output = organizer_get_img('pix/student_slot_available_24x24.png', '',
                    get_string('reg_status_slot_available', 'organizer'));
        }
    }

    return $output;
}

function organizer_student_action($params, $slot) {
    global $DB;

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

    $disabled = $myslotpending || $organizerdisabled || $slotdisabled || !$slotx->organizer_user_has_access() || $slotx->is_evaluated();

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
        $action = $ismyslot ? 'unregister' : 'reregister';
    } else {
        $disabled |= $slotfull || !$canregister || $ismyslot;
        $action = $ismyslot ? 'unregister' : 'register';
    }

    if ($ismyslot || organizer_is_my_slot($slotx)) {
        return organizer_get_reg_button($action, $slotx->id, $params, $disabled) . '<br/>'
                . organizer_get_reg_button('comment', $slotx->id, $params, $organizerdisabled || !$slotx->organizer_user_has_access());
    } else {
        return organizer_get_reg_button($action, $slotx->id, $params, $disabled);
    }
}

function organizer_is_my_slot($slot) {
    $apps = organizer_get_all_user_appointments($slot->organizerid);
    foreach ($apps as $app) {
        if ($app->slotid == $slot->id) {
            return true;
        }
    }
    return false;
}

function organizer_get_reg_button($type, $slotid, $params, $disabled = false) {
    global $OUTPUT;

    $actionurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => $type, 'slot' => $slotid));

    return $OUTPUT->single_button($actionurl, get_string("btn_$type", 'organizer'), 'post',
            array('disabled' => $disabled));
}

function organizer_get_status_icon_new($status) {
    switch ($status) {
        case ORGANIZER_APP_STATUS_ATTENDED:
            return organizer_get_img('pix/status_attended_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'));
        case ORGANIZER_APP_STATUS_ATTENDED_REAPP:
            return organizer_get_img('pix/status_attended_reapp_24x24.png', '',
                    get_string('reg_status_slot_attended_reapp', 'organizer'));
        case ORGANIZER_APP_STATUS_PENDING:
            return organizer_get_img('pix/status_pending_24x24.png', '', get_string('reg_status_slot_pending', 'organizer'));
        case ORGANIZER_APP_STATUS_REGISTERED:
            return organizer_get_img('pix/status_not_occured_24x24.png', '', get_string('reg_status_registered', 'organizer'));
        case ORGANIZER_APP_STATUS_NOT_ATTENDED:
            return organizer_get_img('pix/status_not_attended_24x24.png', '',
                    get_string('reg_status_slot_not_attended', 'organizer'));
        case ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP:
            return organizer_get_img('pix/status_not_attended_reapp_24x24.png', '',
                    get_string('reg_status_slot_not_attended_reapp', 'organizer'));
        case ORGANIZER_APP_STATUS_NOT_REGISTERED:
            return organizer_get_img('pix/status_not_registered_24x24.png', '',
                    get_string('reg_status_not_registered', 'organizer'));
        default:
            print_error("Wrong status code: $status");
    }
}

function organizer_figure_out_unit($time) {
    if ($time % 86400 == 0) {
        $out = (($time / 86400) == 1) ? get_string('day', 'organizer') : get_string('day_pl', 'organizer');
        return array($out, 86400);
    } else if ($time % 3600 == 0) {
        $out = (($time / 3600) == 1) ? get_string('hour', 'organizer') : get_string('hour_pl', 'organizer');
        return array($out, 3600);
    } else if ($time % 60 == 0) {
        return array(get_string('min', 'organizer'), 60);
    } else {
        return array(get_string('sec', 'organizer'), 1);
    }
}

function organizer_get_countdown($time) {
    $secsinday = 24 * ($secsinhour = 60 * ($secsinmin = 60));
    $days = intval($time / $secsinday);
    $hrs = intval(($time % $secsinday) / $secsinhour);
    $min = intval(($time % $secsinhour) / $secsinmin);
    $sec = intval($time % $secsinmin);
    return array($days, $hrs, $min, $sec);
}

function organizer_get_user_idnumber($userid) {
    global $DB;
    return $DB->get_field_select('user', 'idnumber', "id = {$userid}");
}

function organizer_popup_icon($type, $content) {
    $icons = array(
            ORGANIZER_ICON_STUDENT_COMMENT => 'pix/feedback2.png',
            ORGANIZER_ICON_TEACHER_COMMENT => 'pix/feedback2.png',
            ORGANIZER_ICON_TEACHER_FEEDBACK => 'pix/feedback.png',
    );
    $iconid = organizer_register_popup($type, $content);
    return organizer_get_img($icons[$type], '', '', $iconid);
}