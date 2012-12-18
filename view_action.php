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

require_once('../../config.php');
require_once('locallib.php');
require_once('view_action_form_add.php');
require_once('view_action_form_eval.php');
require_once('view_action_form_edit.php');
require_once('view_action_form_delete.php');
require_once('view_action_form_comment.php');
require_once('view_action_form_print.php');
require_once('view_action_form_remind_all.php');
require_once('print.php');
require_once('view_lib.php');
require_once('messaging.php');

//--------------------------------------------------------------------------------------------------

define('ACTION_ADD', 'add');
define('ACTION_EDIT', 'edit');
define('ACTION_DELETE', 'delete');
define('ACTION_EVAL', 'eval');
define('ACTION_PRINT', 'print');
define('ACTION_REGISTER', 'register');
define('ACTION_UNREGISTER', 'unregister');
define('ACTION_REREGISTER', 'reregister');
define('ACTION_REMIND', 'remind');
define('ACTION_REMINDALL', 'remindall');
define('ACTION_COMMENT', 'comment');

//--------------------------------------------------------------------------------------------------

list($cm, $course, $organizer, $context) = get_course_module_data();

require_login($course, false, $cm);
require_sesskey();

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ACTION);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
//optional_param('slots', null, PARAM_RAW); -> this doesn't work, do not attempt to use it!!!
$slots = isset($_POST["slots"]) ? $_POST["slots"] : (isset($_GET["slots"]) ? $_GET["slots"] : null);
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

$PAGE->requires->js('/mod/organizer/js/jquery-1.7.2.min.js', true);

$redirecturl = new moodle_url('/mod/organizer/view.php', array('id' => $cm->id, 'mode' => $mode, 'action' => $action));

$logurl = new moodle_url('/mod/organizer/view_action.php');
$logurl->param('id', $cm->id);

