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
 * view_lib.php
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

use core_availability\info_module;

defined('MOODLE_INTERNAL') || die();

define('ORGANIZER_APP_STATUS_INVALID', -1);
define('ORGANIZER_APP_STATUS_ATTENDED', 0);
define('ORGANIZER_APP_STATUS_PENDING', 2);
define('ORGANIZER_APP_STATUS_REGISTERED', 3);
define('ORGANIZER_APP_STATUS_NOT_ATTENDED', 4);
define('ORGANIZER_APP_STATUS_NOT_REGISTERED', 6);

require_once(dirname(__FILE__) . '/../../course/lib.php');
require_once(dirname(__FILE__) . '/../../calendar/lib.php');
require_once(dirname(__FILE__) . '/infobox.php');
require_once(dirname(__FILE__) . '/custom_table_renderer.php');

/**
 * Displays a Moodle form with the given title.
 *
 * This function outputs the page header, the provided title,
 * a container for the form, renders the form using the given
 * Moodle form instance, and finally ends the page with a footer.
 * It also handles whether calendar integration is enabled or not,
 * adding the calendar if necessary.
 *
 * @param moodleform $mform An instance of the Moodle form to be displayed.
 * @param string $title The title to be displayed on the page.
 * @return void This function does not return a value, it exits using `die()`.
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_display_form(moodleform $mform, $title) {
    global $OUTPUT, $DB;

    $instance = organizer_get_course_module_data_new();
    if ($instance->organizer->hidecalendar != 1) {
        if (!$DB->record_exists('block_instances', ['parentcontextid' => $instance->context->id, 'blockname' => 'calendar_month'])) {
            organizer_add_calendar();
        }
    } else {
        $DB->delete_records('block_instances', ['parentcontextid' => $instance->context->id, 'blockname' => 'calendar_month']);
    }

    echo $OUTPUT->header();
    echo $OUTPUT->heading($title);
    echo $OUTPUT->box_start('', 'organizer_main_cointainer');
    $mform->display();
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();

    die();
}

/**
 * Adds a calendar block to the right position in the page layout.

 * @return void
 * @throws dml_exception
 */
function organizer_add_calendar() {
    global $PAGE;

    if ($PAGE->blocks->is_known_block_type('calendar_month')) {
        $defaultregion = $PAGE->blocks->get_default_region();
        $PAGE->blocks->add_block('calendar_month', $defaultregion, 0, false, 'mod-organizer-*');
    }
}

/**
 * Generates the appointments view for the organizer module.
 *
 * This function prepares and returns the HTML output for the appointments view,
 * including JavaScript initialization, the information box, and the main table content.
 * It includes configuration for limited width views and adds the necessary actions
 * depending on expired organizer settings.
 *
 * @param array $params Parameters for generating the view, including filters and options.
 * @param object $instance The instance of the organizer module containing organizer data.
 * @return string The HTML output of the appointments view.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_generate_appointments_view($params, $instance) {
    global $PAGE;
    $PAGE->requires->js_call_amd('mod_organizer/initcheckboxes', 'init', [false]);

    $organizerexpired = isset($instance->organizer->duedate) && $instance->organizer->duedate - time() < 0;

    $output = organizer_generate_tab_row($params, $instance->context);
    $url = new moodle_url('/mod/organizer/view_action.php');
    $output .= organizer_begin_form($params, $url);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context, $organizerexpired);

    if ($params['limitedwidth']) {
        $columns = ['select', 'singleslotcommands', 'datetime', 'participants', 'teacher', 'details'];
        $align = ['center', 'center', 'left', 'left', 'left', 'center'];
        $sortable = ['datetime', 'participants'];
    } else {
        $columns = ['select', 'singleslotcommands', 'datetime', 'location', 'participants', 'teacher', 'details'];
        $align = ['center', 'center', 'left', 'left', 'left', 'left', 'center'];
        $sortable = ['datetime', 'location', 'participants'];
    }

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_table_header($columns, $sortable, $params);
    $table->data = organizer_generate_table_content($columns, $params, $instance->organizer);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);
    $output .= organizer_generate_actionlink_bar($instance->context, $organizerexpired, $instance->organizer);
    $output .= organizer_end_form();

    return $output;
}

/**
 * Generates the student view for the organizer module.
 *
 * This function creates and returns the HTML output for the organizer's student view.
 * It includes the tab row, information box, and a table that displays available slots
 * for registration based on the organizer's configuration and settings.
 *
 * - If the registration period has started, the function generates a table to display
 *   the available slots with associated details.
 * - If the registration period hasn't started yet, it shows a message indicating
 *   the start date for registrations.
 *
 * @param array $params Parameters for generating the view, including options like
 *                      limited width or filters for the data.
 * @param object $instance The instance of the organizer module containing the organizer data.
 *                         Includes configuration details like registration settings
 *                         and display options.
 * @return string The HTML output of the student view, including the information box
 *                and either the table of slots or a message about the registration period.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_generate_student_view($params, $instance) {
    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    if (time() > $instance->organizer->allowregistrationsfromdate ) {
        if ($params['limitedwidth']) {
            $columns = ['datetime', 'participants', 'teacher', 'status', 'actions'];
            $align = ['left', 'left', 'left', 'center', 'center'];
            $sortable = ['datetime'];
        } else {
            $columns = ['datetime', 'location', 'participants', 'teacher', 'status', 'actions'];
            $align = ['left', 'left', 'left', 'left', 'center', 'center'];
            $sortable = ['datetime', 'location'];
        }

        $table = new html_table();
        $table->id = 'slot_overview';
        $table->attributes['class'] = 'generaltable boxaligncenter overview';
        $table->head = organizer_generate_table_header($columns, $sortable, $params);
        $table->data = organizer_generate_table_content($columns, $params, $instance->organizer);
        $table->align = $align;

        $output .= organizer_render_table_with_footer($table);
    } else {
        if ($instance->organizer->alwaysshowdescription) {
            $message = get_string(
                'allowsubmissionsfromdatesummary', 'organizer',
                userdate($instance->organizer->allowregistrationsfromdate)
            );
        } else {
            $message = get_string(
                'allowsubmissionsanddescriptionfromdatesummary', 'organizer',
                userdate($instance->organizer->allowregistrationsfromdate)
            );
        }
        $output .= html_writer::div($message, 'alert alert-info', ['id' => 'intro']);
    }

    return $output;
}

/**
 * Generates the registration status view for the organizer module.
 *
 * This function creates and returns the HTML output for the organizer's registration status view.
 * It initializes required JavaScript, generates the tab row, and displays a table
 * showing the registration status of all slots. Depending on whether the organizer is configured
 * for groups or individuals, the columns and data representation may vary.
 *
 * The table includes sortable columns as well as actions for managing registrations.
 * Additional alignment settings are applied to ensure proper formatting.
 *
 * @param array $params Parameters for generating the view, including limited width and data filters.
 * @param stdClass $instance The instance object of the organizer module containing configuration
 *                           and contextual data, such as groups mode and organizer settings.
 * @return string The HTML output containing the tab row, registration status table, and action links.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_generate_registration_status_view($params, $instance) {
    global $PAGE;
    $PAGE->requires->js_call_amd('mod_organizer/initcheckboxes', 'init', [false]);

    $output = organizer_generate_tab_row($params, $instance->context);
    $output .= organizer_make_infobox($params, $instance->organizer, $instance->context);

    $columns = ['select', 'status'];
    if ($instance->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $columns[] = 'group';
        $columns[] = 'participants';
    } else {
        $columns[] = 'participants';
    }
    $sortable = ['status', 'group'];
    if ($params['limitedwidth']) {
        $columns = array_merge($columns, ['bookings', 'datetime', 'teacher', 'actions']);
    } else {
        $columns = array_merge($columns, ['bookings', 'datetime', 'location', 'teacher', 'actions']);
    }
    $align = array_fill(0, count($columns), 'center');
    $align[2] = $align[4] = $align[5] = 'left';

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_reg_table_header($columns, $sortable, $params);
    $table->data = organizer_generate_registration_table_content(
        $columns, $params, $instance->organizer, $instance->context
    );
    $table->align = $align;

    $url = new moodle_url('/mod/organizer/send_reminder.php');
    $output .= organizer_begin_form($params, $url);

    $output .= organizer_render_table_with_footer($table);

    $context = context_module::instance($params['id'], MUST_EXIST);
    if (!has_capability("mod/organizer:sendreminders", $context)) {
        $output .= '<div name="actionlink_bar" class="buttons mdl-align">';
        $output .= html_writer::span(get_string('selectedslots', 'organizer'));
        $actions['sendreminder'] = get_string('btn_remind', 'organizer');
        $output .= html_writer::select(
            $actions, 'bulkaction', ['sendreminder' => get_string('btn_remind', 'organizer')], null,
            ['style' => 'margin-left:0.3em;margin-right:0.3em;']
        );
        $output .= '<input type="submit" class="btn btn-primary" name="bulkactionbutton" id="bulkactionbutton" disabled value="' .
            get_string('btn_start', 'organizer') . '"/>';
        $output .= '</div>';
    }

    $output .= organizer_end_form();

    return $output;
}

/**
 * Generates the assignment view for the organizer module.
 *
 * This function creates and returns the HTML output for the organizer's assignment view.
 * It displays available slots for group or individual assignment and generates a table containing the slot list.
 * The structure of columns and alignment depends on the limited width configuration.
 *
 * @param array $params Parameters used for generating the view, such as limited width and assignment details.
 * @param stdClass $instance The instance of the organizer module, including relevant configuration details and context.
 * @return string The HTML output containing the assignment overview and the table.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_generate_assignment_view($params, $instance) {

    if ($instance->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $content = get_string('availableslotsfor', 'organizer') .' <strong>' .
                organizer_fetch_groupname($params['assignid']) . '</strong>';
    } else {
        $content = get_string('availableslotsfor', 'organizer') .' <strong>' .
                organizer_get_name_link($params['assignid']) . '</strong>';
    }
    $output = organizer_make_section('assign', $content);

    if ($params['limitedwidth']) {
        $columns = ['datetime', 'participants', 'teacher', 'actions'];
        $align = ['left', 'left', 'left', 'center'];
        $sortable = ['datetime'];
    } else {
        $columns = ['datetime', 'location', 'participants', 'teacher', 'actions'];
        $align = ['left', 'left', 'left', 'left', 'center'];
        $sortable = ['datetime', 'location'];
    }

    $table = new html_table();
    $table->id = 'slot_overview';
    $table->attributes['class'] = 'generaltable boxaligncenter overview';
    $table->head = organizer_generate_table_header($columns, $sortable, $params);
    $table->data = organizer_generate_assignment_table_content($columns, $params, $instance->organizer);
    $table->align = $align;

    $output .= organizer_render_table_with_footer($table);

    return $output;
}

/**
 * Begins a form for the organizer module.
 *
 * This function generates the opening `<form>` tag and initializes the form structure
 * with all necessary hidden fields for the organizer module, such as the instance ID,
 * mode, and session key. Usage of this function ensures that the form starts properly
 * when used in other modules or views.
 *
 * @param array $params An associative array of parameters for the form:
 *                      - 'id' (int): The instance ID of the organizer.
 *                      - 'mode' (string): The active mode for the organizer view.
 * @param moodle_url $url The URL where the form should be submitted.
 *
 * @return string The HTML string containing the opening `<form>` tag and hidden fields.
 */
function organizer_begin_form($params, $url) {
    $output = '<form name="viewform" action="' . $url->out() . '" method="post">';
    $output .= '<input type="hidden" name="id" value="' . $params['id'] . '" />';
    $output .= '<input type="hidden" name="mode" value="' . $params['mode'] . '" />';
    $output .= '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';

    return $output;
}

/**
 * Ends a form for the organizer module.
 *
 * This function generates the closing `</form>` tag and ends the form structure
 * initiated by functions like `organizer_begin_form()` or `organizer_begin_reg_form()`.
 *
 * @return string The HTML string containing the closing `</form>` tag.
 */
function organizer_end_form() {
    return '</form>';
}

/**
 * Generates a row of navigation tabs for the organizer module.
 *
 * This function creates a navigation row containing tabs for different organizer views
 * (e.g., All Appointments, Registration Status, Student View) based on the user's capabilities.
 * Tabs are rendered as a dropdown selector if multiple options are available. If only one option
 * is enabled, the selector is not displayed.
 *
 * @param array $params Parameters for generating the tab row:
 *                      - 'id' (int): Current organizer instance ID.
 *                      - 'mode' (string): The active mode indicating the current tab view.
 * @param context $context The context of the current organizer module instance.
 *
 * @return string The HTML string for the generated tab row, or an empty string if only
 *                one view is available.
 * @throws \core\exception\coding_exception
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_generate_tab_row($params, $context) {
    global $OUTPUT;

    $thirdnav = [];
    $thirdnavlink = [];
    $url = new moodle_url('/mod/organizer/view.php', ['id' => $params['id']]);

    if (has_capability('mod/organizer:viewallslots', $context, null, true)) {
        $url->param('mode', ORGANIZER_TAB_APPOINTMENTS_VIEW);
        $thirdnavlink[ORGANIZER_TAB_APPOINTMENTS_VIEW] = $url->out();
        $thirdnav[$thirdnavlink[ORGANIZER_TAB_APPOINTMENTS_VIEW]] = get_string('taballapp', 'organizer');
    }

    if (has_capability('mod/organizer:viewregistrations', $context, null, true)) {
        $url->param('mode', ORGANIZER_TAB_REGISTRATION_STATUS_VIEW);
        $thirdnavlink[ORGANIZER_TAB_REGISTRATION_STATUS_VIEW] = $url->out();
        $thirdnav[$thirdnavlink[ORGANIZER_TAB_REGISTRATION_STATUS_VIEW]] = get_string('tabstatus', 'organizer');
    }

    if (has_capability('mod/organizer:viewstudentview', $context, null, true)) {
        $url->param('mode', ORGANIZER_TAB_STUDENT_VIEW);
        $thirdnavlink[ORGANIZER_TAB_STUDENT_VIEW] = $url->out();
        $thirdnav[$thirdnavlink[ORGANIZER_TAB_STUDENT_VIEW]] = get_string('tabstud', 'organizer');
    }

    if (count($thirdnavlink) > 1) {
        $urlselector = new url_select($thirdnav, $thirdnavlink[$params['mode']]);
        return $OUTPUT->render($urlselector);
    } else {
        return ''; // If only one select option is enabled, hide the selector altogether.
    }
}

/**
 * Generates an action link bar for the organizer module.
 *
 * This function creates a collection of buttons and dropdown actions
 * for performing bulk actions on available organizer slots. The available
 * actions depend on the user's capabilities and the state of the organizer.
 *
 * Available actions:
 * - Editing slots
 * - Deleting slots
 * - Printing slots
 * - Evaluating slots
 * - Exporting slots as ics-files
 *
 * The function also ensures buttons are disabled if no slots are selected.
 *
 * @param context $context The context of the current organizer module instance.
 * @param bool $organizerexpired Indicates whether the organizer has expired.
 * @param stdClass $organizer The organizer instance data, including settings like grade.
 *
 * @return string The HTML string containing the action link bar.
 * @throws coding_exception
 */
function organizer_generate_actionlink_bar($context, $organizerexpired, $organizer) {

    $output = '<div name="actionlink_bar" class="buttons mdl-align">';

    $output .= html_writer::span(get_string('selectedslots', 'organizer'));

    $actions = [];
    if (has_capability("mod/organizer:editslots", $context, null, true) && !$organizerexpired) {
        $actions['edit'] = get_string('actionlink_edit', 'organizer');
    }
    if (has_capability("mod/organizer:deleteslots", $context, null, true) && !$organizerexpired) {
        $actions['delete'] = get_string('actionlink_delete', 'organizer');
    }
    if (has_capability("mod/organizer:printslots", $context, null, true)) {
        $actions['print'] = get_string('actionlink_print', 'organizer');
    }
    if (has_capability("mod/organizer:evalslots", $context, null, true) && $organizer->grade) {
        $actions['eval'] = get_string('actionlink_eval', 'organizer');
    }
    $actions['export'] = get_string('exporticsaction', 'organizer');

    $output .= html_writer::select(
        $actions, 'bulkaction', ['edit' => get_string('actionlink_edit', 'organizer')], null,
        ['style' => 'margin-left:0.3em;margin-right:0.3em;']
    );
    $output .= '<input type="submit" id="bulkactionbutton" disabled class="btn btn-primary" value="' .
        get_string('btn_start', 'organizer') . '"/>';

    $output .= '</div>';

    return $output;
}

