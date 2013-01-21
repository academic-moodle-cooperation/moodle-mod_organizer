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
require_once('lib.php');
require_once('legend.php');
require_once('locallib.php');
require_once('slotlib.php');
function organizer_make_infobox($params, $organizer, $context) {
    global $PAGE;
    user_preference_allow_ajax_update('mod_organizer_showpasttimeslots', PARAM_BOOL);
    user_preference_allow_ajax_update('mod_organizer_showmyslotsonly', PARAM_BOOL);
    $PAGE->requires->js_init_call('M.mod_organizer.init_infobox');
    
    $output = organizer_make_description_section($organizer);
    switch($params['mode']) {
        case TAB_APPOINTMENTS_VIEW:
        break;
        case TAB_STUDENT_VIEW:
            $output .= organizer_make_myapp_section($params, $organizer, organizer_get_last_user_appointment($organizer));
        break;
        case TAB_REGISTRATION_STATUS_VIEW:
            $output .= organizer_make_reminder_section($params, $context);
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
        return organizer_get_img('pix/message_warning.png', '', 'Warning');
    } else if (strpos($message, 'info') !== false) {
        return organizer_get_img('pix/message_info.png', '', 'Info');
    } else if (strpos($message, 'error') !== false) {
        return organizer_get_img('pix/message_error.png', '', 'Error');
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
        $sendurl = new moodle_url('view_action.php',
                array('id' => $params['id'], 'mode' => $params['mode'], 'action' => 'remindall'));
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
    if (isset($organizer->enableuntil)) {
        $output .= '<hr />';
        $a = new stdClass();
        $a->date = userdate($organizer->enableuntil, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($organizer->enableuntil, get_string('timetemplate', 'organizer'));
        if ($organizer->enableuntil > time()) {
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
function organizer_make_myapp_section($params, $organizer, $app) {
    global $DB;
    if ($app) {
        $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
        $align = array('left', 'left', 'left', 'left', 'center', 'center');
        $sortable = array();
        $table = new html_table();
        $table->id = 'my_slot_overview';
        $table->attributes['class'] = 'generaltable boxaligncenter overview';
        $table->head = organizer_generate_table_header($columns, $sortable, $params);
        $table->data = organizer_generate_table_content($columns, $params, $organizer, true, false);
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
    if ($params['mode'] != TAB_REGISTRATION_STATUS_VIEW) {
        if ($params['mode'] == TAB_APPOINTMENTS_VIEW) {
            $pref = get_user_preferences('mod_organizer_showmyslotsonly');
            $output .= '<p><input type="checkbox" id="show_my_slots_only" ' .
                        ($pref ? 'checked="true" ' : '') . ' /> ' .
                        get_string('infobox_showmyslotsonly', 'organizer') . '</p>';
        }
        $pref = get_user_preferences('mod_organizer_showpasttimeslots');
        $output .= '<p><input type="checkbox" id="show_past_slots" ' .
                    ($pref ? 'checked="true" ' : '') . ' /> ' .
                    get_string('infobox_showslots', 'organizer') . '</p>';
    }
    $output .= '</div>';
    $output .= '<div style="float:right;"><input id="toggle_legend" type="button" value="' .
            get_string('infobox_showlegend', 'organizer') . '" /></div>';
    $output .= '<div class="clearer"></div>';
    return organizer_make_section('infobox_slotoverview', $output) . organizer_make_section('infobox_legend', organizer_make_legend($params), true);
}