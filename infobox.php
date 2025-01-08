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
 * infobox.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/slotlib.php');

/**
 * HTML code for rendering infobox
 * @param $params
 * @param $organizer
 * @param $context
 * @param $organizerexpired
 * @return string
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_make_infobox($params, $organizer, $context, $organizerexpired = null) {
    global $PAGE, $USER;

    $output = '';

    // Display messages here.
    $output .= organizer_make_messages_section($params);

    // Module description section.
    $output .= organizer_make_description_section($organizer, $context);

    $jsparams = new stdClass();
    $jsparams->studentview = 0;
    $jsparams->registrationview = 0;

    switch($params['mode']) {
        case ORGANIZER_TAB_APPOINTMENTS_VIEW:
            $output .= organizer_make_addslotbutton_section($params, $organizerexpired);
        break;
        case ORGANIZER_TAB_STUDENT_VIEW:
            // My own booking information section.
            $output .= organizer_make_myapp_section($params, $organizer,
                organizer_get_all_user_appointments($organizer));
            $jsparams->studentview = 1;
        break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            // Button for sending reminders to all participants without an appointment.
            $entries = organizer_get_registrationview_entries(
                $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS, $params);
            $output .= organizer_make_sendreminder_section($params, $context, $organizer);
            $output .= organizer_make_registrationstatistic_section($organizer, $entries);
            $jsparams->registrationview = 1;
        break;
    }
    if ($params['mode'] != ORGANIZER_TAB_REGISTRATION_STATUS_VIEW) {
        // Display section with predefined filter view options like "hidden slots only" etc..
        $output .= organizer_make_slotoptions_section($params['mode'], $organizer);
        $output .= organizer_make_filtersection($params['mode']);
    } else {
        $output .= organizer_make_appointmentsstatus_section($organizer);
        $output .= organizer_make_filtersection_reg($organizer->isgrouporganizer ==
            ORGANIZER_GROUPMODE_EXISTINGGROUPS);
    }

    $PAGE->requires->js_call_amd('mod_organizer/initinfobox', 'init', [$jsparams->studentview,
        $jsparams->registrationview, $USER->id]);

    return $output;
}

/**
 * HTML for rendering an info section
 * @param $name
 * @param $content
 * @param $hidden
 * @return string
 * @throws coding_exception
 */
function organizer_make_section($name, $content, $hidden = false) {
    $output = "";
    if ($name) {
        if ($name != 'infobox_messages' && $name != 'infobox_messaging') {
            $output = '<div id="' . $name . '_box" class="block_course_overview block"' .
                ($hidden ? ' style="display: none;"' : '') . '>';
            $output .= '<div id="' . $name . '_header" class="header"><div class="title"><h2>'
                . get_string("{$name}_title", "organizer") . '</h2></div></div>';
            $output .= '</div>';
        }
    }
    $output .= '<div id="' . $name . '_content" class="content mt-1">';
    $output .= $content;
    $output .= '</div>';
    return $output;
}

/**
 * HTML for rendering messages infobox
 * @param $params
 * @return string
 * @throws coding_exception
 */
function organizer_make_messages_section($params) {
    global $OUTPUT;

    $output = '';
    $infoboxmessage = isset($_SESSION['infoboxmessage']) ? $_SESSION['infoboxmessage'] : "";
    if ($infoboxmessage) {
        $output .= $infoboxmessage;
        $_SESSION["infoboxmessage"] = "";
    } else {
        if (isset($params['messages'])) {
            $a = new stdClass();
            if (isset($params['data'])) {
                foreach ($params['data'] as $key => $value) {
                    $a->{$key} = $value;
                }
            }
            foreach ($params['messages'] as $message) {
                $output .= $OUTPUT->notification(get_string($message, 'organizer', $a), 'info');
            }
        }
    }
    if ($output) {
        return organizer_make_section('infobox_messages', $output);
    } else {
        return '';
    }
}

