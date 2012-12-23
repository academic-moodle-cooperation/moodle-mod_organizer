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

require_once('../../config.php');
require_once('../../course/lib.php');
require_once('../../calendar/lib.php');
require_once('lib.php');
require_once('custom_table_renderer.php');
require_once('slotlib.php');
require_once('infobox.php');

function add_calendar() {
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
}

function add_popup() {
    $popup = '<div id="organizer_popup" class="box generalbox"' .
            'style="width: 200px; position: absolute; z-index: 100; left: 0px; top: 0px; display: none; background-color: white;">
    <div id="organizer_popup_title" class="headingblock header outline">
    </div>
    <div id="organizer_popup_content">
    </div>
</div>';
    echo $popup;
}

function generate_appointments_view($params, $instance) {
    global $PAGE;
    $PAGE->requires->js('/mod/organizer/js/checkall.js');

    $output = generate_tab_row($params, $instance->context);
    $output .= make_infobox($params, $instance->organizer, $instance->context);
    $output .= begin_form($params);
    $output .= generate_button_bar($params, $instance->organizer, $instance->context);

    $columns = array('select', 'datetime', 'location', 'participants', 'teacher', 'details');
    $align = array('center', 'left', 'left', 'left', 'left', 'center');
    $sortable = array('datetime', 'location', 'teacher');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = generate_table_header($columns, $sortable, $params, true);
    $table->data = generate_table_content($columns, $params, $instance->organizer, false, false);
    $table->align = $align;

    $output .= render_table_with_footer($table);
    $output .= generate_button_bar($params, $instance->organizer, $instance->context);
    $output .= end_form();

    return $output;
}

function generate_student_view($params, $instance) {
    $output = generate_tab_row($params, $instance->context);
    $output .= make_infobox($params, $instance->organizer, $instance->context);

    $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
    $align = array('left', 'left', 'left', 'left', 'center', 'center');
    $sortable = array('datetime', 'location', 'teacher');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = generate_table_header($columns, $sortable, $params);
    $table->data = generate_table_content($columns, $params, $instance->organizer, false, false);
    $table->align = $align;

    $output .= render_table_with_footer($table);

    return $output;
}

function generate_registration_status_view($params, $instance) {
    $output = generate_tab_row($params, $instance->context);
    $output .= make_infobox($params, $instance->organizer, $instance->context);
    $output .= begin_form($params);

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
    $table->head = generate_reg_table_header($columns, $sortable, $params);
    $table->data = generate_registration_table_content($columns, $params, $instance->organizer, $instance->context);
    $table->align = $align;

    $output .= render_table_with_footer($table);
    $output .= end_form();

    return $output;
}

//------------------------------------------------------------------------------

function begin_form($params) {
    $url = new moodle_url('/mod/organizer/view_action.php');
    $output = '<form name="viewform" action="' . $url->out() . '" method="post">';
    $output .= '<input type="hidden" name="id" value="' . $params['id'] . '" />';
    $output .= '<input type="hidden" name="mode" value="' . $params['mode'] . '" />';
    $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    return $output;
}

function end_form() {
    return '</form>';
}

