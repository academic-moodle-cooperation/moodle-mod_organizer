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
 * @author        Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/legend.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/slotlib.php');

function organizer_make_infobox($params, $organizer, $context, $organizerexpired = null) {
    global $PAGE;

    $output = '';
    if ($organizer->alwaysshowdescription ||  time() > $organizer->allowregistrationsfromdate) {
        // Module description, group and duedate informations.
        $output = organizer_make_description_section($organizer, $params['id']);
    }

    $jsparams = new stdClass();
    $jsparams->studentview = 0;

    switch($params['mode']) {
        case ORGANIZER_TAB_APPOINTMENTS_VIEW:
            $output .= organizer_make_addslotbutton_section($params, $organizerexpired);
        break;
        case ORGANIZER_TAB_STUDENT_VIEW:
            // My own booking information section.
            $output .= organizer_make_myapp_section($params, $organizer,
                organizer_get_last_user_appointment($organizer));
            $jsparams->studentview = 1;
        break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            // Button for sending reminders to all participants without an appointment.
            if ($entries = organizer_get_registrationview_entries(
                $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS, $params)) {
                $output .= organizer_make_sendreminder_section($params, $context);
            }
        break;
        case ORGANIZER_ASSIGNMENT_VIEW:
        break;
        default:
            print_error("Wrong view mode: {$params['mode']}");
    }
    // Display messages here.
    $output .= organizer_make_messages_section($params);
    // Display section with predefined filter view options like "hidden slots only" etc..
    $output .= organizer_make_slotoptions_section($params);
    // Display search field for fulltext search.
    $output .= organizer_make_filtersection();

    $PAGE->requires->js_call_amd('mod_organizer/initinfobox', 'init', array($jsparams->studentview));

    return $output;
}
function organizer_make_section($name, $content, $hidden = false) {
    $output = "";
    if ($name) {
        if ($name != 'infobox_messages') {
            $output = '<div id="' . $name . '_box" class="block_course_overview block"' .
                ($hidden ? ' style="display: none;"' : '') . '>';
            $output .= '<div id="' . $name . '_header" class="header"><div class="title"><h2>'
                . get_string("{$name}_title", "organizer") . '</h2></div></div>';
            $output .= '</div>';
        }
    }
    $output .= '<div id="' . $name . '_content" class="content">';
    $output .= $content;
    $output .= '</div>';
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
function organizer_make_sendreminder_section($params, $context) {
    global $OUTPUT;
    if (has_capability("mod/organizer:sendreminders", $context, null, true)) {
        $sendurl = new moodle_url('send_reminder.php', array('id' => $params['id']));
        $output = '<div name="button_bar" class="organizer_addbutton_div">';
        $output .= $OUTPUT->single_button($sendurl, get_string("btn_sendall", 'organizer'), 'post');
        $output .= '</div>';
        return organizer_make_section('infobox_messaging', $output);
    } else {
        return '';
    }
}
function organizer_make_description_section($organizer, $cmid) {
    global $OUTPUT;

    $output = '<br>';
    $output .= format_module_intro('organizer', $organizer, $cmid);
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
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
        $a = new stdClass();
        $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
        if ($organizer->duedate > time()) {
            $output .= '<p>' . get_string('infobox_organizer_expires', 'organizer', $a) . '</p>';
        } else {
            $output .= '<p>' . get_string('infobox_organizer_expired', 'organizer', $a) . '</p>';
        }
    } else {
        $output .= '<p>' . get_string('infobox_organizer_never_expires', 'organizer') . '</p>';
    }
    $output .= '<br>';

    return $OUTPUT->box($output, 'generalbox', 'intro');
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
        $table->data = organizer_generate_table_content($columns, $params, $organizer, true);
        $table->align = $align;
        $output = organizer_render_table_with_footer($table, false);
        $output = preg_replace('/<th /', '<th style="width: 0%;" ', $output); // Afterburner fix - try to fix it using css!
        $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
        if ($slot->starttime - $organizer->relativedeadline - time() > 0) {
            $a = new stdClass();
            list($a->days, $a->hours, $a->minutes, $a->seconds) = organizer_get_countdown(
                    $slot->starttime - $organizer->relativedeadline - time());
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

    $output = '<div>';

    $displaymyslotsonly = $displayhiddenslots = $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW;
    $displayfreeslots = $displaypastslots = $params['mode'] != ORGANIZER_TAB_REGISTRATION_STATUS_VIEW;
    $displayregistrationsonly = true;

    $output .= '<span' . ($displaymyslotsonly ? '' : ' style="display: none;" ') . '>' .
                '<input type="checkbox" id="show_my_slots_only" /> ' .
                get_string('infobox_showmyslotsonly', 'organizer') . '&nbsp;&nbsp;&nbsp;</span>';

    $output .= '<span' . ($displayfreeslots ? '' : ' style="display: none;" ') . '>' .
            '<input type="checkbox" id="show_free_slots_only"  /> ' .
            get_string('infobox_showfreeslots', 'organizer') . '&nbsp;&nbsp;&nbsp;</span>';

    $output .= '<span' . ($displayhiddenslots ? '' : ' style="display: none;" ') . '>' .
        '<input type="checkbox" id="show_hidden_slots" /> ' .
        get_string('infobox_showhiddenslots', 'organizer') . '&nbsp;&nbsp;&nbsp;</span>';

    $output .= '<span' . ($displaypastslots ? '' : ' style="display: none;" ') . '>' .
                '<input type="checkbox" id="show_past_slots" /> ' .
                get_string('infobox_showslots', 'organizer') . '&nbsp;&nbsp;&nbsp;</span>';

    $output .= '<span' . ($displayregistrationsonly ? '' : ' style="display: none;" ') . '>' .
        '<input type="checkbox" id="show_registrations_only" /> ' .
        get_string('infobox_showregistrationsonly', 'organizer') . '</span>';

    $output .= '</div>';

    return organizer_make_section('infobox_slotoverview', $output);
}

function organizer_make_filtersection() {
    global $OUTPUT;

    $output = '<p class="organizer_filterblock">';
    $output .= '<span id="organizer_filterfield">' . get_string('search') .
        $OUTPUT->help_icon('filtertable', 'organizer', '');
    $output .= html_writer::tag('input', null,
        array('type' => 'text', 'name' => 'filterparticipants', 'class' => 'organizer_filtertable'));
    $output .= '</span>';
    $output .= '</p>';
    $output .= '<div class="clearer">&nbsp;</div>';

    return $output;
}

function organizer_make_addslotbutton_section($params, $organizerexpired) {

    $output = '<div id="organizer_addbutton_div">';

    $slotsaddurl = new moodle_url('/mod/organizer/slots_add.php', array('id' => $params['id']));
    $output .= '<input class="btn btn-primary" type="submit" value="' . get_string('btn_add', 'organizer') .
        '" onClick="this.parentNode.parentNode.setAttribute(\'action\', \'' . $slotsaddurl . '\');" ' .
        ($organizerexpired ? 'disabled ' : '') . '/>';

    $output .= '</div>';

    return $output;
}


