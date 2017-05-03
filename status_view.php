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
 * status_view.php
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

function organizer_generate_registration_table_content($columns, $params) {
    global $DB;

    list($cm, $course, $organizer, $context) = get_course_module_data();

    $groupmode = is_group_mode();

    if ($groupmode) {
        $entries = organizer_get_status_table_entries_group($params);
    } else {
        $entries = organizer_get_status_table_entries($params);
    }

    $rows = array();
    foreach ($entries as $entry) {
        $row = new html_table_row();

        foreach ($columns as $column) {
            switch ($column) {
                case 'participants':
                    if ($groupmode) {
                        $members = $DB->get_fieldset_select('groups_members', 'userid', "groupid = {$entry->id}");
                        $list = "<em>$entry->name</em><br/ >";
                        $list .= organizer_get_teacherapplicant_output($entry->teacherapplicantid, $entry->teacherapplicanttimemodified);
                        foreach ($members as $member) {
                            $idnumber = get_user_idnumber($member);

                            $list .= get_name_link($member) . " ($idnumber) "
                                    . (isset($entry->comments) ? get_img('i/feedback', '', $entry->comments) : '');
                            if ($member == $entry->applicantid) {
                                $list .= ' '
                                        . organizer_get_icon('applicant', 'applicant') . '<br/>';
                            } else {
                                $list .= ' ' . organizer_get_icon('transparent', '') . '<br/>';
                            }
                        }
                        $row->cells[] = new html_table_cell($list);
                    } else {
                        $row->cells[] = new html_table_cell(get_name_link($entry->id) . " ({$entry->idnumber})" . organizer_get_teacherapplicant_output($entry->teacherapplicantid, $entry->teacherapplicanttimemodified));
                    }
                    break;
                case 'registered':
                    $row->cells[] = new html_table_cell(get_status_icon_new($entry->status));
                    break;
                case 'datetime':
                    $row->cells[] = new html_table_cell(date_time($entry));
                    break;
                case 'appdetails':
                    if ($groupmode) {
                        $row->cells[] = new html_table_cell('PLACEHOLDER');
                    } else {
                        $row->cells[] = new html_table_cell(app_details($params, $entry));
                    }
                    break;
                case 'location':
                    $row->cells[] = new html_table_cell(location_link($entry));
                    break;
                case 'teacher':
                    $row->cells[] = new html_table_cell(teacher_data($params, $entry));
                    break;
                case 'actions':
                    $row->cells[] = new html_table_cell(teacher_action_new($params, $entry));
                    break;
            }
        }
        $rows[] = $row;
    }

    return $rows;
}

function organizer_get_status_table_entries_group($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = get_course_module_data();

    $query = "SELECT g.id FROM {groups} g
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :groupingid";
    $par = array('groupingid' => $cm->groupingid);
    $groupids = $DB->get_fieldset_sql($query, $par);

    list($insql, $inparams) = $DB->get_in_or_equal($groupids, SQL_PARAMS_NAMED);

    if ($params['sort'] == 'name') {
        if ($params['dir'] == 'DESC') {
            $orderby = "ORDER BY g.name DESC, status ASC";
        } else {
            $orderby = "ORDER BY g.name ASC, status ASC";
        }
    } else if ($params['sort'] == 'status') {
        if ($params['dir'] == 'DESC') {
            $orderby = "ORDER BY status DESC, g.name ASC";
        } else {
            $orderby = "ORDER BY status ASC, g.name ASC";
        }
    } else {
        $orderby = "ORDER BY g.name ASC, status ASC";
    }

    $par = array();
    $par['now1'] = time();
    $par['now2'] = time();
    $par['organizerid'] = $organizer->id;

    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        g.id, g.name,
        CASE
            WHEN a2.id IS NOT NULL AND a2.attended = 1 THEN 0
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN 1
            WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN 2
            WHEN a2.id IS NOT NULL AND a2.attended = 0 THEN 3
            WHEN a2.id IS NULL THEN 4
        ELSE -1
        END AS status, a2.starttime, a2.duration, a2.location, a2.teacherid, a2.applicantid,
        a2.comments, a2.teachervisible, a2.slotid, a2.teacherapplicantid, a2.teacherapplicanttimemodified
        FROM {groups} g
        LEFT JOIN
        (SELECT
        a.id, a.groupid, s.id as slotid, s.starttime, s.location, s.teacherid, s.teachervisible,
        s.duration, a.applicantid, a.comments, a.teacherapplicantid, a.teacherapplicanttimemodified,
        (SELECT MAX(attended) FROM mdl_organizer_slot_appointments a3 WHERE a3.groupid = a.groupid) AS attended
        FROM {organizer_slot_appointments} a
        INNER JOIN {organizer_slots} s ON a.slotid = s.id
        WHERE s.organizerid = :organizerid ORDER BY s.starttime DESC) a2 ON g.id = a2.groupid
        WHERE g.id $insql
        GROUP BY g.id, g.name, status, a2.starttime, a2.duration, a2.location, a2.teacherid,
            a2.applicantid, a2.comments, a2.teachervisible, a2.slotid,
            a2.teacherapplicantid, a2.teacherapplicanttimemodified
        $orderby";
    return $DB->get_records_sql($query, $par);
}

