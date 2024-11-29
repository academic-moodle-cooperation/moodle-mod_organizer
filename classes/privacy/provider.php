<?php
// This file is part of mod_organizer for Moodle - http://moodle.org/
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
 * Privacy class for requesting user data.
 *
 * @package    mod_organizer
 * @author     Thomas Niedermaier
 * @copyright  2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_organizer\privacy;

use coding_exception;
use context;
use context_module;
use context_system;
use core_privacy\local\metadata\collection;
use core_privacy\local\metadata\provider as metadataprovider;
use core_privacy\local\request\contextlist;
use core_privacy\local\request\plugin\provider as pluginprovider;
use core_privacy\local\request\user_preference_provider as preference_provider;
use core_privacy\local\request\writer;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\helper;
use core_privacy\local\request\core_userlist_provider;
use core_privacy\local\request\userlist;
use core_privacy\local\request\approved_userlist;
use dml_exception;
use stdClass;

/**
 * Privacy class for requesting user data.
 *
 * @package    mod_organizer
 * @author     Thomas Niedermaier
 * @copyright  2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider, pluginprovider, core_userlist_provider {
    /**
     * Provides meta data that is stored about a user with mod_organizer
     *
     * @param  collection $collection A collection of meta data items to be added to.
     * @return  collection Returns the collection of metadata.
     */
    public static function get_metadata(collection $collection): collection {
        $organizerslotappointments = [
            'userid' => 'privacy:metadata:useridappointment',
            'groupid' => 'privacy:metadata:groupidappointment',
            'applicantid' => 'privacy:metadata:applicantidappointment',
            'attended' => 'privacy:metadata:attended',
            'grade' => 'privacy:metadata:grade',
            'feedback' => 'privacy:metadata:feedback',
            'comments' => 'privacy:metadata:comments',
            'teacherapplicantid' => 'privacy:metadata:teacherapplicantid',
            'teacherapplicanttimemodified' => 'privacy:metadata:teacherapplicanttimemodified',
        ];
        $organizerslotqueues = [
            'userid' => 'privacy:metadata:useridqueue',
            'groupid' => 'privacy:metadata:groupidqueue',
            'applicantid' => 'privacy:metadata:applicantidqueue',
        ];
        $organizerslottrainer = [
            'trainerid' => 'privacy:metadata:trainerid',
        ];

        $collection->add_database_table('organizer_slot_appointments', $organizerslotappointments,
            'privacy:metadata:organizerslotappointments');
        $collection->add_database_table('organizer_slot_queues', $organizerslotqueues,
            'privacy:metadata:organizerslotqueues');
        $collection->add_database_table('organizer_slot_trainer', $organizerslottrainer,
            'privacy:metadata:organizerslottrainer');

        return $collection;
    }

    /**
     * Returns all of the contexts that has information relating to the userid.
     *
     * @param  int $userid The user ID.
     * @return contextlist an object with the contexts related to a userid.
     */
    public static function get_contexts_for_userid(int $userid): contextlist {

        $params = [
            'modulename' => 'organizer',
            'contextlevel' => CONTEXT_MODULE,
            'useridappointment' => $userid,
            'applicantidappointment' => $userid,
            'useridqueue' => $userid,
            'applicantidqueue' => $userid,
            'tuserid' => $userid,
            'teacherapplicantid' => $userid,
        ];

        $sql = "SELECT ctx.id
                 FROM {course_modules} cm
                 JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
                 JOIN {organizer} o ON cm.instance = o.id
                 JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
                 JOIN {organizer_slots} s ON o.id = s.organizerid
                 JOIN {organizer_slot_appointments} a ON s.id = a.slotid
            LEFT JOIN {organizer_slot_queues} q ON s.id = q.slotid
            LEFT JOIN {organizer_slot_trainer} t ON s.id = t.slotid
                WHERE (
                      a.userid = :useridappointment
                      OR a.applicantid = :applicantidappointment
                      OR q.userid = :useridqueue
                      OR q.applicantid = :applicantidqueue
                      OR t.trainerid = :tuserid
                      OR a.teacherapplicantid = :teacherapplicantid
                      )";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Get the list of users who have data within a context.
     *
     * @param   userlist $userlist The userlist containing the list of users who have data in this context/plugin combination.
     */
    public static function get_users_in_context(userlist $userlist) {
        $context = $userlist->get_context();

        if ($context->contextlevel != CONTEXT_MODULE) {
            return;
        }

        $params = [
            'modulename' => 'organizer',
            'contextid' => $context->id,
            'contextlevel' => CONTEXT_MODULE,
        ];

        $sql = "SELECT oa.userid, oa.applicantid, oa.teacherapplicantid
                  FROM {context} ctx
                  JOIN {course_modules} cm ON cm.id = ctx.instanceid
                  JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                  JOIN {organizer} o ON o.id = cm.instance
                  JOIN {organizer_slots} os ON o.id = os.organizerid
                  JOIN {organizer_appointments} oa ON oa.slotid = os.id
                 WHERE ctx.id = :contextid AND ctx.contextlevel = :contextlevel";
        // Get all trainers and participants who have an appointment!
        $userlist->add_from_sql('userid', $sql, $params);
        // Get all participants who have applied for an appointment!
        $userlist->add_from_sql('applicantid', $sql, $params);
        // Get all trainers who have assigned participant(s) for an appointment!
        $userlist->add_from_sql('teacherapplicantid', $sql, $params);
    }

    /**
     * Write out the user data filtered by contexts.
     *
     *
     * @param approved_contextlist $contextlist contexts that we are writing data out from.
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $contexts = $contextlist->get_contexts();

        if (empty($contexts)) {
            return;
        }

        [$contextsql, $contextparams] = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT c.id AS contextid, o.id AS organizerid, cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {organizer} o ON o.id = cm.instance
                 WHERE c.id {$contextsql}";

        $organizers = $DB->get_records_sql($sql, $contextparams);

        $user = $contextlist->get_user();

        foreach ($organizers as $organizer) {
            $context = context_module::instance($organizer->cmid);

            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $organizerdata = helper::get_context_data($context, $user);

            writer::with_context($context)->export_data([], $organizerdata);

            static::export_appointments($context, $organizer, $user);

        }
        static::export_user_preferences($user->id);
    }

    /**
     * Stores the user preferences related to mod_organizer.
     *
     * @param  int $userid The user ID that we want the preferences for.
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function export_user_preferences(int $userid) {
        $context = context_system::instance();
    }

    /**
     * Fetches all of the user's appointments and adds them to the export
     *
     * @param  context $context
     * @param  $organizer
     * @param  stdClass $user
     * @param  array $path Current directory path that we are exporting to.
     * @throws dml_exception
     */
    protected static function export_appointments(context $context, $organizer, stdClass $user) {
        global $DB;

        // Fetch all appointments of participants or trainers.
        $params = [
            'guserid' => $user->id,
            'organizer' => $organizer->organizerid,
            'useridappointment' => $user->id,
            'guserid2' => $user->id,
            'organizer2' => $organizer->organizerid,
            'useridqueue' => $user->id,
            'organizer3' => $organizer->organizerid,
            'teacherapplicantid' => $user->id,
            'organizer4' => $organizer->organizerid,
            'trainerid' => $user->id,
        ];

        $sql = "
                  SELECT 1 as participant, 0 as inqueue, 0 as teacherapplicant, 0 as teacher, a.id, a.attended, a.grade,
                  a.comments, a.teacherapplicanttimemodified, a.teacherapplicantid, s.starttime,
                  s.duration, s.location, gm.groupid, g.name as groupname, a.applicantid, a.allownewappointments, a.userid
                  FROM {organizer_slot_appointments} a
                  JOIN {organizer_slots} s ON s.id = a.slotid
                  JOIN {organizer} o ON s.organizerid = o.id
                  LEFT JOIN {groups_members} gm ON gm.userid = :guserid AND gm.groupid = a.groupid
                  LEFT JOIN {groups} g ON gm.groupid = g.id
                  WHERE
                      o.id = :organizer
                      AND (
                      a.userid = :useridappointment
                      )

                  UNION

                  SELECT 0 as participant, 1 as inqueue, 0 as teacherapplicant, 0 as teacher, q.id, 0 as attended, 0 as grade,
                  '' as comments, null as teacherapplicanttimemodified, 0 as teacherapplicantid, s.starttime,
                  s.duration, s.location, gm.groupid, g.name as groupname, q.applicantid, 0 as allownewappointments, q.userid
                  FROM {organizer_slot_queues} q
                  JOIN {organizer_slots} s ON s.id = q.slotid
                  JOIN {organizer} o ON s.organizerid = o.id
                  LEFT JOIN {groups_members} gm ON gm.userid = :guserid2 AND gm.groupid = q.groupid
                  LEFT JOIN {groups} g ON gm.groupid = g.id
                  WHERE
                      o.id = :organizer2
                      AND (
                      q.userid = :useridqueue
                      )

                  UNION

                  SELECT 0 as participant, 0 as inqueue, 1 as teacherapplicant, 0 as teacher, a.id, a.attended, a.grade,
                  a.comments, a.teacherapplicanttimemodified, a.teacherapplicantid, s.starttime,
                  s.duration, s.location, 0 as groupid, '' as groupname, a.applicantid, a.allownewappointments, a.userid
                  FROM {organizer_slot_appointments} a
                  JOIN {organizer_slots} s ON s.id = a.slotid
                  JOIN {organizer} o ON s.organizerid = o.id
                  WHERE
                      o.id = :organizer3
                      AND (
                      a.teacherapplicantid = :teacherapplicantid
                      )

                  UNION

                  SELECT 0 as participant, 0 as inqueue, 0 as teacherapplicant, 1 as teacher, a.id, a.attended, a.grade,
                  a.comments, a.teacherapplicanttimemodified, a.teacherapplicantid, s.starttime,
                  s.duration, s.location, 0 as groupid, '' as groupname, a.applicantid, a.allownewappointments, a.userid
                  FROM {organizer_slot_appointments} a
                  JOIN {organizer_slots} s ON s.id = a.slotid
                  JOIN {organizer} o ON s.organizerid = o.id
                  JOIN {organizer_slot_trainer} t ON s.id = t.slotid
                  WHERE
                      o.id = :organizer4
                      AND (
                      t.trainerid = :trainerid
                      )
              ";

        $rs = $DB->get_recordset_sql($sql, $params);

        foreach ($rs as $id => $cur) {
            if ($cur->participant == "1") {
                static::export_appointment_participant($context, $cur);
            } else if ($cur->inqueue == "1") {
                static::export_appointment_inqueue($context, $cur);
            } else if ($cur->teacherapplicant == "1") {
                static::export_appointment_teacherapplicant($context, $cur);
            } else if ($cur->teacher == "1") {
                static::export_appointment_teacher($context, $cur);
            }
        }

        $rs->close();
    }

    /**
     * Formats and then exports the appointment data for participants.
     *
     * @param  context $context
     * @param  stdClass $appointment
     */
    protected static function export_appointment_participant(context $context, stdClass $appointment) {
        $appointment->groupid = is_null($appointment->groupid) ? 0 : $appointment->groupid;
        $appointment->teacherapplicantid = is_null($appointment->teacherapplicantid) ? 0 : $appointment->teacherapplicantid;
        $appointmentdata = (object)[
            'Appointment slot from' => transform::datetime($appointment->starttime),
            'Appointment slot to' => transform::datetime($appointment->starttime + $appointment->duration),
            'Appointment slot location' => $appointment->location,
            'Assigned by a trainer' => transform::yesno($appointment->teacherapplicantid),
            'Groupmember' => transform::yesno($appointment->groupid),
            'Groupname' => $appointment->groupid ? $appointment->groupname : "",
            'You booked the group' => $appointment->groupid ? transform::yesno(
                $appointment->applicantid == $appointment->userid) : "No",
            'attended' => transform::yesno($appointment->attended),
            'grade' => $appointment->grade,
            'comments' => $appointment->comments,
            'allownewappointments' => transform::yesno($appointment->allownewappointments),
        ];

        writer::with_context($context)->export_data(['participant slot ' . $appointment->id], $appointmentdata);
    }

    /**
     * Formats and then exports the appointment data for participants in waiting queue.
     *
     * @param  context $context
     * @param  stdClass $inqueue
     */
    protected static function export_appointment_inqueue(context $context, stdClass $inqueue) {
        $inqueue->groupid = is_null($inqueue->groupid) ? 0 : $inqueue->groupid;
        $inqueue->teacherapplicantid = is_null($inqueue->teacherapplicantid) ? 0 : $inqueue->teacherapplicantid;
        $inqueuedata = (object)[
            'Waiting queue slot from' => transform::datetime($inqueue->starttime),
            'Waiting queue slot to' => transform::datetime($inqueue->starttime + $inqueue->duration),
            'Appointment slot location' => $inqueue->location,
            'Groupmember' => transform::yesno($inqueue->groupid),
            'Groupname' => $inqueue->groupid ? $inqueue->groupname : "",
        ];

        writer::with_context($context)->export_data(['participant queue ' . $inqueue->id], $inqueuedata);
    }

    /**
     * Formats and then exports the appointment data for teachers which assigned a participant to a slot.
     *
     * @param  context $context
     * @param  stdClass $appointment
     */
    protected static function export_appointment_teacherapplicant(context $context, stdClass $appointment) {
        $appointmentdata = (object)[
            'Appointment slot from' => transform::datetime($appointment->starttime),
            'Appointment slot to' => transform::datetime($appointment->starttime + $appointment->duration),
            'Appointment slot location' => $appointment->location,
            'Participant' => $appointment->userid,
            'Participant assigned by you' => "Yes",
            'Assignment date' => transform::datetime($appointment->teacherapplicanttimemodified),
        ];

        writer::with_context($context)->export_data(['teacherapplicant slot ' . $appointment->id], $appointmentdata);
    }

    /**
     * Formats and then exports the appointment data for teachers assigned to a slot.
     *
     * @param  context $context
     * @param  stdClass $appointment
     */
    protected static function export_appointment_teacher(context $context, stdClass $appointment) {
        $appointmentdata = (object)[
            'Appointment slot from' => transform::datetime($appointment->starttime),
            'Appointment slot to' => transform::datetime($appointment->starttime + $appointment->duration),
            'Appointment slot location' => $appointment->location,
            'You are trainer of this slot' => "Yes",
        ];

        writer::with_context($context)->export_data(['teacher slot ' . $appointment->id], $appointmentdata);
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param context $context The module context.
     * @throws dml_exception
     */
    public static function delete_data_for_all_users_in_context(context $context) {
        global $DB;

        if ($context->contextlevel == CONTEXT_MODULE) {
            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an assign context that matches an existing assignment.
            $sql = "SELECT o.id
                    FROM {organizer} o
                    JOIN {course_modules} cm ON o.id = cm.instance AND o.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'organizer', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $id = $DB->get_field_sql($sql, $params);
            // If we have a count over zero then we can proceed.
            if ($id > 0) {
                // Get all the slots of this organizer instance.
                $slotids = $DB->get_fieldset_select('organizer_slots', 'id', 'organizerid = :organizerid', ['organizerid' => $id]);

                // Delete all appointments of these slots.
                $DB->delete_records_list('organizer_slot_appointments', 'slotid', $slotids);
            }
        }
    }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws dml_exception
     * @throws coding_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {
        global $DB;

        $user = $contextlist->get_user();

        $contextids = $contextlist->get_contextids();

        if (empty($contextids) || $contextids === []) {
            return;
        }

        [$ctxsql, $ctxparams] = $DB->get_in_or_equal($contextids, SQL_PARAMS_NAMED, 'ctx');

        // Apparently we can't trust anything that comes via the context.
        // Go go mega query to find out it we have an grouptool context that matches an existing grouptool.
        $sql = "SELECT ctx.id AS ctxid, o.*
                    FROM {organizer} o
                    JOIN {course_modules} cm ON o.id = cm.instance AND o.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id " . $ctxsql;
        $params = ['modulename' => 'organizer', 'contextmodule' => CONTEXT_MODULE];
        if (!$records = $DB->get_records_sql($sql, $params + $ctxparams)) {
            return;
        }

        // Get all organizer instances of context.
        $organizerids = [];
        foreach ($contextlist as $context) {
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $organizerids[] = $records[$context->id]->id;
        }
        if (empty($organizerids)) {
            return;
        }

        [$select, $params] = $DB->get_in_or_equal($organizerids);
        $slotids = $DB->get_fieldset_select('organizer_slots', 'id', 'organizerid ' . $select, $params);
        if (empty($slotids)) {
            return;
        }

        // Delete all appointments of this user.
        [$slotidssql, $slotidsparams] = $DB->get_in_or_equal($slotids, SQL_PARAMS_NAMED);
        $DB->delete_records_select('organizer_slot_appointments',
            "(userid = :userid) AND slotid " . $slotidssql,
            $slotidsparams + ['userid' => $user->id]);

    }


    /**
     * Delete multiple users within a single context.
     *
     * @param   approved_userlist $userlist The approved context and user information to delete information for.
     */
    public static function delete_data_for_users(approved_userlist $userlist) {
        global $DB;

        $context = $userlist->get_context();

        if ($context->contextlevel == CONTEXT_MODULE) {
            // Apparently we can't trust anything that comes via the context.
            // Go go mega query to find out it we have an checkmark context that matches an existing checkmark.
            $sql = "SELECT o.*
                    FROM {organizer} o
                    JOIN {course_modules} cm ON o.id = cm.instance AND o.course = cm.course
                    JOIN {modules} m ON m.id = cm.module AND m.name = :modulename
                    JOIN {context} ctx ON ctx.instanceid = cm.id AND ctx.contextlevel = :contextmodule
                    WHERE ctx.id = :contextid";
            $params = ['modulename' => 'organizer', 'contextmodule' => CONTEXT_MODULE, 'contextid' => $context->id];
            $organizer = $DB->get_record_sql($sql, $params);
            // If we have an id over zero then we can proceed.
            if (!empty($organizer) && $organizer->id > 0) {
                $userids = $userlist->get_userids();
                if (count($userids) <= 0) {
                    return;
                }
                // Get slots of this organizer instance.
                $slotids = $DB->get_fieldset_select('organizer_slots', 'id', 'organizerid = ?', [$organizer->id]);
                if (empty($slotids)) {
                    $slotids = [0];
                }
                [$slotidssql, $slotidsparams] = $DB->get_in_or_equal($slotids, SQL_PARAMS_NAMED);

                [$usersql, $userparams] = $DB->get_in_or_equal($userids, SQL_PARAMS_NAMED);
                // Delete all appointments of these users in these slots.
                $DB->delete_records_select('organizer_slot_appointments', "slotid " . $slotidssql . " AND userid " . $usersql,
                    $slotidsparams + $userparams);
            }
        }
    }

}