/**
 * Generates the table header for the organizer registration table.
 *
 * This function creates an array of table header cells for the registration table
 * in the organizer module. The headers are determined based on the specified columns,
 * sortable fields, and provided parameters. It supports dynamically generating
 * sorting links for sortable columns and includes a column-specific logic for
 * handling groups and participants columns.
 *
 * Examples of how the table header is rendered:
 * - Sortable columns display sorting icons and update the order when clicked.
 * - Non-sortable columns include static labels.
 * - Custom logic for specific columns (e.g., "group" and "participants").
 *
 * @param array $columns The array of column names for the table.
 * @param array $sortable The array of sortable column names.
 * @param array $params The array of parameters used for sorting and rendering the table.
 *                      Parameters may include 'sort', 'dir', 'id', 'mode', etc.
 *
 * @return array The array of `html_table_cell` objects to be used as the table header.
 * @throws coding_exception|\core\exception\moodle_exception If there are issues with rendering output.
 */
function organizer_generate_table_header($columns, $sortable, $params) {
    global $OUTPUT;

    $header = [];
    foreach ($columns as $column) {
        $columnhelpicon = $OUTPUT->help_icon($column, 'organizer', '');
        if (in_array($column, $sortable)) {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                $columnstr = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = organizer_get_fa_icon("fa $icon ml-1", get_string($columnstr));
            }
            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir,
                'psort' => $params['psort'], 'pdir' => $params['pdir']]
            );
            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon . $columnhelpicon);
        } else if ($column == 'select') {
            $cell = new html_table_cell(
                html_writer::checkbox(
                    'select', null, false, '',
                    ['title' => get_string('select_all_slots', 'organizer')]
                )
            );
        } else if ($column == 'singleslotcommands') {
            $cell = new html_table_cell(get_string("th_actions", 'organizer') . $columnhelpicon);
        } else if ($column == 'participants') {
            $cell = organizer_get_participants_tableheadercell($params, $column, $columnhelpicon);
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer') . $columnhelpicon);
        }
        $cell->header = true;
        $header[] = $cell;
    }
    return $header;
}

/**
 * Generates the registration table header in the organizer module.
 *
 * This function creates an array of table header cells for the registration table,
 * with support for sortable columns and column-specific logic for "group" and "participants".
 * It generates sorting links for appropriate columns and includes custom rendering
 * for non-standard columns. The table header cells are returned as an array
 * of `html_table_cell` objects.
 *
 * Examples of how the table header is rendered:
 * - Sortable columns display sorting icons and update the order when clicked.
 * - Non-sortable columns and custom logic (e.g., "group" and "participants") render specific labels/icons.
 *
 * @param array $columns An array of column names used in the table.
 * @param array $sortable An array of sortable column names.
 * @param array $params An associative array of parameters such as:
 *                      - 'sort' (current sort column)
 *                      - 'dir' (current sort direction: 'ASC' or 'DESC')
 *                      - 'id', 'mode', etc., which might be required for generating URLs.
 *
 * @return array An array of `html_table_cell` objects representing the table headers.
 * @throws coding_exception|\core\exception\moodle_exception If there are any issues with rendering output.
 */
function organizer_generate_reg_table_header($columns, $sortable, $params) {

    $header = [];
    foreach ($columns as $column) {
        if ($column != 'group' && $column != 'participants' && in_array($column, $sortable)) {
            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                $columnstr = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = organizer_get_fa_icon("fa $icon ml-1", get_string($columnstr));
            }

            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => $column, 'dir' => $columndir]
            );
            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon
            );
        } else if ($column == 'group') {

            if ($params['sort'] != $column) {
                $columnicon = '';
                $columndir = 'ASC';
            } else {
                $columndir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                $columnstr = $params['dir'] == 'ASC' ? 'up' : 'down';
                $columnicon = organizer_get_fa_icon("fa $icon ml-1", get_string($columnstr));
            }
            $viewurl = new moodle_url(
                '/mod/organizer/view.php',
                ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'group', 'dir' => $columndir]
            );
            if ($params['sort'] == 'name') {
                $namedir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                $namestr = $params['dir'] == 'ASC' ? 'up' : 'down';
                $nameicon = organizer_get_fa_icon("fa $icon ml-1", get_string($namestr));

            } else {
                $namedir = 'ASC';
                $nameicon = '';
            }

            if ($params['sort'] == 'id') {
                $iddir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
                $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
                $idstr = $params['dir'] == 'ASC' ? 'up' : 'down';
                $idicon = organizer_get_fa_icon("fa $icon ml-1", get_string($idstr));
            } else {
                $iddir = 'ASC';
                $idicon = '';
            }

            $urln = new moodle_url(
                '/mod/organizer/view.php',
                ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'name',
                'dir' => $namedir]
            );
            $urli = new moodle_url(
                '/mod/organizer/view.php',
                ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'id',
                'dir' => $iddir]
            );
            $links = "(" . html_writer::link($urln, get_string('name')) . $nameicon . "/"
                    . html_writer::link($urli, get_string('id', 'organizer')) . $idicon . ")";

            $cell = new html_table_cell(
                html_writer::link($viewurl, get_string("th_{$column}", 'organizer')) . $columnicon . " " . $links
            );
        } else if ($column == 'participants') {
            $columnhelpicon = "";
            $cell = organizer_get_participants_tableheadercell($params, $column, $columnhelpicon);
        } else if ($column == 'select') {
            $cell = new html_table_cell(
                html_writer::checkbox(
                    'select', null, false, '',
                    ['title' => get_string('select_all_entries', 'organizer')]
                )
            );
        } else {
            $cell = new html_table_cell(get_string("th_{$column}", 'organizer'));
        }
        $cell->header = true;
        $cell->style = 'text-align: center; vertical-align: middle;';
        $header[] = $cell;
    }
    return $header;
}

/**
 * Generates table content for the organizer module.
 *
 * This function creates a table with relevant slot and participant data based on the given parameters for the organizer module.
 * It supports different sorting modes and views, including options for filtering only user-specific slots or slots visible
 * to students.
 *
 * @param array $columns The columns to be displayed in the table.
 * @param array $params Parameters for generating the table, including sorting and mode options.
 *                      - 'sort': The column by which the table should be sorted (e.g., 'participants', 'name', 'id').
 *                      - 'dir': The sorting direction ('ASC' or 'DESC').
 *                      - 'mode': The view mode for the organizer.
 * @param stdClass $organizer The organizer instance containing related data.
 * @param bool $onlyownslots Whether to show only slots belonging to the current user. Defaults to false.
 *
 * @return array An array of table rows containing slot and participant data.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 *
 */
function organizer_generate_table_content($columns, $params, $organizer, $onlyownslots = false) {
    global $DB, $USER;

    $translate = ['datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}",
            'teacher' => "lastname {$params['dir']}, firstname {$params['dir']}"];

    if ($params['sort'] != 'participants') {
        $order = $translate[$params['sort']];
    } else {
        $order = "starttime ASC";
    }

    $apps = organizer_get_all_user_appointments($organizer);
    $userslots = array_column($apps, 'slotid', 'id');

    $sqlparams = ['organizerid' => $organizer->id];
    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW) {
        $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid ORDER BY $order";
    } else {
        $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid AND s.visible = 1 ORDER BY $order";
    }
    $slots = $DB->get_records_sql($query, $sqlparams);

    $showpasttimeslots = false;
    $showonlymyslots = false;
    $showonlyfreeslots = false;
    $showpastslots = false;
    $showmyslotsonly = false;

    $onlyownslotsmsg = "";

    $rows = [];
    if (count($slots) != 0) {
        $numshown = 0;
        $weekbefore = -1;
        foreach ($slots as $slot) {
            if ($isuserslot = array_search($slot->id, $userslots)) {
                $app = $apps[$isuserslot];
                if ($onlyownslots) {
                    $onlyownslotsmsg = "";
                    // App occured.
                    if ($slot->starttime - time() < 0) {
                        $textclass = "";
                        if ($slot->starttime + $slot->duration - time() < 0) {
                            $infotxt = get_string('infobox_app_occured', 'organizer');
                        } else {
                            $infotxt = get_string('infobox_app_inprogress', 'organizer');
                        }
                        $onlyownslotsmsg .= organizer_get_icon_msg($infotxt, 'message_info', $textclass);
                    } else {
                        // Deadline countdown on.
                        if ($slot->starttime - $organizer->relativedeadline - time() > 0) {
                            $a = new stdClass();
                            [$a->days, $a->hours, $a->minutes, $a->seconds] = organizer_get_countdown(
                                $slot->starttime - $organizer->relativedeadline - time());
                            $textclass = $a->days > 1 ? "" : ($a->hours > 1 ? "text-info" : "text-danger");
                            $infotxt = get_string('infobox_deadline_countdown', 'organizer', $a);
                            $onlyownslotsmsg .= organizer_get_fa_icon("fa fa-bell-o fa-xs $textclass", $infotxt);
                        } else { // Deadline passed.
                            $textclass = "text-danger";
                            $infotxt = get_string('infobox_deadline_passed', 'organizer');
                            $onlyownslotsmsg .= organizer_get_fa_icon("fa fa-bell-slash-o fa-xs $textclass", $infotxt);
                        }
                        // App countdown on.
                        $a = new stdClass();
                        [$a->days, $a->hours, $a->minutes, $a->seconds] =
                            organizer_get_countdown($slot->starttime - time());
                        $textclass = $a->days > 1 ? "" : ($a->hours > 1 ? "text-info" : "text-danger");
                        $infotxt = get_string('infobox_app_countdown', 'organizer', $a);
                        $onlyownslotsmsg .= organizer_get_fa_icon("fa fa-bell fa-xs $textclass", $infotxt);
                    }
                }
            } else {
                if ($onlyownslots) {
                    continue;
                } else {
                    $app = null;
                }
            }
            $slotx = new organizer_slot($slot);
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
                      $alreadyinqueue = $slotx->is_group_in_queue();
            } else {
                      $alreadyinqueue = $slotx->is_user_in_queue($USER->id);
            }
            if (!$slotx->is_available()) {
                if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $isuserslot) {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = 'unavailable';
                } else {
                    continue; // Slot isn't available yet.
                }
            } else {
                if ($organizer->queue && $alreadyinqueue) {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = 'queueing';
                } else {
                    $row = $rows[] = new html_table_row();
                    $row->attributes['class'] = '';
                }
            }
            if ($slotx->has_participants()) {
                $row->attributes['class'] .= 'registered';
            } else {
                $row->attributes['class'] .= 'not_registered';
            }
            $slotpastdue = $slotx->is_past_due();
            $myslotastrainer = false;
            if ($trainerids = organizer_get_slot_trainers($slot->id)) {
                if (in_array($USER->id, $trainerids)) {
                    $myslotastrainer = true;
                }
            }
            if ($params['sort'] == 'datetime') {
                $week = (int)date('W', $slot->starttime);
                if ($weekbefore != -1 && $week != $weekbefore) {
                    $row->attributes['class'] .= ' newweek';
                }
                $weekbefore = $week;
            }
            $slotvisible = $slot->visible;
            $hidden = false;
            if ($slotpastdue) {
                $row->attributes['class'] .= ' past_due';
                if (!$showpasttimeslots && !$onlyownslots) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
            } else {
                $row->attributes['class'] .= ' not_past_due';
            }
            if ($myslotastrainer) {
                $row->attributes['class'] .= ' my_slot';
            } else {
                if ($showonlymyslots && $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_my_slot';
            }
            if (!$slotx->is_full()) {
                $row->attributes['class'] .= ' free_slot';
            } else {
                if ($showonlyfreeslots) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_free_slot';
            }
            if (!$slotvisible && $params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
                $row->attributes['class'] .= ' unavailable';
            } else {
                if (!$slotvisible && $params['mode'] == ORGANIZER_TAB_STUDENT_VIEW) {
                    $row->style = 'display: none;';
                    $hidden = true;
                }
                $row->attributes['class'] .= ' not_unavailable';
            }
            if (!$hidden) {
                $numshown++;
            }
            if (array_search($slot->id, $params['slots']) !== false) {
                $row->attributes['class'] .= ' affected_slot';
            }
            if (($params['mode'] == ORGANIZER_TAB_STUDENT_VIEW) && $isuserslot) {
                $row->attributes['class'] .= ' registered_slot';
            }
            foreach ($columns as $column) {
                switch ($column) {
                    case 'select':
                        $cell = $row->cells[] = new html_table_cell(
                        html_writer::checkbox('slots[]', $slot->id, false, '',
                            ['class' => 'checkbox_slot']
                        )
                            );
                    break;
                    case 'singleslotcommands':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_commands($slot->id, $params,
                            $organizer->grade));
                    break;
                    case 'datetime':
                        if ($params['limitedwidth']) {
                            $llink = organizer_location_link($slot);
                            $text = organizer_date_time($slot, true);
                            if ($llink != "-") {
                                $text .= "<br>".$llink;
                            }
                            $cell = $row->cells[] = new html_table_cell($text);
                        } else {
                            $cell = $row->cells[] = new html_table_cell(organizer_date_time($slot, true));
                        }
                    break;
                    case 'location':
                        $cell = $row->cells[] = new html_table_cell(organizer_location_link($slot));
                    break;
                    case 'participants':
                        $cell = $row->cells[] = new html_table_cell(
                                organizer_get_participant_list($params, $slot, $app));
                    break;
                    case 'teacher':
                        $cell = $row->cells[] = new html_table_cell(organizer_trainer_data($params, $slot, $trainerids));
                    break;
                    case 'details':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_status($params, $slot));
                    break;
                    case 'status':
                        $cell = $row->cells[] = new html_table_cell(organizer_slot_reg_status($organizer, $slot, $onlyownslotsmsg));
                    break;
                    case 'actions':
                        $cell = $row->cells[] = new html_table_cell(organizer_participants_action($params, $slot));
                    break;
                    default:
                        throw new coding_exception("Unrecognized column type: $column");
                }
                $cell->style .= ' vertical-align: middle;';
            }
        }
        $inforownames = ['no_slots', 'no_due_slots', 'no_my_slots', 'no_due_my_slots'];
        foreach ($inforownames as $inforowname) {
            $defaultrow = $rows[] = new html_table_row();
            $defaultrow->attributes['class'] = "info $inforowname";
            $defaultrow->cells[] = organizer_get_span_cell(get_string($inforowname, 'organizer'), count($columns));
            $defaultrow->style = '';
            if ($numshown == 0) {
                switch ($inforowname) {
                    case 'no_slots':
                        $defaultrow->style = ($showpastslots && !$showmyslotsonly) ? '' : 'display: none;';
                    break;
                    case 'no_due_slots':
                        $defaultrow->style = (!$showpastslots && !$showmyslotsonly) ? '' : 'display: none;';
                    break;
                    case 'no_my_slots':
                        $defaultrow->style = ($showpastslots && $showmyslotsonly) ? '' : 'display: none;';
                    break;
                    case 'no_due_my_slots':
                        $defaultrow->style = (!$showpastslots && $showmyslotsonly) ? '' : 'display: none;';
                    break;
                    default:
                        throw new coding_exception("This shouldn't happen @ generating no slot rows");
                }
            } else {
                $defaultrow->style = 'display: none;';
            }
        }
    } else {
        $defaultrow = $rows[] = new html_table_row();
        if ($params['mode'] == ORGANIZER_TAB_APPOINTMENTS_VIEW) {
            $url = new moodle_url('/mod/organizer/slots_add.php', ['id' => $params['id']]);
            $a = new stdClass();
            $a->link = $url->out();
            $message = get_string('no_slots_defined_teacher', 'organizer', $a);
        } else {
            $message = get_string('no_slots_defined', 'organizer');
        }
        $defaultrow->cells[] = organizer_get_span_cell($message, count($columns));
        $defaultrow->attributes['class'] = "info no_slots_defined";
    }
    return $rows;
}

