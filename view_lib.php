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
 * view_lib.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

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
require_once(dirname(__FILE__) . '/infobox.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

function organizer_display_form(moodleform $mform, $title) {
    global $OUTPUT;

    if (organizer_fetch_hidecalendar() != 1) {
        organizer_add_calendar();
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    echo $OUTPUT->box_start('', 'organizer_main_cointainer');
    $mform->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

    die();
}

function organizer_add_calendar() {
    global $PAGE, $DB, $CFG;

    $courseid = optional_param('course', SITEID, PARAM_INT);

    if ($courseid != SITEID && !empty($courseid)) {
        $course = $DB->get_record('course', array('id' => $courseid));
        $courses = array($course->id => $course);
    } else {
        $course = get_site();
        $courses = calendar_get_default_courses();
    }

    $now = usergetdate(time());

    $calendar = new calendar_information($now['mday'], $now['mon'], $now['year']);
    if ($CFG->branch > 33) {
        $calendar->set_sources($course, $courses);
    } else {
        $calendar->prepare_for_view($course, $courses);
    }
    $renderer = $PAGE->get_renderer('core_calendar');
    $calendar->add_sidecalendar_blocks($renderer, true, 'month');
}

function organizer_generate_appointments_view($params, $instance) {
    global $PAGE;
    $PAGE->requires->js_call_amd('mod_organizer/initcheckboxes', 'init', array(false));

    $organizerexpired = isset($instance->organizer->duedate) && $instance->organizer->duedate - time() < 0;

    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_begin_form($params);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context, $organizerexpired);

    $columns = array('select', 'singleslotcommands', 'datetime', 'location', 'participants', 'teacher', 'details');
    $align = array('center', 'center', 'left', 'left', 'left', 'left', 'center');
    $sortable = array('datetime', 'location');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_table_header($columns, $sortable, $params);
    $table->data = organizer_generate_table_content($columns, $params, $instance->organizer);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);
    $output .= organizer_generate_actionlink_bar($instance->context, $organizerexpired);
    $output .= organizer_end_form();

    return $output;
}

function organizer_generate_student_view($params, $instance) {
    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    if (time() > $instance->organizer->allowregistrationsfromdate ) {
        $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
        $align = array('left', 'left', 'left', 'left', 'center', 'center');
        $sortable = array('datetime', 'location');

        $table = new html_table();
        $table->id = 'slot_overview';
        $table->attributes['class'] = 'generaltable boxaligncenter overview';
        $table->head = organizer_generate_table_header($columns, $sortable, $params);
        $table->data = organizer_generate_table_content($columns, $params, $instance->organizer);
        $table->align = $align;

        $output .= organizer_render_table_with_footer($table);
    } else {
        if ($instance->organizer->alwaysshowdescription) {
            $message = get_string(
                'allowsubmissionsfromdatesummary', 'organizer',
                userdate($instance->organizer->allowregistrationsfromdate)
            );
        } else {
            $message = get_string(
                'allowsubmissionsanddescriptionfromdatesummary', 'organizer',
                userdate($instance->organizer->allowregistrationsfromdate)
            );
        }
        $output .= html_writer::div($message, '', array('id' => 'intro'));
    }

    return $output;
}

function organizer_generate_registration_status_view($params, $instance) {
    $output = organizer_generate_tab_row($params, $instance->context);

    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    $columns = array('status');

    if ($instance->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $columns[] = 'group';
        $columns[] = 'participants';
        $columns[] = 'appdetails';
    } else {
        $columns[] = 'participants';
        $columns[] = 'appdetails';
    }

    $columns = array_merge($columns, array('bookings', 'datetime', 'location', 'teacher', 'actions'));

    $align = array('center', 'left', 'center', 'left', 'left', 'left', 'center');
    $sortable = array('status', 'group');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_reg_table_header($columns, $sortable, $params);
    $table->data = organizer_organizer_generate_registration_table_content(
        $columns, $params, $instance->organizer, $instance->context
    );
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);

    return $output;
}

function organizer_generate_assignment_view($params, $instance) {

    if ($instance->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $content = get_string('availableslotsfor', 'organizer') .' <strong>' .
                organizer_fetch_groupname($params['assignid']) . '</strong>';
    } else {
        $content = get_string('availableslotsfor', 'organizer') .' <strong>' .
                organizer_get_name_link($params['assignid']) . '</strong>';
    }
    $output = organizer_make_section('assign', $content);

    $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
    $align = array('left', 'left', 'left', 'left', 'center', 'center');
    $sortable = array('datetime', 'location');

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_table_header($columns, $sortable, $params);
    $table->data = organizer_generate_assignment_table_content($columns, $params, $instance->organizer);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);

    return $output;
}

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
        $targeturl = new moodle_url(
            '/mod/organizer/view.php',
            array('id' => $params['id'], 'mode' => ORGANIZER_TAB_APPOINTMENTS_VIEW)
        );
        $tabrow[] = new tabobject(ORGANIZER_TAB_APPOINTMENTS_VIEW, $targeturl, get_string('taballapp', 'organizer'));
    }

    if (has_capability('mod/organizer:viewregistrations', $context, null, true)) {
        $targeturl = new moodle_url(
            '/mod/organizer/view.php',
            array('id' => $params['id'], 'mode' => ORGANIZER_TAB_REGISTRATION_STATUS_VIEW)
        );
        $tabrow[] = new tabobject(ORGANIZER_TAB_REGISTRATION_STATUS_VIEW, $targeturl, get_string('tabstatus', 'organizer'));
    }

    if (has_capability('mod/organizer:viewstudentview', $context, null, true)) {
        $targeturl = new moodle_url('/mod/organizer/view.php', array('id' => $params['id'], 'mode' => ORGANIZER_TAB_STUDENT_VIEW));
        $tabrow[] = new tabobject(ORGANIZER_TAB_STUDENT_VIEW, $targeturl, get_string('tabstud', 'organizer'));
    }

    if (count($tabrow) > 1) {
        $tabs = array($tabrow);
        $output = print_tabs($tabs, $params['mode'], null, null, true);
        $output = preg_replace('/<div class="tabtree">/', '<div class="tabtree" style="margin-bottom: 0em;">', $output);
        return $output;
    } else {
        return ''; // If only one tab is enabled, hide the tab row altogether.
    }
}

function organizer_generate_actionlink_bar($context, $organizerexpired) {

    $output = '<div name="actionlink_bar" class="buttons mdl-align">';

    $output .= html_writer::span(get_string('selectedslots', 'organizer'));

    $actions = [];
    if (has_capability("mod/organizer:editslots", $context, null, true) && !$organizerexpired) {
        $actions['edit'] = get_string('actionlink_edit', 'organizer');
    }
    if (has_capability("mod/organizer:deleteslots", $context, null, true) && !$organizerexpired) {
        $actions['delete'] = get_string('actionlink_delete', 'organizer');
    }
    if (has_capability("mod/organizer:printslots", $context, null, true)) {
        $actions['print'] = get_string('actionlink_print', 'organizer');
    }
    if (has_capability("mod/organizer:evalslots", $context, null, true)) {
        $actions['eval'] = get_string('actionlink_eval', 'organizer');
    }

    $output .= html_writer::select(
        $actions, 'bulkaction', array('edit' => get_string('actionlink_edit', 'organizer')), null,
        array('style' => 'margin-left:0.3em;margin-right:0.3em;')
    );
    $output .= '<input type="submit" class="btn btn-primary" value="' . get_string('btn_start', 'organizer') . '"/>';

    $output .= '</div>';

    return $output;
}