/**
 * HTML for rendering sendreminder infobox
 * @param $params
 * @param $context
 * @param $organizer
 * @return string
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_make_sendreminder_section($params, $context, $organizer) {
    global $OUTPUT;
    $recipients = organizer_get_reminder_recipients($organizer);
    $attributes = [];
    [, $places] = organizer_get_freeplaces($organizer);
    if ($disabled = !$recipients || !has_capability("mod/organizer:sendreminders", $context) || !$places) {
        $attributes['disabled'] = true;
    }
    $buttonlabel = get_string("btn_start", 'organizer');
    $buttonlabel .= " (" . count($recipients) . ")";
    $sendurl = new moodle_url('send_reminder.php', ['id' => $params['id'], 'mode' => '3']);
    $output = html_writer::div(get_string("btn_sendall", 'organizer') .
        $OUTPUT->single_button($sendurl, $buttonlabel, true, $attributes), 'ml-1');
    if (!$disabled) {
        $output = str_replace("btn-secondary", "btn-primary", $output);
    }
    return organizer_make_section('infobox_messaging', $output);
}

/**
 * HTML for rendering description infobox
 * @param $organizer
 * @return mixed
 * @throws coding_exception
 */
function organizer_make_description_section($organizer, $context) {
    global $OUTPUT;

    $output = "";
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        if (has_capability('mod/organizer:viewallslots', $context)) {
            $infotxt = get_string('grouporganizer_desc', 'organizer');
            $output .= organizer_get_icon_msg('group', $infotxt);
        } else {
            if ($group = organizer_fetch_my_group()) {
                $a = new stdClass();
                $a->groupname = $group->name;
                $infotxt = get_string('grouporganizer_desc_participant', 'organizer', $a);
                $output .= organizer_get_icon_msg('group', $infotxt);
            } else {
                $infotxt = get_string('grouporganizer_desc_novalidgroup', 'organizer');
                $output .= organizer_get_icon_msg('nogroup', $infotxt);
            }
        }
    }
    if (isset($organizer->duedate)) {
        $a = new stdClass();
        $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
        if ($organizer->duedate > time()) {
            $infotxt = get_string('infobox_organizer_expires', 'organizer', $a);
            $output .= organizer_get_icon_msg('expires', $infotxt);
        } else {
            $infotxt = get_string('infobox_organizer_expired', 'organizer', $a);
            $output .= organizer_get_icon_msg('expired', $infotxt);
        }
    } else {
        $infotxt = get_string('infobox_organizer_never_expires', 'organizer');
        $output .= organizer_get_icon_msg('neverexpires', $infotxt);
    }
    if ($organizer->grade != 0) {
        $infotxt = get_string('grading_desc_grade', 'organizer');
        $output .= organizer_get_icon_msg('grade', $infotxt);
    } else {
        $infotxt = get_string('grading_desc_nograde', 'organizer');
        $output .= organizer_get_icon_msg('nograde', $infotxt);
    }
    if ($organizer->queue != 0) {
        $infotxt = get_string('waitinglists_desc_active', 'organizer');
        $output .= organizer_get_icon_msg('queues', $infotxt);
    } else {
        $infotxt = get_string('waitinglists_desc_notactive', 'organizer');
        $output .= organizer_get_icon_msg('noqueues', $infotxt);
    }
    $a = new stdClass();
    $a->min = $organizer->userslotsmin;
    $a->max = $organizer->userslotsmax;
    $infotxt = get_string('infobox_minmax', 'organizer', $a);
    if ($a->max == 1) {
        $output .= organizer_get_icon_msg('minmax1', $infotxt);
    } else {
        $output .= organizer_get_icon_msg('minmax', $infotxt);
    }

    return $OUTPUT->box($output, 'generalbox', 'intro');
}

/**
 * HTML for rendering my app infobox
 * @param $params
 * @param $organizer
 * @param $apps
 * @return string
 * @throws coding_exception
 */