/**
 * Retrieves the registration status table entries for a group in the organizer.
 *
 * This function generates entries for the registration status table based on the
 * provided parameters. It retrieves groups associated with a course module and
 * organizes entries based on the selected sort and direction options.
 *
 * @param array $params Parameters for retrieving registration status, which may include:
 *                      - 'group': The group ID. If set to 0, all groups are included.
 *                      - 'sort': The column to sort by ('status', 'group', 'datetime', 'name', 'id').
 *                      - 'dir': The sort direction ('ASC' or 'DESC').
 * @return array An array of table entries, where each entry consists of group details
 *               and registration-related information.
 * @throws dml_exception If there is an issue with database querying.
 */
function organizer_get_span_cell($text, $colspan) {
    $cell = new html_table_cell();
    $cell->colspan = $colspan;
    $cell->style = 'text-align: center; vertical-align: middle;';
    $cell->text = $text;

    return $cell;
}

/**
 * Retrieves registration status table entries grouped by group.
 *
 * This function fetches entries for the registration status table for groups in a specific organizer instance.
 * The entries include group-related details and registration information such as registration status, session time,
 * location, and other appointment-related data.
 *
 * The grouping depends on the `$params['group']` parameter, which defines whether all groups or specific ones
 * should be included. Results can also be sorted based on a selected column and direction.
 *
 * @param array $params Parameters for filtering and sorting registration status table entries:
 *                      - 'group' (int): The group ID. If set to 0, all groups will be retrieved.
 *                      - 'sort' (string): The column to sort by. Supported values:
 *                              - 'status': Sort by registration status.
 *                              - 'group': Sort by group name.
 *                              - 'datetime': Sort by appointment start time.
 *                              - 'name': Sort by group name.
 *                              - 'id': Sort by group ID number.
 *                      - 'dir' (string): The sorting direction ('ASC' or 'DESC'). Defaults to 'ASC'.
 * @return array An array of table entries containing group details and registration-related information,
 *               such as:
 *               - `id`: Group ID.
 *               - `name`: Group name.
 *               - `idnumber`: Group ID number.
 *               - `status`: Registration status, based on constants like:
 *                   - ORGANIZER_APP_STATUS_ATTENDED
 *                   - ORGANIZER_APP_STATUS_PENDING
 *                   - ORGANIZER_APP_STATUS_REGISTERED
 *                   - ORGANIZER_APP_STATUS_NOT_REGISTERED
 *                   - ORGANIZER_APP_STATUS_INVALID
 *               - Other appointment-related information: `starttime`, `duration`, `location`,
 *                 `teachercomments`, etc.
 * @throws dml_exception If there is an issue querying the database.
 */
function organizer_get_reg_status_table_entries_group($params) {
    global $DB;
    [$cm, , $organizer, ] = organizer_get_course_module_data();

    if ($params['group'] == 0) {
        $groups = groups_get_all_groups($cm->course, 0, $cm->groupingid, 'g.id');
        $groupids = array_keys($groups);
    } else {
        $groupids = [$params['group']];
    }
    if (!$groupids || count($groupids) == 0) {
        return [];
    }
    [$insql, $inparams] = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);

    $dir = isset($params['dir']) ? $params['dir'] : 'ASC';

    if ($params['sort'] == 'status') {
        $orderby = "ORDER BY status $dir, g.name ASC";
    } else if ($params['sort'] == 'group') {
        $orderby = "ORDER BY g.name $dir, status ASC";
    } else if ($params['sort'] == 'datetime') {
        $orderby = "ORDER BY a2.starttime $dir";
    } else if ($params['sort'] == 'name') {
        $orderby = "ORDER BY g.name $dir";
    } else if ($params['sort'] == 'id') {
        $orderby = "ORDER BY g.idnumber $dir";
    } else {
        $orderby = "ORDER BY g.name ASC, status ASC";
    }

    $par = ['now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id];
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        g.id,
        g.name,
        g.idnumber,
        a2.id AS appid,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1
                THEN " . ORGANIZER_APP_STATUS_ATTENDED . "
            WHEN a2.id IS NOT NULL AND (a2.attended IS NULL OR a2.attended = 0) AND a2.starttime <= :now1
                THEN " . ORGANIZER_APP_STATUS_PENDING . "
            WHEN a2.id IS NOT NULL AND (a2.attended IS NULL OR a2.attended = 0) AND a2.starttime > :now2
                THEN " . ORGANIZER_APP_STATUS_REGISTERED . "
            WHEN a2.id IS NULL
                THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID . "
        END AS status,
        a2.starttime,
        a2.duration,
        a2.location,
        a2.locationlink,
        a2.applicantid,
        a2.teachercomments,
        a2.comments,
        a2.teachervisible,
        a2.slotid,
        a2.allownewappointments,
        a2.teacherapplicantid,
        a2.teacherapplicanttimemodified
        FROM {groups} g
        LEFT JOIN
        (
            SELECT
            a.id,
            a.groupid,
            a.allownewappointments,
            s.id as slotid,
            s.starttime,
            s.location,
            s.locationlink,
            s.teachervisible,
            s.duration,
            a.applicantid,
            a.comments,
            s.comments AS teachercomments,
            a.teacherapplicantid,
            a.teacherapplicanttimemodified,
            (
                SELECT MAX(a3.attended)
                FROM {organizer_slot_appointments} a3
                WHERE a3.groupid = a.groupid
                GROUP BY a3.slotid
                ORDER BY a3.slotid DESC
                LIMIT 1
            ) AS attended
            FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid
            ORDER BY a.id DESC
        ) a2 ON g.id = a2.groupid
        WHERE g.id $insql
        $orderby, a2.slotid ASC";

    $rs = $DB->get_recordset_sql($query, $par);

    return $rs;
}

/**
 * Returns all the participants of this organizer instance including data which indicates the booking status.
 *
 * @param $params ... the sort and dir parameter of these parameters is used here
 * @return array|moodle_recordset   ... one record for each participant
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_get_reg_status_table_entries($params) {
    global $DB;
    [$cm, , $organizer, $context] = organizer_get_course_module_data();

    $students = get_enrolled_users($context, 'mod/organizer:register', $params['group'], 'u.id', null, 0, 0, true);
    $info = new info_module(cm_info::create($cm));
    $filtered = $info->filter_user_list($students);
    $studentids = array_keys($filtered);
    $havebookings = $DB->get_fieldset_sql('SELECT DISTINCT sa.userid
        FROM {organizer_slot_appointments} sa INNER JOIN {organizer_slots} s ON sa.slotid = s.id
        WHERE s.organizerid = :organizerid', ['organizerid' => $organizer->id]
    );
    $studentids = array_merge($studentids, $havebookings);
    if (!$studentids || count($studentids) == 0) {
        return [];
    }

    [$insql, $inparams] = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);

    $dir = isset($params['dir']) ? $params['dir'] : 'ASC';

    if ($params['sort'] == 'status') {
        $orderby = "ORDER BY status $dir, u.lastname ASC, u.firstname ASC, u.idnumber ASC";
    } else if ($params['psort'] == 'name') {
        $pdir = isset($params['pdir']) ? $params['pdir'] : 'ASC';
        $orderby = "ORDER BY u.lastname $pdir, u.firstname $pdir, status ASC, u.idnumber ASC";
    } else if ($params['sort'] == 'id') {
        $orderby = "ORDER BY u.idnumber $dir, status ASC, u.lastname ASC, u.firstname ASC";
    } else if ($params['sort'] == 'participant') {
        $orderby = "ORDER BY u.lastname $dir, u.firstname ASC, status ASC, u.idnumber ASC";
    } else {
        $orderby = "ORDER BY u.lastname ASC, u.firstname ASC, status ASC, u.idnumber ASC";
    }

    $par = ['now1' => time(), 'now2' => time(), 'organizerid' => $organizer->id];
    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        u.id,
        u.firstname,
        u.lastname,
        u.idnumber,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1
                THEN " . ORGANIZER_APP_STATUS_ATTENDED . "
            WHEN a2.id IS NOT NULL AND NULLIF(a2.attended, 0) IS NULL AND a2.starttime <= :now1
                THEN " . ORGANIZER_APP_STATUS_PENDING . "
            WHEN a2.id IS NOT NULL AND NULLIF(a2.attended, 0) IS NULL AND a2.starttime > :now2
                THEN " . ORGANIZER_APP_STATUS_REGISTERED . "
            WHEN a2.id IS NULL
                THEN " . ORGANIZER_APP_STATUS_NOT_REGISTERED . "
            ELSE " . ORGANIZER_APP_STATUS_INVALID . "
        END AS status,
        a2.starttime,
        a2.duration,
        a2.attended,
        a2.location,
        a2.locationlink,
        a2.grade,
        a2.comments,
        a2.teachercomments,
        a2.feedback,
        a2.userid,
        a2.teachervisible,
        a2.slotid,
        a2.id AS appid,
        a2.teacherapplicantid,
        a2.teacherapplicanttimemodified
        FROM {user} u
        LEFT JOIN
        (
            SELECT
                a.id,
                a.attended,
                a.grade,
                a.feedback,
                a.comments,
                a.userid,
                a.allownewappointments,
                s.starttime,
                s.location,
                s.locationlink,
                s.comments AS teachercomments,
                s.duration,
                s.teachervisible,
                s.id AS slotid,
                a.teacherapplicantid,
                a.teacherapplicanttimemodified
            FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid
            ORDER BY a.id DESC
        ) a2 ON u.id = a2.userid
        WHERE u.id $insql
        GROUP BY u.id, a2.id, u.firstname, u.lastname, u.idnumber, status,
        a2.starttime, a2.duration, a2.attended, a2.location, a2.locationlink,
        a2.grade, a2.comments, a2.teachercomments, a2.feedback,
        a2.userid, a2.teachervisible, a2.slotid, a2.allownewappointments,
        a2.teacherapplicantid, a2.teacherapplicanttimemodified
        $orderby";

    $rs = $DB->get_recordset_sql($query, $par);

    return $rs;
}

/**
 * Returns the table rows of the registration view entries.
 *
 * @param $columns ... table columns to show
 * @param $params
 * @param $organizer  ... organizer instance data
 * @param $context    ... context data
 * @return array      ... html table rows, each for each entry
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_generate_registration_table_content($columns, $params, $organizer, $context) {

    $groupmode = $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS;

    $entries = organizer_get_registrationview_entries($groupmode, $params);

    if ($entries) {
        if ($entries->valid()) {
            $rows = [];
            $queueable = organizer_is_queueable();
            if ($groupmode) {
                $slotswitch = "";
                $groupswitch = "";
                foreach ($entries as $entry) {
                    if ($entry->status == ORGANIZER_APP_STATUS_INVALID) {
                        continue;
                    }
                    $bookingnotpossible = organizer_bookingnotpossible($groupmode, $organizer, $entry->id);
                    if ($params['psort'] == 'id') {
                        $orderby = "idnumber {$params['pdir']}, lastname ASC, firstname ASC";
                    } else {
                        $orderby = "lastname {$params['pdir']}, firstname {$params['pdir']}, idnumber ASC";
                    }
                    if (!$groupmembers = get_enrolled_users($context, 'mod/organizer:register',
                        $entry->id, 'u.id', $orderby, 0, 0, true)) {
                        continue;
                    }
                    if ($slotswitch != $entry->slotid || $groupswitch != $entry->id) {
                        $slotswitch = $entry->slotid;
                        $groupswitch = $entry->id;
                        $row = new html_table_row();
                        $rowclass = '';
                        $slotevaluated = false;
                        if ($entry->slotid) {
                            $slotx = new organizer_slot($entry->slotid);
                            if ($slotx->is_past_due()) {
                                $rowclass .= ' past_due_reg';
                            }
                            $slotevaluated = $slotx->is_evaluated();
                        }
                        if ($entry->starttime) {
                            $rowclass .= ' registered';
                        }
                        $row->attributes['class'] = $rowclass;
                        foreach ($columns as $column) {
                            switch ($column) {
                                case 'select':
                                    $attributes = ['class' => 'checkbox_slot'];
                                    if ($bookingnotpossible) {
                                        $attributes['disabled'] = true;
                                    }
                                    $cell = $row->cells[] = new html_table_cell(
                                        html_writer::checkbox('recipients[]', $entry->id, false, '',
                                            $attributes
                                        )
                                    );
                                    break;
                                case 'group':
                                    $list = $entry->name;
                                    if ($entry->idnumber) {
                                        $list .= " (". $entry->idnumber . ")";
                                    }
                                    if ($entry->starttime) {
                                        $list .= organizer_get_teacherapplicant_output(
                                            $entry->teacherapplicantid,
                                            $entry->teacherapplicanttimemodified
                                        );
                                    }
                                    if ($queueable) {
                                        $list .= "<span style='display: table-cell'>" .
                                            organizer_reg_waitinglist_status($organizer->id, null, $entry->id)
                                            . "</span>";
                                    }
                                    $cell = $row->cells[] = new html_table_cell($list);
                                    break;
                                case 'participants':
                                    $members = array_keys($groupmembers);
                                    $list = "<span style='display: table'>";
                                    foreach ($members as $member) {
                                        $list .= "<span style='display: table-row'>";
                                        $identity = organizer_get_user_identity($member);
                                        $identity = $identity != "" ?
                                            "<span class='organizer_identity ml-1'>({$identity})</span>" : "";
                                        $list .= "<span style='display: table-cell'> ".organizer_get_name_link($member);
                                        if ($entry->starttime) {
                                            if ($member == $entry->applicantid) {
                                                $list .= organizer_get_fa_icon('fa fa-star fa-2xs ml-1',
                                                    get_string('applicant', 'organizer'));
                                            } else {
                                                $list .= " ";
                                            }
                                        }
                                        $list .= "$identity</span>";
                                        $list .= "<span style='display: table-cell'>" .
                                            organizer_reg_organizer_app_details($organizer, $groupmode, $entry->slotid,
                                                $member)
                                            . "</span>";
                                        $list .= "</span>";
                                    }
                                    $list .= "</span>";
                                    $cell = $row->cells[] = new html_table_cell($list);
                                    $cell->style .= " text-align: left;";
                                    break;
                                case 'status':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(
                                            organizer_get_status_icon_reg($entry->status, $organizer, $slotevaluated));
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell(
                                        organizer_get_status_icon_reg(ORGANIZER_APP_STATUS_NOT_REGISTERED,
                                            $organizer, $slotevaluated));
                                    }
                                    $cell->style .= " text-align: center;";
                                    break;
                                case 'bookings':
                                    $cell = $row->cells[] = new html_table_cell(
                                        "#".organizer_count_bookedslots($organizer->id, null, $entry->id));
                                    $cell->style .= " text-align: center;";
                                    break;
                                case 'datetime':
                                    if ($params['limitedwidth']) {
                                        if ($entry->starttime) {
                                            $text = organizer_date_time($entry, true);
                                            $llink = organizer_location_link($entry);
                                            if ($llink != "-") {
                                                $text .= "<br>".$llink;
                                            }
                                        } else {
                                            $text = "-";
                                        }
                                        $cell = $row->cells[] = new html_table_cell($text);
                                        if ($text == "-") {
                                            $cell->style .= " text-align: center;";
                                        }
                                    } else {
                                        if ($entry->starttime) {
                                            $cell = $row->cells[] = new html_table_cell(organizer_date_time($entry, true));
                                        } else {
                                            $cell = $row->cells[] = new html_table_cell('-');
                                            $cell->style .= " text-align: center;";
                                        }
                                    }
                                    break;
                                case 'location':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(organizer_location_link($entry));
                                        $cell->style .= " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                    break;
                                case 'teacher':
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(
                                            organizer_trainer_data($params, $entry, organizer_get_slot_trainers($entry->slotid)));
                                        $cell->style .= " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                    break;
                                case 'actions':
                                    $cell = $row->cells[] = new html_table_cell(
                                        organizer_teacher_action($params, $entry, $context, $organizer, $groupmode)
                                    );
                                    $cell->style .= " text-align: center;";
                                    break;
                            }
                            $cell->style .= ' vertical-align: middle;';
                        }  // Foreach column.
                        $rows[] = $row;
                    }// Slotswitch and groupswitch.
                }  // Foreach entry.
            } else {  // No groupmode.
                foreach ($entries as $entry) {
                    $bookingnotpossible = organizer_bookingnotpossible($groupmode, $organizer, $entry->id);
                    $row = new html_table_row();
                    $rowclass = '';
                    $slotevaluated = false;
                    if ($entry->slotid) {
                        $slotx = new organizer_slot($entry->slotid);
                        if ($slotx->is_past_due()) {
                            $rowclass .= ' past_due_reg';
                        }
                        $slotevaluated = $entry->attended || $entry->grade || $entry->feedback;
                    }
                    if ($entry->starttime) {
                        $rowclass .= ' registered';
                    }
                    $row->attributes['class'] = $rowclass;
                    foreach ($columns as $column) {
                        switch ($column) {
                            case 'select':
                                $attributes = ['class' => 'checkbox_slot'];
                                if ($bookingnotpossible) {
                                    $attributes['disabled'] = true;
                                }
                                $cell = $row->cells[] = new html_table_cell(
                                    html_writer::checkbox('recipients[]', $entry->id, false, '',
                                        $attributes
                                    )
                                );
                                break;
                            case 'group':
                            case 'participants':
                                $identity = organizer_get_user_identity($entry);
                                $identity = $identity != "" ? " ({$identity})" : "";
                                $text = organizer_get_name_link($entry->id) . $identity .
                                    organizer_get_teacherapplicant_output($entry->teacherapplicantid,
                                        $entry->teacherapplicanttimemodified);
                                if ($entry->appid) {
                                    $text .= organizer_reg_organizer_app_details($organizer, null,
                                        $entry->slotid, $entry->id);
                                }
                                $cell = $row->cells[] = new html_table_cell($text);
                                break;
                            case 'status':
                                $cell = $row->cells[] = new
                                    html_table_cell(organizer_get_status_icon_reg($entry->status,
                                    $organizer, $slotevaluated));
                                break;
                            case 'bookings':
                                $cell = $row->cells[] = new html_table_cell(
                                        "#".organizer_count_bookedslots($organizer->id, $entry->id));
                                $cell->style .= " text-align: center;";
                                break;
                            case 'datetime':
                                if ($params['limitedwidth']) {
                                    if ($entry->starttime) {
                                        $text = organizer_date_time($entry, true);
                                        $llink = organizer_location_link($entry);
                                        if ($llink != "_") {
                                            $text .= "<br>".$llink;
                                        }
                                    } else {
                                        $text = "-";
                                    }
                                    $cell = $row->cells[] = new html_table_cell($text);
                                    if ($text == "-") {
                                        $cell->style .= " text-align: center;";
                                    }
                                } else {
                                    if ($entry->starttime) {
                                        $cell = $row->cells[] = new html_table_cell(organizer_date_time($entry, true));
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell('-');
                                        $cell->style .= " text-align: center;";
                                    }
                                }
                                break;
                            case 'appdetails':
                                if ($queueable) {
                                    $outcell = organizer_reg_waitinglist_status($organizer->id, $entry->id);
                                } else {
                                    $outcell = '';
                                }
                                $outcell .= organizer_reg_organizer_app_details($organizer, $groupmode, $entry->appid);
                                $outcell = trim($outcell);
                                if ($outcell != "-" && $outcell != "") {
                                    $cell = $row->cells[] = new html_table_cell($outcell);
                                    $cell->style = " text-align: left;";
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'location':
                                $text = organizer_location_link($entry);
                                if ($text != "-") {
                                    $cell = $row->cells[] = new html_table_cell($text);
                                    $cell->style = " text-align: left;";
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'teacher':
                                if ($entry->slotid) {
                                    $trainerdata = organizer_trainer_data($params, $entry,
                                        organizer_get_slot_trainers($entry->slotid));
                                    if ($trainerdata != "-") {
                                        $cell = $row->cells[] = new html_table_cell($trainerdata);
                                        $cell->style = " text-align: left;";
                                    } else {
                                        $cell = $row->cells[] = new html_table_cell("-");
                                        $cell->style = " text-align: center;";
                                    }
                                } else {
                                    $cell = $row->cells[] = new html_table_cell("-");
                                    $cell->style = " text-align: center;";
                                }
                                break;
                            case 'actions':
                                $cell = $row->cells[] = new html_table_cell(
                                    organizer_teacher_action($params, $entry, $context, $organizer, $groupmode));
                                $cell->style .= " text-align: center;";
                                break;
                        }
                        $cell->style .= ' vertical-align: middle;';
                    }
                    $rows[] = $row;
                }  // Foreach entry.
            } // Groupmode or not.
            $entries->close();
        } else {
            $row = new html_table_row();
            $cell = $row->cells[] = new html_table_cell(get_string('novalidparticipants', 'organizer'));
            $cell->colspan = count($columns);
            $rows[] = $row;
        }
    } else {  // No entries - course has no participants.
        $row = new html_table_row();
        $cell = $row->cells[] = new html_table_cell(get_string('noparticipants', 'organizer'));
        $cell->colspan = count($columns);
        $rows[] = $row;
    }
    return $rows;
}

/**
 * Generates the content for an assignment table based on the organizer and provided parameters.
 *
 * This function retrieves slot records associated with the given organizer and processes them
 * to populate a table structure with rows and cells corresponding to the selected columns.
 * It handles different column types and their associated data, adding styles to the cells
 * as needed. If no slots are shown or available, it generates a message row to indicate this.
 *
 * @param array $columns List of columns to include in the table (e.g., 'datetime', 'location').
 * @param array $params Parameters for filtering and rendering the table (e.g., 'limitedwidth', 'sort').
 * @param object $organizer The organizer object containing data used for generating slots.
 *
 * @return array An array of html_table_row objects representing the rows of the table.
 *
 * @throws coding_exception If an unrecognized column type is encountered.
 */