function organizer_generate_table_header($columns, $sortable, $params, $usersort = false) {
    global $OUTPUT;

    $header = array();
    foreach ($columns as $column) {
        $columnhelpicon = $OUTPUT->help_icon($column, 'organizer', '');
        if (in_array($column, $sortable)) {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $columnicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = ' ' . $OUTPUT->pix_icon('t/' . $columnicon, get_string($columnicon));
            }
            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir,
                'psort' => $params['psort'], 'pdir' => $params['pdir'])
            );
            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon . $columnhelpicon);
        } else if ($column == 'select') {
            $cell = new html_table_cell(
                html_writer::checkbox(
                    'select', null, false, '',
                    array('title' => get_string('select_all_slots', 'organizer'))
                )
            );
        } else if ($column == 'singleslotcommands') {
            $cell = new html_table_cell(get_string("th_actions", 'organizer') . $columnhelpicon);
        } else if ($column == 'participants' && $usersort) {
            $cell = organizer_get_participants_tableheadercell($params, $column, $columnhelpicon);
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer') . $columnhelpicon);
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
                $columnicon = ' ' . $OUTPUT->pix_icon('t/' . $columnicon, get_string($columnicon));
            }

            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir)
            );
            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon
            );
        } else if ($column == 'group') {

            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $columnicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = ' ' . $OUTPUT->pix_icon('t/' . $columnicon, get_string($columnicon));
            }
            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'group', 'dir' => $columndir)
            );
            if ($params['sort'] == 'name') {
                $namedir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $nameicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $nameicon = ' ' . $OUTPUT->pix_icon('t/' . $nameicon, get_string($nameicon));
            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['sort'] == 'id') {
                $iddir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $idicon = $params['dir'] == 'ASC' ? 'up' : 'down';
                $idicon = ' ' . $OUTPUT->pix_icon('t/' . $idicon, get_string($idicon));
            } else {
                $iddir = 'ASC';
                $idicon = '';
            }

            $urln = new moodle_url(
                '/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'name',
                'dir' => $namedir)
            );
            $urli = new moodle_url(
                '/mod/organizer/view.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'id',
                'dir' => $iddir)
            );
            $links = "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/"
                    . html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon . " " . $links
            );
        } else if ($column == 'participants') {
            $columnhelpicon = "";
            $cell = organizer_get_participants_tableheadercell($params, $column, $columnhelpicon);
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer'));
        }
        $cell->header = true;
        $cell->style = 'text-align: center; vertical-align: middle;';
        $header[] = $cell;
    }
    return $header;
}

function organizer_generate_table_content($columns, $params, $organizer, $onlyownslots = false) {
    global $DB, $USER;

    $translate = array('datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}",
            'teacher' => "lastname {$params['dir']}, firstname {$params['dir']}");

    $order = $translate[$params['sort']];

    $apps = organizer_get_all_user_appointments($organizer);
    $userslots = array_column($apps, 'slotid', 'id');

    $sqlparams = array('organizerid' => $organizer->id);
    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW) {
        $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid ORDER BY $order";
    } else {
        $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid AND s.visible = 1 ORDER BY $order";
    }
    $slots = $DB->get_records_sql($query, $sqlparams);

    $showpasttimeslots = false;
    $showonlymyslots = false;
    $showonlyfreeslots = false;
    $showpastslots = false;
    $showmyslotsonly = false;

    $onlyownslotsmsg = "";

    $rows = array();
    if (count($slots) != 0) {
        $numshown = 0;
        foreach ($slots as $slot) {
            if ($isuserslot = array_search($slot->id, $userslots)) {
                $app = $apps[$isuserslot];
                if ($onlyownslots) {
                    $onlyownslotsmsg = "";
                    if ($slot->starttime - $organizer->relativedeadline - time() > 0) {
                        $a = new stdClass();
                        list($a->days, $a->hours, $a->minutes, $a->seconds) = organizer_get_countdown(
                            $slot->starttime - $organizer->relativedeadline - time());
                        $class = $a->days > 1 ? "countdown_normal" : ($a->hours > 1 ? "countdown_hurry" : "countdown_critical");
                        $onlyownslotsmsg .= "<div><span class=\"$class\">".
                            get_string('infobox_deadline_countdown', 'organizer', $a).'</span></div>';
                    } else {
                        $onlyownslotsmsg .= '<div><span class="countdown_passed">'.
                            get_string('infobox_deadline_passed', 'organizer').'</span></div>';
                    }
                    if ($slot->starttime - time() > 0) {
                        $a = new stdClass();
                        list($a->days, $a->hours, $a->minutes, $a->seconds) = organizer_get_countdown($slot->starttime - time());
                        $class = $a->days > 1 ? "countdown_normal" : ($a->hours > 1 ? "countdown_hurry" : "countdown_critical");
                        $onlyownslotsmsg .= "<div><span class=\"$class\">".
                            get_string('infobox_app_countdown', 'organizer', $a).'</span></div>';
                    } else {
                        $onlyownslotsmsg .= '<div><span class="countdown_passed">'.
                            get_string('infobox_app_occured', 'organizer').'</span></div>';
                    }
                }
            } else {
                if ($onlyownslots) {
                    continue;
                } else {
                    $app = null;
                }
            }
            $slotx = new organizer_slot($slot);
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                      $alreadyinqueue = $slotx->is_group_in_queue();
            } else {
                      $alreadyinqueue = $slotx->is_user_in_queue($USER->id);
            }
            if (!$slotx->is_available()) {
                if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $isuserslot) {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = 'unavailable';
                } else {
                    continue; // Slot isn't available yet.
                }
            } else {
                if ($organizer->queue && $alreadyinqueue) {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = 'queueing';
                } else {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = '';
                }
            }
            if ($slotx->has_participants()) {
                $row->attributes['class'] .= 'registered';
            } else {
                $row->attributes['class'] .= 'not_registered';
            }
            $slotpastdue = $slotx->is_past_due();
            $myslotastrainer = false;
            if ($trainerids = organizer_get_slot_trainers($slotx->id)) {
                if (in_array($USER->id, $trainerids)) {
                    $myslotastrainer = true;
                }
            }
            $slotvisible = $slot->visible;
            $hidden = false;
            if ($slotpastdue) {
                $row->attributes['class'] .= ' past_due';
                if (!$showpasttimeslots && !$onlyownslots) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
            } else {
                $row->attributes['class'] .= ' not_past_due';
            }
            if ($myslotastrainer) {
                $row->attributes['class'] .= ' my_slot';
            } else {
                if ($showonlymyslots && $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_my_slot';
            }
            if (!$slotx->is_full()) {
                $row->attributes['class'] .= ' free_slot';
            } else {
                if ($showonlyfreeslots) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_free_slot';
            }
            if (!$slotvisible && $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                $row->attributes['class'] .= ' unavailable';
            } else {
                if (!$slotvisible && $params['mode'] == ORGANIZER_TAB_STUDENT_VIEW) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_unavailable';
            }
            if (!$hidden) {
                $numshown++;
            }
            if (array_search($slot->id, $params['slots']) !== false) {
                $row->attributes['class'] .= ' affected_slot';
            }
            if (($params['mode'] == ORGANIZER_TAB_STUDENT_VIEW) && $isuserslot) {
                $row->attributes['class'] .= ' registered_slot';
            }
            foreach ($columns as $column) {
                switch ($column) {
                    case 'select':
                        $cell = $row->cells[] = new html_table_cell(
                        html_writer::checkbox('slots[]', $slot->id, false, '',
                            array('class' => 'checkbox_slot')
                        )
                            );
                    break;
                    case 'singleslotcommands':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_commands($slot->id, $params));
                    break;
                    case 'datetime':
                        $cell = $row->cells[] = new html_table_cell(organizer_date_time($slot));
                    break;
                    case 'location':
                        $cell = $row->cells[] = new html_table_cell(organizer_location_link($slot));
                    break;
                    case 'participants':
                        $cell = $row->cells[] = new html_table_cell(
                                organizer_get_participant_list($params, $slot, $app)
                        );
                    break;
                    case 'teacher':
                        $cell = $row->cells[] = new html_table_cell(organizer_trainer_data($params, $slot, $trainerids));
                    break;
                    case 'details':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_status($params, $slot));
                    break;
                    case 'status':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_reg_status($organizer, $slot, $onlyownslotsmsg));
                    break;
                    case 'actions':
                        $cell = $row->cells[] = new html_table_cell(organizer_participants_action($params, $slot));
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
            $defaultrow->cells[] = organizer_get_span_cell(get_string($inforowname, 'organizer'), count($columns));
            $defaultrow->style = '';
            if ($numshown == 0) {
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
        }
    } else {
        $defaultrow = $rows[] = new html_table_row();
        if ($params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
            $url = new moodle_url('/mod/organizer/slots_add.php', array('id' => $params['id']));
            $a = new stdClass();
            $a->link = $url->out();
            $message = get_string('no_slots_defined_teacher', 'organizer', $a);
        } else {
            $message = get_string('no_slots_defined', 'organizer');
        }
        $defaultrow->cells[] = organizer_get_span_cell($message, count($columns));
        $defaultrow->attributes['class'] = "info no_slots_defined";
    }
    return $rows;
}