function organizer_get_status_table_entries($params) {
    global $DB;
    list($cm, $course, $organizer, $context) = get_course_module_data();

    if ($cm->groupingid == 0) {
        $students = get_enrolled_users($context, 'mod/organizer:register');
        $studentids = array();
        foreach ($students as $student) {
            $studentids[] = $student->id;
        }
    } else {
        $query = "SELECT u.id FROM {user} u
            INNER JOIN {groups_members} gm ON u.id = gm.userid
            INNER JOIN {groups} g ON gm.groupid = g.id
            INNER JOIN {groupings_groups} gg ON g.id = gg.groupid
            WHERE gg.groupingid = :grouping";
        $par = array('grouping' => $cm->groupingid);
        $studentids = $DB->get_fieldset_sql($query, $par);
    }

    list($insql, $inparams) = $DB->get_in_or_equal($studentids, SQL_PARAMS_NAMED);

    if ($params['sort'] == 'name') {
        if ($params['dir'] == 'DESC') {
            $orderby = "ORDER BY u.lastname DESC, u.firstname DESC, u.idnumber ASC, status ASC";
        } else {
            $orderby = "ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC, status ASC";
        }
    } else if ($params['sort'] == 'status') {
        if ($params['dir'] == 'DESC') {
            $orderby = "ORDER BY status DESC, u.lastname ASC, u.firstname ASC, u.idnumber ASC";
        } else {
            $orderby = "ORDER BY status ASC, u.lastname ASC, u.firstname ASC, u.idnumber ASC";
        }
    } else if ($params['sort'] == 'id') {
        if ($params['dir'] == 'DESC') {
            $orderby = "ORDER BY u.idnumber DESC, u.lastname ASC, u.firstname ASC, status ASC";
        } else {
            $orderby = "ORDER BY u.idnumber ASC, u.lastname ASC, u.firstname ASC, status ASC";
        }
    } else {
        $orderby = "ORDER BY u.lastname ASC, u.firstname ASC, u.idnumber ASC, status ASC";
    }

    $par = array();
    $par['now1'] = time();
    $par['now2'] = time();
    $par['organizerid'] = $organizer->id;

    $par = array_merge($par, $inparams);

    $query = "SELECT DISTINCT
        u.id, u.firstname, u.lastname, u.idnumber,
        CASE
        WHEN a2.id IS NOT NULL AND a2.attended = 1 THEN 0
        WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime <= :now1 THEN 1
        WHEN a2.id IS NOT NULL AND a2.attended IS NULL AND a2.starttime > :now2 THEN 2
        WHEN a2.id IS NOT NULL AND a2.attended = 0 THEN 3
        WHEN a2.id IS NULL THEN 4
        ELSE -1
        END AS status,
        a2.starttime, a2.duration, a2.attended, a2.location, a2.grade, a2.comments,
        a2.feedback, a2.teacherid, a2.userid, a2.teachervisible, a2.slotid, 
        a2.teacherapplicantid, a2.teacherapplicanttimemodified
        FROM {user} u
        LEFT JOIN
        (SELECT a.id, a.attended, a.grade, a.feedback, a.userid, s.starttime, s.location,
        s.teacherid, s.comments, s.duration, s.teachervisible, s.id as slotid, a.teacherapplicantid, a.teacherapplicanttimemodified
        FROM {organizer_slot_appointments} a INNER JOIN {organizer_slots} s ON a.slotid = s.id
        WHERE s.organizerid = :organizerid ORDER BY s.starttime DESC) a2 ON u.id = a2.userid
        WHERE u.id $insql
        GROUP BY u.id, u.firstname, u.lastname, u.idnumber, status, a2.starttime, a2.duration, a2.attended,
            a2.location, a2.grade, a2.comments, a2.feedback, a2.teacherid, a2.userid, a2.teachervisible, a2.slotid,
            a2.teacherapplicantid, a2.teacherapplicanttimemodified
        $orderby";
    return $DB->get_records_sql($query, $par);
}