function organizer_generate_assignment_table_content($columns, $params, $organizer) {
    global $DB;

    $translate = ['datetime' => "starttime {$params['dir']}", 'location' => "location {$params['dir']}"];

    $order = $translate[$params['sort']];
    $assignid = $params['assignid'];

    $sqlparams = ['organizerid' => $organizer->id];
    $query = "SELECT s.* FROM {organizer_slots} s WHERE s.organizerid = :organizerid ORDER BY $order";
    $slots = $DB->get_records_sql($query, $sqlparams);

    $rows = [];
    if (count($slots) != 0) {
        $numshown = 0;
        foreach ($slots as $slot) {
            if (organizer_slot_is_free($slot, $assignid, true)) {
                $row = new html_table_row();
                foreach ($columns as $column) {
                    switch ($column) {
                        case 'datetime':
                            $text = organizer_date_time($slot, true);
                            if ($params['limitedwidth']) {
                                $llink = organizer_location_link($slot);
                                if ($llink != "-") {
                                    $text .= "<br>".$llink;
                                }
                            }
                            $cell = $row->cells[] = new html_table_cell($text);
                            if ($text == '-') {
                                $cell->style .= " text-align:center;";
                            }
                        break;
                        case 'location':
                            $cell = $row->cells[] = new html_table_cell(organizer_location_link($slot));
                        break;
                        case 'participants':
                            $cell = $row->cells[] = new html_table_cell(
                            organizer_get_participant_list($params, $slot, null)
                                );
                        break;
                        case 'teacher':
                            $cell = $row->cells[] = new html_table_cell(
                                    organizer_trainer_data($params, $slot, organizer_get_slot_trainers($slot->id))
                            );
                        break;
                        case 'details':
                            $cell = $row->cells[] = new html_table_cell(organizer_slot_status($params, $slot));
                        break;
                        case 'actions':
                            $cell = $row->cells[] = new html_table_cell(organizer_get_assign_button($slot->id, $params));
                        break;
                        default:
                            throw new coding_exception("Unrecognized column type: $column");
                    }  // End switch.

                    $cell->style .= ' vertical-align: middle;';
                } // End foreach column.
                $numshown++;
                $rows[] = $row;
            } // End is_free_slot.
        } // End foreach slot.
    } // End if slots.

    if ($numshown == 0) { // If no slots shown.
        $row = new html_table_row();
        $cell = new html_table_cell(get_string('slotlistempty', 'organizer'));
        $cell->colspan = count($columns);
        $row->cells[] = $cell;
        $rows[] = $row;
    } // End if no slots shown.
    return $rows;
}

/**
 * Provides detailed information about an organizer appointment.
 *
 * This function generates HTML output containing information about a given
 * organizer appointment, including comments, attendance status, grade, and feedback.
 *
 * @param stdClass $appointment The appointment object containing details about the user's appointment.
 * @return string HTML output displaying the appointment details. Returns an empty string if no appointment exists.
 * @throws coding_exception
 */
function organizer_app_details($appointment) {

    if (!isset($appointment)) {
        return '';
    }

    $organizer = organizer_get_organizer();

    $list = '<span style="display: table-cell;">';
    if ($appointment->comments) {
        $list .= organizer_get_fa_icon('fa fa-comment text-primary ml-1', organizer_filter_text($appointment->comments));
    }
    $list .= '</span>';

    if ($organizer->grade != 0) {

        $list .= '<span style="display: table-cell;">';
        $list .= organizer_get_attended_icon($appointment, $organizer->grade);
        $list .= '</span>';

        $grade = organizer_display_grade($organizer, $appointment->grade, $appointment->userid);
        if ($grade != get_string("nograde")) {
            $list .= '<span style="display: table-cell;" class="text-right mr-1">';
            $list .= $grade;
            $list .= '</span>';
        }
    }

    $list .= '<span style="display: table-cell">';
    $list .= $appointment->feedback ? organizer_get_fa_icon('fa fa-file-text-o ml-1', $appointment->feedback) : "";
    $list .= '</span>';

    return $list;
}

/**
 * Generates a string containing formatted HTML for displaying the date and time
 * of an organizer slot along with its duration.
 *
 * Depending on whether the slot is within the same day or spans multiple days,
 * the output includes styled date and time badges, showing the start and end time
 * of the slot. The duration is also included as a title attribute on the generated HTML.
 *
 * @param stdClass $slot The slot object containing start time and duration information.
 * @param bool $nobreak Whether to display the result in a single line without breaking. Defaults to false.
 * @return string A string of HTML elements displaying the date, time, and duration of the slot.
 *                Returns '-' if the slot or its start time is not set.
 * @throws coding_exception
 */
function organizer_date_time($slot, $nobreak = false) {
    if (!isset($slot) || !isset($slot->starttime)) {
        return '-';
    }

    [$unitname, $value] = organizer_figure_out_unit($slot->duration);
    $duration = ($slot->duration / $value) . ' ' . $unitname;

    // If slot is within a day.
    if (organizer_userdate($slot->starttime, get_string('datetemplate', 'organizer')) ==
        organizer_userdate($slot->starttime + $slot->duration, get_string('datetemplate', 'organizer'))) {
        $datefrom = html_writer::span(organizer_userdate($slot->starttime, '%a'), 'badge badge-info font-big mr-1');
        $datefrom .= organizer_userdate($slot->starttime, get_string('datetemplate', 'organizer')) . " " .
            html_writer::span(organizer_userdate($slot->starttime, get_string('timetemplate', 'organizer')),
                'badge badge-dark font-big mr-1');
        $dateto = html_writer::span(organizer_userdate($slot->starttime + $slot->duration,
            get_string('timetemplate', 'organizer')), 'badge badge-dark font-big ml-1');
    } else {
        $datefrom = html_writer::span(organizer_userdate($slot->starttime, '%a'), 'badge badge-info font-big mr-1');
        $datefrom .= organizer_userdate($slot->starttime, get_string('datetemplate', 'organizer')) . " " .
            html_writer::span(organizer_userdate($slot->starttime, get_string('timetemplate', 'organizer')),
                'badge badge-dark font-big mr-1');
        $slotendtime = $slot->starttime + $slot->duration;
        $dateto = html_writer::span(organizer_userdate($slotendtime, '%a'), 'badge badge-info font-big mr-1');
        $dateto .= organizer_userdate($slotendtime, get_string('datetemplate', 'organizer')) .
            html_writer::span(organizer_userdate($slotendtime, get_string('timetemplate', 'organizer')),
                'badge badge-dark font-big ml-1');
    }

    if ($nobreak) {
        $datestr = html_writer::span("$datefrom-$dateto", "slotdates text-nowrap", ["title" => $duration]);
    } else {
        $datestr = html_writer::span("$datefrom-<br />$dateto", "slotdates", ["title" => $duration]);
    }
    return $datestr;

}

/**
 * Formats a given date using a specified template.
 *
 * This function wraps the `userdate()` function and ensures the date
 * is formatted according to the given template.
 *
 * @param int $date The Unix timestamp of the date to be formatted.
 * @param string $format The template string to format the date.
 * @return string The formatted date string.
 */
function organizer_userdate($date, $format) {
    return userdate($date, $format, null, false, false);
}

/**
 * Collects and formats trainer data for a given slot.
 *
 * This function retrieves information about trainers assigned to a slot, evaluates their visibility,
 * and generates a formatted output based on the trainers' access rights and parameters provided.
 * It supports modes such as limited-width views and different tabs (e.g., student view).
 *
 * The function evaluates conditions such as whether the slot is accessible, expired, or beyond deadlines
 * and takes care of cases where trainers might be invisible to students. It also handles comments
 * added by trainers to the slot.
 *
 * @param array $params Parameters that include settings like mode and limited width.
 * @param stdClass $slot The slot object containing information about the appointment, such as deadlines and visibility.
 * @param array|null $trainerids A list of trainer IDs assigned to the slot. Defaults to null.
 *                                Passing an empty or null value results in returning a placeholder output.
 * @return string A formatted string with links or pictures of trainers, optionally including comments or icons.
 *                Returns '-' if the slot or trainer IDs are not set.
 * @throws dml_exception If a database read fails.
 */
function organizer_trainer_data($params, $slot, $trainerids = null) {
    global $USER, $DB;

    if (!isset($slot) || !$trainerids) {
        return '-';
    }

    $limitedwidth = isset($params['limitedwidth']) ? $params['limitedwidth'] : false;

    $query = "SELECT a.*
    FROM {organizer_slot_appointments} a
    WHERE a.slotid = :slotid";
    $param = ['slotid' => $slot->id];
    $appointments = $DB->get_records_sql($query, $param);

    $context = organizer_get_context();

    $canregister = has_capability('mod/organizer:register', $context, null, false);
    $canunregister = has_capability('mod/organizer:unregister', $context, null, false);

    $slotx = new organizer_slot($slot);

    $wasownslot = false;
    foreach ($appointments as $someapp) {
        if ($someapp->userid == $USER->id) {
            $wasownslot = true;
            break;
        }
    }

    $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotdisabled = $slotx->is_past_due() || $slotx->is_past_deadline();
    $myslotpending = $wasownslot && $slotx->is_past_deadline() && !$slotx->is_evaluated();

    $showteacher = $myslotpending || $organizerdisabled || $slotdisabled ||
        !$slotx->organizer_groupmode_user_has_access() || $slotx->is_evaluated();

    if ($wasownslot) {
        if (!$slotdisabled) {
               $showteacher |= !$canunregister;
        }
    }

    if ($params['mode'] == ORGANIZER_TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    } else if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $slot->teachervisible || $showteacher) {
        $output = "";
        $connector = "";
        foreach ($trainerids as $trainerid) {
            if ($limitedwidth) {
                $output .= organizer_get_userpicture_link($trainerid);
            } else {
                $output .= $connector . organizer_get_name_link($trainerid);
                $connector = "<br>";
            }
        }
    } else {
        $output = '<em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    if (isset($slot->teachercomments)) {
        if ($slot->teachercomments) {
            $output .= organizer_get_fa_icon('fa fa-comment text-primary ml-1',
                organizer_filter_text($slot->teachercomments));
        }
    } else {
        if ($slot->comments) {
            $output .= organizer_get_fa_icon('fa fa-comment text-primary ml-1',
                organizer_filter_text($slot->comments));
        }
    }

    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW && !$slot->teachervisible) {
        $output .= '<br /><em>' . get_string('teacherinvisible', 'organizer') . '</em>';
    }

    return $output;
}