function organizer_get_span_cell($text, $colspan) {
    $cell = new html_table_cell();
    $cell->colspan = $colspan;
    $cell->style = 'text-align: center; vertical-align: middle;';
    $cell->text = $text;

    return $cell;
}

function organizer_organizer_organizer_get_status_table_entries_group($params) {
    global $DB;
    list($cm, , $organizer, ) = organizer_get_course_module_data();

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
    } else if ($params['sort'] == 'datetime') {
        $orderby = "ORDER BY a2.starttime $dir";
    } else if ($params['sort'] == 'name') {
        $orderby = "ORDER BY g.name $dir";
    } else if ($params['sort'] == 'id') {
        $orderby = "ORDER BY g.id $dir";
    } else {
        $orderby = "ORDER BY g.name ASC, status ASC";
    }

    $par = array('now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id);
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        g.id,
        g.name,
        a2.id AS appid,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1
                THEN " . ORGANIZER_APP_STATUS_ATTENDED . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1
                THEN " . ORGANIZER_APP_STATUS_PENDING . "
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2
                THEN " . ORGANIZER_APP_STATUS_REGISTERED . "
            WHEN a2.id IS NULL
                THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID . "
        END AS status,
        a2.starttime,
        a2.duration,
        a2.location,
        a2.locationlink,
        a2.applicantid,
        a2.teachercomments,
        a2.comments,
        a2.teachervisible,
        a2.slotid,
        a2.allownewappointments,
        a2.teacherapplicantid,
        a2.teacherapplicanttimemodified
        FROM {groups} g
        LEFT JOIN
        (
            SELECT
            a.id,
            a.groupid,
            a.allownewappointments,
            s.id as slotid,
            s.starttime,
            s.location,
            s.locationlink,
            s.teachervisible,
            s.duration,
            a.applicantid,
            a.comments,
            s.comments AS teachercomments,
            a.teacherapplicantid,
            a.teacherapplicanttimemodified,
            (
                SELECT MAX(a3.attended)
                FROM {organizer_slot_appointments} a3
                WHERE a3.groupid = a.groupid
                GROUP BY a3.slotid
                ORDER BY a3.slotid DESC
                LIMIT 1
            ) AS attended
            FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid
            ORDER BY a.id DESC
        ) a2 ON g.id = a2.groupid
        WHERE g.id $insql
        $orderby";

    $rs = $DB->get_recordset_sql($query, $par);

    return $rs;
}

