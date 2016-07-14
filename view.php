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
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define("ORGANIZER_TAB_APPOINTMENTS_VIEW", 1);
define("ORGANIZER_TAB_STUDENT_VIEW", 2);
define("ORGANIZER_TAB_REGISTRATION_STATUS_VIEW", 3);
define("ORGANIZER_ASSIGNMENT_VIEW", 4);

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/locallib.php');

$instance = organizer_get_course_module_data_new();

require_login($instance->course, false, $instance->cm);

$params = organizer_load_params($instance);

if($params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW && ("".$_SESSION["organizer_new_instance"]=="".$instance->organizer->id)) {
	$_SESSION["organizer_new_instance"] = null;
	$redirecturl = new moodle_url('/mod/organizer/slots_add.php', array('id' => $params['id']));
	redirect($redirecturl);
}

$url = organizer_create_url($params);
$logurl = 'view.php?id=' . $params['id'] . '&mode=' . $params['mode'];

$PAGE->set_url($url);
$PAGE->set_title($instance->organizer->name);
$PAGE->set_heading($instance->course->shortname);

// Output.
$jsmodule = array(
        'name' => 'mod_organizer',
        'fullpath' => '/mod/organizer/module.js',
        'requires' => array('node', 'event', 'node-screen', 'panel', 'node-event-delegate'),
        'strings' => array(
                array('teachercomment_title', 'organizer'),
                array('studentcomment_title', 'organizer'),
                array('teacherfeedback_title', 'organizer'),
                array('infobox_showlegend', 'organizer'),
                array('infobox_hidelegend', 'organizer'),
        ),
);
$PAGE->requires->js_module($jsmodule);

if($instance->organizer->hidecalendar!=1) {
	organizer_add_calendar();
}

echo $OUTPUT->header();

$popups = array();

echo $OUTPUT->box_start('', 'organizer_main_cointainer');
switch ($params['mode']) {
    case ORGANIZER_TAB_APPOINTMENTS_VIEW:
        if (has_capability('mod/organizer:viewallslots', $instance->context)) {
            $event = \mod_organizer\event\slot_viewed::create(array(
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context
            ));
            $event->trigger();

            echo organizer_generate_appointments_view($params, $instance, $popups);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    case ORGANIZER_TAB_STUDENT_VIEW:
        if (has_capability('mod/organizer:viewstudentview', $instance->context)) {
            $event = \mod_organizer\event\course_module_viewed::create(array(
                    'objectid' => $PAGE->cm->instance,
                    'context' => $PAGE->context,
            ));
            $event->add_record_snapshot('course', $PAGE->course);
            $event->trigger();

            echo organizer_generate_student_view($params, $instance, $popups);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
        if (has_capability('mod/organizer:viewregistrations', $instance->context)) {
            $event = \mod_organizer\event\registrations_viewed::create(array(
                    'objectid' => $PAGE->cm->id,
                    'context' => $PAGE->context
            ));
            $event->trigger();

            echo organizer_generate_registration_status_view($params, $instance, $popups);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    case ORGANIZER_ASSIGNMENT_VIEW:
        if (has_capability('mod/organizer:assignslots', $instance->context)) {
            echo organizer_generate_assignment_view($params, $instance, $popups);
        } else {
            print_error('You do not have the permission to view this page!');
        }
        break;
    default:
        print_error("Invalid view mode: {$params['mode']}");
        break;
}

$PAGE->requires->js_init_call('M.mod_organizer.init_popups', array($popups));

echo $OUTPUT->box_end();
echo $OUTPUT->footer();

die;

// Utility functions.

function organizer_register_popup($title, $content, &$popups) {
    static $id = 0;

    if (!isset($popups[$title])) {
        $popups[$title] = array();
    }
    $popups[$title][$id] = str_replace(array("\n", "\r"), "<br />", $content);

    $elementid = "organizer_popup_icon_{$title}_{$id}";
    $id++;

    return $elementid;
}

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

function organizer_load_params($instance) {
    global $CFG;

    $params = array();
    $params['id'] = required_param('id', PARAM_INT);
    $params['mode'] = optional_param('mode', 0, PARAM_INT);

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
            $params['sort'] = optional_param('sort', 'status', PARAM_ALPHA);
            break;
		case ORGANIZER_ASSIGNMENT_VIEW:
            $params['assignid'] = required_param('assignid', PARAM_INT);
            $params['sort'] = optional_param('sort', 'datetime', PARAM_ALPHA);
            break;    
	}

    $params['slots'] = optional_param_array('slots', array(), PARAM_INT);
    $params['pdir'] = optional_param('pdir', 'ASC', PARAM_ALPHA);
    $params['psort'] = optional_param('psort', 'name', PARAM_ALPHA);
    $params['dir'] = optional_param('dir', 'ASC', PARAM_ALPHA);
    $params['data'] = optional_param_array('data', array(), PARAM_INT);
    $params['messages'] = optional_param_array('messages', array(), PARAM_ALPHAEXT);
    $params['usersort'] = optional_param('usersort', 'name', PARAM_ALPHA);

    return $params;
}