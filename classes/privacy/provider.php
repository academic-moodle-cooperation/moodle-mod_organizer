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

defined('MOODLE_INTERNAL') || die();

use \core_privacy\local\metadata\collection;
use \core_privacy\local\metadata\provider as metadataprovider;
use \core_privacy\local\request\contextlist;
use \core_privacy\local\request\plugin\provider as pluginprovider;
use \core_privacy\local\request\user_preference_provider as preference_provider;
use \core_privacy\local\request\writer;
use \core_privacy\local\request\approved_contextlist;
use \core_privacy\local\request\helper;

/**
 * Privacy class for requesting user data.
 *
 * @package    mod_organizer
 * @author     Thomas Niedermaier
 * @copyright  2018 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class provider implements metadataprovider, pluginprovider, preference_provider {
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

        $collection->add_database_table('organizer_slot_appointments', $organizerslotappointments, 'privacy:metadata:organizerslotappointments');
        $collection->add_database_table('organizer_slot_queues', $organizerslotqueues, 'privacy:metadata:organizerslotqueues');
        $collection->add_database_table('organizer_slot_trainer', $organizerslottrainer, 'privacy:metadata:organizerslottrainer');

        $collection->add_user_preference('mod_organizer_showhiddenslots', 'privacy:metadata:showhiddenslots');
        $collection->add_user_preference('mod_organizer_showmyslotsonly', 'privacy:metadata:showmyslotsonly');
        $collection->add_user_preference('mod_organizer_showfreeslotsonly', 'privacy:metadata:showfreeslotsonly');
        $collection->add_user_preference('mod_organizer_showpasttimeslots', 'privacy:metadata:showpasttimeslots');

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
                'guserid' => $userid,
                'tuserid' => $userid,
                'teacherapplicantid' => $userid,
        ];

        $sql = "
   SELECT ctx.id
     FROM {course_modules} cm
     JOIN {modules} m ON cm.module = m.id AND m.name = :modulename
     JOIN {organizer} o ON cm.instance = o.id
     JOIN {context} ctx ON cm.id = ctx.instanceid AND ctx.contextlevel = :contextlevel
     JOIN {organizer_slots} s ON o.id = s.organizerid
LEFT JOIN {organizer_slot_appointments} a ON s.id = a.slotid
LEFT JOIN {organizer_slot_queues} q ON s.id = q.slotid
LEFT JOIN {organizer_slot_trainer} t ON s.id = t.slotid
LEFT JOIN {groups} g ON g.courseid = o.course
LEFT JOIN {groups_members} gm ON g.id = gm.groupid AND gm.userid = :guserid
    WHERE (
          a.userid = :useridappointment OR a.groupid = g.id OR a.applicantid = :applicantidappointment
          OR q.userid = :useridqueue OR q.groupid = g.id OR q.applicantid = :applicantidqueue
          OR t.trainerid = :tuserid
          OR a.teacherapplicantid = :teacherapplicantid
          )";
        $contextlist = new contextlist();
        $contextlist->add_from_sql($sql, $params);

        return $contextlist;
    }

    /**
     * Write out the user data filtered by contexts.
     *
     *
     * @param approved_contextlist $contextlist contexts that we are writing data out from.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_data(approved_contextlist $contextlist) {
        global $DB;

        $contexts = $contextlist->get_contexts();

        if (empty($contexts)) {
            return;
        }

        list($contextsql, $contextparams) = $DB->get_in_or_equal($contextlist->get_contextids(), SQL_PARAMS_NAMED);

        $sql = "SELECT c.id AS contextid, o.*, cm.id AS cmid
                  FROM {context} c
                  JOIN {course_modules} cm ON cm.id = c.instanceid
                  JOIN {organizer} o ON o.id = cm.instance
                 WHERE c.id {$contextsql}";

        // Keep a mapping of organizerid to contextid.
        $mappings = [];

        $organizers = $DB->get_records_sql($sql, $contextparams);

        $user = $contextlist->get_user();

        foreach ($organizers as $organizer) {
            $context = \context_module::instance($organizer->cmid);
            $mappings[$organizer->id] = $organizer->contextid;

            // Check that the context is a module context.
            if ($context->contextlevel != CONTEXT_MODULE) {
                continue;
            }

            $organizerdata = helper::get_context_data($context, $user);

            writer::with_context($context)->export_data([], $organizerdata);

            /* We don't differentiate between roles, if we have data about the user, we give it freely ;) - no sensible
             * information here! */

            static::export_user_preferences($user->id);
        }
    }

    /**
     * Stores the user preferences related to mod_organizer.
     *
     * @param  int $userid The user ID that we want the preferences for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function export_user_preferences(int $userid) {
        $context = \context_system::instance();
        $value = get_user_preferences('mod_organizer_showhiddenslots', null, $userid);
        if ($value !== null) {
            writer::with_context($context)->export_user_preference('mod_organizer', 'mod_organizer_showhiddenslots', $value,
                    get_string('privacy:metadata:showhiddenslots', 'mod_organizer'));
        }
        $value = get_user_preferences('mod_organizer_showmyslotsonly', null, $userid);
        if ($value !== null) {
            writer::with_context($context)->export_user_preference('mod_organizer', 'mod_organizer_showmyslotsonly', $value,
                    get_string('privacy:metadata:showmyslotsonly', 'mod_organizer'));
        }
        $value = get_user_preferences('mod_organizer_showfreeslotsonly', null, $userid);
        if ($value !== null) {
            writer::with_context($context)->export_user_preference('mod_organizer', 'mod_organizer_showfreeslotsonly', $value,
                    get_string('privacy:metadata:showfreeslotsonly', 'mod_organizer'));
        }
        $value = get_user_preferences('mod_organizer_showpasttimeslots', null, $userid);
        if ($value !== null) {
            writer::with_context($context)->export_user_preference('mod_organizer', 'mod_organizer_showpasttimeslots', $value,
                    get_string('privacy:metadata:showpasttimeslots', 'mod_organizer'));
        }
    }

    /**
     * Delete all use data which matches the specified context.
     *
     * @param \context $context The module context.
     * @throws \dml_exception
     */
    public static function delete_data_for_all_users_in_context(\context $context) {

        // TODO.
        return;
  }

    /**
     * Delete all user data for the specified user, in the specified contexts.
     *
     * @param approved_contextlist $contextlist The approved contexts and user information to delete information for.
     * @throws \dml_exception
     * @throws \coding_exception
     */
    public static function delete_data_for_user(approved_contextlist $contextlist) {

        // TODO.
        return;

    }
}