function organizer_make_myapp_section($params, $organizer, $apps) {
    global $USER;

    $output = "";
    $groupstr = "";
    $novalidgroup = false;
    if (organizer_is_group_mode()) {
        if (!$group = organizer_fetch_user_group($USER->id, $organizer->id)) {
            $novalidgroup = true;
        }
        $groupstr = "_group";
    }
    if (!$novalidgroup) {
        $a = new stdClass();
        $a->booked = organizer_count_bookedslots($organizer->id, isset($group->id) ? null : $USER->id, $group->id ?? null);
        $userslotsstate = organizer_multiplebookings_status($a->booked, $organizer);
        $a->max = $organizer->userslotsmax;
        $a->min = $organizer->userslotsmin;
        $a->left = $organizer->userslotsmax - $a->booked;
        $statusbarmsg = "";
        $minreached = false;
        $statusbarstatusmsg = get_string('infobox_myslot_userslots_status', 'organizer', $a);
        if ($userslotsstate == USERSLOTS_MIN_NOT_REACHED) {
            $statusbarmsg .= get_string('infobox_myslot_userslots_min_not_reached'.$groupstr, 'organizer', $a);
        } else if ($userslotsstate == USERSLOTS_MAX_REACHED) {
            $minreached = true;
            $statusbarmsg .= get_string('infobox_myslot_userslots_max_reached'.$groupstr, 'organizer', $a);
        } else {
            $minreached = true;
            $statusbarmsg .= get_string('infobox_myslot_userslots_min_reached'.$groupstr, 'organizer', $a).
                ' '.get_string('infobox_myslot_userslots_left'.$groupstr, 'organizer', $a);
        }
        $statusbar = organizer_userstatus_bar($a->booked, $a->max, $minreached, $statusbarstatusmsg, $statusbarmsg);
        $output .= $statusbar;
    }
    if (count($apps) > 0) {
        if ($params['limitedwidth']) {
            $columns = ['datetime', 'participants', 'teacher', 'status', 'actions'];
            $align = ['left', 'left', 'left', 'center', 'center'];
        } else {
            $columns = ['datetime', 'location', 'participants', 'teacher', 'status', 'actions'];
            $align = ['left', 'left', 'left', 'left', 'center', 'center'];
        }
        $params['participantslist'] = 'notcollapsed';
        $sortable = [];
        $table = new html_table();
        $table->id = 'my_slot_overview';
        $table->attributes['class'] = 'generaltable boxaligncenter overview';
        $table->head = organizer_generate_table_header($columns, $sortable, $params);
        $table->data = organizer_generate_table_content($columns, $params, $organizer, true);
        $table->align = $align;
        $output .= organizer_render_table_with_footer($table, false);
    }
    return organizer_make_section('infobox_myslot', $output);
}

/**
 * HTML for rendering registration statistic infobox
 * @param $organizer
 * @param $entries
 * @return string
 * @throws coding_exception
 */
function organizer_make_registrationstatistic_section($organizer, $entries) {
    $barwidth = 25;
    $a = new stdClass();
    $a->min = $organizer->userslotsmin;
    $a->max = $organizer->userslotsmax;
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        [$a->entries, $undermin, $a->maxreached] =
            organizer_registration_statistics($organizer, true, $entries,
                $organizer->userslotsmin, $organizer->userslotsmax);
        $a->minreached = (int) $a->entries - (int) $undermin;
        $messageminreached = get_string('infobox_statistic_minreached_group', 'organizer', $a);
        $messagemaxreached = get_string('infobox_statistic_maxreached_group', 'organizer', $a);
    } else {
        [$a->entries, $undermin, $a->maxreached] =
            organizer_registration_statistics($organizer, false, $entries,
                $organizer->userslotsmin, $organizer->userslotsmax);
        $a->minreached = (int) $a->entries - (int) $undermin;
        $messageminreached = get_string('infobox_statistic_minreached', 'organizer', $a);
        $messagemaxreached = get_string('infobox_statistic_maxreached', 'organizer', $a);
    }
    $allminreached = $undermin ? false : true;
    $allmaxreached = (int) $a->entries == (int) $a->maxreached ? true : false;

    $out = "";

    // Minreached bar.
    $out .= html_writer::start_div('registrationstatusbar mb-3 w-100', ['title' => $messageminreached]);
    if ($allminreached) {
        $out .= html_writer::div(' ',
            'registrationstatusbarleg align-middle border border-success bg-success rounded-left rounded-right',
            ['style' => "width: $barwidth%"]);
    } else {
        $partialfullwidth = $a->minreached * 100 / $a->entries * $barwidth / 100;
        $partialemptywidth = $barwidth - $partialfullwidth;
        if ($partialfullwidth > 0) {
            $out .= html_writer::div(' ',
                'registrationstatusbarleg align-middle border border-info bg-info rounded-left',
                ['style' => "width: $partialfullwidth%"]);
            $out .= html_writer::div(' ', 'registrationstatusbarleg align-middle border border-info rounded-right',
                ['style' => "width: $partialemptywidth%"]);
        } else {
            $out .= html_writer::div(' ',
                'registrationstatusbarleg align-middle border border-info rounded-right rounded-left',
                ['style' => "width: $partialemptywidth%"]);
        }
    }
    $out .= html_writer::div($messageminreached, 'd-inline ml-3');
    $out .= html_writer::end_div();

    // Maxreached bar.
    if ($organizer->userslotsmin != $organizer->userslotsmax) {
        $out .= html_writer::start_div('registrationstatusbar mb-4 w-100', ['title' => $messagemaxreached]);
        if ($allmaxreached) {
            $out .= html_writer::div(' ',
                'registrationstatusbarleg align-middle border border-success bg-success rounded-left rounded-right',
                ['style' => "width: $barwidth%"]);
        } else {
            $partialfullwidth = $a->maxreached * 100 / $a->entries * $barwidth / 100;
            $partialemptywidth = $barwidth - $partialfullwidth;
            if ($partialfullwidth > 0) {
                $out .= html_writer::div(' ',
                    'registrationstatusbarleg align-middle border border-info bg-info rounded-left',
                    ['style' => "width: $partialfullwidth%"]);
                $out .= html_writer::div(' ', 'registrationstatusbarleg align-middle border border-info rounded-right',
                    ['style' => "width: $partialemptywidth%"]);
            } else {
                $out .= html_writer::div(' ',
                    'registrationstatusbarleg align-middle border border-info rounded-right rounded-left',
                    ['style' => "width: $partialemptywidth%"]);
            }
        }
        $out .= html_writer::div($messagemaxreached, 'd-inline-block ml-3');
        $out .= html_writer::end_div();
    }

    return organizer_make_section('infobox_registrationstatistic', $out);
}