function generate_tab_row($params, $context) {
    $tabrow = array();

    if (has_capability('mod/organizer:viewallslots', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => TAB_APPOINTMENTS_VIEW));
        $tabrow[] = new tabobject(TAB_APPOINTMENTS_VIEW, $targeturl, get_string('taballapp', 'organizer'));
    }

    if (has_capability('mod/organizer:viewstudentview', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php', array('id' => $params['id'], 'mode' => TAB_STUDENT_VIEW));
        $tabrow[] = new tabobject(TAB_STUDENT_VIEW, $targeturl, get_string('tabstud', 'organizer'));
    }

    if (has_capability('mod/organizer:viewregistrations', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => TAB_REGISTRATION_STATUS_VIEW));
        $tabrow[] = new tabobject(TAB_REGISTRATION_STATUS_VIEW, $targeturl, get_string('tabstatus', 'organizer'));
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

function generate_button_bar($params, $organizer, $context) {
    global $PAGE;
    $PAGE->requires->js('/mod/organizer/js/newwindow.js');

    $actions = array('add', 'edit', 'delete', 'print', 'eval');

    $organizerexpired = isset($organizer->enableuntil) && $organizer->enableuntil - time() < 0;
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

function generate_table_header($columns, $sortable, $params, $usersort = false) {
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
                $columnicon = ' ' . get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
            }

            $viewurl = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir,
                            'psort' => $params['psort'], 'pdir' => $params['pdir']));
            $cell = new html_table_cell(
                    html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon);
        } else if ($column == 'select') {
            $cell = new html_table_cell(
                    html_writer::checkbox('select', null, false, '',
                            array('onclick' => 'checkAll(this);',
                                    'title' => get_string('select_all_slots', 'organizer'))));
        } else if ($column == 'participants' && $usersort) {
            if ($params['psort'] == 'name') {
                $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['psort'] == 'id') {
                $iddir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
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

function generate_reg_table_header($columns, $sortable, $params) {
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
                $columnicon = ' ' . get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
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
                $columnicon = ' ' . get_img($OUTPUT->pix_url('t/' . $columnicon), '', '');
            }
            $viewurl = new moodle_url('/mod/organizer/view.php',
                    array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'group', 'dir' => $columndir));
            if ($params['psort'] == 'name') {
                $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['psort'] == 'id') {
                $iddir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['pdir'] == 'ASC' ? 'up' : 'down')), '', '');
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
                $nameicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['dir'] == 'ASC' ? 'up' : 'down')), '', '');
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['sort'] == 'id') {
                $iddir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = ' ' . get_img($OUTPUT->pix_url('t/' . ($params['dir'] == 'ASC' ? 'up' : 'down')), '', '');
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

function generate_table_content($columns, $params, $organizer, $showonlyregslot = false) {
    global $DB, $USER;

    $translate = array('datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}",
            'teacher' => "lastname {$params['dir']}, firstname {$params['dir']}");

    $order = $translate[$params['sort']];

    $app = get_last_user_appointment($organizer);

    if ($showonlyregslot) {
        if ($app) {
            $evaldapp = get_last_user_appointment($organizer, 0, false, true);
            if ($app->id == $evaldapp->id) {
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

    $showpasttimeslots = get_user_preferences('mod_organizer_showpasttimeslots');
    $showonlymyslots = get_user_preferences('mod_organizer_showmyslotsonly');

    $rows = array();
    if (count($slots) != 0) {
        $numshown = 0;
        foreach ($slots as $slot) {
            $slotx = new organizer_slot($slot);
            if (!$slotx->is_available()) {
                if ($params['mode'] != TAB_STUDENT_VIEW) {
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
                if ($showonlymyslots) {
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

            if (($params['mode'] == TAB_STUDENT_VIEW) && $app && ($app->slotid == $slot->id)) {
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
                        $cell = $row->cells[] = new html_table_cell(date_time($slot));
                        break;
                    case 'location':
                        $cell = $row->cells[] = new html_table_cell(location_link($slot));
                        break;
                    case 'participants':
                        if ($showonlyregslot) {
                            $cell = $row->cells[] = new html_table_cell(get_participant_list_infobox($params, $slot));
                        } else {
                            $cell = $row->cells[] = new html_table_cell(
                                    get_participant_list($params, $slot, $app));
                        }
                        break;
                    case 'teacher':
                        $cell = $row->cells[] = new html_table_cell(teacher_data($params, $slot));
                        break;
                    case 'details':
                        $cell = $row->cells[] = new html_table_cell(slot_status($params, $slot));
                        break;
                    case 'status':
                        $cell = $row->cells[] = new html_table_cell(slot_reg_status($organizer, $slot));
                        break;
                    case 'actions':
                        $cell = $row->cells[] = new html_table_cell(student_action($params, $slot));
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

            $defaultrow->cells[] = get_span_cell(get_string($inforowname, 'organizer'), count($columns));

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
            if ($params['mode'] == TAB_APPOINTMENTS_VIEW) {
                $url = new moodle_url('/mod/organizer/view_action.php',
                        array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'add',
                                'sesskey' => sesskey()));
                $a = new stdClass();
                $a->link = $url->out();
                $message = get_string('no_slots_defined_teacher', 'organizer', $a);
            } else {
                $message = get_string('no_slots_defined', 'organizer');
            }

            $defaultrow->cells[] = get_span_cell($message, count($columns));
            $defaultrow->attributes['class'] = "info no_slots_defined";
        }
    }
    return $rows;
}

function get_span_cell($text, $colspan) {
    $cell = $defaultrow->cells[] = new html_table_cell();
    $cell->colspan = $colspan;
    $cell->style = 'text-align: center; vertical-align: middle;';
    $cell->text = $text;

    return $cell;
}

function get_status_table_entries_group($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = get_course_module_data();

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
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 0 THEN " . APP_STATUS_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 1  THEN "
            . APP_STATUS_ATTENDED_REAPP
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN " . APP_STATUS_PENDING
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN " . APP_STATUS_REGISTERED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 0 THEN " . APP_STATUS_NOT_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 1 THEN "
            . APP_STATUS_NOT_ATTENDED_REAPP . "
            WHEN a2.id IS NULL THEN " . APP_STATUS_NOT_REGISTERED . "
            ELSE " . APP_STATUS_INVALID
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

function get_status_table_entries($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = get_course_module_data();

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
        WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 0 THEN " . APP_STATUS_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 1 AND a2.allownewappointments = 1  THEN "
            . APP_STATUS_ATTENDED_REAPP
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN " . APP_STATUS_PENDING
            . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN " . APP_STATUS_REGISTERED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 0 THEN " . APP_STATUS_NOT_ATTENDED
            . "
            WHEN a2.id IS NOT NULL AND a2.attended = 0 AND a2.allownewappointments = 1 THEN "
            . APP_STATUS_NOT_ATTENDED_REAPP . "
            WHEN a2.id IS NULL THEN " . APP_STATUS_NOT_REGISTERED . "
            ELSE " . APP_STATUS_INVALID
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

function generate_registration_table_content($columns, $params, $organizer, $context) {
    global $DB;

    $groupmode = is_group_mode();

    if ($groupmode) {
        $entries = get_status_table_entries_group($params);
    } else {
        $entries = get_status_table_entries($params);
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
        if ($entry->status == APP_STATUS_INVALID) {
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
                            $idnumber = get_user_idnumber($member);

                            $list .= get_name_link($member) . ($idnumber ? " ($idnumber) " : " ")
                                    . ($entry->comments != '' ? get_img('pix/feedback2.png', '', '', '',
                            'onmouseover="showPopup(event, \'' . get_string('studentcomment_title', 'organizer')
                                    . ':\', \'' . str_replace(array("\n", "\r"), "<br />", $entry->comments) . '\')" onmouseout="hidePopup()"') :
                                            get_img('pix/transparent.png', '', ''));
                            if ($member == $entry->applicantid) {
                                $list .= ' '
                                        . get_img('pix/applicant.gif', 'applicant',
                                                get_string('applicant', 'organizer')) . '<br/>';
                            } else {
                                $list .= ' ' . get_img('pix/transparent.png', '', '') . '<br />';
                            }
                        }
                        $cell = $row->cells[] = new html_table_cell($list);
                    } else {
                        $cell = $row->cells[] = new html_table_cell(
                                get_name_link($entry->id) . ($entry->idnumber ? " ($entry->idnumber)" : ""));
                    }
                    break;
                case 'status':
                    $cell = $row->cells[] = new html_table_cell(get_status_icon_new($entry->status));
                    break;
                case 'datetime':
                    $cell = $row->cells[] = new html_table_cell(date_time($entry));
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
                            $list .= reg_app_details($organizer, $member);
                        }
                        $cell = $row->cells[] = new html_table_cell($list);
                    } else {
                        $cell = $row->cells[] = new html_table_cell(reg_app_details($organizer, $entry->id));
                    }

                    break;
                case 'location':
                    $cell = $row->cells[] = new html_table_cell(location_link($entry));
                    break;
                case 'teacher':
                    $cell = $row->cells[] = new html_table_cell(teacher_data($params, $entry));
                    break;
                case 'actions':
                    $cell = $row->cells[] = new html_table_cell(teacher_action_new($params, $entry, $context));
                    $cell->style .= " text-align: center;";
                    break;
            }

            $cell->style .= ' vertical-align: middle;';
        }
        $rows[] = $row;
    }

    return $rows;
}

function get_participant_entry($entry) {
    $icon = ' ' . (isset($entry->comments) && $entry->comments != '' ? get_img('pix/feedback2.png', '', '', '',
            get_popup(get_string('studentcomment_title', 'organizer'), $entry->comments)) : get_img('pix/transparent.png', '', ''));
    return get_name_link($entry->id) . " {$icon}<br/>({$entry->idnumber})";
}

function app_details($params, $appointment) {
    global $USER;
    if (!isset($appointment)) {
        return '';
    }

    $list = ' ' . ($appointment->comments != '' ? get_img('pix/feedback2.png', '', '', '',
            get_popup(get_string('studentcomment_title', 'organizer'), $appointment->comments)) : get_img('pix/transparent.png', '', ''));
    $list .= ' ' . get_attended_icon($appointment);

    list($cm, $course, $organizer, $context) = get_course_module_data();
    if ($organizer->grade != 0) {
        $list .= ' ' . display_grade($organizer, $appointment->grade); /// OVER HERE
    }

    $list .= ' ' . ($appointment->feedback != '' ? get_img('pix/feedback.png', '', '', '',
            get_popup(get_string('teachercomment_title', 'organizer'), $appointment->feedback)) : get_img('pix/transparent.png', '', ''));

    return $list;
}

function registration_allowed($organizer, $userid = null) {
    $app = get_last_user_appointment($organizer, $userid);
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

function date_time($slot) {
    if (!isset($slot) || !isset($slot->starttime)) {
        return '-';
    }

    $date = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer'));
    $time = userdate($slot->starttime, get_string('timetemplate', 'organizer')) . ' - '
            . userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
    list($unitname, $value) = figure_out_unit($slot->duration);
    $duration = ($slot->duration / $value) . ' ' . $unitname;

    return "$date<br />$time ($duration)";
}

function teacher_data($params, $slot) {
    global $PAGE, $USER;
    $PAGE->requires->js('/mod/organizer/js/popup.js');

    if (!isset($slot) || !isset($slot->teacherid)) {
        return '-';
    }

    global $DB;
    $query = "SELECT a.*
    FROM {organizer_slot_appointments} a
    WHERE a.slotid = :slotid";
    $param = array('slotid' => $slot->id);
    $appointments = $DB->get_records_sql($query, $param);

    $wasownslot = false;
    foreach ($appointments as $someapp) {
        if ($someapp->userid == $USER->id) {
            $wasownslot = true;
            break;
        }
    }

    if ($params['mode'] == TAB_STUDENT_VIEW && !$slot->teachervisible && !$wasownslot) {
        $output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    } else {
        $output = get_name_link($slot->teacherid);
    }

    if (isset($slot->teachercomments)) {
        if ($slot->teachercomments != '') {
            $output .= ' ' . get_img('pix/feedback2.png', '', '', '', get_popup(get_string('teachercomment_title', 'organizer'), $slot->teachercomments));
        }
    } else {
        if ($slot->comments != '') {
            $output .= ' ' . get_img('pix/feedback2.png', '', '', '', get_popup(get_string('teachercomment_title', 'organizer'), $slot->comments));
        }
    }

    if ($params['mode'] != TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output .= '<br /><em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    return $output;
}

function get_popup($title, $content) {
    $title = str_replace(array("\n", "\r"), "<br />", $title);
    $content = str_replace(array("\n", "\r"), "<br />", $content);
    $output = "onmouseover=\"showPopup(event, '{$title}:', '{$content}')\" onmouseout=\"hidePopup()\"";

    return $output;
}

function reg_app_details($organizer, $userid) {
    $appointment = get_last_user_appointment($organizer, $userid, false);
    if ($appointment) {
        $list = '';
        if (is_group_mode()) {
            $list .= get_attended_icon($appointment) . ' ';
        }
        if ($organizer->grade != 0) {
            $list .= display_grade($organizer, $appointment->grade);
            $list .= ' '
                    . (isset($appointment->feedback) && $appointment->feedback != '' ? get_img('pix/feedback.png', '',
                                    '', '', get_popup(get_string('teacherfeedback_title', 'organizer'), $appointment->feedback)) : '');
        }
    } else {
        $list = '-';
    }

    return $list;
}

define('APP_STATUS_INVALID', -1);
define('APP_STATUS_ATTENDED', 0);
define('APP_STATUS_ATTENDED_REAPP', 1);
define('APP_STATUS_PENDING', 2);
define('APP_STATUS_REGISTERED', 3);
define('APP_STATUS_NOT_ATTENDED', 4);
define('APP_STATUS_NOT_ATTENDED_REAPP', 5);
define('APP_STATUS_NOT_REGISTERED', 6);

function teacher_action_new($params, $entry, $context) {
    global $OUTPUT;

    $evalurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'eval', 'slots[]' => $entry->slotid));

    $remindurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'remind', 'user' => $entry->id));

    switch ($entry->status) {
        case APP_STATUS_ATTENDED:
            return $OUTPUT->single_button($evalurl, get_string("btn_reeval", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_ATTENDED_REAPP:
            return $OUTPUT->single_button($evalurl, get_string("btn_reeval", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_PENDING:
            return $OUTPUT->single_button($evalurl, get_string("btn_eval_short", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_REGISTERED:
            return $OUTPUT->single_button($evalurl, get_string("btn_eval_short", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_NOT_ATTENDED:
            return $OUTPUT->single_button($evalurl, get_string("btn_reeval", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_NOT_ATTENDED_REAPP:
            return $OUTPUT->single_button($remindurl, get_string("btn_remind", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:sendreminders', $context, null, true)))
                    . $OUTPUT->single_button($evalurl, get_string("btn_reeval", 'organizer'), 'post',
                            array('disabled' => !has_capability('mod/organizer:evalslots', $context, null, true)));
        case APP_STATUS_NOT_REGISTERED:
            return $OUTPUT->single_button($remindurl, get_string("btn_remind", 'organizer'), 'post',
                    array('disabled' => !has_capability('mod/organizer:sendreminders', $context, null, true)));
        default:
            print_error("Wrong status code: $entry->status");
    }
}

function get_participant_list_infobox($params, $slot, $userid = 0) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_group_mode()) {
        $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
        $firstapp = reset($apps);
        $groupname = $DB->get_field('groups', 'name', array('id' => $firstapp->groupid));
        $output = "<em>{$groupname}</em><br />";
        foreach($apps as $app) {
            $name = get_name_link($app->userid);
            $idnumber = get_user_idnumber($app->userid);
            if ($app->userid == $userid) {
                $output .= $name . ($idnumber ? " ($idnumber) " : " ");
                $output .= ($app->userid == $app->applicantid) ? 
                        get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                $output .= '<div style="float: right;">' . app_details($params, $app, true) .
                        '</div><div style="clear: both;"></div>';
            } else if (!$slot->isanonymous) {
                $output .= $name . ($idnumber ? " ($idnumber) " : " ");
                $output .= ($app->userid == $app->applicantid) ?
                        get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
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
        $name = get_name_link($userid);
        $idnumber = get_user_idnumber($userid);
        $output = $name . ($idnumber ? " ($idnumber) " : " ") . '<div style="float: right;">' .
                app_details($params, $app) .
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
    
    $output .= $slot->isanonymous ? ' ' . get_img('pix/anon.png', 'anonymous', get_string('slot_anonymous', 'organizer')) : '';
    
    return $output;
}

function get_participant_list($params, $slot, $app) {
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
    $groupmode = is_group_mode();

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
    $studentview = $params['mode'] == TAB_STUDENT_VIEW;
    $ismyslot = $isownslot || $wasownslot;
    $groupmode = is_group_mode();
    
    if ($studentview) {
        if ($slot->isanonymous) {
            if ($ismyslot) {
                $idnumber = get_user_idnumber($app->userid);
                $content .= get_name_link($app->userid) .
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
                $idnumber = get_user_idnumber($appointment->userid);
                $content .= get_name_link($appointment->userid) .
                            ($idnumber ? " ($idnumber) " : " ");
                if ($groupmode) {
                    $content .= ' ';
                    $content .= (is_group_mode() && $appointment->userid == $appointment->applicantid) ? get_img(
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
                $idnumber = get_user_idnumber($appointment->userid);
                $list .= '<div style="float: left;">';
                $list .= get_name_link($appointment->userid) . ($idnumber ? " ($idnumber)" : "");
                if ($groupmode) {
                    $list .= ' ';
                    $list .= (is_group_mode() && $appointment->userid == $appointment->applicantid) ? get_img(
                                    'pix/applicant.gif', 'applicant', get_string('applicant', 'organizer')) : '';
                }
                $list .= '</div>';
                $list .= '<div style="float: right;">' . app_details($params, $appointment)
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
            . ($slot->isanonymous ? get_img('pix/anon.png', 'anonymous', get_string('slot_anonymous', 'organizer')) : '');

    return $content;
}

function get_attended_icon($appointment) {
    return (isset($appointment->attended) ? ($appointment->attended == 1 ? ($appointment->allownewappointments ? get_img(
                                    'pix/yes_reg_small.png', '',
                                    get_string('reg_status_slot_attended_reapp', 'organizer'))
                            : get_img('pix/yes_small.png', '', get_string('reg_status_slot_attended', 'organizer')))
                    : ($appointment->allownewappointments ? get_img('pix/no_reg_small.png', '',
                                    get_string('reg_status_slot_not_attended_reapp', 'organizer'))
                            : get_img('pix/no_small.png', '', get_string('reg_status_slot_not_attended', 'organizer'))))
            : get_img('pix/slot_pending_small.png', '', get_string('reg_status_slot_pending', 'organizer')));
}

function location_link($slot) {
    if (!isset($slot) || !isset($slot->location) || $slot->location == '') {
        return get_string('unknown', 'organizer');
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

function get_img($src, $alt, $title, $id = '', $other = '') {
    return '<img src="' . $src . '" alt="' . $alt . '" title="' . $title . '" id="' . $id . '" ' . $other . ' />';
}

function slot_status($params, $slot) {
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
                . get_img('pix/yes_24x24.png', '', get_string('img_title_evaluated', 'organizer')) . '</a>';
    } else if ($slotnoparticipants) {
        return get_img('pix/no_participants_24x24.png', '', get_string('img_title_no_participants', 'organizer'));
    } else if ($slotpending) {
        return '<a href="' . $actionurl->out(false) . '">'
                . get_img('pix/slot_pending_24x24.png', '', get_string('img_title_pending', 'organizer')) . '</a>';
    } else if ($slotgradeable) {
        return '<a href="' . $actionurl->out(false) . '">'
                . get_img('pix/student_slot_past_deadline_24x24.png', '',
                        get_string('img_title_past_deadline', 'organizer')) . '</a>';
    } else if ($slotdueempty) {
        return get_img('pix/student_slot_available_24x24.png', '', get_string('img_title_due', 'organizer'));
    } else if ($slotdue) {
        return '<a href="' . $actionurl->out(false) . '">'
                . get_img('pix/student_slot_available_24x24.png', '', get_string('img_title_due', 'organizer'))
                . '</a>';
    } else {
        print_error("This shouldn't happen.");
    }
}

function slot_reg_status($organizer, $slot) {
    global $DB;

    $slotx = new organizer_slot($slot);

    $app = get_last_user_appointment($organizer);

    if ($slotx->organizer_expired()) {
        $output = get_img('pix/organizer_expired_24x24.png', '',
                get_string('reg_status_organizer_expired', 'organizer'));
    } else if ($slotx->is_past_due()) {
        if ($app) {
            $regslot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            if ($slotx->id == $regslot->id) {
                if (!isset($app->attended)) {
                    $output = get_img('pix/slot_pending_24x24.png', '',
                            get_string('reg_status_slot_pending', 'organizer'));
                } else if ($app->attended == 0 && $app->allownewappointments == 0) {
                    $output = get_img('pix/no_24x24.png', '', get_string('reg_status_slot_not_attended', 'organizer'));
                } else if ($app->attended == 1 && $app->allownewappointments == 0) {
                    $output = get_img('pix/yes_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'));
                } else if ($app->attended == 0 && $app->allownewappointments == 1) {
                    $output = get_img('pix/no_reg_24x24.png', '',
                            get_string('reg_status_slot_not_attended_reapp', 'organizer'));
                } else if ($app->attended == 1 && $app->allownewappointments == 1) {
                    $output = get_img('pix/yes_reg_24x24.png', '',
                            get_string('reg_status_slot_attended_reapp', 'organizer'));
                }
            } else {
                $output = get_img('pix/student_slot_expired_24x24.png', '',
                        get_string('reg_status_slot_expired', 'organizer'));
            }
        } else {
            $output = get_img('pix/student_slot_expired_24x24.png', '',
                    get_string('reg_status_slot_expired', 'organizer'));
        }
    } else if ($slotx->is_past_deadline()) {
        $output = get_img('pix/student_slot_past_deadline_24x24.png', '',
                get_string('reg_status_slot_past_deadline', 'organizer'));
    } else {
        if ($slotx->is_full()) {
            $output = get_img('pix/student_slot_full_24x24.png', '', get_string('reg_status_slot_full', 'organizer'));
        } else {
            $output = get_img('pix/student_slot_available_24x24.png', '',
                    get_string('reg_status_slot_available', 'organizer'));
        }
    }

    return $output;
}

function student_action($params, $slot) {
    global $DB;

    $slotx = new organizer_slot($slot);

    list($cm, $course, $organizer, $context) = get_course_module_data();

    $canregister = has_capability('mod/organizer:register', $context, null, false);
    $canunregister = has_capability('mod/organizer:unregister', $context, null, false);
    $canreregister = $canregister && $canunregister;

    $myapp = get_last_user_appointment($organizer);
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
    $didnotattend = isset($myapp->attended) && $myapp->attended == 0;

    $disabled = $myslotpending || $organizerdisabled || $slotdisabled || !$slotx->user_has_access() || $slotx->is_evaluated();

    if ($myslotexists && !$didnotattend) {
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

    if ($ismyslot || is_my_slot($slotx)) {
        return get_reg_button($action, $slotx->id, $params, $disabled) . '<br/>'
                . get_reg_button('comment', $slotx->id, $params, $organizerdisabled || !$slotx->user_has_access());
    } else {
        return get_reg_button($action, $slotx->id, $params, $disabled);
    }
}

function is_my_slot($slot) {
    $apps = get_all_user_appointments($slot->organizerid);
    foreach ($apps as $app) {
        if ($app->slotid == $slot->id) {
            return true;
        }
    }
    return false;
}

function get_reg_button($type, $slotid, $params, $disabled = false) {
    global $OUTPUT;

    $actionurl = new moodle_url('/mod/organizer/view_action.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'action' => $type, 'slot' => $slotid));

    return $OUTPUT->single_button($actionurl, get_string("btn_$type", 'organizer'), 'post',
            array('disabled' => $disabled));
}

function get_status_icon_new($status) {
    switch ($status) {
        case APP_STATUS_ATTENDED:
            return get_img('pix/status_attended_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'));
        case APP_STATUS_ATTENDED_REAPP:
            return get_img('pix/status_attended_reapp_24x24.png', '',
                    get_string('reg_status_slot_attended_reapp', 'organizer'));
        case APP_STATUS_PENDING:
            return get_img('pix/status_pending_24x24.png', '', get_string('reg_status_slot_pending', 'organizer'));
        case APP_STATUS_REGISTERED:
            return get_img('pix/status_not_occured_24x24.png', '', get_string('reg_status_registered', 'organizer'));
        case APP_STATUS_NOT_ATTENDED:
            return get_img('pix/status_not_attended_24x24.png', '',
                    get_string('reg_status_slot_not_attended', 'organizer'));
        case APP_STATUS_NOT_ATTENDED_REAPP:
            return get_img('pix/status_not_attended_reapp_24x24.png', '',
                    get_string('reg_status_slot_not_attended_reapp', 'organizer'));
        case APP_STATUS_NOT_REGISTERED:
            return get_img('pix/status_not_registered_24x24.png', '',
                    get_string('reg_status_not_registered', 'organizer'));
        default:
            print_error("Wrong status code: $status");
    }
}

function figure_out_unit($time) {
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

function get_countdown($time) {
    $secsinday = 24 * ($secsinhour = 60 * ($secsinmin = 60));
    $days = intval($time / $secsinday);
    $hrs = intval(($time % $secsinday) / $secsinhour);
    $min = intval(($time % $secsinhour) / $secsinmin);
    $sec = intval($time % $secsinmin);
    return array($days, $hrs, $min, $sec);
}

function get_user_idnumber($userid) {
    global $DB;
    return $DB->get_field_select('user', 'idnumber', "id = {$userid}");
}