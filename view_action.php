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
 * view_action.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_ACTION_REGISTER', 'register');
define('ORGANIZER_ACTION_UNREGISTER', 'unregister');
define('ORGANIZER_ACTION_REREGISTER', 'reregister');
define('ORGANIZER_ACTION_COMMENT', 'comment');
define('ORGANIZER_ACTION_QUEUE', 'queue');
define('ORGANIZER_ACTION_UNQUEUE', 'unqueue');

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/view_action_form_comment.php');
require_once(dirname(__FILE__) . '/view_action_form_print.php');
require_once(dirname(__FILE__) . '/messaging.php');
require_once($CFG->dirroot . '/mod/organizer/classes/event/queue_added.php');
require_once($CFG->dirroot . '/mod/organizer/classes/event/queue_removed.php');

list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

require_login($course, false, $cm);
require_sesskey();

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHA);
$bulkaction = optional_param('bulkaction', null, PARAM_ALPHA);
$user = optional_param('user', null, PARAM_INT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = organizer_get_param_slots();
$app = optional_param('app', null, PARAM_INT);
$tsort = optional_param('tsort', null, PARAM_ALPHA);

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

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

if ($bulkaction) {
    if (!$slots) {
        // If an action is chosen but no slots were selected: redirect with message.
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_warning_no_slots_selected', 'organizer'),
            'error');
        redirect($redirecturl->out());
    } else {
        $slotids = implode(',', array_values($slots));

        $organizerexpired = isset($organizer->duedate) && $organizer->duedate - time() < 0;
        switch($bulkaction) {
            case 'edit':
                require_capability('mod/organizer:editslots', $context);
                $redirecturl = new moodle_url('/mod/organizer/slots_edit.php',
                    array('id' => $cm->id, 'mode' => $mode, 'slots' => $slotids));
                redirect($redirecturl);
            break;
            case 'delete':
                require_capability('mod/organizer:deleteslots', $context);
                $redirecturl = new moodle_url('/mod/organizer/slots_delete.php',
                    array('id' => $cm->id, 'mode' => $mode, 'slots' => $slotids));
                redirect($redirecturl);
            break;
            case 'print':
                require_capability('mod/organizer:printslots', $context);
                $redirecturl = new moodle_url('/mod/organizer/slots_print.php',
                    array('id' => $cm->id, 'mode' => $mode, 'slots' => $slotids));
                redirect($redirecturl);
            break;
            case 'eval':
                require_capability('mod/organizer:evalslots', $context);
                $redirecturl = new moodle_url('/mod/organizer/slots_eval.php',
                    array('id' => $cm->id, 'mode' => $mode, 'slots' => $slotids));
                redirect($redirecturl);
            break;
            default:
                print_error('Unknown bulkaction!');
            die;
        }
    }

}

