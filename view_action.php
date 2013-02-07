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
 * Manage files in folder module instance
 *
 * @package    mod
 * @subpackage organizer
 * @copyright  2011 Ivan Šakić
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_ACTION_ADD', 'add');
define('ORGANIZER_ACTION_EDIT', 'edit');
define('ORGANIZER_ACTION_DELETE', 'delete');
define('ORGANIZER_ACTION_EVAL', 'eval');
define('ORGANIZER_ACTION_PRINT', 'print');
define('ORGANIZER_ACTION_REGISTER', 'register');
define('ORGANIZER_ACTION_UNREGISTER', 'unregister');
define('ORGANIZER_ACTION_REREGISTER', 'reregister');
define('ORGANIZER_ACTION_REMIND', 'remind');
define('ORGANIZER_ACTION_REMINDALL', 'remindall');
define('ORGANIZER_ACTION_COMMENT', 'comment');

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_add.php');
require_once(dirname(__FILE__) . '/view_action_form_eval.php');
require_once(dirname(__FILE__) . '/view_action_form_edit.php');
require_once(dirname(__FILE__) . '/view_action_form_delete.php');
require_once(dirname(__FILE__) . '/view_action_form_comment.php');
require_once(dirname(__FILE__) . '/view_action_form_print.php');
require_once(dirname(__FILE__) . '/view_action_form_remind_all.php');
require_once(dirname(__FILE__) . '/print.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

//--------------------------------------------------------------------------------------------------

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);
require_sesskey();

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = optional_param_array('slots', array(), PARAM_INT);
$app = optional_param('app', null, PARAM_INT);

$url = new moodle_url('/mod/organizer/view_action.php');
$url->param('id', $cm->id);
$url->param('mode', $mode);
$url->param('action', $action);
$url->param('sesskey', sesskey());

$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');
$PAGE->set_title($organizer->name);
$PAGE->set_heading($course->fullname);

$jsmodule = array(
        'name' => 'mod_organizer',
        'fullpath' => '/mod/organizer/module.js',
        'requires' => array('node', 'event', 'node-screen', 'panel', 'node-event-delegate'),
);
$PAGE->requires->js_module($jsmodule);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = new moodle_url('/mod/organizer/view_action.php');
$logurl->param('id', $cm->id);