/**
 * HTML for rendering filter section
 * @param $mode
 * @return string
 * @throws coding_exception
 */
function organizer_make_filtersection($mode) {
    global $OUTPUT;

    // Display filter - options.
    $output = html_writer::start_div('organizer_filterblock pt-1');
    $output .= html_writer::start_span('', ['id' => 'organizer_filterfield']).
        get_string('searchfilter', 'organizer').$OUTPUT->help_icon('filtertable', 'organizer', '');
    $output .= html_writer::tag('input', null,
        ['type' => 'text', 'name' => 'filterparticipants', 'class' => 'organizer_filtertable']);
    $output .= html_writer::end_span();

    $displaymyslotsonly = true;
    $displayregistrationsonly = $displayfreeslots = $displayallparticipants =
        true;
    if ($prefs = get_user_preferences('mod_organizer_slotsviewoptions', false)) {
        $showmyslotsonly = substr($prefs, 0, 1) ? true : false;
        $showfreeslotsonly = substr($prefs, 1, 1) ? true : false;
        $showregistrationsonly = substr($prefs, 4, 1) ? true : false;
        $showallparticipants = substr($prefs, 5, 1) ? true : false;
    } else {
        $showmyslotsonly = $showfreeslotsonly = $showregistrationsonly = false;
        $showallparticipants = true;
    }
    if ($mode != ORGANIZER_TAB_STUDENT_VIEW) {
        if ($displaymyslotsonly) {
            $output .= html_writer::checkbox('show_my_slots_only', '1', $showmyslotsonly,
                get_string('infobox_showmyslotsonly', 'organizer'),
                ['id' => 'show_my_slots_only', 'class' => 'slotoptions']);
        }
    }
    if ($displayfreeslots) {
        $output .= html_writer::checkbox('show_free_slots_only', '1', $showfreeslotsonly,
            get_string('infobox_showfreeslots', 'organizer'),
            ['id' => 'show_free_slots_only', 'class' => 'slotoptions']);
    }
    if ($displayregistrationsonly) {
        $output .= html_writer::checkbox('show_registrations_only', '1', $showregistrationsonly,
            get_string('infobox_showregistrationsonly', 'organizer'),
            ['id' => 'show_registrations_only', 'class' => 'slotoptions']);
    }
    $output .= html_writer::end_div();

    // Write empty slotcounter.
    $output .= html_writer::start_div();
    $output .= html_writer::span('', 'text-info', ['id' => 'counttabrows']);
    $output .= html_writer::span(get_string('infobox_counter_slotrows', 'mod_organizer'), 'ml-1 text-info');
    if ($displayallparticipants) {
        $output .= html_writer::checkbox('show_all_participants', '1', $showallparticipants,
            get_string('infobox_showallparticipants', 'organizer'),
            ['id' => 'show_all_participants', 'class' => 'slotoptions']);
    }
    $output .= html_writer::end_div();

    $output .= html_writer::div('', 'clearer');

    return $output;
}

