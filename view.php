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
 * view.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use mod_organizer\event\course_module_viewed;
use mod_organizer\event\registrations_viewed;
use mod_organizer\event\slot_viewed;

define("ORGANIZER_TAB_APPOINTMENTS_VIEW", 1);
define("ORGANIZER_TAB_STUDENT_VIEW", 2);
define("ORGANIZER_TAB_REGISTRATION_STATUS_VIEW", 3);
define("ORGANIZER_ASSIGNMENT_VIEW", 4);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->libdir.'/completionlib.php');

$instance = organizer_get_course_module_data_new();

require_login($instance->course, false, $instance->cm);

$params = organizer_load_params($instance);

if (isset($_SESSION["organizer_new_instance"])) {
    if ($params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW &&
            ("".$_SESSION["organizer_new_instance"] == "".$instance->organizer->id)) {
        $_SESSION["organizer_new_instance"] = null;
        $redirecturl = new moodle_url('/mod/organizer/slots_add.php', ['id' => $params['id']]);
        redirect($redirecturl);
    }
}

$url = organizer_create_url($params);
$PAGE->set_url($url);
$PAGE->set_context($instance->context);
$PAGE->set_title($instance->organizer->name);
$PAGE->set_heading($instance->course->fullname);
$PAGE->set_activity_record($instance->organizer);

$params['limitedwidth'] = organizer_get_limitedwidth();

if ($instance->organizer->hidecalendar != 1) {
    if (!$DB->record_exists('block_instances', ['parentcontextid' => $instance->context->id, 'blockname' => 'calendar_month'])) {
        organizer_add_calendar();
    }
} else {
    $DB->delete_records('block_instances', ['parentcontextid' => $instance->context->id, 'blockname' => 'calendar_month']);
}

// Completion.
$completion = new completion_info($instance->course);
$completion->set_module_viewed($instance->cm);

echo $OUTPUT->header();

echo $OUTPUT->box_start('', 'organizer_main_cointainer');
switch ($params['mode']) {
    case ORGANIZER_TAB_APPOINTMENTS_VIEW:
        if (has_capability('mod/organizer:viewallslots', $instance->context)) {
            $event = slot_viewed::create(
                [
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context,
                ]
            );
            $event->trigger();
            echo organizer_generate_appointments_view($params, $instance);
        } else {
            throw new coding_exception('You do not have the permission to view this page!');
        }
    break;
    case ORGANIZER_TAB_STUDENT_VIEW:
        if (has_capability('mod/organizer:viewstudentview', $instance->context)) {
            $event = course_module_viewed::create(
                [
                    'objectid' => $PAGE->cm->instance,
                    'context' => $PAGE->context,
                ]
            );
            $event->add_record_snapshot('course', $PAGE->course);
            $event->trigger();
            echo organizer_generate_student_view($params, $instance);
        } else {
            throw new coding_exception('You do not have the permission to view this page!');
        }
    break;
    case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
        if (has_capability('mod/organizer:viewregistrations', $instance->context)) {
            $event = registrations_viewed::create(
                [
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context,
                ]
            );
            $event->trigger();
            echo organizer_generate_registration_status_view($params, $instance);
        } else {
            throw new coding_exception('You do not have the permission to view this page!');
        }
    break;
    case ORGANIZER_ASSIGNMENT_VIEW:
        if (has_capability('mod/organizer:assignslots', $instance->context)) {
            echo organizer_generate_assignment_view($params, $instance);
        } else {
            throw new coding_exception('You do not have the permission to view this page!');
        }
    break;
    default:
        throw new coding_exception("Invalid view mode: {$params['mode']}");
    break;
}
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

// Utility functions.

/**
 * Creates a URL for the organizer view with specific parameters.
 *
 * @param array $params The parameters used to generate the URL. The array can include:
 *                      - 'id' (int): The course module id.
 *                      - 'mode' (int, optional): The organizer view mode (default is 0).
 *                      - 'sort' (string, optional): The sorting field (default is 'datetime').
 *                      - 'dir' (string, optional): The sorting direction (default is 'ASC').
 * @param bool $short Whether to generate a short version of the URL (default is false).
 *
 * @return moodle_url The composed URL object for the organizer view.
 */
function organizer_create_url($params, $short = false) {
    $url = new moodle_url('/mod/organizer/view.php');
    $url->param('id', $params['id']);
    if (!$short) {
        if ($params['mode'] !== 0) {
            $url->param('mode', $params['mode']);
        }
        if ($params['sort'] !== 'datetime') {
            $url->param('sort', $params['sort']);
        }
        if ($params['dir'] !== 'ASC') {
            $url->param('dir', $params['dir']);
        }
    }
    return $url;
}

/**
 * Loads and prepares parameters for the organizer based on the current instance.
 *
 * This function retrieves parameters used throughout the organizer module
 * and initializes them based on both required and optional inputs from the user
 * or default values. It handles different organizer views, sorts, and other param-specific logic.
 *
 * @param stdClass $instance The current instance of the organizer module.
 *
 * @return array The prepared array of parameters containing:
 * - 'id' (int): The course module ID.
 * - 'mode' (int): The organizer view mode.
 * - 'group' (int): The group ID.
 * - 'sort' (string): The sorting field.
 * - 'assignid' (int, optional): The assignment ID, only present for assignment views.
 * - 'slots' (array): Slots-related parameters.
 * - 'pdir' (string): Sorting direction for participants.
 * - 'psort' (string): Sorting field for participants.
 * - 'dir' (string): Sorting direction.
 * - 'data' (array): Additional data inputs.
 * - 'messages' (array): Messages linked to the organizer.
 * - 'xmessages' (array): External messages linked to the organizer.
 * - 'usersort' (string): Field used for user sorting.
 * @throws coding_exception
 */
function organizer_load_params($instance) {

    $params = [];
    $params['id'] = required_param('id', PARAM_INT);
    $params['mode'] = optional_param('mode', 0, PARAM_INT);
    $params['group'] = optional_param('group', 0, PARAM_INT);

    if ($params['mode'] == 0) {
        if (has_capability('mod/organizer:addslots', $instance->context, null, true)) {
            $params['mode'] = ORGANIZER_TAB_APPOINTMENTS_VIEW;
        } else {
            $params['mode'] = ORGANIZER_TAB_STUDENT_VIEW;
        }
    }

    switch ($params['mode']) {
        case ORGANIZER_TAB_APPOINTMENTS_VIEW:
        case ORGANIZER_TAB_STUDENT_VIEW:
            $params['sort'] = optional_param('sort', 'datetime', PARAM_ALPHA);
        break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            $params['sort'] = optional_param('sort', '', PARAM_ALPHA);
        break;
        case ORGANIZER_ASSIGNMENT_VIEW:
            $params['assignid'] = required_param('assignid', PARAM_INT);
            $params['sort'] = optional_param('sort', 'datetime', PARAM_ALPHA);
        break;
    }

    $params['slots'] = organizer_get_param_slots();
    $params['pdir'] = optional_param('pdir', 'ASC', PARAM_ALPHA);
    $params['psort'] = optional_param('psort', 'name', PARAM_ALPHA);
    $params['dir'] = optional_param('dir', 'ASC', PARAM_ALPHA);
    $params['data'] = optional_param_array('data', [], PARAM_INT);
    $params['messages'] = optional_param_array('messages', [], PARAM_ALPHAEXT);
    $params['xmessages'] = optional_param_array('xmessages', [], PARAM_ALPHAEXT);
    $params['usersort'] = optional_param('usersort', 'name', PARAM_ALPHA);

    return $params;
}