if ($action == ORGANIZER_ACTION_ADD) {
    require_capability('mod/organizer:addslots', $context);
    add_to_log($course->id, 'organizer', 'add', "{$logurl}", $organizer->name, $cm->id);
    $mform = new organizer_add_slots_form(null, array('id' => $cm->id, 'mode' => $mode));

    if ($data = $mform->get_data()) {
        if (isset($data->reviewslots) || isset($data->addday)) {
            organizer_display_form($mform, get_string('title_add', 'organizer'));
        } else if (isset($data->createslots)) {
            $count = count($slotids = organizer_add_appointment_slots($data));
            if ($count == 0) {
                $redirecturl->param('messages[]', 'message_warning_no_slots_added');
            } else {
                $redirecturl->param('data[count]', $count);
                if ($count == 1) {
                    $redirecturl->param('messages[]', 'message_info_slots_added_sg');
                } else {
                    $redirecturl->param('messages[]', 'message_info_slots_added_pl');
                }

                $redirecturl = $redirecturl->out();
                foreach ($slotids as $slotid) {
                    $redirecturl .= '&slots[]=' . $slotid;
                }
            }
            redirect($redirecturl);
        } else {
            print_error('Something went wrong with the submission of the add action!');
        }
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_add', 'organizer'));
    }
    print_error('If you see this, something went wrong with add action!');
} else if ($action == ORGANIZER_ACTION_EDIT) {
    require_capability('mod/organizer:editslots', $context);
    add_to_log($course->id, 'organizer', 'edit', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new organizer_edit_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots),
            'post', '', array('name' => 'form_edit'));

    if ($data = $mform->get_data()) {
        $slotids = organizer_update_appointment_slot($data);

        organizer_prepare_and_send_message($data, 'edit_notify:teacher');
        organizer_prepare_and_send_message($data, 'edit_notify:student'); // ---------------------------------------- MESSAGE!!!

        $newurl = $redirecturl->out();
        foreach ($slotids as $slotid) {
            $newurl .= '&slots[]=' . $slotid;
        }

        redirect($newurl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_edit', 'organizer'));
    }
    print_error('If you see this, something went wrong with edit action!');
} else if ($action == ORGANIZER_ACTION_EVAL) {
    require_capability('mod/organizer:evalslots', $context);
    add_to_log($course->id, 'organizer', 'eval', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new organizer_evaluate_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        $slotids = organizer_evaluate_slots($data);

        organizer_prepare_and_send_message($data, 'eval_notify:student'); // ---------------------------------------- MESSAGE!!!

        $newurl = $redirecturl->out();
        foreach ($slotids as $slotid) {
            $newurl .= '&slots[]=' . $slotid;
        }

        redirect($newurl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_eval', 'organizer'));
    }
    print_error('If you see this, something went wrong with edit action!');
} else if ($action == ORGANIZER_ACTION_DELETE) {
    require_capability('mod/organizer:deleteslots', $context);
    add_to_log($course->id, 'organizer', 'delete', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new organizer_delete_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        if (isset($slots)) {
            foreach ($slots as $slotid) {
                organizer_delete_appointment_slot($slotid);
            }
        }
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_delete', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else if ($action == ORGANIZER_ACTION_PRINT) {
    require_capability('mod/organizer:printslots', $context);

    add_to_log($course->id, 'organizer', 'print', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!organizer_security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new organizer_print_slots_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        organizer_display_printable_table($data->cols, $data->slots, $data->entriesperpage, $data->textsize,
                $data->pageorientation, $data->headerfooter);
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_print', 'organizer'), true);
    }
    print_error('If you see this, something went wrong with print action!');
} else if ($action == ORGANIZER_ACTION_COMMENT) {
    require_capability('mod/organizer:comment', $context);
    add_to_log($course->id, 'organizer', 'comment', "{$logurl}", $organizer->name, $cm->id);

    if (!organizer_security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    $mform = new organizer_comment_slot_form(null, array('id' => $cm->id, 'mode' => $mode, 'slot' => $slot));

    if ($data = $mform->get_data()) {
        $app = $DB->get_record('organizer_slot_appointments', array('slotid' => $slot, 'userid' => $USER->id));
        organizer_update_comments($app->id, $data->comments);
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('title_comment', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else if ($action == ORGANIZER_ACTION_REGISTER) {
    require_capability('mod/organizer:register', $context);
    add_to_log($course->id, 'organizer', 'register', "{$logurl}", $organizer->name, $cm->id);

    if (!organizer_security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    if (!organizer_organizer_student_action_allowed($action, $slot)) {
        print_error('Inconsistent state: Cannot execute registration action! Please navigate back and refresh your browser!');
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = organizer_register_appointment($slot, $groupid);

    if ($success) {
        organizer_prepare_and_send_message($slot, 'register_notify:teacher:register'); // ---------------------------------------- MESSAGE!!!
        if ($group) {
            organizer_prepare_and_send_message($slot, 'group_registration_notify:student:register');
        }
    } else {
        if (organizer_is_group_mode()) {
            $redirecturl->param('messages[]', 'message_error_slot_full_group');
        } else {
            $redirecturl->param('messages[]', 'message_error_slot_full_single');
        }
    }

    redirect($redirecturl);
} else if ($action == ORGANIZER_ACTION_UNREGISTER) {
    require_capability('mod/organizer:unregister', $context);
    add_to_log($course->id, 'organizer', 'unregister', "{$logurl}", $organizer->name, $cm->id);

    if (!organizer_security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    if (!organizer_organizer_student_action_allowed($action, $slot)) {
        print_error('Inconsistent state: Cannot execute registration action! Please navigate back and refresh your browser!');
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;

    organizer_prepare_and_send_message($slot, 'register_notify:teacher:unregister'); // ---------------------------------------- MESSAGE!!!
    if ($group) {
        organizer_prepare_and_send_message($slot, 'group_registration_notify:student:unregister');
    }

    organizer_unregister_appointment($slot, $groupid);

    redirect($redirecturl);
} else if ($action == ORGANIZER_ACTION_REREGISTER) {
    require_capability('mod/organizer:register', $context);
    require_capability('mod/organizer:unregister', $context);
    add_to_log($course->id, 'organizer', 'reregister', "{$logurl}", $organizer->name, $cm->id);

    if (!organizer_security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    if (!organizer_organizer_student_action_allowed($action, $slot)) {
        print_error('Inconsistent state: Cannot execute registration action! Please navigate back and refresh your browser!');
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = organizer_reregister_appointment($slot, $groupid);

    if ($success) {
        organizer_prepare_and_send_message($slot, 'register_notify:teacher:reregister');
        if ($group) {
            organizer_prepare_and_send_message($slot, 'group_registration_notify:student:reregister');
        }
    } else {
        if (organizer_is_group_mode()) {
            $redirecturl->param('messages[]', 'message_error_slot_full_group');
        } else {
            $redirecturl->param('messages[]', 'message_error_slot_full_single');
        }
    }

    redirect($redirecturl);
} else if ($action == ORGANIZER_ACTION_REMIND) {
    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
    $count = 0;
    if ($organizer->isgrouporganizer) {
        $members = groups_get_members($user);
        foreach ($members as $member) {
            $success = organizer_prepare_and_send_message(array('user' => $member->id, 'organizer' => $organizer),
                    'register_reminder:student'); // ---------------------------------------- MESSAGE!!!
            if ($success) {
                $count++;
            }
        }
    } else {
        $success = organizer_prepare_and_send_message(array('user' => $user, 'organizer' => $organizer), 'register_reminder:student');
        if ($success) {
            $count++;
        }
    }

    $redirecturl->param('data[count]', $count);
    if ($count == 1) {
        $redirecturl->param('messages[]', 'message_info_reminders_sent_sg');
    } else {
        $redirecturl->param('messages[]', 'message_info_reminders_sent_pl');
    }
    
    $redirecturl = $redirecturl->out();
    
    redirect($redirecturl);
} else if ($action == ORGANIZER_ACTION_REMINDALL) {
    add_to_log($course->id, 'organizer', 'remindall', "{$logurl}", $organizer->name, $cm->id);

    $mform = new organizer_remind_all_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        $count = organizer_remind_all();

        $redirecturl->param('data[count]', $count);
        if ($count == 1) {
            $redirecturl->param('messages[]', 'message_info_reminders_sent_sg');
        } else {
            $redirecturl->param('messages[]', 'message_info_reminders_sent_pl');
        }

        $redirecturl = $redirecturl->out();
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        organizer_display_form($mform, get_string('organizer_remind_all_title', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else {
    print_error('Either a wrong method or no method was selected!');
}

die;

function organizer_organizer_student_action_allowed($action, $slot) {
    global $DB;
    
    if (!$DB->record_exists('organizer_slots', array('id' => $slot))) {
        return false;
    }

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
    $didnotattend = isset($myapp->attended) && $myapp->attended == 0;

    $disabled = $myslotpending || $organizerdisabled || $slotdisabled || !$slotx->organizer_user_has_access() || $slotx->is_evaluated();

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
        $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REREGISTER;
    } else {
        $disabled |= $slotfull || !$canregister || $ismyslot;
        $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REGISTER;
    }

    return !$disabled && ($action == $allowedaction);
}

function organizer_display_form(moodleform $mform, $title, $addcalendar = true) {
    global $OUTPUT;

    if ($addcalendar) {
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

function organizer_remind_all() {
    global $DB;

    list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

    if ($cm->groupingid == 0) {
        $entries = get_enrolled_users($context, 'mod/organizer:register');
    } else {
        $query = "SELECT u.* FROM {user} u
            INNER JOIN {groups_members} gm ON u.id = gm.userid
            INNER JOIN {groups} g ON gm.groupid = g.id
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :grouping";
        $par = array('grouping' => $cm->groupingid);
        $entries = $DB->get_records_sql($query, $par);
    }

    $query = "SELECT DISTINCT u.id FROM {organizer} o
        INNER JOIN {organizer_slots} s ON o.id = s.organizerid
        INNER JOIN {organizer_slot_appointments} a ON s.id = a.slotid
        INNER JOIN {user} u ON a.userid = u.id
        WHERE o.id = :id AND (a.attended = 1 OR a.attended IS NULL)";
    $par = array('id' => $organizer->id);
    $nonrecepients = $DB->get_fieldset_sql($query, $par);

    $count = 0;
    foreach ($entries as $entry) {
        if (!in_array($entry->id, $nonrecepients)) {
            organizer_prepare_and_send_message(array('user' => $entry->id, 'organizer' => $organizer),
                    'register_reminder:student'); // ---------------------------------------- MESSAGE!!!
            $count++;
        }
    }
    return $count;
}

function organizer_prepare_and_send_message($data, $type) {
    global $DB, $USER;

    require_once('lib.php');
    
    switch ($type) {
        case 'edit_notify:student':
            foreach ($data->slots as $slotid) {
                $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slotid));
                $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
                foreach ($apps as $app) {
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    organizer_send_message(intval($slot->teacherid), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'edit_notify:teacher':
            foreach ($data->slots as $slotid) {
                $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
                if ($USER->id != $slot->teacherid) {
                    organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
                }
            }
            break;
        case 'eval_notify:student':
            if (isset($data->apps) && count($data->apps) > 0) {
                foreach ($data->apps as $appid => $app) {
                    $app = $DB->get_record('organizer_slot_appointments', array('id' => $appid));
                    if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                        continue;
                    }
                    $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));
                    organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'register_notify:teacher:register': // TODO: check how it was actually originally defined
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'register_notify:teacher:reregister':
        case 'register_notify:teacher:unregister':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
            if ($organizer->emailteachers == ORGANIZER_MESSAGES_RE_UNREG || $organizer->emailteachers == ORGANIZER_MESSAGES_ALL) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'group_registration_notify:student:register':
        case 'group_registration_notify:student:reregister':
        case 'group_registration_notify:student:unregister':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            $apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $slot->id));
            foreach ($apps as $app) {
                if ($app->groupid && !groups_is_member($app->groupid, $app->userid)) {
                    continue;
                }
                if ($app->userid != $USER->id) {
                    organizer_send_message(intval($USER->id), intval($app->userid), $slot, $type);
                }
            }
            break;
        case 'register_reminder:student':
            return organizer_send_message(intval($USER->id), intval($data['user']), $data['organizer'], $type);
        default:
            print_error('Not debugged yet!');
    }
    return;
}