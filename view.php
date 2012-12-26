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
 * Displays a particular instance of organizer
 *
 * @package   mod_organizer
 * @copyright 2011 Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("TAB_APPOINTMENTS_VIEW", 1);
define("TAB_STUDENT_VIEW", 2);
define("TAB_REGISTRATION_STATUS_VIEW", 3);

require_once('../../config.php');
require_once("../../calendar/lib.php");
require_once('lib.php');
require_once('view_lib.php');
require_once('locallib.php');

//------------------------------------------------------------------------------

$instance = get_course_module_data_new();

require_login($instance->course, false, $instance->cm);

$params = load_params($instance);

$url = create_url($params);
$logurl = create_url($params, true);

$PAGE->set_url($url);
$PAGE->set_title($instance->organizer->name);
$PAGE->set_heading($instance->course->shortname);

//----------------------------- OUTPUT -----------------------------------------

$jsmodule = array(
        'name' => 'mod_organizer',
        'fullpath' => '/mod/organizer/module.js',
        'requires' => array('node', 'event', 'node-screen', 'panel', 'node-event-delegate'),
        'strings' => array(
                array('teachercomment_title', 'organizer'),
                array('studentcomment_title', 'organizer'),
                array('teacherfeedback_title', 'organizer'),
        ),
);
$PAGE->requires->js_module($jsmodule);

add_calendar();

echo $OUTPUT->header();

$popups = array();

switch ($params['mode']) {
    case TAB_APPOINTMENTS_VIEW:
        if (has_capability('mod/organizer:viewallslots', $instance->context)) {
            log_action('allview', $logurl, $instance);
            echo generate_appointments_view($params, $instance);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    case TAB_STUDENT_VIEW:
        if (has_capability('mod/organizer:viewstudentview', $instance->context)) {
            log_action('studview', $logurl, $instance);
            echo generate_student_view($params, $instance);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    case TAB_REGISTRATION_STATUS_VIEW:
        if (has_capability('mod/organizer:viewregistrations', $instance->context)) {
            log_action('statusview', $logurl, $instance);
            echo generate_registration_status_view($params, $instance);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    default:
        print_error("Invalid view mode: {$params['mode']}");
        break;
}

$PAGE->requires->js_init_call('M.mod_organizer.init_popups', array($popups));

echo $OUTPUT->footer();
die();

//---------------- UTILITY FUNCTIONS -------------------------------------------

function register_popup($title, $content) {
    static $id = 0;
    global $popups;
    if (!isset($popups[$title])) {
        $popups[$title] = array();
    }
    $popups[$title][$id] = str_replace(array("\n", "\r"), "<br />", $content);

    $elementid = "organizer_popup_icon_{$title}_{$id}";
    $id++;
    
    return $elementid;
}

function create_url($params, $short = false) {
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

function load_params($instance) {
    global $CFG;

    $params = array();
    $params['id'] = required_param('id', PARAM_INT);
    $params['mode'] = optional_param('mode', 0, PARAM_INT);

    if ($params['mode'] == 0) {
        if (has_capability('mod/organizer:addslots', $instance->context, null, true)) {
            $params['mode'] = TAB_APPOINTMENTS_VIEW;
        } else {
            $params['mode'] = TAB_STUDENT_VIEW;
        }
    }

    switch ($params['mode']) {
        case TAB_APPOINTMENTS_VIEW:
        case TAB_STUDENT_VIEW:
            $params['sort'] = optional_param('sort', 'datetime', PARAM_ALPHA);
            break;
        case TAB_REGISTRATION_STATUS_VIEW:
            $params['sort'] = optional_param('sort', 'status', PARAM_ALPHA);
            break;
    }

    if ($CFG->version <= 2011041200) {
        $params['slots'] = optional_param('slots', array(), PARAM_RAW); // TODO: might have problems in the new version
    } else {
        $params['slots'] = (isset($_POST['slots']) ? $_POST['slots']
                : (isset($_GET['slots']) ? $_GET['slots'] : array()));
    }
    $params['pdir'] = optional_param('pdir', 'ASC', PARAM_ALPHA);
    $params['psort'] = optional_param('psort', 'name', PARAM_ALPHA);
    $params['dir'] = optional_param('dir', 'ASC', PARAM_ALPHA);
    $params['data'] = optional_param_array('data', array(), PARAM_RAW);
    $params['messages'] = optional_param_array('messages', array(), PARAM_RAW);
    $params['usersort'] = optional_param('usersort', 'name', PARAM_ALPHA);

    return $params;
}