/**
 * Retrieves a formatted list of organizer appointment details based on the provided parameters.
 *
 * @param object $organizer The organizer object containing relevant organizer information.
 * @param bool $groupmode Indicates whether group mode is enabled.
 * @param int $id The identifier for the slot or appointment, depending on context.
 * @param null $userid Optional user ID for fetching a specific user's appointment details.
 *
 * @return string A formatted HTML string containing the appointment details, including grades,
 *                feedback, and comments, if available.
 * @throws coding_exception
 * @throws dml_exception
 *
 */
function organizer_reg_organizer_app_details($organizer, $groupmode, $id, $userid = null) {
    global $DB;

    $list = '';
    // Groupmode with userid: id=slotid, no groupmode when no userid: id=appid.
    if ($userid) {
        $appointment = $DB->get_record('organizer_slot_appointments', ['slotid' => $id, 'userid' => $userid]);
    } else {
        $appointment = $DB->get_record('organizer_slot_appointments', ['id' => $id]);
    }
    if ($appointment) {
        if ($organizer->grade > 0) {
            $list .= organizer_get_attended_icon($appointment, $organizer->grade);
            $grade = organizer_display_grade($organizer, $appointment->grade, $appointment->userid);
            if ($grade != get_string("nograde")) {
                $list .= $grade;
            }
        }
        if (isset($appointment->feedback) && $appointment->feedback != '') {
            $list .= $appointment->feedback ? organizer_get_fa_icon('fa fa-file-text-o ml-1',
                $appointment->feedback) : "";
        }
        if (isset($appointment->comments) && $appointment->comments != '') {
            $list .= organizer_get_fa_icon('fa fa-comment text-primary ml-1', organizer_filter_text($appointment->comments));
        }
    }

    return $list;
}

/**
 * Generates the waiting list status description for a given user or group in an organizer activity.
 *
 * This function retrieves details about a user's or group's position in the waiting list
 * for an appointment slot within the specified organizer activity.
 *
 * @param int $organizerid The ID of the organizer activity.
 * @param int $userid Optional user ID to retrieve their waiting list status. Default is 0.
 * @param int $groupid Optional group ID to retrieve the waiting list status for a group. Default is 0.
 *
 * @return string HTML content describing the waiting list status and relevant details.
 * @throws dml_exception|coding_exception If there is an issue with the database query.
 */
function organizer_reg_waitinglist_status($organizerid, $userid = 0, $groupid = 0) {
    global $DB;

    $list = "";
    if ($groupid) {
        $query = "SELECT DISTINCT s.id, s.starttime, s.duration, s.location
                FROM {organizer_slot_queues} q
				INNER JOIN {organizer_slots} s ON s.id = q.slotid
				WHERE q.groupid = :groupid and s.organizerid = :organizerid";
        $par = ['groupid' => $groupid, 'organizerid' => $organizerid];
    } else {
        $query = "SELECT DISTINCT s.id, s.starttime, s.duration, s.location FROM {user} u
				INNER JOIN {organizer_slot_queues} q ON q.userid = u.id
				INNER JOIN {organizer_slots} s ON s.id = q.slotid
				WHERE u.id = :userid and s.organizerid = :organizerid";
        $par = ['userid' => $userid, 'organizerid' => $organizerid];
    }
    if ($slot = $DB->get_record_sql($query, $par)) {
        $slotx = new organizer_slot($slot->id);
        if ($groupid) {
            $position = $slotx->is_group_in_queue($groupid);
        } else {
            $position = $slotx->is_user_in_queue($userid);
        }
        $list = html_writer::span(get_string('inwaitingqueue', 'organizer'), 'font-italic');
        $slotinfo = str_replace("<br />", " ", organizer_date_time($slot));
        $slotinfo .= "<br>" . get_string('teacherid', 'organizer') . ": ";
        $trainerstr = "";
        if ($trainers = organizer_get_slot_trainers($slot->id, true)) {
            $conn = "";
            foreach ($trainers as $trainer) {
                $trainerstr .= $conn . $trainer->firstname . " " . $trainer->lastname;
                $conn = ", ";
            }
        }
        $slotinfo .= $trainerstr ?? "-";
        $slotinfo .= "<br>" . get_string('location', 'organizer') . ": ";
        $slotinfo .= $slot->location ?? "-";
        $slotinfo .= "<br>" . get_string('position', 'organizer') . ": ";
        $slotinfo .= $position;
        $list .= "<span style=\"cursor:help;\"> " .
            organizer_get_fa_icon('fa fa-info-circle', $slotinfo) . "</span>";
    }

    return $list;

}

/**
 * Handles teacher actions for a specific appointment entry in an organizer activity.
 *
 * This function generates various actions that teachers can perform on an appointment entry.
 * These actions include grading the appointment, sending reminders, assigning new users,
 * and deleting the appointment. It also determines the availability of each action based
 * on the user's capabilities, the organizer's settings, and the appointment's status.
 *
 * @param array $params Array containing parameters such as the organizer activity ID.
 * @param stdClass $entry The appointment entry containing details such as slot ID and status.
 * @param context $context The context of the organizer activity.
 * @param stdClass $organizer The organizer activity object containing settings such as grading.
 * @param bool $groupmode Whether the organizer activity is in group mode.
 *
 * @return array An array of button objects representing the available actions for the teacher.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_teacher_action($params, $entry, $context, $organizer, $groupmode) {

    $evalurl = new moodle_url(
        '/mod/organizer/slots_eval.php',
        ['id' => $params['id'], 'slots[]' => $entry->slotid, 'mode' => '3']
    );
    $remindurl = new moodle_url(
        '/mod/organizer/send_reminder.php',
        ['id' => $params['id'], 'recipient' => $entry->id, 'mode' => '3']
    );
    $assignurl = new moodle_url(
        '/mod/organizer/view.php',
        ['id' => $params['id'], 'sort' => 'datetime', 'mode' => '4', 'assignid' => $entry->id]
    );
    $deleteurl = new moodle_url(
        '/mod/organizer/appointment_delete.php',
        ['id' => $params['id'], 'appid' => $entry->appid]
    );

    $buttons = [];

    // Grade button.
    if ($organizer->grade) {
        $button = new stdClass();
        $button->text = get_string("btn_eval_short", 'organizer');
        $button->url = $evalurl;
        // If entry is appointment => grade button active.
        $button->disabled = !has_capability('mod/organizer:evalslots', $context, null, true)
            || $entry->status == ORGANIZER_APP_STATUS_NOT_REGISTERED;
        $button->icon = "fa fa-th-list";
        $buttons[] = $button;
    }

    // Reminder button.
    $button = new stdClass();
    $button->text = get_string("btn_remind", 'organizer');
    $button->url = $remindurl;
    // If max booking is not reached => show reminder button.
    $button->disabled = !has_capability('mod/organizer:sendreminders', $context, null, true) ||
        organizer_bookingnotpossible($groupmode, $organizer, $entry->id);
    $button->icon = "fa fa-paper-plane-o fw";
    $buttons[] = $button;

    $organizerconfig = get_config('organizer');
    if (isset($organizerconfig->allowcreationofpasttimeslots) &&
        $organizerconfig->allowcreationofpasttimeslots == 1) {
        $allowexpiredslotsassignment = true;
    } else {
        $allowexpiredslotsassignment = false;
    }

    // Assign button.
    $button = new stdClass();
    $button->text = get_string("btn_assign", 'organizer');
    $button->url = $assignurl;
    $button->icon = "fa fa-calendar-plus-o fw";
    // If max booking is not reached => show assign button.
    $button->disabled = !has_capability('mod/organizer:assignslots', $context, null, true) ||
        organizer_bookingnotpossible($groupmode, $organizer, $entry->id, $allowexpiredslotsassignment);
    $buttons[] = $button;

    // Delete appointment button.
    if (isset($entry->appid) && intval($entry->appid)) {
        $button = new stdClass();
        $button->text = get_string("btn_deleteappointment", 'organizer');
        $button->url = $deleteurl;
        $button->icon = "fa fa-trash fw";
        // If it is an appointment show button for deleting the appointment.
        $button->disabled = !has_capability('mod/organizer:deleteappointments', $context, null, true);
        $buttons[] = $button;
    }

    $output = "";

    foreach ($buttons as $button) {
        if ($button->disabled) {
            $button->icon .= " d-inline-block icon text-secondary";
            $output .= organizer_get_fa_icon($button->icon, $button->text);
        } else {
            $button->icon .= " d-inline-block icon text-primary";
            $output .= '<a href="' . $button->url . '">' .
                organizer_get_fa_icon($button->icon, $button->text) . '</a>';
        }
    }

    return $output;
}

/**
 * Retrieves a list of participants for a given slot, including their relevant details,
 * and generates the appropriate content based on sorting parameters, slot visibility,
 * and group mode settings.
 *
 * @param array $params Parameters for sorting and filtering the participant list (e.g., 'psort', 'pdir', etc.).
 * @param stdClass $slot Slot object containing details like visibility, start time, and max participants.
 * @param stdClass|null $app Appointment object for the current user and slot, if available.
 *
 * @return string HTML content for the participant list or an unavailability notice if the slot is not available.
 *
 * Generates a summary indicating the slot's availability, participant visibility,
 * and details of registered appointments. Additionally, handles various display cases
 * based on slot visibility mode and group mode configuration.
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_get_participant_list($params, $slot, $app) {
    global $DB, $USER;

    // Fetch relevant data.
    $dir = isset($params['pdir']) ? $params['pdir'] : 'ASC';
    if (isset($params['sort']) && $params['sort'] == 'participants') {
        $orderby = " ORDER BY u.lastname ASC, u.firstname ".$params['dir'];
    } else if (isset($params['psort']) && $params['psort'] == 'name') {
        $orderby = " ORDER BY u.lastname $dir, u.firstname $dir, u.idnumber ASC";
    } else if (isset($params['psort']) && $params['psort'] == 'id') {
        $orderby = " ORDER BY u.idnumber $dir, u.lastname ASC, u.firstname ASC";
    } else {
        $orderby = " ORDER BY u.lastname $dir, u.firstname $dir, u.idnumber ASC";
    }
    $query = "SELECT a.*, u.firstname, u.lastname, u.idnumber
        FROM {organizer_slot_appointments} a
        INNER JOIN {user} u ON a.userid = u.id
        WHERE a.slotid = :slotid $orderby";
    $param = ['slotid' => $slot->id];
    $appointments = $DB->get_records_sql($query, $param);
    $countapps = count($appointments);
    $ismyslot = $app && ($app->slotid == $slot->id);
    if (!(($slot->availablefrom == 0) || ($slot->starttime - $slot->availablefrom <= time()))) {
        $when = userdate($slot->starttime - $slot->availablefrom, get_string('fulldatetimetemplate', 'organizer'));
        return "<em>" . get_string('unavailableslot', 'organizer') . "<br/>{$when}</em>";
    }
    $studentview = (isset($params['mode']) ? $params['mode'] : '') == ORGANIZER_TAB_STUDENT_VIEW;
    $groupmode = organizer_is_group_mode();
    if ($slot->visibility == ORGANIZER_VISIBILITY_ANONYMOUS) {
        $slotvisibilitystr = organizer_get_fa_icon('fa fa-user-secret', get_string('slot_anonymous', 'organizer'));
    } else if ($slot->visibility == ORGANIZER_VISIBILITY_SLOT) {
        $slotvisibilitystr = organizer_get_fa_icon('fa fa-user-times', get_string('slot_slotvisible', 'organizer'));
    } else {
        $slotvisibilitystr = "";
    }

    $content = '';

    // Compose first summary line.
    $firstline = "";
    $notcollapsed = (isset($params['participantslist']) ? $params['participantslist'] : '') == 'notcollapsed';
    if (!$groupmode) {
        $maxparticipants = $slot->maxparticipants;
        $a = new stdClass();
        $a->numtakenplaces = $countapps;
        $a->totalplaces = $maxparticipants;
        if ($maxparticipants - $countapps != 0) {
            if ($maxparticipants == 1) {
                $firstline .= html_writer::span(get_string('places_taken_sg', 'organizer', $a), 'font-italic mr-1');
            } else {
                $firstline .= html_writer::span(get_string('places_taken_pl', 'organizer', $a), 'font-italic mr-1');
            }
        } else {
            if ($maxparticipants == 1) {
                $firstline .= html_writer::span(get_string('places_taken_sg', 'organizer', $a), 'font-italic text-danger mr-1');
            } else {
                $firstline .= html_writer::span(get_string('places_taken_pl', 'organizer', $a), 'font-italic text-danger mr-1');
            }
            if (organizer_is_queueable()) {
                $inqueue = count($DB->get_records('organizer_slot_queues', ['slotid' => $slot->id]));
                if ($inqueue) {
                    $a->inqueue = $inqueue;
                    $slotx = new organizer_slot($slot);
                    if ($a->queueposition = $slotx->is_user_in_queue($USER->id)) {
                        $firstline .= organizer_write_places_inqueue_position($a);
                    } else {
                        $firstline .= organizer_write_places_inqueue($a, $slot, $params);
                    }
                }
            }
        }
        if ($studentview) {
            $participantsvisible = $countapps && !$notcollapsed &&
                ($slot->visibility == ORGANIZER_VISIBILITY_ALL ||
                    ($slot->visibility == ORGANIZER_VISIBILITY_SLOT && $ismyslot));
        } else {
            $participantsvisible = $countapps;
        }
        if ($participantsvisible) {
            $firstline = organizer_get_icon('plus-square', get_string('clicktohideshow'),
                    null, null, 'collapseicon').$firstline.$slotvisibilitystr;
            $firstline = html_writer::div($firstline, 'collapseclick text-nowrap', ['data-target' => '.s'.$slot->id]);
        } else {
            $firstline = html_writer::div($firstline.$slotvisibilitystr, 'text-nowrap');
        }
    } else { // If groupmode.
        if ($countapps == 0) {
            $firstline .= html_writer::span(get_string('group_slot_available', 'organizer'), 'font-italic mr-1');
        } else {
            $firstline .= html_writer::span(get_string('group_slot_full', 'organizer'), 'font-italic text-danger mr-1');
            if (organizer_is_queueable()) {
                $sql = "SELECT COUNT(distinct q.groupid) FROM {organizer_slot_queues} q
                        WHERE q.slotid = :slotid";
                $paramssql = ['slotid' => $slot->id];
                $inqueues = $DB->count_records_sql($sql, $paramssql);
                if ($inqueues) {
                    $a = new stdClass();
                    $a->inqueue = $inqueues;
                    $slotx = new organizer_slot($slot);
                    if ($a->queueposition = $slotx->is_group_in_queue()) {
                        $firstline .= organizer_write_places_inqueue_position($a);
                    } else {
                        $firstline .= organizer_write_places_inqueue($a, $slot, $params);
                    }
                }
            }
        }
        $firstline = html_writer::div($firstline.$slotvisibilitystr, 'text-nowrap');
    }
    $content .= $firstline;

    // Write participant's list.
    if ($studentview) {
        if ($slot->visibility == ORGANIZER_VISIBILITY_ANONYMOUS) {
            if ($ismyslot) {
                $content .= organizer_get_name_link($app->userid);
                $content .= organizer_get_teacherapplicant_output($app->teacherapplicantid,
                        $app->teacherapplicanttimemodified) . '&nbsp;';
                if ($app->comments) {
                    $content .= organizer_get_fa_icon('fa fa-comment text-primary ml-1',
                            organizer_filter_text($app->comments)) . "<br />";
                } else {
                    $content .= "<br />";
                }
            }
        } else { // Not anonymous.
            if ($groupmode) {
                $app = reset($appointments);
                if ($app === false) {
                    $content .= html_writer::span(get_string('nogroup', 'organizer'), 'font-italic ml-1');
                } else {
                    $groupname = $DB->get_field('groups', 'name', ['id' => $app->groupid]);
                    $content .= html_writer::span($groupname, 'font-italic');
                    $content .= html_writer::span(organizer_get_teacherapplicant_output($app->teacherapplicantid,
                            $app->teacherapplicanttimemodified));
                }
            }
            $showparticipants = ($slot->visibility == ORGANIZER_VISIBILITY_ALL) || $ismyslot;
            if ($showparticipants) {
                $content .= html_writer::start_span('', ['style' => 'display: table']);
                $apps = count($appointments);
                foreach ($appointments as $appointment) {
                    $class = $apps || $groupmode ? 'mycollapse s'.$slot->id : '';
                    $content .= html_writer::start_span($class, ['style' => 'display: table-row']);
                    $namelink = organizer_get_name_link($appointment->userid);
                    if ($groupmode) {
                        if ($appointment->userid == $appointment->applicantid) {
                            $namelink .= organizer_get_fa_icon('fa fa-star fa-2xs',
                                get_string('applicant', 'organizer'));
                        }
                    } else {
                        $namelink .= organizer_get_teacherapplicant_output($appointment->teacherapplicantid,
                            $appointment->teacherapplicanttimemodified);
                    }
                    $content .= html_writer::span($namelink, '', ['style' => 'display: table-cell']);
                    $content .= organizer_app_details($appointment);
                    $content .= html_writer::end_span();
                }
                $content .= html_writer::end_span();
            }
        }
    } else {  // Not studentview.
        if ($countapps == 0) {
            $content .= $groupmode ? ('<em>' . get_string('nogroup', 'organizer') .
                    '</em><br />') : ('<em>' . get_string('noparticipants', 'organizer') . '</em><br />');
        } else {
            $list = "";
            if ($groupmode) {
                $app = reset($appointments);
                if ($app !== false) {
                    $groupname = $DB->get_field('groups', 'name', ['id' => $app->groupid]);
                    if (!$notcollapsed) {
                        $groupnameline = organizer_get_icon('plus-square',
                                get_string('clicktohideshow'), null, null, 'collapseicon').$groupname;
                        $groupnameline .= html_writer::span(organizer_get_teacherapplicant_output($app->teacherapplicantid,
                            $app->teacherapplicanttimemodified));
                        $groupnameline = html_writer::div($groupnameline, 'collapseclick font-italic',
                            ['data-target' => '.s'.$slot->id]);
                    } else {
                        $groupnameline = $groupname;
                        $groupnameline .= html_writer::span(organizer_get_teacherapplicant_output($app->teacherapplicantid,
                            $app->teacherapplicanttimemodified));
                        $groupnameline = html_writer::div($groupnameline);
                    }
                    $content .= $groupnameline;
                }
            }

            $list .= html_writer::start_span('', ['style' => 'display: table']);
            $apps = count($appointments);
            foreach ($appointments as $appointment) {
                $class = $apps || $groupmode ? 'mycollapse s'.$slot->id : '';
                $list .= html_writer::start_span($class, ['style' => 'display: table-row']);
                $list .= html_writer::start_span('', ['style' => 'display: table-cell']);
                $identity = organizer_get_user_identity($appointment->userid);
                $identity = $identity ? "<span class='organizer_identity ml-1'>({$identity})</span>" : "";
                $list .= organizer_get_name_link($appointment->userid);
                if ($groupmode) {
                    if (organizer_is_group_mode() && $appointment->userid == $appointment->applicantid) {
                        $list .= organizer_get_fa_icon('fa fa-star fa-2xs', get_string('applicant', 'organizer'));
                    }
                } else {
                    $list .= organizer_get_teacherapplicant_output(
                        $appointment->teacherapplicantid,
                        $appointment->teacherapplicanttimemodified
                    );
                }
                $list .= $identity;
                $list .= html_writer::end_span();
                $list .= organizer_app_details($appointment);
                $list .= html_writer::end_span();
            }
            $list .= html_writer::end_span();
            $content .= $list;
        }
    }
    return $content;
}

/**
 * Retrieves the appropriate icon depending on the attendance status of the appointment.
 *
 * @param stdClass $appointment The appointment object containing attendance details.
 * @param bool $gradeactive A flag indicating whether the grading is active or not.
 *
 * @return string HTML representation of the attendance status icon.
 * @throws coding_exception
 */
