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
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/legend.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/slotlib.php');

function organizer_make_infobox($params, $organizer, $context, &$popups) {
    global $PAGE;
    user_preference_allow_ajax_update('mod_organizer_showpasttimeslots', PARAM_BOOL);
    user_preference_allow_ajax_update('mod_organizer_showmyslotsonly', PARAM_BOOL);
    user_preference_allow_ajax_update('mod_organizer_showfreeslotsonly', PARAM_BOOL);
    $PAGE->requires->js_init_call('M.mod_organizer.init_infobox');

    $output = '';
    if ($organizer->alwaysshowdescription ||  time() > $organizer->allowregistrationsfromdate) {
        $output = organizer_make_description_section($organizer);
    }

    switch($params['mode']) {
        case ORGANIZER_TAB_APPOINTMENTS_VIEW:
        break;
        case ORGANIZER_TAB_STUDENT_VIEW:
            $output .= organizer_make_myapp_section($params, $organizer, organizer_get_last_user_appointment($organizer), $popups);
        break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            $output .= organizer_make_reminder_section($params, $context);
        break;
        case ORGANIZER_ASSIGNMENT_VIEW:
        break;
        default:
            print_error("Wrong view mode: {$params['mode']}");
    }
    $output .= organizer_make_slotoptions_section($params);
    $output .= organizer_make_messages_section($params);
    return $output;
}
function organizer_make_section($name, $content, $hidden = false) {
    $output = '<div id="' . $name . '_box" class="block_course_overview block"' . ($hidden ? ' style="display: none;"' : '') . '>';
    if ($name) {
        $output .= '<div id="' . $name . '_header" class="header">';
        $output .= '<div class="title"><h2>' . get_string("{$name}_title", 'organizer') . '</h2></div>';
        $output .= '</div>';
    }
    $output .= '<div id="' . $name . '_content" class="content">';
    $output .= $content;
    $output .= '</div></div>';
    return $output;
}
function organizer_add_message_icon($message) {
    if (strpos($message, 'warning') !== false) {
        return organizer_get_icon('message_warning', get_string('Warning', 'organizer'));
    } else if (strpos($message, 'info') !== false) {
        return organizer_get_icon('message_info', get_string('Info', 'organizer'));
    } else if (strpos($message, 'error') !== false) {
        return organizer_get_icon('message_error', get_string('Error', 'organizer'));
    } else {
        return '';
    }
}
function organizer_make_messages_section($params) {
    if ($params['messages']) {
        $output = '<div style="overflow: auto;">';
        $a = new stdClass();
        foreach ($params['data'] as $key => $value) {
            $a->$key = $value;
        }
        foreach ($params['messages'] as $message) {
            $output .= '<p>' . organizer_add_message_icon($message);
            $output .= ' ' . get_string($message, 'organizer', $a) . '</p>';
        }
        $output .= '</div>';
        return organizer_make_section('infobox_messages', $output);
    } else {
        return '';
    }
}
function organizer_make_reminder_section($params, $context) {
    global $OUTPUT;
    if (has_capability("mod/organizer:sendreminders", $context, null, true)) {
        $sendurl = new moodle_url('send_reminder.php', array('id' => $params['id']));
        $output = '<div name="button_bar" class="buttons mdl-align">';
        $output .= get_string('remindall_desc', 'organizer') . '<br />';
        $output .= $OUTPUT->single_button($sendurl, get_string("btn_send", 'organizer'), 'post');
        $output .= '</div>';
        return organizer_make_section('infobox_messaging', $output);
    } else {
        return '';
    }
}
function organizer_make_description_section($organizer) {
    $output = $organizer->intro;
    if ($organizer->isgrouporganizer) {
        $output .= '<hr />';
        $group = organizer_fetch_my_group();
        if ($group) {
            $a = new stdClass();
            $a->groupname = $group->name;
            $output .= '<p> ' . get_string('grouporganizer_desc_hasgroup', 'organizer', $a) . '</p>';
        } else {
            $output .= '<p> ' . get_string('grouporganizer_desc_nogroup', 'organizer') . '</p>';
        }
    }
    if (isset($organizer->duedate)) {
        $output .= '<hr />';
        $a = new stdClass();
        $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
        if ($organizer->duedate > time()) {
            $output .= '<p>' . get_string('infobox_organizer_expires', 'organizer', $a) . '</p>';
        } else {
            $output .= '<p>' . get_string('infobox_organizer_expired', 'organizer', $a) . '</p>';
        }
    } else {
        $output .= '<hr />';
        $output .= '<p>' . get_string('infobox_organizer_never_expires', 'organizer') . '</p>';
    }
    return organizer_make_section('infobox_description', $output);
}
function organizer_make_myapp_section($params, $organizer, $app, &$popups) {
    global $DB;
    if ($app) {
        $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
        $align = array('left', 'left', 'left', 'left', 'center', 'center');
        $sortable = array();
        $table = new html_table();
        $table->id = 'my_slot_overview';
        $table->attributes['class'] = 'generaltable boxaligncenter overview';
        $table->head = organizer_generate_table_header($columns, $sortable, $params);
        $table->data = organizer_generate_table_content($columns, $params, $organizer, $popups, true, false);
        $table->align = $align;
        $output = organizer_render_table_with_footer($table, false);
        $output = preg_replace('/<th /', '<th style="width: 0%;" ', $output); // Afterburner fix - try to fix it using css!
        $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
        if ($slot->starttime - $organizer->relativedeadline - time() > 0) {
            $a = new stdClass();
            list($a->days, $a->hours, $a->minutes, $a->seconds) =
                organizer_get_countdown($slot->starttime - $organizer->relativedeadline - time());
            $class = $a->days > 1 ? "countdown_normal" : ($a->hours > 1 ? "countdown_hurry" : "countdown_critical");
            $output .= "<p><span class=\"$class\">" . get_string('infobox_deadline_countdown', 'organizer', $a) . '</span></p>';
        } else {
            $output .= '<p><span class="countdown_passed">' . get_string('infobox_deadline_passed', 'organizer') . '</span></p>';
        }
        if ($slot->starttime - time() > 0) {
            $a = new stdClass();
            list($a->days, $a->hours, $a->minutes, $a->seconds) = organizer_get_countdown($slot->starttime - time());
            $class = $a->days > 1 ? "countdown_normal" : ($a->hours > 1 ? "countdown_hurry" : "countdown_critical");
            $output .= "<p><span class=\"$class\">" . get_string('infobox_app_countdown', 'organizer', $a) . '</span></p>';
        } else {
            $output .= '<p><span class="countdown_passed">' . get_string('infobox_app_occured', 'organizer') . '</span></p>';
        }
    } else {
        $output = '<p>' . get_string('infobox_myslot_noslot', 'organizer') . '</p>';
    }
    return organizer_make_section('infobox_myslot', $output);
}
function organizer_make_slotoptions_section($params) {
    $output = '<div style="float:left;">';

    $displaymyslotsonly = $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW;
    $displayfreeslots = $displaypastslots = $params['mode'] != ORGANIZER_TAB_REGISTRATION_STATUS_VIEW;

    $pref = get_user_preferences('mod_organizer_showmyslotsonly', false);
    $output .= '<p' . ($displaymyslotsonly ? '' : ' style="display: none;" ') . '>' .
                '<input type="checkbox" id="show_my_slots_only" ' .
                ($pref ? 'checked="true" ' : '') . ' /> ' .
                get_string('infobox_showmyslotsonly', 'organizer') . '</p>';

    $pref = get_user_preferences('mod_organizer_showfreeslotsonly', false);
    $output .= '<p' . ($displayfreeslots ? '' : ' style="display: none;" ') . '>' .
            '<input type="checkbox" id="show_free_slots_only" ' .
            ($pref ? 'checked="true" ' : '') . ' /> ' .
            get_string('infobox_showfreeslots', 'organizer') . '</p>';

    $pref = get_user_preferences('mod_organizer_showpasttimeslots', true);
    $output .= '<p' . ($displaypastslots ? '' : ' style="display: none;" ') . '>' .
                '<input type="checkbox" id="show_past_slots" ' .
                ($pref ? 'checked="true" ' : '') . ' /> ' .
                get_string('infobox_showslots', 'organizer') . '</p>';

    $output .= '</div>';
    $output .= '<div style="float:right;"><input id="toggle_legend" type="button" value="' .
            get_string('infobox_showlegend', 'organizer') . '" /></div>';
    $output .= '<div class="clearer"></div>';
    return organizer_make_section('infobox_slotoverview', $output) .
        organizer_make_section('infobox_legend', organizer_make_legend($params), true);
}