if ($action == ACTION_ADD) {
    require_capability('mod/organizer:addslots', $context);
    add_to_log($course->id, 'organizer', 'add', "{$logurl}", $organizer->name, $cm->id);
    $mform = new mod_organizer_slots_form(null, array('id' => $cm->id, 'mode' => $mode));

    if ($data = $mform->get_data()) {
        if (isset($data->reviewslots) || isset($data->addday)) {
            display_form($mform, get_string('title_add', 'organizer'));
        } else if (isset($data->createslots)) {
            $count = count($slotids = add_appointment_slots($data));
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
        display_form($mform, get_string('title_add', 'organizer'));
    }
    print_error('If you see this, something went wrong with add action!');
} else if ($action == ACTION_EDIT) {
    require_capability('mod/organizer:editslots', $context);
    add_to_log($course->id, 'organizer', 'edit', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new mod_organizer_slots_edit_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots),
            'post', '', array('name' => 'form_edit'));

    if ($data = $mform->get_data()) {
        $slotids = update_appointment_slot($data);

        prepare_and_send_message($data, 'edit_notify:teacher');
        prepare_and_send_message($data, 'edit_notify:student'); // ---------------------------------------- MESSAGE!!!

        $newurl = $redirecturl->out();
        foreach ($slotids as $slotid) {
            $newurl .= '&slots[]=' . $slotid;
        }

        redirect($newurl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        display_form($mform, get_string('title_edit', 'organizer'));
    }
    print_error('If you see this, something went wrong with edit action!');
} else if ($action == ACTION_EVAL) {
    require_capability('mod/organizer:evalslots', $context);
    add_to_log($course->id, 'organizer', 'eval', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new evaluate_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        $slotids = evaluate_slots($data);

        prepare_and_send_message($data, 'eval_notify:student'); // ---------------------------------------- MESSAGE!!!

        $newurl = $redirecturl->out();
        foreach ($slotids as $slotid) {
            $newurl .= '&slots[]=' . $slotid;
        }

        redirect($newurl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        display_form($mform, get_string('title_eval', 'organizer'));
    }
    print_error('If you see this, something went wrong with edit action!');
} else if ($action == ACTION_DELETE) {
    require_capability('mod/organizer:deleteslots', $context);
    add_to_log($course->id, 'organizer', 'delete', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new mod_organizer_slots_delete_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        if (isset($slots)) {
            foreach ($slots as $slotid) {
                delete_appointment_slot($slotid);
            }
        }
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        display_form($mform, get_string('title_delete', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else if ($action == ACTION_PRINT) {
    require_capability('mod/organizer:printslots', $context);

    add_to_log($course->id, 'organizer', 'print', "{$logurl}", $organizer->name, $cm->id);

    if (!$slots) {
        $redirecturl->param('messages[]', 'message_warning_no_slots_selected');
        redirect($redirecturl);
    }

    if (!security_check_slots($slots)) {
        print_error('Security failure: Some of selected slots don\'t belong to this organizer!');
    }

    $mform = new print_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        display_printable_table($data->cols, $data->slots, $data->entriesperpage, $data->textsize,
                $data->pageorientation, $data->headerfooter);
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        display_form($mform, get_string('title_print', 'organizer'), true);
    }
    print_error('If you see this, something went wrong with print action!');
} else if ($action == ACTION_COMMENT) {
    require_capability('mod/organizer:comment', $context);
    add_to_log($course->id, 'organizer', 'comment', "{$logurl}", $organizer->name, $cm->id);

    if (!security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    $mform = new mod_organizer_slots_comment_form(null, array('id' => $cm->id, 'mode' => $mode, 'slot' => $slot));

    if ($data = $mform->get_data()) {
        $app = $DB->get_record('organizer_slot_appointments', array('slotid' => $slot, 'userid' => $USER->id));
        update_comments($app->id, $data->comments);
        redirect($redirecturl);
    } else if ($mform->is_cancelled()) {
        redirect($redirecturl);
    } else {
        display_form($mform, get_string('title_comment', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else if ($action == ACTION_REGISTER) {
    require_capability('mod/organizer:register', $context);
    add_to_log($course->id, 'organizer', 'register', "{$logurl}", $organizer->name, $cm->id);

    if (!security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    $group = fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = register_appointment($slot, $groupid);

    if ($success) {
        prepare_and_send_message($slot, 'register_notify:teacher:register'); // ---------------------------------------- MESSAGE!!!
        if ($group) {
            prepare_and_send_message($slot, 'group_registration_notify:student:register');
        }
    } else {
        if (is_group_mode()) {
            $redirecturl->param('messages[]', 'message_error_slot_full_group');
        } else {
            $redirecturl->param('messages[]', 'message_error_slot_full_single');
        }
    }

    redirect($redirecturl);
} else if ($action == ACTION_UNREGISTER) {
    require_capability('mod/organizer:unregister', $context);
    add_to_log($course->id, 'organizer', 'unregister', "{$logurl}", $organizer->name, $cm->id);

    if (!security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    $group = fetch_my_group();
    $groupid = $group ? $group->id : 0;

    prepare_and_send_message($slot, 'register_notify:teacher:unregister'); // ---------------------------------------- MESSAGE!!!
    if ($group) {
        prepare_and_send_message($slot, 'group_registration_notify:student:unregister');
    }

    unregister_appointment($slot, $groupid);

    redirect($redirecturl);
} else if ($action == ACTION_REREGISTER) {
    require_capability('mod/organizer:register', $context);
    require_capability('mod/organizer:unregister', $context);
    add_to_log($course->id, 'organizer', 'reregister', "{$logurl}", $organizer->name, $cm->id);

    if (!security_check_slots($slot)) {
        print_error('Security failure: Selected slot doesn\'t belong to this organizer!');
    }

    $group = fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = reregister_appointment($slot, $groupid);

    if ($success) {
        prepare_and_send_message($slot, 'register_notify:teacher:reregister');
        if ($group) {
            echo "SHIT!";
            prepare_and_send_message($slot, 'group_registration_notify:student:reregister');
        }
    } else {
        if (is_group_mode()) {
            $redirecturl->param('messages[]', 'message_error_slot_full_group');
        } else {
            $redirecturl->param('messages[]', 'message_error_slot_full_single');
        }
    }

    redirect($redirecturl);
} else if ($action == ACTION_REMIND) {
    // WARNING! ADD GROUP CHECK!
    list($cm, $course, $organizer, $context) = get_course_module_data();
    if ($organizer->isgrouporganizer) {
        $members = groups_get_members($user);
        foreach ($members as $member) {
            prepare_and_send_message(array('user' => $member->id, 'organizer' => $organizer),
                    'register_reminder:student'); // ---------------------------------------- MESSAGE!!!
        }
    } else {
        prepare_and_send_message(array('user' => $user, 'organizer' => $organizer), 'register_reminder:student');
    }

    redirect($redirecturl);
} else if ($action == ACTION_REMINDALL) {
    add_to_log($course->id, 'organizer', 'remindall', "{$logurl}", $organizer->name, $cm->id);

    $mform = new mod_organizer_slots_remind_all_form(null, array('id' => $cm->id, 'mode' => $mode, 'slots' => $slots));

    if ($data = $mform->get_data()) {
        $count = remind_all();

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
        display_form($mform, get_string('remind_all_title', 'organizer'));
    }
    print_error('If you see this, something went wrong with delete action!');
} else {
    print_error('Either a wrong method or no method was selected!');
}

function display_form(moodleform $mform, $title, $addcalendar = true) {
    global $OUTPUT;

    if ($addcalendar) {
        add_calendar();
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    echo $OUTPUT->box_start('');
    $mform->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

    die();
}

function remind_all() {
    global $DB;

    list($cm, $course, $organizer, $context) = get_course_module_data();

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
            prepare_and_send_message(array('user' => $entry->id, 'organizer' => $organizer),
                    'register_reminder:student'); // ---------------------------------------- MESSAGE!!!
            $count++;
        }
    }
    return $count;
}

function prepare_and_send_message($data, $type) {
    global $DB, $USER;

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
            if ($organizer->emailteachers) {
                organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
            }
            break;
        case 'register_notify:teacher:reregister':
        case 'register_notify:teacher:unregister':
            $slot = $DB->get_record('organizer_slots', array('id' => $data));
            organizer_send_message(intval($USER->id), intval($slot->teacherid), $slot, $type);
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
            organizer_send_message(intval($USER->id), intval($data['user']), $data['organizer'], $type);
            break;
        default:
            print_error('Not debugged yet!');
    }
    return;
}