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
 * legend.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
function organizer_make_legend($params) {
    $output = html_writer::start_tag('table',
            array('class' => 'generaltable boxaligncenter legend', 'style' => 'width: 100%; table-layout: fixed;'));
    switch ($params['mode']) {
        case ORGANIZER_TAB_APPOINTMENTS_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '3'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes', get_string('legend_evaluated', 'organizer')), 'width: 33%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('pending', get_string('legend_pending', 'organizer')), 'width: 33%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no_participants', get_string('legend_no_participants', 'organizer')), 'width: 33%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_available', get_string('legend_due', 'organizer')), 'width: 33%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_past_deadline', get_string('legend_past_deadline', 'organizer')), 'width: 33%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('organizer_expired', get_string('legend_organizer_expired', 'organizer')), 'width: 33%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::end_tag('table');
            $output .= html_writer::start_tag('table',
                    array('class' => 'generaltable boxaligncenter legend',
                            'style' => 'width: 100%; table-layout: fixed;'));
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '5'));
            $output .= get_string('legend_section_details', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes', get_string('reg_status_slot_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes_reg', get_string('reg_status_slot_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no', get_string('reg_status_slot_not_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no_reg', get_string('reg_status_slot_not_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('pending', get_string('reg_status_slot_pending', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('anon', get_string('legend_anonymous', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('slotanon', get_string('legend_halfanonymous', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('applicant', get_string('legend_group_applicant', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback2', get_string('legend_comments', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback', get_string('legend_feedback', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            break;
        case ORGANIZER_TAB_STUDENT_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '5'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes', get_string('reg_status_slot_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes_reg', get_string('reg_status_slot_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no', get_string('reg_status_slot_not_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no_reg', get_string('reg_status_slot_not_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('pending', get_string('reg_status_slot_pending', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_available', get_string('reg_status_slot_available', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_full', get_string('reg_status_slot_full', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_past_deadline', get_string('reg_status_slot_past_deadline', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('student_slot_expired', get_string('reg_status_slot_expired', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('organizer_expired', get_string('reg_status_organizer_expired', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
           $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('anon', get_string('legend_anonymous', 'organizer'), get_string('legend_anonymous', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('slotanon', get_string('legend_halfanonymous', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('applicant', get_string('legend_group_applicant', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback2', get_string('legend_comments', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback', get_string('legend_feedback', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            break;
        case ORGANIZER_TAB_REGISTRATION_STATUS_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '4'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_attended', get_string('reg_status_slot_attended', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_attended_reapp', get_string('reg_status_slot_attended_reapp', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_pending', get_string('reg_status_slot_pending', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_not_occured', get_string('legend_not_occured', 'organizer')), 'width: 25%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_not_registered', get_string('reg_status_not_registered', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_not_attended', get_string('reg_status_slot_not_attended', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('status_not_attended_reapp', get_string('reg_status_slot_not_attended_reapp', 'organizer')), 'width: 25%');
            $output .= organizer_make_cell(' ', 'width: 25%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::end_tag('table');
            $output .= html_writer::start_tag('table',
                    array('class' => 'generaltable boxaligncenter legend',
                            'style' => 'width: 100%; table-layout: fixed;'));
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '5'));
            $output .= get_string('legend_section_details', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes', get_string('reg_status_slot_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('yes_reg', get_string('reg_status_slot_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no', get_string('reg_status_slot_not_attended', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('no_reg', get_string('reg_status_slot_not_attended_reapp', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('pending', get_string('reg_status_slot_pending', 'organizer')), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('applicant', get_string('legend_group_applicant', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback2', get_string('legend_comments', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(
                    organizer_get_icon_plus_string('feedback', get_string('legend_feedback', 'organizer')), 'width: 20%');
            $output .= organizer_make_cell(' ', 'width: 20%');
            $output .= organizer_make_cell(' ', 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::end_tag('tr');
            break;
        case ORGANIZER_ASSIGNMENT_VIEW:
            break;
        default:
            print_error('Unknown view mode: ' . $params['mode']);
    }
    $output .= html_writer::end_tag('table');
    return $output;
}
function organizer_make_cell($content, $style) {
    $output = html_writer::start_tag('td', array('style' => $style));
    $output .= $content;
    $output .= html_writer::end_tag('td');
    return $output;
}

function organizer_get_icon_plus_string($iconname, $string) {
    global $OUTPUT;
    return $OUTPUT->pix_icon($iconname, $string, 'mod_organizer') . ' ' . $string;
}