if ($action == ORGANIZER_ACTION_REGISTER || $action == ORGANIZER_ACTION_QUEUE) {
    require_capability('mod/organizer:register', $context);

    if (!organizer_participants_action_allowed($action, $slot, $organizer, $context)) {
        $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_action_notallowed',
            'organizer'), 'success');
        redirect($redirecturl);
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = organizer_register_appointment($slot, $groupid);

    if ($success) {
        if ($action == ORGANIZER_ACTION_QUEUE) {
            if ($groupid) {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_queued_group',
                    'organizer'), 'success');
            } else {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_queued',
                    'organizer'), 'success');
            }
            $event = \mod_organizer\event\queue_added::create(
                array(
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context
                )
            );
            organizer_prepare_and_send_message($slot, 'register_notify_teacher:queue'); // Message.
            if ($group) {
                organizer_prepare_and_send_message($slot, 'group_registration_notify:student:queue');
            }
        } else {
            if ($groupid) {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_registered_group',
                    'organizer'), 'success');
            } else {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_registered',
                    'organizer'), 'success');
            }
            $event = \mod_organizer\event\appointment_added::create(
                array(
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context
                )
            );
            organizer_prepare_and_send_message($slot, 'register_notify_teacher:register'); // Message.
            if ($group) {
                organizer_prepare_and_send_message($slot, 'group_registration_notify:student:register');
            }
        }
        $event->trigger();
    } else {
        if ($groupid) {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_slot_full_group',
                'organizer'), 'error');
        } else {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_slot_full_single',
                'organizer'), 'error');
        }
    }

    redirect($redirecturl);

} else if ($action == ORGANIZER_ACTION_UNREGISTER || $action == ORGANIZER_ACTION_UNQUEUE) {

    require_capability('mod/organizer:unregister', $context);

    if (!organizer_participants_action_allowed($action, $slot, $organizer, $context)) {
        print_error('Inconsistent state: Cannot execute registration action! Please navigate back and refresh your browser!');
        die();
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;


    if ($action == ORGANIZER_ACTION_UNREGISTER) {
        $success = organizer_unregister_appointment($slot, $groupid, $organizer->id);
    } else {
        $success = organizer_delete_from_queue($slot, $USER->id, $groupid);
    }

    if ($success) {
        if ($action == ORGANIZER_ACTION_UNREGISTER) {
            if ($groupid) {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_unregistered_group',
                    'organizer'), 'success');
            } else {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_unregistered',
                    'organizer'), 'success');
            }
            $event = \mod_organizer\event\appointment_removed::create(
                array(
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context
                )
            );
            organizer_prepare_and_send_message($slot, 'register_notify_teacher:unregister'); // Message.
            if ($group) {
                organizer_prepare_and_send_message($slot, 'group_registration_notify:student:unregister');
            }
        } else {
            if ($groupid) {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_unqueued_group',
                    'organizer'), 'success');
            } else {
                $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_unqueued',
                    'organizer'), 'success');
            }
            $event = \mod_organizer\event\queue_removed::create(
                array(
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context
                )
            );
            organizer_prepare_and_send_message($slot, 'register_notify_teacher:unqueue'); // Message.
            if ($group) {
                organizer_prepare_and_send_message($slot, 'group_registration_notify:student:unqueue');
            }
        }
        $event->trigger();
    } else {
        if ($action == ORGANIZER_ACTION_UNREGISTER) {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_unknown_unregister',
                'organizer'), 'error');
        } else {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_unknown_unqueue',
                'organizer'), 'error');
        }
    }

    redirect($redirecturl);

} else if ($action == ORGANIZER_ACTION_REREGISTER) {

    require_capability('mod/organizer:register', $context);
    require_capability('mod/organizer:unregister', $context);

    if (!organizer_participants_action_allowed($action, $slot, $organizer, $context)) {
        print_error('Inconsistent state: Cannot execute registration action! Please navigate back and refresh your browser!');
    }

    $group = organizer_fetch_my_group();
    $groupid = $group ? $group->id : 0;
    $success = organizer_reregister_appointment($slot, $groupid);

    if ($success) {
        if ($groupid) {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_reregistered_group',
                'organizer'), 'success');
        } else {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_info_reregistered',
                'organizer'), 'success');
        }
        $event = \mod_organizer\event\appointment_removed::create(
            array(
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context
            )
        );
        $event->trigger();

        $event = \mod_organizer\event\appointment_added::create(
            array(
                'objectid' => $PAGE->cm->id,
                'context' => $PAGE->context
            )
        );
        $event->trigger();

        organizer_prepare_and_send_message($slot, 'register_notify_teacher:reregister');
        if ($group) {
            organizer_prepare_and_send_message($slot, 'group_registration_notify:student:reregister');
        }
    } else {
        if (organizer_is_group_mode()) {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_slot_full_group',
                'organizer'), 'error');
        } else {
            $_SESSION["infoboxmessage"] = $OUTPUT->notification(get_string('message_error_slot_full_single',
                'organizer'), 'error');
        }
    }

    redirect($redirecturl);

} else {

    $_SESSION["infoboxmessage"] = "";
        // If no actionselectbutton was chosen: redirect to view.php.
    redirect($redirecturl);

}

die;

/**
 * Checks if the participant is allowed and able to process the given action on the given slot.
 *
 * @param string $action   Participant's action like register, unregister, etc...
 * @param object $slot     The slot to which the action is applied.
 * @param object $organizer
 * @param object $context
 * @return boolean        Whether this action is allowed or possible for the participant or not
 * @throws dml_exception
 * @throws coding_exception
 */
function organizer_participants_action_allowed($action, $slot, $organizer, $context) {
    global $DB, $USER;

    if (!$DB->record_exists('organizer_slots', array('id' => $slot))) {
        return false;
    }
    $slotx = new organizer_slot($slot);
    $rightreg = has_capability('mod/organizer:register', $context, null, false);
    $rightunreg = has_capability('mod/organizer:unregister', $context, null, false);
    $userslot = organizer_get_slot_user_appointment($slotx) ? true : false;
    $slotexpired = $slotx->is_past_due() || $slotx->is_past_deadline();
    $instancedisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotfull = $slotx->is_full();
    $notavailable = $instancedisabled || !$slotx->organizer_groupmode_user_has_access() || $slotx->is_evaluated();
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $alreadyinqueue = $slotx->is_group_in_queue();
        $group = organizer_fetch_user_group($USER->id, $organizer->id);
        $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, null, $group->id);
    } else {
        $alreadyinqueue = $slotx->is_user_in_queue($USER->id);
        $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, $USER->id);
    }
    $queueable = $organizer->queue && !$alreadyinqueue && !$notavailable;
    if ($userslot) {
        $allowedaction = ORGANIZER_ACTION_UNREGISTER;
        $notavailable |= !$rightunreg || $slotexpired || $slotx->is_evaluated();
    } else if (!$slotfull) {
        $notavailable |= !$rightreg || $slotexpired;
        if ($lefttobook) {
            $allowedaction = ORGANIZER_ACTION_REGISTER;
        } else {
            $allowedaction = ORGANIZER_ACTION_REREGISTER;
        }
    } else if ($slotfull) {
        if ($queueable) {
            $allowedaction = ORGANIZER_ACTION_QUEUE;
            $notavailable |= !$rightreg || $slotexpired || !$lefttobook;
        }
    }
    if ($alreadyinqueue) {
        $allowedaction = ORGANIZER_ACTION_UNQUEUE;
        $notavailable |= !$rightunreg || $slotexpired;
    }
    return ($allowedaction == $action) && !$notavailable;
}