function organizer_get_attended_icon($appointment, $gradeactive) {
    if (isset($appointment->attended) && $appointment->attended == 1) {
        return organizer_get_fa_icon('fa fa-circle fa-xs ml-1 mr-1 green',
            get_string('reg_status_slot_attended', 'organizer'));
    } else {
        if ($gradeactive) {
            return organizer_get_fa_icon('fa fa-circle-o fa-xs ml-1 mr-1',
                get_string('reg_status_slot_not_attended', 'organizer'));
        } else {
            return "";
        }
    }
}

/**
 * Renders a location link for a given slot.
 *
 * If the slot has a valid location link, it generates a clickable hyperlink.
 * If the location link is missing or invalid, it returns the plain text of the location or a placeholder.
 *
 * @param stdClass $slot An object containing slot details, including `location` and `locationlink`.
 *
 * @return string The HTML link or location text, or a placeholder if no location is set.
 */
function organizer_location_link($slot) {
    if (!isset($slot) || !isset($slot->location) || $slot->location == '') {
        return '-';
    }

    if (isset($slot->locationlink)) {
        if (strpos($slot->locationlink, 'http://') === false &&
                strpos($slot->locationlink, 'https://') === false  ) {
            $link = 'http://' . $slot->locationlink;
        } else {
            $link = $slot->locationlink;
        }
        if (filter_var($link, FILTER_VALIDATE_URL)) {
            return html_writer::link($link, $slot->location, ['target' => '_blank']);
        }
    }

    return $slot->location;
}

/**
 * Generates an HTML image element with the specified attributes.
 *
 * This function creates an HTML `<img>` element using the provided source URL, alternative text,
 * title attribute, ID, and additional attributes. It is commonly used to include images with
 * desired metadata in a webpage.
 *
 * @param string $src The source URL of the image. This is a required attribute for the image tag.
 * @param string $alt The alternative text for the image. This text provides context when the image cannot be displayed.
 * @param string $title The title attribute for the image, typically displayed as a tooltip upon hover.
 * @param string $id An optional ID attribute for the image element. Defaults to an empty string if not provided.
 * @param string $other Any additional attributes to include in the image element. Defaults to an empty string.
 *
 * @return string The generated HTML `<img>` tag.
 */
function organizer_get_img($src, $alt, $title, $id = '', $other = '') {
    return '<img src="' . $src . '" alt="' . $alt . '" title="' . $title . '" id="' . $id . '" ' . $other . ' />';
}

/**
 * Generates an HTML image element with specified source, alt text, title, and additional attributes.
 *
 * @param string $src The source URL of the image.
 * @param string $alt The alternative text for the image.
 * @param string $title The title attribute for the image, typically shown as a tooltip.
 * @param string $id An optional ID attribute for the image element. Defaults to an empty string.
 * @param string $other Any additional attributes to include in the HTML element. Defaults to an empty string.
 *
 * @return string The generated HTML <img> tag.
 */
function organizer_get_icon_msg($name, $infotxt) {
    $out = "";
    switch ($name) {
        case 'group':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-primary',
                'fa-group fa-stack-1x text-primary', $infotxt);
            break;
        case 'nogroup':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-danger',
                'fa-group fa-stack-1x text-danger', $infotxt).'<span class="text-danger">'.$infotxt.'</span>';
            break;
        case 'expires':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-primary',
                'fa-hourglass-3 fa-stack-1x text-primary', $infotxt);
            break;
        case 'expired':
            $out = organizer_get_fa_icon_stacked('fa-hourglass fa-stack-1x text-danger',
                'fa-ban fa-stack-2x text-danger', $infotxt);
            break;
        case 'neverexpires':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-secondary',
                'fa-hourglass fa-stack-1x text-secondary', $infotxt);
            break;
        case 'grade':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-primary',
                'fa-th-list fa-stack-1x text-primary', $infotxt);
            break;
        case 'nograde':
            $out = organizer_get_fa_icon_stacked('fa-th-list fa-stack-1x text-primary',
                'fa-ban fa-stack-2x text-primary', $infotxt);
            break;
        case 'queues':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-primary',
                'fa-coffee fa-stack-1x text-primary', $infotxt);
            break;
        case 'noqueues':
            $out = organizer_get_fa_icon_stacked('fa-coffee fa-stack-1x text-primary',
                'fa-ban fa-stack-2x text-primary', $infotxt);
            break;
        case 'minmax1':
            $out = "";
            break;
        case 'minmax':
            $out = organizer_get_fa_icon_stacked('fa-circle-thin fa-stack-2x text-primary',
                    'fa-files-o fa-stack-1x text-primary', $infotxt).$infotxt;
            break;
        case 'enoughplaces':
            $out = organizer_get_fa_icon('fa fa-check-circle text-success mr-1', $infotxt).$infotxt;
            break;
        case 'notenoughplaces':
            $out = organizer_get_fa_icon('fa fa-exclamation-circle text-warning mr-1', $infotxt).$infotxt;
            break;
    }
    return $out;
}

/**
 * Retrieves an icon element with specified Font Awesome classes, tooltip, and additional attributes.
 *
 * This function generates an HTML string for a Font Awesome icon. It optionally includes
 * a tooltip and additional attributes to enhance the icon's functionality and accessibility.
 *
 * @param string $classes The CSS classes to apply to the icon element.
 * @param string $tooltiptext The text for the tooltip, displayed when hovering over the icon. Defaults to an empty string.
 * @param string $other Additional attributes to include in the icon's HTML element. This is useful for customizations.
 *  Defaults to an empty string.
 *
 * @return string The generated HTML <i> tag for the icon, including the specified classes and attributes.
 */
function organizer_get_fa_icon($classes, $tooltiptext = "", $other = "") {
    if ($tooltiptext) {
        $other .= $tooltiptext ?
            "data-toggle='tooltip' data-html='true' title='$tooltiptext' aria-label='$tooltiptext'" : '';
    }
    return "<i class='$classes' $other></i>";
}

/**
 * Generates a stacked Font Awesome icon with optional tooltip and additional attributes.
 *
 * This function creates an HTML structure for a Font Awesome stacked icon,
 * combining two Font Awesome icons with different CSS classes. Tooltip functionality
 * and additional HTML attributes can also be included if required.
 *
 * @param string $classesback The CSS classes for the background icon of the stack.
 * @param string $classesfront The CSS classes for the foreground icon of the stack.
 * @param string $tooltiptext The text for the tooltip, displayed when hovering over the icon. Defaults to an empty string.
 * @param string $other Additional attributes to include in the stacked icon's HTML element. This is useful for customizations.
 *  Defaults to an empty string.
 *
 * @return string The generated HTML for the stacked Font Awesome icon (<span> element with included icons and attributes).
 */
function organizer_get_fa_icon_stacked($classesback, $classesfront, $tooltiptext = "", $other = "") {
    if ($tooltiptext) {
        $other .= $tooltiptext ?
            "data-toggle='tooltip' data-html='true' title='$tooltiptext' aria-label='$tooltiptext'" : '';
    }
    $out = "<span class='fa-stack fa-lg' $other>";
    $out .= "<i class='fa $classesback'></i>";
    $out .= "<i class='fa $classesfront'></i>";
    $out .= '</span>';
    return $out;
}

/**
 * Returns the appropriate icon based on the given status and available Font Awesome styles.
 *
 * This function serves as a utility to fetch correct HTML markup
 * for various icons depending on a provided case or status type.
 *
 * @param string $status The status determining which icon to retrieve.
 * @param string $infotxt Optional text that can be displayed as a tooltip or accompanying text for the icon.
 *
 * @return string The generated HTML for the icon element corresponding to the status.
 */
function organizer_get_icon($iconname, $string, $size="small", $id="", $class="") {
    global $OUTPUT;

    $attributes = $id != '' ? ['id' => $id] : [];
    $alt = "";
    if ($string) {
        $attributes['data-toggle'] = "tooltip";
        $attributes['title'] = $string;
        $attributes['aria-label'] = $string;
        $attributes['class'] = $class;
        $alt = $string;
    }
    return $icon = $OUTPUT->pix_icon($iconname, $alt, 'mod_organizer', $attributes);
}