/**
 * Returns all the participants of this organizer instance including data which indicates the booking status.
 *
 * @param $params ... the sort and dir parameter of these parameters is used here
 * @return array|moodle_recordset   ... one record for each participant
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_organizer_get_status_table_entries($params) {
    global $DB;
    list($cm, , $organizer, $context) = organizer_get_course_module_data();

    $studentids = array();

    if ($organizer->isgrouporganizer != ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $students = get_enrolled_users($context, 'mod/organizer:register');
        foreach ($students as $student) {
            $studentids[] = $student->id;
        }
    } else if ($cm->groupingid != 0) {
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
    } else if ($params['sort'] == 'participant') {
        $orderby = "ORDER BY u.lastname $dir, u.firstname ASC, status ASC, u.idnumber ASC";
    } else {
        $orderby = "ORDER BY u.lastname ASC, u.firstname ASC, status ASC, u.idnumber ASC";
    }

    $par = array('now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id);
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        u.id,
        u.firstname,
        u.lastname,
        u.idnumber,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1
                THEN " . ORGANIZER_APP_STATUS_ATTENDED . "
            WHEN a2.id IS NOT NULL AND NULLIF(a2.attended, 0) IS NULL AND a2.starttime <= :now1
                THEN " . ORGANIZER_APP_STATUS_PENDING . "
            WHEN a2.id IS NOT NULL AND NULLIF(a2.attended, 0) IS NULL AND a2.starttime > :now2
                THEN " . ORGANIZER_APP_STATUS_REGISTERED . "
            WHEN a2.id IS NULL
                THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID . "
        END AS status,
        a2.starttime,
        a2.duration,
        a2.attended,
        a2.location,
        a2.locationlink,
        a2.grade,
        a2.comments,
        a2.teachercomments,
        a2.feedback,
        a2.userid,
        a2.teachervisible,
        a2.slotid,
        a2.id AS appid,
        a2.teacherapplicantid,
        a2.teacherapplicanttimemodified
        FROM {user} u
        LEFT JOIN
        (
            SELECT
                a.id,
                a.attended,
                a.grade,
                a.feedback,
                a.comments,
                a.userid,
                a.allownewappointments,
                s.starttime,
                s.location,
                s.locationlink,
                s.comments AS teachercomments,
                s.duration,
                s.teachervisible,
                s.id AS slotid,
                a.teacherapplicantid,
                a.teacherapplicanttimemodified
            FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid
            ORDER BY a.id DESC
        ) a2 ON u.id = a2.userid
        WHERE u.id $insql
        GROUP BY u.id, a2.id, u.firstname, u.lastname, u.idnumber, status,
        a2.starttime, a2.duration, a2.attended, a2.location, a2.locationlink,
        a2.grade, a2.comments, a2.teachercomments, a2.feedback,
        a2.userid, a2.teachervisible, a2.slotid, a2.allownewappointments,
        a2.teacherapplicantid, a2.teacherapplicanttimemodified
        $orderby";

    $rs = $DB->get_recordset_sql($query, $par);

    return $rs;
}

/**
 * Returns the table rows of the registration view entries.
 *
 * @param $columns ... table columns to show
 * @param $params
 * @param $organizer  ... organizer instance data
 * @param $context    ... context data
 * @return array      ... html table rows, each for each entry
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_organizer_generate_registration_table_content($columns, $params, $organizer, $context) {
    global $DB;

    $groupmode = $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS;

    $entries = organizer_get_registrationview_entries($groupmode, $params);

    if ($entries) {
        if ($entries->valid()) {
            $rows = array();
            $queueable = organizer_is_queueable();

            if ($groupmode) {
                $slotswitch = "";
                $groupswitch = "";
                foreach ($entries as $entry) {
                    if ($entry->status == ORGANIZER_APP_STATUS_INVALID) {
                        continue;
                    }
                    if ($slotswitch != $entry->slotid or $groupswitch != $entry->id) {
                        $slotswitch = $entry->slotid;
                        $groupswitch = $entry->id;
                        $row = new html_table_row();

                        if ($entry->starttime) {
                            $row->attributes['class'] = 'registered';
                        }
                        foreach ($columns as $column) {
                            switch ($column) {
                                case 'group':
                                    $list = $entry->name;
                                    if ($entry->starttime) {
                                        $list .= organizer_get_teacherapplicant_output(
                                            $entry->teacherapplicantid,
                                            $entry->teacherapplicanttimemodified
                                        );
                                    }
                                    $cell = $row->cells[] = new html_table_cell($list);
                                    break;
                                case 'participants':
                                    if ($params['psort'] == 'id') {
                                        $orderby = "ORDER BY idnumber {$params['pdir']}, lastname ASC, firstname ASC";
                                    } else {
                                        $orderby = "ORDER BY lastname {$params['pdir']}, firstname {$params['pdir']}, idnumber ASC";
                                    }
                                    $members = $DB->get_fieldset_sql(
                                        'SELECT userid FROM {groups_members} gm
                                        INNER JOIN {user} u ON gm.userid = u.id WHERE groupid = :groupid '.
                                        $orderby, array('groupid' => $entry->id)
                                    );
                                    $list = "<span style='display:table'>";
                                    foreach ($members as $member) {
                                        $list .= "<span style='display:table-row'>";
                                        $identity = organizer_get_user_identity($member);
                                        $identity = $identity != "" ? " ({$identity})" : "";
                                        $list .= "<span style='display:table-cell'>" . organizer_get_name_link($member) . $identity;
                                        if ($entry->starttime) {
                                            if ($member == $entry->applicantid) {
                                                $list .= organizer_get_img(
                                                    'pix/applicant.gif', 'applicant',
                                                    get_string('applicant', 'organizer')
                                                );
                                            } else {
                                                $list .= " ";
                                            }
                                        }
                                        $list .= "</span>";
                                        if ($queueable) {
                                            $list .= "<span style='display:table-cell'>" .
                                                organizer_reg_waitinglist_status($organizer->id, $groupmode, $member)
                                                . "</span>";
                                        }
                                        $list .= "<span style='display:table-cell'>" .
                                            organizer_reg_organizer_app_details($organizer, $groupmode, $entry->appid)
                                            . "</span>";
                                        $list .= "</span>";
                                    }
                                    $list .= "</span>";
                                    $cell = $row->cells[] = new html_table_cell($list);
                                    $cell->style .= " text-align: left;";
                                    break;
                                case 'status':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(organizer_get_status_icon_new($entry->status));
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell(
                                            organizer_get_status_icon_new(ORGANIZER_APP_STATUS_NOT_REGISTERED));
                                    }
                                    $cell->style .= " text-align: center;";
                                    break;
                                case 'bookings':
                                    $cell = $row->cells[] = new html_table_cell(
                                        "#".organizer_count_bookedslots($organizer->id, null, $entry->id));
                                    $cell->style .= " text-align: center;";
                                    break;
                                case 'datetime':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(organizer_date_time($entry));
                                        $cell->style .= " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                    break;
                                case 'appdetails':
                                    $cell = $row->cells[] = new html_table_cell('-');
                                    $cell->style .= " text-align: center;";
                                    break;
                                case 'location':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(organizer_location_link($entry));
                                        $cell->style .= " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                    break;
                                case 'teacher':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(
                                            organizer_trainer_data($params, $entry, organizer_get_slot_trainers($entry->slotid)));
                                        $cell->style .= " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                    break;
                                case 'actions':
                                    $cell = $row->cells[] = new html_table_cell(
                                        organizer_teacher_action($params, $entry, $context, $organizer, $groupmode)
                                    );
                                    $cell->style .= " text-align: center;";
                                    break;
                            }
                            $cell->style .= ' vertical-align: middle;';
                        }  // Foreach column.
                        $rows[] = $row;
                    } else {  // Slotswitch and groupswitch.
                        continue;
                    }// Slotswitch and groupswitch.
                }  // Foreach entry.
            } else {  // No groupmode.
                foreach ($entries as $entry) {
                    $row = new html_table_row();
                    if ($entry->starttime) {
                        $row->attributes['class'] = 'registered';
                    }

                    foreach ($columns as $column) {
                        switch ($column) {
                            case 'group':
                            case 'participants':
                                $identity = organizer_get_user_identity($entry);
                                $identity = $identity != "" ? " ({$identity})" : "";
                                $cell = $row->cells[] = new html_table_cell(
                                    organizer_get_name_link($entry->id) . $identity .
                                    organizer_get_teacherapplicant_output($entry->teacherapplicantid,
                                        $entry->teacherapplicanttimemodified));
                                break;
                            case 'status':
                                $cell = $row->cells[] = new html_table_cell(organizer_get_status_icon_new($entry->status));
                                break;
                            case 'bookings':
                                $cell = $row->cells[] = new html_table_cell(
                                        "#".organizer_count_bookedslots($organizer->id, $entry->id));
                                $cell->style .= " text-align: center;";
                                break;
                            case 'datetime':
                                $text = organizer_date_time($entry);
                                if ($text != "-") {
                                    $cell = $row->cells[] = new html_table_cell($text);
                                    $cell->style .= " text-align: left;";
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'appdetails':
                                if ($queueable) {
                                    $outcell = organizer_reg_waitinglist_status($organizer->id, $groupmode);
                                } else {
                                    $outcell = '';
                                }
                                $outcell .= organizer_reg_organizer_app_details($organizer, $groupmode, $entry->appid);
                                $outcell = trim($outcell);
                                if ($outcell != "-" && $outcell != "") {
                                    $cell = $row->cells[] = new html_table_cell($outcell);
                                    $cell->style = " text-align: left;";
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'location':
                                $text = organizer_location_link($entry);
                                if ($text != "-") {
                                    $cell = $row->cells[] = new html_table_cell($text);
                                    $cell->style = " text-align: left;";
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'teacher':
                                if ($entry->slotid) {
                                    $trainerdata = organizer_trainer_data($params, $entry,
                                        organizer_get_slot_trainers($entry->slotid));
                                    if ($trainerdata != "-") {
                                        $cell = $row->cells[] = new html_table_cell($trainerdata);
                                        $cell->style = " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell("-");
                                        $cell->style = " text-align: center;";
                                    }
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'actions':
                                $cell = $row->cells[] = new html_table_cell(
                                    organizer_teacher_action($params, $entry, $context, $organizer, $groupmode));
                                $cell->style .= " text-align: center;";
                                break;
                        }

                        $cell->style .= ' vertical-align: middle;';
                    }
                    $rows[] = $row;
                }  // Foreach entry.
            } // Groupmode or not.
            $entries->close();
        } else {
            $row = new html_table_row();
            $cell = $row->cells[] = new html_table_cell(get_string('novalidparticipants', 'organizer'));
            $cell->colspan = count($columns);
            $rows[] = $row;
        }
    } else {  // No entries - course has no participants.
        $row = new html_table_row();
        $cell = $row->cells[] = new html_table_cell(get_string('noparticipants', 'organizer'));
        $cell->colspan = count($columns);
        $rows[] = $row;
    }
    return $rows;
}

function organizer_generate_assignment_table_content($columns, $params, $organizer, $redirecturl = null) {
    global $DB;

    $translate = array('datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}");

    $order = $translate[$params['sort']];
    $assignid = $params['assignid'];

    $sqlparams = array('organizerid' => $organizer->id);
    $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid AND s.visible = 1 ORDER BY $order";
    $slots = $DB->get_records_sql($query, $sqlparams);

    $rows = array();
    if (count($slots) != 0) {
        $numshown = 0;
        foreach ($slots as $slot) {
            if (organizer_slot_is_free($slot, $assignid, true)) {
                   $row = new html_table_row();
                foreach ($columns as $column) {
                    switch ($column) {
                        case 'datetime':
                            $cell = $row->cells[] = new html_table_cell(organizer_date_time($slot));
                        break;
                        case 'location':
                            $cell = $row->cells[] = new html_table_cell(organizer_location_link($slot));
                        break;
                        case 'participants':
                            $cell = $row->cells[] = new html_table_cell(
                            organizer_get_participant_list($params, $slot, null)
                                );
                        break;
                        case 'teacher':
                            $cell = $row->cells[] = new html_table_cell(
                                    organizer_trainer_data($params, $slot, organizer_get_slot_trainers($slot->id))
                            );
                        break;
                        case 'details':
                            $cell = $row->cells[] = new html_table_cell(organizer_slot_status($params, $slot));
                        break;
                        case 'status':
                            $cell = $row->cells[] = new html_table_cell(organizer_slot_reg_status($organizer, $slot));
                        break;
                        case 'actions':
                            $cell = $row->cells[] = new html_table_cell(organizer_get_assign_button($slot->id, $params));
                        break;
                        default:
                            print_error("Unrecognized column type: $column");
                    }  // End switch.

                    $cell->style .= ' vertical-align: middle;';
                } // End foreach column.
                      $numshown++;
                            $rows[] = $row;
            } // End is_free_slot.
        } // End foreach slot.
    } // End if slots.

    if ($numshown == 0) { // If no slots shown.
        $row = new html_table_row();
        $cell = new html_table_cell(get_string('slotlistempty', 'organizer'));
        $cell->colspan = count($columns);
        $row->cells[] = $cell;
        $rows[] = $row;
    } // End if no slots shown.
    return $rows;
}

function organizer_app_details($appointment) {

    if (!isset($appointment)) {
        return '';
    }

    $list = '<span style="display: table-cell;">';
    if ($appointment->comments) {
        $list .= organizer_popup_icon(ORGANIZER_ICON_STUDENT_COMMENT, organizer_filter_text($appointment->comments));
    } else {
        $list .= "&nbsp;";
    }
    $list .= '</span>';

    $list .= '<span style="display: table-cell;">';
    $list .= '&nbsp;' . organizer_get_attended_icon($appointment);
    $list .= '</span>';

    $organizer = organizer_get_organizer();
    if ($organizer->grade != 0) {
        $grade = organizer_display_grade($organizer, $appointment->grade, $appointment->userid);
        if ($grade != get_string("nograde")) {
            $list .= '<span style="display: table-cell; text-align: right; padding-right: 6px;">';
            $list .= $grade;
            $list .= '</span>';
        }
    }

    $list .= '<span style="display:table-cell">';
    $list .= $appointment->feedback ? organizer_popup_icon(ORGANIZER_ICON_TEACHER_FEEDBACK, $appointment->feedback) : " ";
    $list .= '</span>';

    return $list;
}

function organizer_registration_allowed($organizer, $userid = null) {
    $app = organizer_get_last_user_appointment($organizer, $userid);
    if ($app) { // Appointment made, check the flag.
        $slot = new organizer_slot($app->slotid);
        if ($slot->is_past_deadline()) {
            return isset($app->allownewappointments) && $app->allownewappointments;
        } else {
            return !isset($app->allownewappointments) || $app->allownewappointments;
        }
    } else { // No appointment made, allowed.
        return true;
    }
}

// Content generating functions.

function organizer_date_time($slot) {
    if (!isset($slot) || !isset($slot->starttime)) {
        return '-';
    }

    $datefrom = userdate($slot->starttime, get_string('fulldatetemplate', 'organizer')) . " " .
            userdate($slot->starttime, get_string('timetemplate', 'organizer'));
    $dateto = userdate($slot->starttime + $slot->duration, get_string('fulldatetemplate', 'organizer')) . " " .
            userdate($slot->starttime + $slot->duration, get_string('timetemplate', 'organizer'));
    list($unitname, $value) = organizer_figure_out_unit($slot->duration);
    $duration = ($slot->duration / $value) . ' ' . $unitname;

    return "$datefrom -<br />$dateto ($duration)";
}

function organizer_trainer_data($params, $slot, $trainerids = null) {
    global $USER, $DB;

    if (!isset($slot) || !$trainerids) {
        return '-';
    }

    $query = "SELECT a.*
    FROM {organizer_slot_appointments} a
    WHERE a.slotid = :slotid";
    $param = array('slotid' => $slot->id);
    $appointments = $DB->get_records_sql($query, $param);

    $context = organizer_get_context();

    $canregister = has_capability('mod/organizer:register', $context, null, false);
    $canunregister = has_capability('mod/organizer:unregister', $context, null, false);

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

    $showteacher = $myslotpending || $organizerdisabled || $slotdisabled ||
        !$slotx->organizer_groupmode_user_has_access() || $slotx->is_evaluated();

    if ($wasownslot) {
        if (!$slotdisabled) {
               $showteacher |= !$canunregister || (isset($regslotx) && $regslotx->is_evaluated());
        }
    }

    if ($params['mode'] == ORGANIZER_TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    } else if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $slot->teachervisible || $showteacher) {
        $output = "";
        $connector = "";
        foreach ($trainerids as $trainerid) {
            $output .= $connector . organizer_get_name_link($trainerid);
            $connector = "<br>";
        }
    } else {
        $output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    if (isset($slot->teachercomments)) {
        if ($slot->teachercomments != '') {
            $output .= ' ' . organizer_popup_icon(ORGANIZER_ICON_TEACHER_COMMENT, s($slot->teachercomments));
        }
    } else {
        if ($slot->comments != '') {
            $output .= ' ' . organizer_popup_icon(ORGANIZER_ICON_TEACHER_COMMENT, s($slot->comments));
        }
    }

    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output .= '<br /><em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    return $output;
}

function organizer_reg_organizer_app_details($organizer, $groupmode, $appid) {
    global $DB;

    $appointment = $DB->get_record('organizer_slot_appointments', array('id' => $appid));
    if ($appointment) {
        $list = '';
        if ($groupmode) {
            $list .= ' ' . organizer_get_attended_icon($appointment) . ' ';
        }
        if ($organizer->grade > 0) {
                $grade = organizer_display_grade($organizer, $appointment->grade, $appointment->userid);
            if ($grade != get_string("nograde")) {
                $list .= $grade;
            }
        }
        if (isset($appointment->feedback) && $appointment->feedback != '') {
            $list .= ' ' . organizer_popup_icon(ORGANIZER_ICON_TEACHER_FEEDBACK, $appointment->feedback);
        } else {
            $list .= ' ';
        }
        if (isset($appointment->comments) && $appointment->comments != '') {
            $list .= ' ' . organizer_popup_icon(ORGANIZER_ICON_STUDENT_COMMENT, $appointment->comments);
        }
    } else {
        $list = '-';
    }

    return $list;
}

function organizer_reg_waitinglist_status($organizerid, $groupmode, $userid = 0) {
    global $DB;

    $list = "";
    if ($groupmode) {
        $group = organizer_fetch_user_group($userid, $organizerid);
        $query = "SELECT DISTINCT s.id, s.starttime, s.duration, s.location
                FROM {organizer_slot_queues} q
				INNER JOIN {organizer_slots} s ON s.id = q.slotid
				WHERE q.groupid = :groupid and s.organizerid = :organizerid";
        $par = array('groupid' => $group->id, 'organizerid' => $organizerid);
    } else {
        $query = "SELECT DISTINCT s.id, s.starttime, s.duration, s.location FROM {user} u
				INNER JOIN {organizer_slot_queues} q ON q.userid = u.id
				INNER JOIN {organizer_slots} s ON s.id = q.slotid
				WHERE u.id = :userid and s.organizerid = :organizerid";
        $par = array('userid' => $userid, 'organizerid' => $organizerid);
    }
    if ($slot = $DB->get_record_sql($query, $par)) {
        $slotx = new organizer_slot($slot->id);
        if ($groupmode) {
            $position = $slotx->is_group_in_queue($group->id);
        } else {
            $position = $slotx->is_user_in_queue($userid);
        }
        $list = "&nbsp;" . get_string('inwaitingqueue', 'organizer');
        $slotinfo = str_replace("<br />", " ", organizer_date_time($slot));
        $slotinfo .= "/ " . get_string('teacherid', 'organizer') . ":";
        $trainerstr = "";
        if ($trainers = organizer_get_slot_trainers($slot->id, true)) {
            $conn = "";
            foreach ($trainers as $trainer) {
                $trainerstr .= $conn . $trainer->firstname . " " . $trainer->lastname;
                $conn = ", ";
            }
        }
        $slotinfo .= $trainerstr ? $trainerstr : "-";
        $slotinfo .= "/ " . get_string('location', 'organizer') . ":";
        $slotinfo .= $slot->location ? $slot->location : "-";
        $slotinfo .= "/ " . get_string('position', 'organizer') . ":";
        $slotinfo .= $position;
        $list .= "<span style=\"cursor:help;\"> " . organizer_get_icon('docs', $slotinfo) . "</span>";
    }

    return $list;

}

function organizer_teacher_action($params, $entry, $context, $organizer, $groupmode) {

    $evalenabled = has_capability('mod/organizer:evalslots', $context, null, true);
    $evalurl = new moodle_url(
        '/mod/organizer/slots_eval.php',
        array('id' => $params['id'], 'slots[]' => $entry->slotid)
    );
    $remindurl = new moodle_url(
        '/mod/organizer/send_reminder.php',
        array('id' => $params['id'], 'user' => $entry->id)
    );
    $assignurl = new moodle_url(
        '/mod/organizer/view.php',
        array('id' => $params['id'], 'sort' => 'datetime', 'mode' => '4', 'assignid' => $entry->id)
    );
    $deleteurl = new moodle_url(
        '/mod/organizer/appointment_delete.php',
        array('id' => $params['id'], 'appid' => $entry->appid)
    );

    $buttons = array();

    // If entry is appointment => show grade button.
    if ($entry->status != ORGANIZER_APP_STATUS_NOT_REGISTERED) {
        $button = new stdClass();
        $button->text = get_string("btn_eval_short", 'organizer');
        $button->url = $evalurl;
        $button->disabled = !$evalenabled;
        $buttons[] = $button;
    }
    // If max booking is not reached => show reminder and assign button.
    if ($groupmode) {
        $booked = organizer_count_bookedslots($organizer->id, null, $entry->id);
    } else {
        $booked = organizer_count_bookedslots($organizer->id, $entry->id, null);
    }
    if (organizer_multiplebookings_status($booked, $organizer) != USERSLOTS_MAX_REACHED) {
        $button = new stdClass();
        $button->text = get_string("btn_remind", 'organizer');
        $button->url = $remindurl;
        $button->disabled = !has_capability('mod/organizer:sendreminders', $context, null, true);
        $buttons[] = $button;
        $button = new stdClass();
        $button->text = get_string("btn_assign", 'organizer');
        $button->url = $assignurl;
        $button->disabled = !has_capability('mod/organizer:assignslots', $context, null, true);
        $buttons[] = $button;
    }
    // If it is a trainer assigned slot show button for deleting the appointment.
    if ($entry->teacherapplicantid && has_capability('mod/organizer:deleteslots', $context, null, true)) {
        $button = new stdClass();
        $button->text = get_string("btn_deleteappointment", 'organizer');
        $button->url = $deleteurl;
        $button->disabled = !has_capability('mod/organizer:assignslots', $context, null, true);
        $buttons[] = $button;
    }

    $output = "";

    foreach ($buttons as $button) {
        if ($button->disabled) {
            $output .= '<a href="#" class="action disabled">' . $button->text . '</a>';
        } else {
            $output .= '<a href="' . $button->url . '" class="action">' . $button->text . '</a>';
        }
    }

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

        if ($slot->visibility == ORGANIZER_VISIBILITY_ANONYMOUS) {
            if ($ismyslot) {
                $content .= organizer_get_name_link($app->userid) .
                        organizer_get_teacherapplicant_output($app->teacherapplicantid, $app->teacherapplicanttimemodified) .
                        '<br />';
            }
        } else { // Not anonymous.
            if ($groupmode) {
                $app = reset($appointments);
                if ($app === false) {
                    $content = '<em>' . get_string('nogroup', 'organizer') . '</em><br />';
                } else {
                    $groupname = $DB->get_field('groups', 'name', array('id' => $app->groupid));
                    $content = "<em>{$groupname}</em>" .
                            organizer_get_teacherapplicant_output($app->teacherapplicantid, $app->teacherapplicanttimemodified) .
                            "<br />";
                }
            }

            $showparticipants = ($slot->visibility == ORGANIZER_VISIBILITY_ALL) || $ismyslot;
            if ($showparticipants) {
                $content .= "<span style='display: table;'>";
                foreach ($appointments as $appointment) {
                    $content .= "<span style='display: table-row;'>";
                    $content .= "<span style='display: table-cell;'>";
                    $content .= organizer_get_name_link($appointment->userid);
                    if ($groupmode) {
                        if ($appointment->userid == $appointment->applicantid) {
                            $content .= organizer_get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer'));
                        }
                    } else {
                        $content .= organizer_get_teacherapplicant_output(
                            $appointment->teacherapplicantid,
                            $appointment->teacherapplicanttimemodified
                        );
                    }
                    $content .= '</span>';
                       $content .= '</span>';
                }
                         $content .= '</span>';
            }
        }

    } else {  // Not studentview.

        if ($count == 0) {
            $content .= $groupmode ? ('<em>' . get_string('nogroup', 'organizer') .
                    '</em><br />') : ('<em>' . get_string('noparticipants', 'organizer') . '</em><br />');
        } else {
            $list = "";
            if ($groupmode) {
                $app = reset($appointments);
                if ($app !== false) {
                             $groupname = $DB->get_field('groups', 'name', array('id' => $app->groupid));
                             $list .= "<em>{$groupname}</em>" .
                    organizer_get_teacherapplicant_output($app->teacherapplicantid, $app->teacherapplicanttimemodified)
                        . "<br />";
                }
            }

            $list .= '<span style="display: table;">';
            foreach ($appointments as $appointment) {
                $list .= '<span style="display: table-row;">';
                $list .= '<span style="display: table-cell;">';
                $identity = organizer_get_user_identity($appointment->userid);
                $identity = $identity != "" ? " ({$identity})" : "";
                $list .= organizer_get_name_link($appointment->userid) . $identity;
                if ($groupmode) {
                    if (organizer_is_group_mode() && $appointment->userid == $appointment->applicantid) {
                        $list .= organizer_get_img('pix/applicant.gif', 'applicant', get_string('applicant', 'organizer'));
                    }
                } else {
                    $list .= organizer_get_teacherapplicant_output(
                        $appointment->teacherapplicantid,
                        $appointment->teacherapplicanttimemodified
                    );
                }
                $list .= '</span>';
                $list .= organizer_app_details($appointment);
                $list .= '</span>';
            }
            $list .= '</span>';
            $content .= $list;
        }
    }

    if (!$groupmode) {
        $maxparticipants = $slot->maxparticipants;
        $a = new stdClass();
        $a->numtakenplaces = $count;
        $a->totalplaces = $maxparticipants;

        if ($maxparticipants - $count != 0) {
            if ($maxparticipants == 1) {
                $content .= "<em>" . get_string('places_taken_sg', 'organizer', $a) . "</em>";
            } else {
                $content .= "<em>" . get_string('places_taken_pl', 'organizer', $a) . "</em>";
            }
        } else {
            if ($maxparticipants == 1) {
                $content .= "<span style=\"color: red;\"><em>" . get_string('places_taken_sg', 'organizer', $a)
                        . "</em></span>";
            } else {
                $content .= "<span style=\"color: red;\"><em>" . get_string('places_taken_pl', 'organizer', $a)
                        . "</em></span>";
            }
            if (organizer_is_queueable()) {
                      $inqueue = count($DB->get_records('organizer_slot_queues', array('slotid' => $slot->id)));
                if ($inqueue) {
                    $a->inqueue = $inqueue;
                    if ($a->queueposition = $slotx->is_user_in_queue($USER->id)) {
                        $content .= organizer_write_places_inqueue_position($a);
                    } else {
                        $content .= organizer_write_places_inqueue($a, $slot, $params);
                    }
                }
            }
        }

    } else { // If groupmode: .

        if ($count == 0) {
            $content .= "<em>" . get_string('group_slot_available', 'organizer') . "&nbsp;</em>";
        } else {
            $content .= "<span style=\"color: red;\"><em>" . get_string('group_slot_full', 'organizer')
                    . "&nbsp;</em></span>";
            if (organizer_is_queueable()) {
                $sql = "SELECT COUNT(distinct q.groupid) FROM {organizer_slot_queues} q
                        WHERE q.slotid = :slotid";
                $paramssql = array('slotid' => $slot->id);
                $inqueues = $DB->count_records_sql($sql, $paramssql);
                if ($inqueues) {
                    $a = new stdClass();
                    $a->inqueue = $inqueues;
                    if ($a->queueposition = $slotx->is_group_in_queue()) {
                        $content .= organizer_write_places_inqueue_position($a);
                    } else {
                        $content .= organizer_write_places_inqueue($a, $slot, $params);
                    }
                }
            }
        }
    }

    if ($slot->visibility == ORGANIZER_VISIBILITY_ANONYMOUS) {
        $content .= organizer_get_icon('anon', get_string('slot_anonymous', 'organizer'));
    } else if ($slot->visibility == ORGANIZER_VISIBILITY_SLOT) {
        $content .= organizer_get_icon('slotanon', get_string('slot_slotvisible', 'organizer'));
    }

    return $content;
}

function organizer_get_attended_icon($appointment) {
    if (isset($appointment->attended)) {
        if ($appointment->attended == 1) {
            if ($appointment->allownewappointments) {
                return organizer_get_icon(
                    'yes_reg',
                    get_string('reg_status_slot_attended_reapp', 'organizer')
                );
            } else {
                return organizer_get_icon(
                    'yes',
                    get_string('reg_status_slot_attended', 'organizer')
                );
            }
        } else {
            if ($appointment->allownewappointments ) {
                return organizer_get_icon(
                    'no_reg',
                    get_string('reg_status_slot_not_attended_reapp', 'organizer')
                );
            } else {
                return organizer_get_icon(
                    'no',
                    get_string('reg_status_slot_not_attended', 'organizer')
                );
            }
        }
    }

    if (organizer_with_grading()) {
        return organizer_get_icon('pending', get_string('reg_status_slot_pending', 'organizer'));
    } else {
        return "";
    }
}

function organizer_location_link($slot) {
    if (!isset($slot) || !isset($slot->location) || $slot->location == '') {
        return '-';
    }

    if (isset($slot->locationlink)) {
        if (strpos($slot->locationlink, 'http://') === false &&
                strpos($slot->locationlink, 'https://') === false  ) {
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

function organizer_get_icon($iconname, $string, $size="small", $id="") {
    global $OUTPUT;
    if ($size == "big") {
        $alt = "";
        $title = "";
        $other = 'width="32" height="32"';
        if ($string) {
            $alt = $string;
            $title = $string;
            $other .= ' data-toggle="tooltip"';
        }
        $icon = organizer_get_img('pix/' . $iconname . '.png', $alt, $title, $id, $other);
    } else {
        $attributes = $id != '' ? array('id' => $id) : array();
        $alt = "";
        if ($string) {
            $attributes['data-toggle'] = "tooltip";
            $attributes['title'] = $string;
            $alt = $string;
        }
        $icon = $OUTPUT->pix_icon($iconname, $alt, 'mod_organizer', $attributes);
    }
    return $icon;
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

    $actionurl = new moodle_url(
        '/mod/organizer/slots_eval.php',
        array('id' => $params['id'], 'slot' => $slotx->id)
    );

    if ($slotevaluated) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_icon('yes', get_string('img_title_evaluated', 'organizer'), "big") . '</a>';
    } else if ($slotnoparticipants) {
        return organizer_get_icon('no_participants', get_string('img_title_no_participants', 'organizer'), "big");
    } else if ($slotpending) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_icon('pending', get_string('img_title_pending', 'organizer'), "big") . '</a>';
    } else if ($slotgradeable) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_icon(
                    'student_slot_past_deadline',
                    get_string('img_title_past_deadline', 'organizer'), "big"
                ) . '</a>';
    } else if ($slotdueempty) {
        return organizer_get_icon('student_slot_available', get_string('img_title_due', 'organizer'), "big");
    } else if ($slotdue) {
        return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_icon('student_slot_available', get_string('img_title_due', 'organizer'), "big")
                . '</a>';
    } else {
        print_error("This shouldn't happen.");
    }
}

function organizer_slot_commands($slotid, $params) {
    global $OUTPUT, $PAGE;

    $outstr = "";

    $context = $PAGE->context;

    // EDIT.
    if (has_capability("mod/organizer:editslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_edit.php',
                array('id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode'])
        );
        $outstr .= \html_writer::link($actionurl,
                $OUTPUT->image_icon('t/edit', get_string('btn_editsingle', 'organizer')),
                array('class' => 'editbutton', 'title' => get_string('btn_editsingle', 'organizer')));
    }

    // DELETE.
    if (has_capability("mod/organizer:deleteslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_delete.php',
                array('id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode'])
        );
        $outstr .= \html_writer::link($actionurl,
                $OUTPUT->image_icon('t/delete', get_string('btn_deletesingle', 'organizer')),
                array('class' => 'deletebutton', 'title' => get_string('btn_deletesingle', 'organizer')));
    }

    // PRINT.
    if (has_capability("mod/organizer:printslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_printdetail.php',
                array('id' => $params['id'], 'slot' => $slotid, 'mode' => $params['mode'])
        );
        $outstr .= \html_writer::link($actionurl,
                $OUTPUT->image_icon('t/print', get_string('btn_printsingle', 'organizer')),
                array('class' => 'printbutton', 'title' => get_string('btn_printsingle', 'organizer')));
    }

    // GRADE/EVALUATE.
    if (has_capability("mod/organizer:evalslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_eval.php',
                array('id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode'])
        );
        $outstr .= \html_writer::link($actionurl,
                $OUTPUT->image_icon('t/grades', get_string('btn_evalsingle', 'organizer')),
                array('class' => 'gradesbutton', 'title' => get_string('btn_evalsingle', 'organizer')));
    }

    return $outstr;
}

function organizer_slot_reg_status($organizer, $slot, $onlyownslotsmsg = null) {
    global $DB;

    $slotx = new organizer_slot($slot);

    $app = organizer_get_last_user_appointment($organizer);

    if ($slotx->organizer_expired()) {
        $output = organizer_get_icon(
            'organizer_expired',
            get_string('reg_status_organizer_expired', 'organizer'), "big"
        );
    } else if ($slotx->is_past_due()) {
        if ($app) {
            $regslot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
            if ($slotx->id == $regslot->id) {
                if (!isset($app->attended)) {
                    if (organizer_with_grading()) {
                                    $output = organizer_get_icon(
                                        'pending',
                                        get_string('reg_status_slot_pending', 'organizer'), "big"
                                    );
                    } else {
                                    $output = organizer_get_icon(
                                        'student_slot_expired', '',
                                        get_string('reg_status_slot_expired', 'organizer'), "big"
                                    );
                    }
                } else if ($app->attended == 0 && $app->allownewappointments == 0) {
                    $output = organizer_get_icon('no', get_string('reg_status_slot_not_attended', 'organizer'), "big");
                } else if ($app->attended == 1 && $app->allownewappointments == 0) {
                    $output = organizer_get_icon('yes', get_string('reg_status_slot_attended', 'organizer'), "big");
                } else if ($app->attended == 0 && $app->allownewappointments == 1) {
                    $output = organizer_get_icon(
                        'no_reg',
                        get_string('reg_status_slot_not_attended_reapp', 'organizer'), "big"
                    );
                } else if ($app->attended == 1 && $app->allownewappointments == 1) {
                    $output = organizer_get_icon(
                        'yes_reg',
                        get_string('reg_status_slot_attended_reapp', 'organizer'), "big"
                    );
                }
            } else {
                $output = organizer_get_icon(
                    'student_slot_expired',
                    get_string('reg_status_slot_expired', 'organizer'), "big"
                );
            }
        } else {
            $output = organizer_get_icon(
                'student_slot_expired',
                get_string('reg_status_slot_expired', 'organizer'), "big"
            );
        }
    } else if ($slotx->is_past_deadline()) {
        $output = organizer_get_icon(
            'student_slot_past_deadline',
            get_string('reg_status_slot_past_deadline', 'organizer'), "big"
        );
    } else {
        if ($slotx->is_full()) {
            $output = organizer_get_icon('student_slot_full', get_string('reg_status_slot_full', 'organizer'), "big");
        } else {
            $output = organizer_get_icon(
                'student_slot_available',
                get_string('reg_status_slot_available', 'organizer'), "big"
            );
        }
    }

    if ($onlyownslotsmsg) {
        $output .= $onlyownslotsmsg;
    }
    return $output;
}

function organizer_participants_action($params, $slot) {
    global $OUTPUT, $USER;

    $slotx = new organizer_slot($slot);

    list(, , $organizer, $context) = organizer_get_course_module_data();

    $rightregister = has_capability('mod/organizer:register', $context, null, false);
    $rightunregister = has_capability('mod/organizer:unregister', $context, null, false);
    $isuserslot = organizer_get_slot_user_appointment($slotx) ? true : false;
    $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotexpired = $slotx->is_past_due() || $slotx->is_past_deadline();
    $slotfull = $slotx->is_full();
    $disabled = $organizerdisabled || !$slotx->organizer_groupmode_user_has_access() || $slotx->is_evaluated();
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $isalreadyinqueue = $slotx->is_group_in_queue();
        $group = organizer_fetch_user_group($USER->id, $organizer->id);
        $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, null, $group->id);
    } else {
        $isalreadyinqueue = $slotx->is_user_in_queue($USER->id);
        $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, $USER->id);
    }
    $isqueueable = $organizer->queue && !$isalreadyinqueue && !$disabled;
    if ($isuserslot) {
        $action = 'unregister';
        $disabled |= !$rightunregister || $slotexpired || $slotx->is_evaluated();
    } else if (!$slotfull) {
        $disabled |= !$rightregister || $slotexpired;
        if ($lefttobook) {
            $action = 'register';
        } else {
            $action = 'reregister';
        }
    } else if ($slotfull) {
        if ($isqueueable) {
            $action = 'queue';
            $disabled |= !$rightregister || $slotexpired || !$lefttobook;
        }
    }
    if ($isalreadyinqueue) {
        $action = 'unqueue';
        $disabled |= !$rightunregister || $slotexpired;
    }

    // Show slot comments if user is owner.
    $commentbtn = "";
    if ($isuserslot) {
        $commenturl = new moodle_url(
            '/mod/organizer/comment_edit.php',
            array('id' => $params['id'], 'slot' => $slotx->id)
        );
        $commentbtndisabled = $organizerdisabled || !$slotx->organizer_groupmode_user_has_access();
        $commentbtn = $OUTPUT->single_button(
            $commenturl, get_string("btn_comment", 'organizer'), 'post',
            array('disabled' => $commentbtndisabled)
        );
        $commentbtn = "<br/>".$commentbtn;
    }

    return organizer_get_reg_button($action, $slotx->id, $params, $disabled).$commentbtn;

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

    $actionurl = new moodle_url(
        '/mod/organizer/view_action.php',
        array('id' => $params['id'], 'mode' => $params['mode'], 'action' => $type, 'slot' => $slotid)
    );

    return $OUTPUT->single_button(
        $actionurl, get_string("btn_$type", 'organizer'), 'post', array('disabled' => $disabled)
    );
}

function organizer_get_assign_button($slotid, $params) {
    global $OUTPUT;

    $actionurl = new moodle_url(
        '/mod/organizer/slot_assign.php',
        array('id' => $params['id'], 'mode' => $params['mode'], 'assignid' => $params['assignid'], 'slot' => $slotid)
    );

    return $OUTPUT->single_button($actionurl, get_string("btn_assign", 'organizer'), 'post');
}

function organizer_get_status_icon_new($status) {
    switch ($status) {
        case ORGANIZER_APP_STATUS_ATTENDED:
        return organizer_get_icon('status_attended', get_string('reg_status_slot_attended', 'organizer'), "big");
        case ORGANIZER_APP_STATUS_PENDING:
            if (organizer_with_grading()) {
                  return organizer_get_icon('status_pending', get_string('reg_status_slot_pending', 'organizer'), "big");
            } else {
                return "";
            }
        case ORGANIZER_APP_STATUS_REGISTERED:
        return organizer_get_icon('status_not_occured', get_string('reg_status_registered', 'organizer'), "big");
        case ORGANIZER_APP_STATUS_NOT_ATTENDED:
        return organizer_get_icon(
            'status_not_attended',
            get_string('reg_status_slot_not_attended', 'organizer'), "big"
        );
        case ORGANIZER_APP_STATUS_NOT_REGISTERED:
        return organizer_get_icon(
            'status_not_registered',
            get_string('reg_status_not_registered', 'organizer'), "big"
        );
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
        $out = (($time / 60) == 1) ? get_string('min', 'organizer') : get_string('min_pl', 'organizer');
        return array($out, 60);
    } else {
        $out = (($time == 1) ? get_string('sec', 'organizer') : get_string('sec_pl', 'organizer'));
        return array($out, 1);
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
            ORGANIZER_ICON_STUDENT_COMMENT => 'feedback2',
            ORGANIZER_ICON_TEACHER_COMMENT => 'feedback2',
            ORGANIZER_ICON_TEACHER_FEEDBACK => 'feedback',
    );
    $iconid = organizer_register_popup($type, $content);
    $output = organizer_get_icon($icons[$type], $content, '', $iconid);
    return $output;
}

function organizer_slot_is_free($slot, $userid, $assignmentview = null) {

    $slotx = new organizer_slot($slot);
    if ($assignmentview) {
        $organizerconfig = get_config('organizer');
        if (isset($organizerconfig->allowcreationofpasttimeslots) &&
            $organizerconfig->allowcreationofpasttimeslots != 1) {
            $ispastdue = $slotx->is_past_due();
        } else {
            $ispastdue = false;
        }

    }
    if (!$ispastdue && !$slotx->is_full() && $slotx->is_available() ) {

        $apps = organizer_get_all_user_appointments($slotx->organizerid, $userid);
        foreach ($apps as $app) {  // Is own slot?
            if ($app->slotid == $slotx->id) {
                return false;
            }
        }
        return true;
    }

    return false;
}

function organizer_register_popup() {
    static $id = 0;

    $elementid = "organizer_popup_icon_{$id}";
    $id++;

    return $elementid;
}


function organizer_write_places_inqueue_position($a) {

    $output = "";
    $output .= "<span style=\"color: red;\">&nbsp;(" . get_string('places_inqueue_withposition', 'organizer', $a) . ")</span>";
    return $output;
}

function organizer_write_places_inqueue($a, $slot, $params) {

    $output = "";
    $output .= "<span style=\"color: red;\">&nbsp;(" . get_string('places_inqueue', 'organizer', $a);
    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $slot->visibility == ORGANIZER_VISIBILITY_ALL) {
        if (organizer_is_group_mode()) {
            $wlinfo = organizer_get_entries_queue_group($slot);
        } else {
            $wlinfo = organizer_get_entries_queue($slot);
        }
            $output .= "<span style=\"cursor:help;\"> " .
                    organizer_get_icon('docs', $wlinfo) . "</span>";
    }
    $output .= ")</span>";
    return $output;
}

function organizer_get_entries_queue($slot) {
    global $DB;

    $output = "";
    $paramssql = array('slotid' => $slot->id);
    $slotquery = 'SELECT u.id, u.firstname, u.lastname, q.id
				FROM {organizer_slots} s
				INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
				INNER JOIN {user} u ON u.id = q.userid
				WHERE s.id = :slotid
				ORDER BY q.id';

    if ($queueentries = $DB->get_records_sql($slotquery, $paramssql)) {
        $i = 0;
        $lf = "";
        foreach ($queueentries as $qe) {
            $i++;
            $output .= $lf . $i . ". " . $qe->firstname . " " . $qe->lastname;
            $lf = "\n";
        }
    }

    return $output;
}

function organizer_get_entries_queue_group($slot) {
    global $DB;

    $output = "";
    $paramssql = array('slotid' => $slot->id);
    $slotquery = 'SELECT DISTINCT g.id, g.name, q.id
				FROM {organizer_slots} s
				INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
				INNER JOIN {groups} g ON g.id = q.groupid
				WHERE s.id = :slotid
				ORDER BY q.id';

    if ($queueentries = $DB->get_records_sql($slotquery, $paramssql)) {
        $i = 0;
        $lf = "";
        foreach ($queueentries as $qe) {
            $i++;
            $output .= $lf . $i . ". " . $qe->name;
            $lf = "\n";
        }
    }

    return $output;
}

/**
 * Same codelines in the header for slots_print, slots_delete, slots_add, slots_eval.
 *
 * @return array
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function organizer_slotpages_header() {
    global $PAGE;

    $mode = optional_param('mode', null, PARAM_INT);
    $action = optional_param('action', null, PARAM_ALPHANUMEXT);

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    require_login($course, false, $cm);

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

    return array($cm, $course, $organizer, $context, $redirecturl);
}

function organizer_get_participants_tableheadercell($params, $column, $columnhelpicon) {
    global $OUTPUT;

    if ($params['psort'] == 'name') {
        $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
        $nameicon = $params['pdir'] == 'ASC' ? 'up' : 'down';
        $nameicon = ' ' . $OUTPUT->pix_icon('t/' . $nameicon, get_string($nameicon));
    } else {
        $namedir = 'ASC';
        $nameicon = '';
    }

    if ($params['psort'] == 'id') {
        $iddir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
        $idicon = $params['pdir'] == 'ASC' ? 'up' : 'down';
        $idicon = ' ' . $OUTPUT->pix_icon('t/' . $idicon, get_string($idicon));
    } else {
        $iddir = 'ASC';
        $idicon = '';
    }

    if (!isset($params['sort'])) {
        $participantdir = 'DESC';
        $participanticon = 'down';
        $participanticon = ' ' . $OUTPUT->pix_icon('t/' . $participanticon, get_string($participanticon));
    } else if ($params['sort'] == 'participant') {
        $participantdir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
        $participanticon = $params['dir'] == 'ASC' ? 'up' : 'down';
        $participanticon = ' ' . $OUTPUT->pix_icon('t/' . $participanticon, get_string($participanticon));
    } else {
        $participantdir = 'ASC';
        $participanticon = 'up';
        $participanticon = ' ' . $OUTPUT->pix_icon('t/' . $participanticon, get_string($participanticon));
    }

    $urlp = new moodle_url(
        '/mod/organizer/view.php',
        array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'participant',
            'dir' => $participantdir)
    );

    $urln = new moodle_url(
            '/mod/organizer/view.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                    'dir' => $params['dir'], 'psort' => 'name', 'pdir' => $namedir)
    );
    $urli = new moodle_url(
            '/mod/organizer/view.php',
            array('id' => $params['id'], 'mode' => $params['mode'], 'sort' => $params['sort'],
                    'dir' => $params['dir'], 'psort' => 'id', 'pdir' => $iddir)
    );

    $links = html_writer::link($urlp, get_string("th_{$column}", 'organizer')) . $participanticon . " ";
    $links .= "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/";
    $links .= html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

    $cell = new html_table_cell($links . $columnhelpicon);

    return $cell;
}