/**
 * HTML for rendering filter section in registration view
 * @param $groupmode
 * @return string
 * @throws coding_exception
 */
function organizer_make_filtersection_reg($groupmode) {
    global $OUTPUT, $PAGE;

    $output = html_writer::start_div('row organizer_filterblock_reg');

    $groupselectorstyle = $groupmode ? "span6" : "";
    // Display filter - options and input field.
    $output .= html_writer::start_div("$groupselectorstyle pt-3");
    $output .= html_writer::start_span('', ['id' => 'organizer_filterfield']).
        get_string('searchfilter', 'organizer').$OUTPUT->help_icon('filtertable', 'organizer', '');
    $output .= html_writer::tag('input', null,
        ['type' => 'text', 'name' => 'filterparticipants', 'class' => 'organizer_filtertable']);
    $output .= html_writer::end_span();
    $output .= html_writer::end_div();  // Filter options.

    if ($groupmode) {
        $pagebodyclasses = $PAGE->bodyclasses;
        if (strpos($pagebodyclasses, 'limitedwidth')) {
            $output .= html_writer::start_div("$groupselectorstyle pt-2");
        } else {
            $output .= html_writer::start_div("$groupselectorstyle ml-5 pt-2");
        }
        $output .= groups_print_activity_menu($PAGE->cm, $PAGE->url, true);
        $output .= html_writer::end_div();
    }

    $output .= html_writer::end_div(); // Row.

    $output .= html_writer::div('', 'clearer');

    return $output;
}

/**
 * HTML for rendering slotoptions section
 * @param $mode
 * @param $organizer
 * @return string
 * @throws coding_exception
 */
function organizer_make_slotoptions_section($mode, $organizer) {
    global $OUTPUT;

    if ($mode == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
        $output = organizer_appointmentsstatus_bar($organizer);
    } else {
        $output = "";
    }

    // Display show more - options.
    $output .= html_writer::start_div('mt-3');
    $output .= html_writer::span(get_string('showmore', 'organizer').
        $OUTPUT->help_icon('slotoptionstable', 'organizer'));
    $displayhiddenslots = $mode == ORGANIZER_TAB_APPOINTMENTS_VIEW;
    $displaypastslots = true;
    if ($prefs = get_user_preferences('mod_organizer_slotsviewoptions', false)) {
        $showhiddenslots = substr($prefs, 2, 1) ? true : false;
        $showpastslots = substr($prefs, 3, 1) ? true : false;
    } else {
        $showhiddenslots = $showpastslots = false;
    }
    if ($displayhiddenslots) {
        $output .= html_writer::checkbox('show_hidden_slots', '1', $showhiddenslots,
            get_string('infobox_showhiddenslots', 'organizer'),
            ['id' => 'show_hidden_slots', 'class' => 'slotoptions']);
    }
    if ($displaypastslots) {
        $output .= html_writer::checkbox('show_past_slots', '1', $showpastslots,
            get_string('infobox_showslots', 'organizer'),
            ['id' => 'show_past_slots', 'class' => 'slotoptions']);
    }
    $output .= html_writer::end_div();

    return organizer_make_section('infobox_slotoverview', $output);
}

/**
 * HTML for rendering add slots button
 * @param $params
 * @param $organizerexpired
 * @return string
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_make_addslotbutton_section($params, $organizerexpired) {

    $output = html_writer::start_div('mb-3 mt-1');

    $slotsaddurl = new moodle_url('/mod/organizer/slots_add.php', ['id' => $params['id']]);
    $output .= '<input class="btn btn-primary" type="submit" value="' . get_string('btn_add', 'organizer') .
        '" onClick="this.parentNode.parentNode.setAttribute(\'action\', \'' . $slotsaddurl . '\');" ' .
        ($organizerexpired ? 'disabled ' : '') . '/>';

    $output .= html_writer::end_div();

    return $output;
}

/**
 * HTML for rendering appointment status infobox
 * @param $organizer
 * @return string
 * @throws coding_exception
 */
function organizer_make_appointmentsstatus_section($organizer) {
    $output = html_writer::div(organizer_appointmentsstatus_bar($organizer), 'mb-3 mt-1');
    return organizer_make_section("", $output);
}