/**
 * Determines the status of a given organizer slot and returns the corresponding icon or link.
 *
 * This function evaluates various properties of an organizer slot, such as whether it is full,
 * past its deadline, or contains participants, and generates appropriate status representations
 * (e.g., icons or links) using Font Awesome and tooltips. The representation changes depending on the
 * slot's state and actions that can be performed on it.
 *
 * @param array $params An associative array containing parameters such as 'id' (the course module ID)
 *                      and 'limitedwidth' (a boolean indicating if a smaller icon size is used).
 * @param object $slot A slot object containing details about the organizer slot being evaluated.
 *
 * @return string The generated HTML string representing the status of the slot,
 *                which could include icons, links, or tooltips.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_slot_status($params, $slot) {
    $slotx = new organizer_slot($slot);

    $slotisfull = $slotx->is_full();
    $slotevaluated = $slotx->is_evaluated();
    $slotpastdue = $slotx->is_past_due();
    $slotpastdeadline = $slotx->is_past_deadline();
    $slothasparticipants = $slotx->has_participants();
    $gradingactive = $slotx->gradingisactive();

    $slotpending = $slotpastdue && $slothasparticipants && $gradingactive;

    $actionurl = new moodle_url(
        '/mod/organizer/slots_eval.php',
        ['id' => $params['id'], 'slot' => $slot->id]
    );

    if ($params['limitedwidth']) {
        $sizeclass = "1x";
    } else {
        $sizeclass = "2x";
    }
    if ($slotpastdue) {  // Slot starttime has passed.
        if ($slotevaluated) {
            return '<a href="' . $actionurl->out(false) . '">'
                . organizer_get_fa_icon("fa fa-check-square fa-$sizeclass text-primary",
                get_string('img_title_evaluated', 'organizer'));
        } else {
            if ($slotpending) {
                return '<a href="'.$actionurl->out(false).'">'.
                    organizer_get_fa_icon("fa fa-flag-o fa-$sizeclass text-primary",
                    get_string('img_title_pending', 'organizer')) . '</a>';
            } else {
                if ($slotisfull) {
                    return organizer_get_fa_icon("fa fa-circle fa-$sizeclass text-primary",
                        get_string('img_title_full', 'organizer'));
                } else if ($slothasparticipants) {
                    return organizer_get_fa_icon("fa fa-dot-circle-o fa-$sizeclass text-primary",
                        get_string('img_title_past_deadline', 'organizer'));
                } else {
                    return organizer_get_fa_icon("fa fa-ban fa-$sizeclass text-primary",
                        get_string('img_title_no_participants', 'organizer'));
                }
            }
        }
    } else { // Slot starttime not reached.
        $slotcolor = $slotpastdeadline ? "slotovercolor" : "slotactivecolor";
        if ($slotisfull) {
            return organizer_get_fa_icon("fa fa-circle fa-$sizeclass $slotcolor",
                get_string('img_title_full', 'organizer'));
        } else {
            $imgtitle = $slotpastdeadline ? "img_title_past_deadline" : "img_title_due";
            if ($slothasparticipants) {
                return organizer_get_fa_icon("fa fa-dot-circle-o fa-$sizeclass $slotcolor",
                    get_string($imgtitle, 'organizer'));
            } else {
                return organizer_get_fa_icon("fa fa-circle-o fa-$sizeclass $slotcolor",
                    get_string($imgtitle, 'organizer'));
            }
        }
    }
}

/**
 * Generates a string of HTML representing various actions (edit, delete, print, grade)
 * available for a specific organizer slot.
 *
 * The actions are determined based on user capabilities and slot properties.
 *
 * @param int $slotid The ID of the slot for which actions are being generated.
 * @param array $params Parameters for the organizer, including 'id' (the course module ID),
 *                      and 'mode' (current organizer view mode).
 * @param bool $grades Indicates whether grading options are available for the slot.
 *
 * @return string The generated HTML string of action links and icons for the slot.
 *                Includes links for editing, deleting, printing, or evaluating the slot.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_slot_commands($slotid, $params, $grades) {
    global $PAGE;

    $outstr = "";

    $context = $PAGE->context;

    // EDIT.
    if (has_capability("mod/organizer:editslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_edit.php',
                ['id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode']]
        );
        $outstr .= html_writer::link($actionurl, organizer_get_fa_icon(
            "fa fa-pencil fa-fw", get_string('btn_editsingle', 'organizer'))
        );
    }

    // DELETE.
    if (has_capability("mod/organizer:deleteslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_delete.php',
                ['id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode']]
        );
        $outstr .= html_writer::link($actionurl, organizer_get_fa_icon(
                "fa fa-trash fa-fw", get_string('btn_deletesingle', 'organizer'))
        );
    }

    // PRINT.
    if (has_capability("mod/organizer:printslots", $context)) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_printdetail.php',
                ['id' => $params['id'], 'slot' => $slotid, 'mode' => $params['mode']]
        );
        $outstr .= html_writer::link($actionurl, organizer_get_fa_icon(
                "fa fa-print fa-fw", get_string('btn_printsingle', 'organizer'))
        );
    }

    // GRADE/EVALUATE.
    if (has_capability("mod/organizer:evalslots", $context) && $grades) {
        $actionurl = new moodle_url(
                '/mod/organizer/slots_eval.php',
                ['id' => $params['id'], 'slots[]' => $slotid, 'mode' => $params['mode']]
        );
        $outstr .= html_writer::link($actionurl, organizer_get_fa_icon(
                "fa fa-th-list fa-fw", get_string('btn_evalsingle', 'organizer'))
        );
    }

    // EXPORT ICS.
    $actionurl = new moodle_url('/mod/organizer/slots_export.php',
        ['id' => $params['id'], 'slots' => $slotid, 'mode' => $params['mode']]);
    $outstr .= \html_writer::link($actionurl,
        organizer_get_fa_icon("fa fa-calendar fa-fw", get_string('btn_exportics', 'organizer')));

    return $outstr;
}

/**
 * Determines the registration status of a specific slot for a given organizer.
 *
 * This function will check various conditions (e.g., expiration, deadlines, fullness, attendance)
 * to generate and return an appropriate HTML output including an icon and a message representing
 * the current registration status of a given organizer slot.
 *
 * @param object $organizer The organizer instance, containing properties such as grading options.
 * @param object $slot The slot object containing relevant slot data (e.g., availability, due status).
 * @param null $onlyownslotsmsg Optional message displayed for own slots.
 *
 * @return string HTML string containing the icon and message for the slot's registration status.
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_slot_reg_status($organizer, $slot, $onlyownslotsmsg = null) {
    global $PAGE;

    $slotx = new organizer_slot($slot);

    $app = organizer_get_slot_user_appointment($slotx);

    $pagebodyclasses = $PAGE->bodyclasses;
    if (strpos($pagebodyclasses, 'limitedwidth') && !$onlyownslotsmsg) {
        $sizeclass = "1x";
    } else {
        $sizeclass = "2x";
    }

    if ($slotx->organizer_expired()) {
        $output = organizer_get_fa_icon("fa fa-calendar-times-o fa-$sizeclass",
            get_string('reg_status_organizer_expired', 'organizer'));
    } else if ($slotx->is_past_due()) {
        if ($app) {
            if (!isset($app->attended)) {
                if ($organizer->grade != 0) {
                    $output = organizer_get_fa_icon("fa fa-flag-o fa-$sizeclass slotovercolor",
                        get_string('reg_status_slot_pending', 'organizer'));
                } else {
                    $output = organizer_get_fa_icon("fa fa-circle fa-$sizeclass slotovercolor",
                        get_string('reg_status_slot_expired', 'organizer'));
                }
            } else if ($app->attended == 0) {
                $output = organizer_get_fa_icon("fa fa-check-square-o fa-$sizeclass slotovercolor",
                    get_string('reg_status_slot_not_attended', 'organizer'));
            } else if ($app->attended == 1) {
                $output = organizer_get_fa_icon("fa fa-check-square fa-$sizeclass slotovercolor",
                    get_string('reg_status_slot_attended', 'organizer'));
            }
        } else {
            $output = organizer_get_fa_icon("fa fa-remove fa-$sizeclass slotovercolor",
                get_string('reg_status_slot_expired', 'organizer'), "big");
        }
    } else if ($slotx->is_past_deadline()) {
        $output = organizer_get_fa_icon("fa fa-calendar-times-o fa-$sizeclass slotovercolor",
            get_string('reg_status_slot_past_deadline', 'organizer'));
    } else {
        if ($slotx->is_full()) {
            if ($app) {
                $output = organizer_get_fa_icon("fa fa-check-circle fa-$sizeclass slotactivecolor",
                    get_string('reg_not_occured', 'organizer'));
            } else {
                $output = organizer_get_fa_icon("fa fa-circle fa-$sizeclass slotactivecolor",
                    get_string('reg_status_slot_full', 'organizer'));
            }
        } else {
            $output = organizer_get_fa_icon("fa fa-circle-o fa-$sizeclass slotactivecolor",
                get_string('reg_status_slot_available', 'organizer'));
        }
    }

    if ($onlyownslotsmsg) {
        $output .= "<br>".$onlyownslotsmsg;
    }
    return $output;
}

/**
 * Executes actions related to participants for a given appointment slot.
 *
 * Depending on the user's capabilities, the slot's status, and booking limits,
 * this function determines the action the participant can perform, such as
 * registering, unregistering, queuing, or unqueuing for a slot.
 *
 * @param array $params An array containing parameters for the action.
 * @param stdClass $slot The appointment slot object.
 *
 * @return string The action to be performed, such as 'register', 'unregister',
 *                'queue', 'unqueue', 'reregister', or an empty string if no action is allowed.
 *
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 */
function organizer_participants_action($params, $slot) {
    global $USER;

    $slotx = new organizer_slot($slot);
    [, , $organizer, $context] = organizer_get_course_module_data();
    $action = "";

    $rightregister = has_capability('mod/organizer:register', $context, null, false);
    $rightunregister = has_capability('mod/organizer:unregister', $context, null, false);
    $isuserslot = organizer_get_slot_user_appointment($slotx) ? true : false;
    $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
    $slotexpired = $slotx->is_past_due() || $slotx->is_past_deadline();
    $slotfull = $slotx->is_full();
    $disabled = $organizerdisabled || !$slotx->organizer_groupmode_user_has_access() || $slotx->is_evaluated();
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $isalreadyinqueue = $slotx->is_group_in_queue();
        if ($group = organizer_fetch_user_group($USER->id, $organizer->id)) {
            $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, null, $group->id);
            $hasbookedalready = organizer_get_all_group_appointments($organizer, $group->id);
            $dailylimitreached = organizer_userslotsdailylimitreached($organizer, 0, $group->id);
        } else {
            $lefttobook = 0;
            $hasbookedalready = 0;
            $dailylimitreached = true;
        }
    } else {
        $isalreadyinqueue = $slotx->is_user_in_queue($USER->id);
        $lefttobook = organizer_multiplebookings_slotslefttobook($organizer, $USER->id);
        $hasbookedalready = organizer_get_last_user_appointment($organizer);
        $dailylimitreached = organizer_userslotsdailylimitreached($organizer, $USER->id, 0);
    }
    $isqueueable = $organizer->queue && !$isalreadyinqueue && !$disabled;
    if ($isuserslot) {
        $action = 'unregister';
        $disabled |= !$rightunregister || $slotexpired || $slotx->is_evaluated();
    } else if (!$slotfull) {
        $disabled |= !$rightregister || $slotexpired || $dailylimitreached;
        if ($lefttobook) {
            $action = 'register';
        } else {
            if ($hasbookedalready) {
                if ($organizer->noreregistrations) {
                    if (organizer_exist_bookedslotbeforedeadline($organizer->id, $USER->id)) {
                        $action = 'reregister';
                    } else {
                        return "";
                    }
                } else {
                    $action = 'reregister';
                }
            } else {
                return "";
            }
        }
    } else if ($slotfull) {
        if ($isqueueable) {
            $action = 'queue';
            $disabled |= !$rightregister || $slotexpired || !$lefttobook;
        }
    }
    if ($isalreadyinqueue) {
        $action = 'unqueue';
        $disabled |= !$rightunregister || $slotexpired;
    }

    // Show slot comments and ICS export if user is owner.
    $commentbtn = "";
    $exporticsbtn = "";
    if ($isuserslot) {
        $commenturl = new moodle_url(
            '/mod/organizer/comment_edit.php',
            ['id' => $params['id'], 'slot' => $slotx->get_id()]
        );
        $commentbtndisabled = $organizerdisabled || !$slotx->organizer_groupmode_user_has_access();
        $commentlabel = get_string("btn_comment", 'organizer');
        if ($commentbtndisabled) {
            $commentbtn = '<a href="#" class="action disabled">' . $commentlabel .
                organizer_get_fa_icon('fa fa-commenting text-secondary ml-1', $commentlabel) . '</a>';
        } else {
            $commentbtn = '<a href="' . $commenturl . '" class="action">' . $commentlabel .
                organizer_get_fa_icon('fa fa-commenting ml-1', $commentlabel)  . '</a>';
        }

        // Allow users to EXPORT ICS.
        $actionurl = new moodle_url(
            '/mod/organizer/slots_export.php', ['id' => $params['id'], 'slots' => $slotx->get_id()]
        );
        $exporticsbtn = \html_writer::link($actionurl,
            get_string('exportics', 'organizer') .  organizer_get_fa_icon(
                "fa fa-calendar fa-fw", get_string('exportics', 'organizer'))
        );
    }

    return organizer_get_reg_button($action, $slotx->get_id(), $params, $disabled).$commentbtn.$exporticsbtn;

}

/**
 * Generates a button for handling organizer registration actions.
 *
 * This function generates a registration button for an organizer slot based
 * on the provided action, slot ID, and parameters. It also optionally disables
 * the button depending on the state.
 *
 * @param string $action The action to be performed (e.g., 'register', 'unregister', 'queue').
 * @param int $slotid The ID of the organizer slot.
 * @param array $params An array of parameters including organizer details such as ID and mode.
 * @param bool $disabled Optional. Determines whether the button is disabled. Defaults to false.
 *
 * @return string The HTML output of the rendered button.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 *
 */
function organizer_get_reg_button($action, $slotid, $params, $disabled = false) {
    global $OUTPUT;

    $out = "";
    if ($action) {
        $actionurl = new moodle_url(
            '/mod/organizer/view_action.php',
            ['id' => $params['id'], 'mode' => $params['mode'], 'action' => $action, 'slot' => $slotid]
        );

        $out = $OUTPUT->single_button(
            $actionurl, get_string("btn_$action", 'organizer'), 'post', ['disabled' => $disabled]
        );
        $out = str_replace("btn-secondary", "btn-primary mb-2", $out);
    }
    return $out;
}

/**
 * Retrieves a button for assigning a user to an organizer slot.
 *
 * This function generates a button for assigning users to an organizer slot.
 * It forms a link to the slot assignment page with the necessary parameters
 * and ensures consistent styling for the button.
 *
 * @param int $slotid The ID of the organizer slot.
 * @param array $params An array of parameters including:
 *                      - 'id': Organizer ID.
 *                      - 'mode': Organizer mode.
 *                      - 'assignid': ID of the user to be assigned.
 *
 * @return string The HTML output of the rendered button.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_get_assign_button($slotid, $params) {
    global $OUTPUT;

    $actionurl = new moodle_url(
        '/mod/organizer/slot_assign.php',
        ['id' => $params['id'], 'mode' => $params['mode'], 'assignid' => $params['assignid'], 'slot' => $slotid]
    );

    $out = $OUTPUT->single_button($actionurl, get_string("btn_assign", 'organizer'));
    $out = str_replace(" id=", " name=", str_replace("btn-secondary", "btn-primary", $out));

    return $out;
}

/**
 * Determines the size of the font-awesome icon class and returns a specific icon for the organizer slot status.
 *
 * This function evaluates the current organizer slot's status and chooses the appropriate font-awesome icon
 * along with its size and explanation from the language strings. It handles different statuses such as attended,
 * pending, registered, and not registered, as well as evaluated slots.
 *
 * @param int $status The status of the organizer slot (e.g., attended, pending, etc.).
 * @param object $organizer The organizer object containing configuration data.
 * @param bool $slotevaluated Optional. True if the slot has been evaluated. Defaults to false.
 *
 * @return string HTML for the font-awesome icon with the corresponding status and description.
 * @throws coding_exception
 */
function organizer_get_status_icon_reg($status, $organizer, $slotevaluated = false) {
    global $PAGE;

    $pagebodyclasses = $PAGE->bodyclasses;
    if (strpos($pagebodyclasses, 'limitedwidth')) {
        $sizeclass = "1x";
    } else {
        $sizeclass = "2x";
    }

    $out = "";
    if ($slotevaluated) {
        $out = organizer_get_fa_icon("fa fa-check-square fa-$sizeclass text-primary",
            get_string('img_title_evaluated', 'organizer'));
    } else {
        switch ($status) {
            case ORGANIZER_APP_STATUS_ATTENDED:
                $out = organizer_get_fa_icon("fa fa-check-square-o fa-$sizeclass slotovercolor",
                    get_string('reg_status_slot_attended', 'organizer'));
                break;
            case ORGANIZER_APP_STATUS_PENDING:
                if ($organizer->grade) {
                    $out = organizer_get_fa_icon("fa fa-flag-o fa-$sizeclass slotovercolor",
                        get_string('reg_status_slot_pending', 'organizer'));
                } else {
                    $out = organizer_get_fa_icon("fa fa-circle fa-$sizeclass slotovercolor",
                        get_string('reg_status_registered', 'organizer'));
                }
                break;
            case ORGANIZER_APP_STATUS_REGISTERED:
                $out = organizer_get_fa_icon("fa fa-circle fa-$sizeclass slotactivecolor",
                    get_string('reg_status_registered', 'organizer'));
                break;
            case ORGANIZER_APP_STATUS_NOT_REGISTERED:
                $out = organizer_get_fa_icon("fa fa-circle-thin fa-$sizeclass slotovercolor",
                    get_string('reg_status_not_registered', 'organizer'));
                break;
            default:
                throw new \Exception('Unexpected value');
        }
    }
    return $out;
}

/**
 * Determines the appropriate time unit (days, hours, minutes, or seconds) for a given time in seconds.
 *
 * This function analyzes the input time in seconds and selects the largest applicable time unit
 * (e.g., days, hours, minutes, or seconds). It also returns the corresponding language string
 * for the unit name (singular or plural) and the unit factor.
 *
 * @param int $time The time in seconds to be analyzed.
 *
 * @return array An array containing:
 *               - string $unit: The language string for the unit name (e.g., day, hour).
 *               - int $factor: The factor representing the chosen unit in seconds (e.g., 86400 for days).
 *
 * @throws coding_exception If a language string is missing or cannot be retrieved.
 */
function organizer_figure_out_unit($time) {
    if ($time % 86400 == 0) {
        $out = (($time / 86400) == 1) ? get_string('day', 'organizer') : get_string('day_pl', 'organizer');
        return [$out, 86400];
    } else if ($time % 3600 == 0) {
        $out = (($time / 3600) == 1) ? get_string('hour', 'organizer') : get_string('hour_pl', 'organizer');
        return [$out, 3600];
    } else if ($time % 60 == 0) {
        $out = (($time / 60) == 1) ? get_string('min', 'organizer') : get_string('min_pl', 'organizer');
        return [$out, 60];
    } else {
        $out = (($time == 1) ? get_string('sec', 'organizer') : get_string('sec_pl', 'organizer'));
        return [$out, 1];
    }
}

