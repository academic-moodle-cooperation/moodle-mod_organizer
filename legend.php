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
function make_legend($params) {
    $output = html_writer::start_tag('table',
            array('class' => 'generaltable boxaligncenter legend', 'style' => 'width: 100%; table-layout: fixed;'));
    switch ($params['mode']) {
        case TAB_APPOINTMENTS_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '3'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/yes_24x24.png', '', get_string('legend_evaluated', 'organizer'), '') . ' '
                            . get_string('legend_evaluated', 'organizer'), 'width: 33%');
            $output .= make_cell(
                    get_img('pix/slot_pending_24x24.png', '', get_string('legend_pending', 'organizer'), '') . ' '
                            . get_string('legend_pending', 'organizer'), 'width: 33%');
            $output .= make_cell(
                    get_img('pix/no_participants_24x24.png', '', get_string('legend_no_participants', 'organizer'), '')
                            . ' ' . get_string('legend_no_participants', 'organizer'), 'width: 33%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/student_slot_available_24x24.png', '', get_string('legend_due', 'organizer'), '')
                            . ' ' . get_string('legend_due', 'organizer'), 'width: 33%');
            $output .= make_cell(
                    get_img('pix/student_slot_past_deadline_24x24.png', '',
                            get_string('legend_past_deadline', 'organizer'), '') . ' '
                            . get_string('legend_past_deadline', 'organizer'), 'width: 33%');
            $output .= make_cell(
                    get_img('pix/organizer_expired_24x24.png', '', get_string('legend_organizer_expired', 'organizer'),
                            '') . ' ' . get_string('legend_organizer_expired', 'organizer'), 'width: 33%');
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
            $output .= make_cell(
                    get_img('pix/yes_small.png', '', get_string('reg_status_slot_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/yes_reg_small.png', '', get_string('reg_status_slot_attended_reapp', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_small.png', '', get_string('reg_status_slot_not_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_not_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_reg_small.png', '', get_string('reg_status_slot_not_attended_reapp', 'organizer'),
                            '') . ' ' . get_string('reg_status_slot_not_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/slot_pending_small.png', '', get_string('reg_status_slot_pending', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_pending', 'organizer'), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/anon.png', '', get_string('legend_anonymous', 'organizer'), '') . ' '
                            . get_string('legend_anonymous', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/applicant.gif', '', get_string('legend_group_applicant', 'organizer'), '') . ' '
                            . get_string('legend_group_applicant', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/feedback2.png', '', get_string('legend_comments', 'organizer'), '') . ' '
                            . get_string('legend_comments', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/feedback.png', '', get_string('legend_feedback', 'organizer'), '') . ' '
                            . get_string('legend_feedback', 'organizer'), 'width: 20%');
            $output .= make_cell(' ', 'width: 20%');
            $output .= html_writer::end_tag('tr');
            break;
        case TAB_STUDENT_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '5'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/yes_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/yes_reg_24x24.png', '', get_string('reg_status_slot_attended_reapp', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_24x24.png', '', get_string('reg_status_slot_not_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_not_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_reg_24x24.png', '', get_string('reg_status_slot_not_attended_reapp', 'organizer'),
                            '') . ' ' . get_string('reg_status_slot_not_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/slot_pending_24x24.png', '', get_string('reg_status_slot_pending', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_pending', 'organizer'), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/student_slot_available_24x24.png', '',
                            get_string('reg_status_slot_available', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_available', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/student_slot_full_24x24.png', '', get_string('reg_status_slot_full', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_full', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/student_slot_past_deadline_24x24.png', '',
                            get_string('reg_status_slot_past_deadline', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_past_deadline', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/student_slot_expired_24x24.png', '',
                            get_string('reg_status_slot_expired', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_expired', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/organizer_expired_24x24.png', '',
                            get_string('reg_status_organizer_expired', 'organizer'), '') . ' '
                            . get_string('reg_status_organizer_expired', 'organizer'), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            break;
        case TAB_REGISTRATION_STATUS_VIEW:
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::start_tag('th', array('colspan' => '4'));
            $output .= get_string('legend_section_status', 'organizer');
            $output .= html_writer::end_tag('th');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/status_attended_24x24.png', '', get_string('reg_status_slot_attended', 'organizer'),
                            '') . ' ' . get_string('reg_status_slot_attended', 'organizer'), 'width: 25%');
            $output .= make_cell(
                    get_img('pix/status_attended_reapp_24x24.png', '',
                            get_string('reg_status_slot_attended_reapp', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_attended_reapp', 'organizer'), 'width: 25%');
            $output .= make_cell(
                    get_img('pix/status_pending_24x24.png', '', get_string('reg_status_slot_pending', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_pending', 'organizer'), 'width: 25%');
            $output .= make_cell(
                    get_img('pix/status_not_occured_24x24.png', '', get_string('legend_not_occured', 'organizer'), '')
                            . ' ' . get_string('legend_not_occured', 'organizer'), 'width: 25%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/status_not_registered_24x24.png', '',
                            get_string('reg_status_not_registered', 'organizer'), '') . ' '
                            . get_string('reg_status_not_registered', 'organizer'), 'width: 25%');
            $output .= make_cell(
                    get_img('pix/status_not_attended_24x24.png', '',
                            get_string('reg_status_slot_not_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_not_attended', 'organizer'), 'width: 25%');
            $output .= make_cell(
                    get_img('pix/status_not_attended_reapp_24x24.png', '',
                            get_string('reg_status_slot_not_attended_reapp', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_not_attended_reapp', 'organizer'), 'width: 25%');
            $output .= make_cell(' ', 'width: 25%');
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
            $output .= make_cell(
                    get_img('pix/yes_small.png', '', get_string('reg_status_slot_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/yes_reg_small.png', '', get_string('reg_status_slot_attended_reapp', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_small.png', '', get_string('reg_status_slot_not_attended', 'organizer'), '') . ' '
                            . get_string('reg_status_slot_not_attended', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/no_reg_small.png', '', get_string('reg_status_slot_not_attended_reapp', 'organizer'),
                            '') . ' ' . get_string('reg_status_slot_not_attended_reapp', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/slot_pending_small.png', '', get_string('reg_status_slot_pending', 'organizer'), '')
                            . ' ' . get_string('reg_status_slot_pending', 'organizer'), 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::start_tag('tr');
            $output .= make_cell(
                    get_img('pix/applicant.gif', '', get_string('legend_group_applicant', 'organizer'), '') . ' '
                            . get_string('legend_group_applicant', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/feedback2.png', '', get_string('legend_comments', 'organizer'), '') . ' '
                            . get_string('legend_comments', 'organizer'), 'width: 20%');
            $output .= make_cell(
                    get_img('pix/feedback.png', '', get_string('legend_feedback', 'organizer'), '') . ' '
                            . get_string('legend_feedback', 'organizer'), 'width: 20%');
            $output .= make_cell(' ', 'width: 20%');
            $output .= make_cell(' ', 'width: 20%');
            $output .= html_writer::end_tag('tr');
            $output .= html_writer::end_tag('tr');
            break;
        default:
            print_error('Unknown view mode: ' . $params['mode']);
    }
    $output .= html_writer::end_tag('table');
    return $output;
}
function make_cell($content, $style) {
    $output = html_writer::start_tag('td', array('style' => $style));
    $output .= $content;
    $output .= html_writer::end_tag('td');
    return $output;
}
