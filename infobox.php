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

require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/legend.php');
require_once(dirname(__FILE__) . '/slotlib.php');

function organizer_make_infobox($params, $organizer, $context, $organizerexpired = null) {
    global $PAGE, $USER;

    $output = '';

    // Display messages here.
    $output .= organizer_make_messages_section($params);

    // Module description section
    if ($organizer->alwaysshowdescription ||  time() > $organizer->allowregistrationsfromdate) {
        // Module description, group and duedate informations.
        $output .= organizer_make_description_section($organizer, $params['id']);
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
                organizer_get_all_user_appointments($organizer));
            $jsparams->studentview = 1;
        break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            // Button for sending reminders to all participants without an appointment.
            if ($entries = organizer_get_registrationview_entries(
                $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS, $params)) {
                $output .= organizer_make_sendreminder_section($params, $context);
            }
            $output .= organizer_make_registrationstatistic_section($organizer, $entries);
        break;
        case ORGANIZER_ASSIGNMENT_VIEW:
        break;
        default:
            print_error("Wrong view mode: {$params['mode']}");
    }
    if ($params['mode'] != ORGANIZER_TAB_REGISTRATION_STATUS_VIEW) {
        // Display section with predefined filter view options like "hidden slots only" etc..
        $output .= organizer_make_slotoptions_section($params['mode']);
    }
    // Display search field for fulltext search.
    $output .= organizer_make_filtersection($params['mode']);

    $PAGE->requires->js_call_amd('mod_organizer/initinfobox', 'init', array($jsparams->studentview, $USER->id));

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
function organizer_make_messages_section($params) {
    global $OUTPUT;

    $output = '';
    $infoboxmessage = $_SESSION["infoboxmessage"] ?? '';
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
function organizer_make_sendreminder_section($params, $context) {
    global $OUTPUT;
    if (has_capability("mod/organizer:sendreminders", $context, null, true)) {
        $sendurl = new moodle_url('send_reminder.php', array('id' => $params['id']));
        $output = $OUTPUT->single_button($sendurl, get_string("btn_sendall", 'organizer'), true);
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
            $output .= $OUTPUT->notification(get_string('grouporganizer_desc_hasgroup', 'organizer', $a), 'success');
        } else {
            $output .= $OUTPUT->notification(get_string('grouporganizer_desc_novalidgroup', 'organizer'), 'warning');
        }
    }
    if (isset($organizer->duedate)) {
        $a = new stdClass();
        $a->date = userdate($organizer->duedate, get_string('fulldatetemplate', 'organizer'));
        $a->time = userdate($organizer->duedate, get_string('timetemplate', 'organizer'));
        if ($organizer->duedate > time()) {
            $output .= $OUTPUT->notification(get_string('infobox_organizer_expires', 'organizer', $a), 'info');
        } else {
            $output .= $OUTPUT->notification(get_string('infobox_organizer_expired', 'organizer', $a), 'warning');
        }
    } else {
        $output .= $OUTPUT->notification(get_string('infobox_organizer_never_expires', 'organizer'), 'info');
    }
    $output .= '<br>';

    return $OUTPUT->box($output, 'generalbox', 'intro');
}
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
        $columns = array('datetime', 'location', 'participants', 'teacher', 'status', 'actions');
        $align = array('left', 'left', 'left', 'left', 'center', 'center');
        $sortable = array();
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
function organizer_make_registrationstatistic_section($organizer, $entries) {
    $a = new stdClass();
    $a->max = $organizer->userslotsmax;
    $a->min = $organizer->userslotsmin;
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        list($a->entries, $a->undermin, $a->maxreached) =
            organizer_registration_statistics($organizer, true, $entries);
        $messageminreached = get_string('infobox_statistic_minreached_group', 'organizer', $a);
        $messagemaxreached = get_string('infobox_statistic_maxreached_group', 'organizer', $a);
    } else {
        list($a->entries, $a->undermin, $a->maxreached) =
            organizer_registration_statistics($organizer, false, $entries);
        $messageminreached = get_string('infobox_statistic_minreached', 'organizer', $a);
        $messagemaxreached = get_string('infobox_statistic_maxreached', 'organizer', $a);
    }
    $allminreached = $a->undermin ? true : false;
    $allmaxreached = (int) $a->entries == (int) $a->maxreached ? true : false;

    $out = "";

    $out .= html_writer::start_div('registrationstatusbar w-50', array('title' => $messageminreached));
    if ($allminreached) {
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-success bg-success',
            array('width' => '100%'));
    } else {
        $partialfullwidth = ((int) $a->undermin * 100 / (int) $a->entries);
        $partialemptywidth = 100 - $partialfullwidth;
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-warning bg-warning',
            array('width' => $partialfullwidth."%"));
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-warning',
            array('width' => $partialemptywidth."%"));
    }
    $out .= html_writer::span($messageminreached, 'ml-3');
    $out .= html_writer::end_div();

    $out .= html_writer::start_div('registrationstatusbar w-50', array('title' => $messagemaxreached));
    if ($allmaxreached) {
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-success bg-success',
            array('width' => '100%'));
    } else {
        $partialfullwidth = ((int) $a->maxreached * 100 / (int) $a->entries);
        $partialemptywidth = 100 - $partialfullwidth;
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-warning bg-warning',
            array('width' => $partialfullwidth."%"));
        $out .= html_writer::span(' ', 'registrationstatusbarleg align-middle border border-warning',
            array('width' => $partialemptywidth."%"));
    }
    $out .= html_writer::span($messagemaxreached, 'ml-3');
    $out .= html_writer::end_div();

    return organizer_make_section('infobox_registrationstatistic', $out);
}
function organizer_make_filtersection($mode) {
    global $OUTPUT;

    // Display filter - options.

    $output = html_writer::start_div('organizer_filterblock');
    $output .= html_writer::start_span('', array('id' => 'organizer_filterfield')).
        get_string('searchfilter', 'organizer').$OUTPUT->help_icon('filtertable', 'organizer', '');
    $output .= html_writer::tag('input', null,
        array('type' => 'text', 'name' => 'filterparticipants', 'class' => 'organizer_filtertable'));
    $output .= html_writer::end_span();

    $displaymyslotsonly = $mode == ORGANIZER_TAB_APPOINTMENTS_VIEW;
    $displayregistrationsonly = $displayfreeslots = $mode == ORGANIZER_TAB_APPOINTMENTS_VIEW;
    if ($prefs = get_user_preferences('mod_organizer_slotsviewoptions', false)) {
        $showmyslotsonly = substr($prefs, 0, 1) ? true : false;
        $showfreeslotsonly = substr($prefs, 1, 1) ? true : false;
        $showregistrationsonly = substr($prefs, 4, 1) ? true : false;
    } else {
        $showmyslotsonly = $showfreeslotsonly = $showregistrationsonly = false;
    }
    if ($displaymyslotsonly) {
        $output .= html_writer::checkbox('show_my_slots_only', '1', $showmyslotsonly,
            get_string('infobox_showmyslotsonly', 'organizer'),
            array('id' => 'show_my_slots_only', 'class' => 'slotoptions'));
    }
    if ($displayfreeslots) {
        $output .= html_writer::checkbox('show_free_slots_only', '1', $showfreeslotsonly,
            get_string('infobox_showfreeslots', 'organizer'),
            array('id' => 'show_free_slots_only', 'class' => 'slotoptions'));
    }
    if ($displayregistrationsonly) {
        $output .= html_writer::checkbox('show_registrations_only', '1', $showregistrationsonly,
            get_string('infobox_showregistrationsonly', 'organizer'),
            array('id' => 'show_registrations_only', 'class' => 'slotoptions'));
    }

    $output .= html_writer::end_div();
    $output .= html_writer::div('', 'clearer');

    return $output;
}
function organizer_make_slotoptions_section($mode) {
    global $OUTPUT;

    // Display show more - options.

    $output = html_writer::start_div();
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
            array('id' => 'show_hidden_slots', 'class' => 'slotoptions'));
    }
    if ($displaypastslots) {
        $output .= html_writer::checkbox('show_past_slots', '1', $showpastslots,
            get_string('infobox_showslots', 'organizer'),
            array('id' => 'show_past_slots', 'class' => 'slotoptions'));
    }

    $output .= html_writer::end_div();;

    return organizer_make_section('infobox_slotoverview', $output);
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