/**
 * Retrieves a countdown representation of a given time in days, hours, minutes, and seconds.
 *
 * This function takes a time period in seconds as input and splits it into an
 * array containing the number of days, hours, minutes, and seconds remaining.
 *
 * @param int $time The time in seconds to be converted to a countdown format.
 *
 * @return array An indexed array containing:
 *               - int $days: The number of days in the countdown.
 *               - int $hrs: The number of hours in the countdown.
 *               - int $min: The number of minutes in the countdown.
 *               - int $sec: The number of seconds in the countdown.
 */
function organizer_get_countdown($time) {
    $secsinday = 24 * ($secsinhour = 60 * ($secsinmin = 60));
    $days = intval($time / $secsinday);
    $hrs = intval(($time % $secsinday) / $secsinhour);
    $min = intval(($time % $secsinhour) / $secsinmin);
    $sec = intval($time % $secsinmin);
    return [$days, $hrs, $min, $sec];
}

/**
 * Checks if a given organizer slot is free for a user.
 *
 * This function checks if the provided organizer slot is available based on various conditions, such as:
 * - Whether the slot is already full.
 * - Whether the slot is past its due time.
 * - Whether the user already has an appointment for this slot.
 *
 * @param stdClass $slot The slot object containing details about the organizer slot.
 * @param int $userid The ID of the user for whom the slot availability is being checked.
 * @param bool|null $assignmentview Optional. Whether to consider the current assignment view when evaluating the slot.
 *
 * @return bool True if the slot is free for the user, false otherwise.
 * @throws dml_exception
 */
function organizer_slot_is_free($slot, $userid, $assignmentview = null) {
    $slotx = new organizer_slot($slot);
    if ($assignmentview) {
        $organizerconfig = get_config('organizer');
        if (isset($organizerconfig->allowcreationofpasttimeslots) &&
            $organizerconfig->allowcreationofpasttimeslots != 1) {
            $ispastdue = $slotx->is_past_due();
        } else {
            $ispastdue = false;
        }
    }
    if (!$ispastdue && !$slotx->is_full() && $slotx->is_available() ) {
        $apps = organizer_get_all_user_appointments($slot->organizerid, $userid);
        foreach ($apps as $app) {  // Its own slot?
            if ($app->slotid == $slot->id) {
                return false;
            }
        }
        return true;
    }
    return false;
}

/**
 * Outputs HTML for displaying places in the queue with the user's position.
 *
 * This function generates a stylized HTML output that includes an icon and a
 * formatted string showing the user's position in the queue for the organizer.
 *
 * @param stdClass $a An object containing data related to the position in the queue.
 *
 * @return string The formatted HTML output showing the queue position with styling.
 * @throws coding_exception
 */
function organizer_write_places_inqueue_position($a) {
    $output = organizer_get_fa_icon("fa fa-coffee mr-1 ml-1")."<span class='mr-1 text-danger font-italic'>" .
        get_string('places_inqueue_withposition', 'organizer', $a) . "</span>";
    return $output;
}

/**
 * Retrieves the formatted output for places in the queue based on the user's position.
 *
 * This function combines the queue data, slot information, and additional parameters
 * to generate an HTML string that can be displayed to the user. It includes stylized
 * elements like icons and conditional formatting based on parameters such as
 * viewing mode and slot visibility.
 *
 * - If the view mode is not student view or the slot visibility is set to public,
 *   it retrieves detailed queue information.
 * - Supports both individual and group modes to fetch appropriate entries in the queue.
 *
 * @param stdClass $a An object containing user or queue-related information.
 * @param stdClass $slot The slot object that includes organizer slot details.
 * @param array $params An associative array of additional options:
 *                      - 'mode': The viewing mode for the organizer.
 *
 * @return string The HTML string containing the formatted output for places in the queue.
 * @throws coding_exception
 * @throws moodle_exception
 */
function organizer_write_places_inqueue($a, $slot, $params) {
    $output = "";
    $output .= "<span class='mr-1 font-italic'>" . get_string('places_inqueue', 'organizer', $a);
    if ($params['mode'] != ORGANIZER_TAB_STUDENT_VIEW || $slot->visibility == ORGANIZER_VISIBILITY_ALL) {
        if (organizer_is_group_mode()) {
            $wlinfo = organizer_get_entries_queue_group($slot);
        } else {
            $wlinfo = organizer_get_entries_queue($slot);
        }
            $output .= organizer_get_fa_icon('fa fa-info-circle ml-1', $wlinfo);
    }
    $output .= "</span>";
    return $output;
}

/**
 * Retrieves the queue entries for a specific slot in individual mode.
 *
 * This function fetches and formats the list of queue entries for the given slot
 * where users have registered in the queue. It returns an HTML-formatted string
 * listing the queue entries along with their position in the queue.
 *
 * @param stdClass $slot The slot object containing details of the organizer slot.
 *
 * @return string The formatted HTML string containing the queue entries.
 * @throws dml_exception If there is an issue executing the database query.
 */
function organizer_get_entries_queue($slot) {
    global $DB;

    $output = "";
    $paramssql = ['slotid' => $slot->id];
    $slotquery = 'SELECT u.id, u.firstname, u.lastname, q.id
				FROM {organizer_slots} s
				INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
				INNER JOIN {user} u ON u.id = q.userid
				WHERE s.id = :slotid
				ORDER BY q.id';

    if ($queueentries = $DB->get_records_sql($slotquery, $paramssql)) {
        $i = 0;
        $lf = "";
        foreach ($queueentries as $qe) {
            $i++;
            $output .= $lf . $i . ". " . $qe->firstname . " " . $qe->lastname;
            $lf = "<br>";
        }
    }

    return $output;
}

/**
 * Retrieves the queue entries for a specific slot in group mode.
 *
 * This function fetches and formats the list of queue entries for the given slot
 * where groups have registered in the queue. It returns an HTML-formatted string
 * listing the group names along with their position in the queue.
 *
 * @param stdClass $slot The slot object containing details of the organizer slot.
 *
 * @return string The formatted HTML string containing the queue entries for groups.
 * @throws dml_exception If there is an issue executing the database query.
 */
function organizer_get_entries_queue_group($slot) {
    global $DB;

    $output = "";
    $paramssql = ['slotid' => $slot->id];
    $slotquery = 'SELECT DISTINCT g.id, g.name as gname, q.id
				FROM {organizer_slots} s
				INNER JOIN {organizer_slot_queues} q ON s.id = q.slotid
				INNER JOIN {groups} g ON g.id = q.groupid
				WHERE s.id = :slotid
				ORDER BY q.id';

    if ($queueentries = $DB->get_records_sql($slotquery, $paramssql)) {
        $i = 0;
        $lf = "";
        foreach ($queueentries as $qe) {
            $i++;
            $output .= $lf . $i . ". " . $qe->gname;
            $lf = "<br>";
        }
    }

    return $output;
}

/**
 * Same codelines in the header for slots_print, slots_delete, slots_add, slots_eval.
 *
 * @return array
 * @throws coding_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function organizer_slotpages_header() {
    global $PAGE;

    $mode = optional_param('mode', null, PARAM_INT);
    $action = optional_param('action', null, PARAM_ALPHANUMEXT);

    [$cm, $course, $organizer, $context] = organizer_get_course_module_data();

    require_login($course, false, $cm);

    $url = new moodle_url('/mod/organizer/view_action.php');
    $url->param('id', $cm->id);
    $url->param('mode', $mode);
    $url->param('action', $action);
    $url->param('sesskey', sesskey());

    $PAGE->set_url($url);
    $PAGE->set_pagelayout('standard');
    $PAGE->set_title($organizer->name);
    $PAGE->set_heading($course->fullname);

    $redirecturl = new moodle_url('/mod/organizer/view.php', ['id' => $cm->id, 'mode' => $mode, 'action' => $action]);

    return [$cm, $course, $organizer, $context, $redirecturl];
}

/**
 * Retrieves a table header cell for the participants table with sorting functionality
 * and an optional help icon.
 *
 * This function generates a table header cell, including sorting options for
 * participant columns (sorted by "participant" or "name"). It also
 * appends a help icon to the header if provided.
 *
 * @param array $params An associative array containing parameters for the table.
 *                      It must include:
 *                      - 'sort' (string): The current sorting field.
 *                      - 'dir' (string): The current sorting direction, 'ASC' or 'DESC'.
 *                      - 'psort' (string): The current "name" sorting field.
 *                      - 'pdir' (string): The current "name" sorting direction.
 *                      - 'id' (int): The course module ID.
 *                      - 'mode' (int): The organizer mode.
 * @param string $column The column name to generate the header for.
 * @param string $columnhelpicon HTML string representing the help icon for the column.
 *
 * @return html_table_cell The formatted header cell for the participants table.
 * @throws \core\exception\moodle_exception
 * @throws coding_exception
 */
function organizer_get_participants_tableheadercell($params, $column, $columnhelpicon) {

    if ($params['sort'] == 'participant') {
        $participantdir = $params['dir'] == 'ASC' ? 'DESC' : 'ASC';
        $icon = $params['dir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
        $participantstr = $params['dir'] == 'ASC' ? 'up' : 'down';
        $participanticon = organizer_get_fa_icon("fa $icon ml-1", get_string($participantstr));
        $urlp = new moodle_url(
            '/mod/organizer/view.php',
            ['id' => $params['id'], 'mode' => $params['mode'], 'sort' => 'participant',
                'dir' => $participantdir]
        );
        $links = html_writer::link($urlp, get_string("th_{$column}", 'organizer')) . $participanticon . " ";
    } else if ($params['psort'] == 'name') {
        $namedir = $params['pdir'] == 'ASC' ? 'DESC' : 'ASC';
        $icon = $params['pdir'] == 'ASC' ? 'fa-sort-up' : 'fa-sort-down';
        $namestr = $params['pdir'] == 'ASC' ? 'up' : 'down';
        $nameicon = organizer_get_fa_icon("fa $icon ml-1", get_string($namestr));
        $urln = new moodle_url(
            '/mod/organizer/view.php',
            ['id' => $params['id'], 'mode' => $params['mode'], 'psort' => 'name', 'pdir' => $namedir]
        );
        $links = html_writer::link($urln, get_string("th_{$column}", 'organizer')) . $nameicon;
    }

    $cell = new html_table_cell($links . $columnhelpicon);

    return $cell;
}

/**
 * Returns the html of a status bar indicating the user's status regarding his bookings.
 *
 * @param $organizer
 * @return object $out html output of status bar
 * @throws coding_exception
 * @throws dml_exception
 */
function organizer_appointmentsstatus_bar($organizer) {
    global $DB;

    $cm = get_coursemodule_from_instance('organizer', $organizer->id, $organizer->course, false, MUST_EXIST);
    $context = context_module::instance($cm->id, MUST_EXIST);

    $a = new stdClass();
    $min = $organizer->userslotsmin;
    $tooless = 0;
    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        $params = ['groupingid' => $cm->groupingid];
        $query = 'SELECT {groups}.id FROM {groups}
                INNER JOIN {groupings_groups} ON {groups}.id = {groupings_groups}.groupid
                WHERE {groupings_groups}.groupingid = :groupingid';
        $groups = $DB->get_records_sql($query, $params);
        foreach ($groups as $group) {
            if (!get_enrolled_users($context, 'mod/organizer:register', $group->id, 'u.id', null, 0, 0, true)) {
                continue;
            }
            $apps = organizer_get_all_group_appointments($organizer, $group->id);
            $diff = $min - count($apps);
            $tooless += $diff > 0 ? $diff : 0;
        }
        $a->tooless = $tooless;
    } else {
        $students = get_enrolled_users($context, 'mod/organizer:register', 0, 'u.id', null, 0, 0, true);
        $info = new info_module(cm_info::create($cm));
        $filtered = $info->filter_user_list($students);
        $studentids = array_keys($filtered);
        $havebookings = $DB->get_fieldset_sql('SELECT DISTINCT sa.userid
        FROM {organizer_slot_appointments} sa INNER JOIN {organizer_slots} s ON sa.slotid = s.id
        WHERE s.organizerid = :organizerid', ['organizerid' => $organizer->id]
        );
        $studentids = array_merge($studentids, $havebookings);
        foreach ($studentids as $studentid) {
            $apps = organizer_get_all_user_appointments($organizer, $studentid);
            $diff = $min - count($apps);
            $tooless += $diff > 0 ? $diff : 0;
        }
        $a->tooless = $tooless;
    }

    [$slotscount, $places] = organizer_get_freeplaces($organizer);

    $a->slots = $slotscount;
    $a->places = $places;
    if ($a->places == 1) {
        $msg = get_string('infobox_appointmentsstatus_sg', 'mod_organizer', $a);
    } else {
        $msg = get_string('infobox_appointmentsstatus_pl', 'mod_organizer', $a);
    }
    if ($places >= $tooless) {
        $output = organizer_get_icon_msg('enoughplaces', $msg);
    } else {
        $output = organizer_get_icon_msg('notenoughplaces', $msg);
    }

    return $output;

}

/**
 * Calculates and returns the number of free slots and free places in the organizer.
 *
 * This function determines the number of unexpired, available slots and their remaining capacity
 * in terms of participant places. Expired slots are excluded from the calculation unless explicitly
 * allowed by the `$allowexpiredslotsassignment` parameter.
 *
 * @param stdClass $organizer The organizer object containing relevant organizer details.
 * @param bool $allowexpiredslotsassignment Optional. If true, expired slots are included in the calculation.
 * @return array An indexed array where the first element represents the total number of free slots
 *               and the second element represents the total number of free places.
 * @throws dml_exception If a database query fails.
 */
function organizer_get_freeplaces($organizer, $allowexpiredslotsassignment = false) {
    global $DB;

    $slotscount = 0;
    $places = 0;
    $paramssql = ['organizerid' => $organizer->id];
    $query = "SELECT s.id, s.starttime, s.maxparticipants FROM {organizer_slots} s
        WHERE s.organizerid = :organizerid";
    $slots = $DB->get_records_sql($query, $paramssql);
    foreach ($slots as $slot) {
        if ($slot->starttime <= time() && !$allowexpiredslotsassignment) {
            continue;
        } else {
            $slotscount++;
            $apps = $DB->count_records('organizer_slot_appointments', ['slotid' => $slot->id]);
            $diff = $slot->maxparticipants - $apps;
            $places += $diff > 0 ? $diff : 0;
        }
    }
    return [$slotscount, $places];
}

/**
 * Determines whether a booking is possible for a user or group in the specified organizer.
 *
 * This function checks if there are free places available in the organizer and whether the maximum
 * number of bookings allowed for the user or group has been reached. The function considers group
 * mode and whether expired slots are included in the booking.
 *
 * @param bool $groupmode Indicates if the booking is in group mode. True if group mode is enabled, false otherwise.
 * @param stdClass $organizer The organizer object containing necessary organizer details.
 * @param int $entryid The ID of the user or group for which the booking is being checked.
 * @param bool $allowexpiredslotsassignment Optional. If true, expired slots are considered as available for booking.
 * @return bool True if booking is not possible, false if booking is possible.
 * @throws dml_exception
 */
function organizer_bookingnotpossible($groupmode, $organizer, $entryid, $allowexpiredslotsassignment = false) {

    if ($groupmode) {
        $booked = organizer_count_bookedslots($organizer->id, null, $entryid);
    } else {
        $booked = organizer_count_bookedslots($organizer->id, $entryid, null);
    }
    $maxnotreached = organizer_multiplebookings_status($booked, $organizer) != USERSLOTS_MAX_REACHED;
    [, $places] = organizer_get_freeplaces($organizer, $allowexpiredslotsassignment);

    return !$maxnotreached || !$places;
}


