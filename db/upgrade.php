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
 * db/update.php
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

/**
 * xmldb_organizer_upgrade
 *
 * @param  int $oldversion
 * @return bool
 */
function xmldb_organizer_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012081401) {

        // Changing precision of field grade on table organizer to (10, 5).
        $table = new xmldb_table('organizer');
        $field = new xmldb_field(
            'grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0',
            'relativedeadline'
        );

        // Launch change of precision, sign and the default value for field grade.
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_unsigned($table, $field);
        $dbman->change_field_default($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2012081401, 'organizer');
    }

    if ($oldversion < 2012081404) {

        // Changing precision of field grade on table organizer to (10, 5).
        $table = new xmldb_table('organizer_slot_appointments');
        $field = new xmldb_field(
            'grade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null,
            'attended'
        );

        // Launch change of precision, sign and the default value for field grade.
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_unsigned($table, $field);
        $dbman->change_field_default($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2012081404, 'organizer');
    }

    if ($oldversion < 2012122601) {

        // Define index slots_eventid (not unique) to be dropped form organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $index = new xmldb_index('slots_organizerid', XMLDB_INDEX_NOTUNIQUE, ['organizerid']);

        // Conditionally launch drop index slots_eventid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index slots_eventid (not unique) to be added to organizer_slots.
        $index = new xmldb_index('slots_eventid', XMLDB_INDEX_NOTUNIQUE, ['eventid']);

        // Conditionally launch add index slots_eventid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define key organizer (foreign) to be added to organizer_slots.
        $key = new xmldb_key('organizer', XMLDB_KEY_FOREIGN, ['organizerid'], 'organizer', ['id']);

        // Launch add key organizer.
        $dbman->add_key($table, $key);

        // Define index slot_appointments_slotid (not unique) to be dropped form organizer_slot_appointments.
        $table = new xmldb_table('organizer_slot_appointments');
        $index = new xmldb_index('slot_appointments_slotid', XMLDB_INDEX_NOTUNIQUE, ['slotid']);

        // Conditionally launch drop index slot_appointments_slotid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index slot_appointments_eventid (not unique) to be added to organizer_slot_appointments.
        $index = new xmldb_index('slot_appointments_eventid', XMLDB_INDEX_NOTUNIQUE, ['eventid']);

        // Conditionally launch add index slot_appointments_eventid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define key slot (foreign) to be added to organizer_slot_appointments.
        $key = new xmldb_key('slot', XMLDB_KEY_FOREIGN, ['slotid'], 'organizer_slots', ['id']);

        // Launch add key slot.
        $dbman->add_key($table, $key);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2012122601, 'organizer');
    }

    if ($oldversion < 2013112900) {
        $table = new xmldb_table('organizer');

        // Rename enableuntil field to duedate.
        $field = new xmldb_field('enableuntil', XMLDB_TYPE_INTEGER, '10', false, false, false, '0', 'enablefrom');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'duedate');
        }

        // Fename enablefrom to allowsubmissionsfromdate.
        $field = new xmldb_field('enablefrom', XMLDB_TYPE_INTEGER, '10', false, false, false, '0', 'emailteachers');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'allowregistrationsfromdate');
        }

        // Add field alwaysshowdescription.
        $field = new xmldb_field('alwaysshowdescription', XMLDB_TYPE_INTEGER, '2', false, false, false, '0', 'duedate');

        // Conditionally launch add field alwaysshowdescription.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2013112900, 'organizer');
    }

    if ($oldversion < 2013112901) {
        $table = new xmldb_table('organizer');

        $field = new xmldb_field(
            'duedate', XMLDB_TYPE_INTEGER, '10',
            XMLDB_UNSIGNED, false, null, '0', 'allowregistrationsfromdate'
        );
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field(
            'allowregistrationsfromdate', XMLDB_TYPE_INTEGER,
            '10', XMLDB_UNSIGNED, false, null, '0', 'emailteachers'
        );
        $dbman->change_field_notnull($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2013112901, 'organizer');
    }

    if ($oldversion < 2013122300) {
        $DB->execute(
            'UPDATE mdl_log SET url = RIGHT(url, LOCATE("/", REVERSE(url))-1) ' .
            'WHERE module="organizer" and url LIKE "http://%"'
        );

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2013122300, 'organizer');
    }

    if ($oldversion < 2014032400) {

        $table = new xmldb_table('organizer');

        $field = new xmldb_field(
            'duedate', XMLDB_TYPE_INTEGER,
            '10', XMLDB_UNSIGNED, false, null, '0', 'allowregistrationsfromdate'
        );
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field(
            'allowregistrationsfromdate', XMLDB_TYPE_INTEGER,
            '10', XMLDB_UNSIGNED, false, null, '0', 'emailteachers'
        );
        $dbman->change_field_notnull($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2014032400, 'organizer');
    }

    if ($oldversion < 2015012004) {

        // Changing precision of field grade on table organizer to INT(10), like in all the other modules.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('grade', XMLDB_TYPE_INTEGER, '10');

        // Launch change of precision, sign and the default value for field grade.
        $dbman->change_field_precision($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2015012004, 'organizer');
    }

    if ($oldversion < 2015111900) {

        // Define field gap to be added to organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('gap', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'duration');

        // Conditionally launch add field gap.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2015111900, 'organizer');
    }

    if ($oldversion < 2016041800) {

        // Define field queue to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('queue', XMLDB_TYPE_INTEGER, '2', null, XMLDB_NOTNULL, null, '0', 'grade');

        // Conditionally launch add field queue.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define table organizer_slot_queues to be created.
        $table = new xmldb_table('organizer_slot_queues');

        // Adding fields to table organizer_slot_queues.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('slotid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('groupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('applicantid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('eventid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('notified', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0');

        // Adding keys to table organizer_slot_queues.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('slot', XMLDB_KEY_FOREIGN, ['slotid'], 'organizer_slots', ['id']);

        // Adding indexes to table organizer_slot_queues.
        $table->add_index('slot_queue_userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        $table->add_index('slot_queue_groupid', XMLDB_INDEX_NOTUNIQUE, ['groupid']);
        $table->add_index('slot_queue_eventid', XMLDB_INDEX_NOTUNIQUE, ['eventid']);

        // Conditionally launch create table for organizer_slot_queues.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define field visibility to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'queue');

        // Conditionally launch add field visibility.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field visibility to be added to organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '0', 'gap');

        // Conditionally launch add field visibility.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field hidecalendar to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('hidecalendar', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'visibility');

        // Conditionally launch add field hidecalendar.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Drop field calendar of organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('calendar');

        // Conditionally drop field calender.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2016041800, 'organizer');
    }

    if ($oldversion < 2016060801) {

        // Define field teacherapplicantid and teacherapplicanttimemodified to be added to organizer_slot_appointments.
        $table = new xmldb_table('organizer_slot_appointments');
        $field = new xmldb_field('teacherapplicantid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'allownewappointments');

        // Conditionally launch add field teacherapplicantid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('teacherapplicanttimemodified',
                XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'teacherapplicantid');

        // Conditionally launch add field teacherapplicanttimemodified.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2016060801, 'organizer');
    }

    if ($oldversion < 2016062800) {

         // Changing precision of field duration on table organizer_slots to INT(10).
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '10');

        // Launch change of precision, sign and the default value for field duration.
        $dbman->change_field_precision($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2016062800, 'organizer');
    }

    if ($oldversion < 2017062300) {

        // Changing events created with organizer version 3.2 and before to work with calender action events.

        include_once(dirname(__FILE__) . '/../locallib.php');

        $query = 'SELECT {organizer_slot_appointments}.id appid, {organizer_slots}.eventid, {organizer_slots}.id slotid,
                  {organizer_slots}.teacherid slotuser,
                  {organizer_slot_appointments}.userid appuser,
                  {event}.userid eventuser,
                  {organizer_slots}.organizerid
                  FROM {organizer_slots} INNER JOIN {organizer_slot_appointments} ON
                  {organizer_slots}.id = {organizer_slot_appointments}.slotid INNER JOIN {event} ON
                  {organizer_slots}.eventid = {event}.id
                  WHERE {event}.modulename <> :modulename';
        $records = $DB->get_records_sql($query, ['modulename' => 'organizer']);

        $query = "SELECT e.* FROM {event} e WHERE e.id = :eventid";

        foreach ($records as $record) {

            $event = $DB->get_record_sql($query, ["eventid" => $record->eventid]);
            $courseid = $DB->get_field('organizer', 'course', ['id' => $record->organizerid]);

            $event->type = CALENDAR_EVENT_TYPE_ACTION;
            $event->courseid = $courseid;
            $event->modulename = 'organizer';

            if ($record->eventuser == $record->appuser) {
                $event->uuid = $record->appid;
                $event->eventtype = ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT;
            } else {
                if ($record->eventuser == $record->slotuser) {
                    $event->uuid = $record->slotid;
                    $event->eventtype = ORGANIZER_CALENDAR_EVENTTYPE_SLOT;
                }
            }
            $update = $DB->update_record('event', $event);
            // Insert event-ds for the organizer instance, if there is none yet.
            if (!$DB->get_field(
                'event', 'id', ['modulename' => 'organizer', 'instance' => $record->organizerid,
                'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE]
            )) {
                organizer_change_event_instance($record->organizerid);
            }
        }

        upgrade_mod_savepoint(true, 2017062300, 'organizer');
    }

    if ($oldversion < 2017112201) {

        // Define field visible to be added to organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('visible', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, null, '1', 'notified');

        // Conditionally launch add field visibility.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2017112201, 'organizer');
    }

    if ($oldversion < 2018062602) {

        // Define table organizer_slot_trainer to be created.
        $table = new xmldb_table('organizer_slot_trainer');

        // Adding fields to table organizer_slot_trainer.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('slotid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('trainerid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('eventid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table organizer_slot_trainer.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key('slot', XMLDB_KEY_FOREIGN, ['slotid'], 'organizer_slots', ['id']);
        $table->add_key('trainer', XMLDB_KEY_FOREIGN, ['trainerid'], 'user', ['id']);

        // Conditionally launch create table for organizer_slot_trainer.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
            // Shift field trainerid of table organizer_slots to the new table organizer_slot_trainer.
            $query = 'SELECT {organizer_slots}.id slotid, {organizer_slots}.teacherid, {organizer_slots}.eventid
                      FROM {organizer_slots}';
            $records = $DB->get_records_sql($query);

            $newrecord = new stdClass();

            foreach ($records as $record) {

                $newrecord->slotid = $record->slotid;
                $newrecord->trainerid = $record->teacherid;
                $newrecord->eventid = $record->eventid;
                $newid = $DB->insert_record('organizer_slot_trainer', $newrecord);
            }

        }

        // Define index slots_userid (not unique) to be dropped form organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $index = new xmldb_index('slots_userid', XMLDB_INDEX_NOTUNIQUE, ['teacherid']);
        $field = new xmldb_field('teacherid');

        // Conditionally launch drop index slots_userid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Conditionally drop field teacherid.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Define field nocalendareventslotcreation to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('nocalendareventslotcreation', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'hidecalendar');

        // Conditionally launch add field nocalendareventslotcreation.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field coursegroup to be added to organizer_slots.
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('coursegroup', XMLDB_TYPE_INTEGER, '4', null, null, null, null, 'visible');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field includetraineringroups to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('includetraineringroups', XMLDB_TYPE_INTEGER, '4', null, null, null, '0',
            'nocalendareventslotcreation');

        // Conditionally launch add field includetraineringroups.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define fields singleslotprintfields to be added to organizer.
        $field = new xmldb_field('singleslotprintfield0', XMLDB_TYPE_TEXT, null, null, null, null, null, 'includetraineringroups');
        // Conditionally launch add field singleslotprintfield0.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield1', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield0');
        // Conditionally launch add field singleslotprintfield1.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield2', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield1');
        // Conditionally launch add field singleslotprintfield2.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield3', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield2');
        // Conditionally launch add field singleslotprintfield3.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield4', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield3');
        // Conditionally launch add field singleslotprintfield4.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield5', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield4');
        // Conditionally launch add field singleslotprintfield5.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield6', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield5');
        // Conditionally launch add field singleslotprintfield6.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield7', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield6');
        // Conditionally launch add field singleslotprintfield7.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield8', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield7');
        // Conditionally launch add field singleslotprintfield8.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $field = new xmldb_field('singleslotprintfield9', XMLDB_TYPE_TEXT, null, null, null, null, null, 'singleslotprintfield8');
        // Conditionally launch add field singleslotprintfield9.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $sqlparams = ['modulename' => 'organizer', 'eventtype' => 'Instance'];
        $query = 'SELECT {event}.*
                  FROM {event}
                  WHERE {event}.modulename = :modulename AND {event}.eventtype = :eventtype AND {event}.timeduration > 0';
        $records = $DB->get_records_sql($query, $sqlparams);

        // Change old calendar instance events to new scheme: One event fromdate, one event todate, duration 0.
        foreach ($records as $record) {
            // Change the old events only if there is a duedate.
            if ($newtimestart = $DB->get_field('organizer', 'duedate', ['id' => $record->instance])) {
                $newname = get_string('allowsubmissionstodate', 'organizer') . ": " . $record->name;
                $record->timeduration = 0;
                $record->name = get_string('allowsubmissionsfromdate', 'organizer') . ": " . $record->name;
                $DB->update_record('event', $record);
                $record->timestart = $newtimestart;
                $record->timesort = $newtimestart;
                $record->name = $newname;
                unset($record->id);
                $DB->insert_record('event', $record);
            }
        }

        upgrade_mod_savepoint(true, 2018062602, 'organizer');

    }

    if ($oldversion < 2018081002) {

        // Changing precision of field coursegroup on table organizer_slots to (10).
        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('coursegroup',  XMLDB_TYPE_INTEGER, '10');
        // Launch change of precision.
        $dbman->change_field_precision($table, $field);

        upgrade_mod_savepoint(true, 2018081002, 'organizer');
    }

    if ($oldversion < 2018081003) {

        // Delete instance events where the underlaying module instances had been deleted.
        $select = "select distinct(e.id) as id from {event} e left join {organizer} o ON e.instance = o.id
                    where e.modulename = :modulename and e.eventtype = :eventtype and o.id is null;";
        $parms = ['modulename' => 'organizer', 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE];
        if ($ids = $DB->get_fieldset_sql($select, $parms)) {
            list($insql, $inparams) = $DB->get_in_or_equal($ids, SQL_PARAMS_NAMED);
            $select = "id $insql";
            $DB->delete_records_select('event', $select);
        }

        // Delete instance events where the timestart is 0.
        $select = 'modulename = :modulename AND eventtype = :eventtype AND timestart = 0';
        $parms = ['modulename' => 'organizer', 'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_INSTANCE];
        $DB->delete_records_select('event', $select, $parms);

        // Replace existing slot events and appointment events of future slots by new programmed events.

        // Delete old slot and appointment events.
        $select = 'modulename = :modulename AND (eventtype = :eventtype1 OR eventtype = :eventtype2)';
        $parms = ['modulename' => 'organizer', 'eventtype1' => ORGANIZER_CALENDAR_EVENTTYPE_SLOT,
            'eventtype2' => ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT];
        $DB->delete_records_select('event', $select, $parms);
        $DB->set_field('organizer_slot_appointments', 'eventid', null);
        $DB->set_field('organizer_slot_trainer', 'eventid', null);

        include_once(dirname(__FILE__) . '/../locallib.php');

        $now = time();

        $params = ['now' => $now];
        $query = "SELECT s.id, cm.id as cmid, o.nocalendareventslotcreation
                  FROM {organizer_slots} s
                  INNER JOIN {organizer} o ON s.organizerid = o.id
                  INNER JOIN {course_modules} cm ON o.id = cm.instance
                  INNER JOIN {modules} m ON cm.module = m.id
                  WHERE m.name = 'organizer'
                  AND s.starttime > :now";

        $slots = $DB->get_records_sql($query, $params);

        foreach ($slots as $slot) {
            if ($apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $slot->id])) {
                $appointment = new stdClass();
                foreach ($apps as $app) {
                    $eventid = organizer_add_event_appointment($slot->cmid, $app->id);
                    $app->eventid = $eventid;
                    $appointment->eventid = $eventid;
                    $appointment->id = $app->id;
                    $DB->update_record('organizer_slot_appointments', $appointment);
                }
                organizer_add_event_appointment_trainer($slot->cmid, $app);
            } else {
                if (!$slot->nocalendareventslotcreation) {
                    $trainerslot = new stdClass();
                    $slottrainers = $DB->get_records_select(
                        'organizer_slot_trainer', 'slotid = :slotid', ['slotid' => $slot->id]);
                    foreach ($slottrainers as $trainer) {
                        $trainerslot->id = $trainer->id;
                        $trainerslot->eventid = organizer_add_event_slot($slot->cmid, $slot->id, $trainer->trainerid);
                        $DB->update_record('organizer_slot_trainer', $trainerslot);
                    }
                }
            }
        }

        upgrade_mod_savepoint(true, 2018081003, 'organizer');

    }

    if ($oldversion < 2019052700) {

        // Define field locationfieldmandatory to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('locationfieldmandatory', XMLDB_TYPE_INTEGER, '4', null, null, null, '0',
            'nocalendareventslotcreation');

        // Conditionally launch add field locationfieldmandatory.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2019052700, 'organizer');
    }

    if ($oldversion < 2020020501) {

        $table = new xmldb_table('organizer');
        $field = new xmldb_field('allowedprofilefieldsprint', XMLDB_TYPE_TEXT, null,
            null, null, null, null, 'singleslotprintfield9');
        // Conditionally launch add field allowedprofilefieldsprint. Github-issue #43.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('enableprintslotuserfields', XMLDB_TYPE_INTEGER, '4', null,
            null, null, null, 'allowedprofilefieldsprint');
        // Conditionally launch add field enableprintslotuserfields. Github-issue #43.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2020020501, 'organizer');
    }

    if ($oldversion < 2020020502) {
        $sql = 'UPDATE {event} SET type=:type WHERE modulename=:modulename AND eventtype=:eventtype AND type=:oldtype';
        $params = [
            'type' => CALENDAR_EVENT_TYPE_ACTION,
            'modulename' => 'organizer',
            'eventtype' => ORGANIZER_CALENDAR_EVENTTYPE_APPOINTMENT,
            'oldtype' => CALENDAR_EVENT_TYPE_STANDARD,
        ];
        $DB->execute($sql, $params);
        $params['eventtype'] = ORGANIZER_CALENDAR_EVENTTYPE_SLOT;
        $DB->execute($sql, $params);
        upgrade_mod_savepoint(true, 2020020502, 'organizer');
    }

    if ($oldversion < 2021062301) {
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '4', null, false, null, '1', 'queue');
        $dbman->change_field_default($table, $field);
        $dbman->change_field_notnull($table, $field);

        $table = new xmldb_table('organizer_slots');
        $field = new xmldb_field('visibility', XMLDB_TYPE_INTEGER, '4', null, false, null, '0', 'gap');
        $dbman->change_field_notnull($table, $field);
        $field = new xmldb_field('duration', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, 'starttime');
        $dbman->change_field_notnull($table, $field);

        upgrade_mod_savepoint(true, 2021062301, 'organizer');
    }

    if ($oldversion < 2023053100) {
        $table = new xmldb_table('organizer');

        $field = new xmldb_field('userslotsmin', XMLDB_TYPE_INTEGER, '4', null, false, null, '1', 'enableprintslotuserfields');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userslotsmax', XMLDB_TYPE_INTEGER, '4', null, false, null, '1', 'userslotsmin');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('gradeaggregationmethod', XMLDB_TYPE_INTEGER, '4', null, true, null, '1', 'grade');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $sql = 'UPDATE {organizer} SET userslotsmin=1,userslotsmax=1,gradeaggregationmethod=2';
            $DB->execute($sql, []);
        }

        $field = new xmldb_field('scale', XMLDB_TYPE_INTEGER, '4', null, true, null, '0', 'gradeaggregationmethod');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2023053100, 'organizer');
    }

    if ($oldversion < 2024041502) {
        $table = new xmldb_table('organizer');

        $field = new xmldb_field('synchronizegroupmembers', XMLDB_TYPE_INTEGER, '4', null, null, null, '0',
            'userslotsmax');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $field = new xmldb_field('userslotsdailymax', XMLDB_TYPE_INTEGER, '4', null, false, null, '0', 'synchronizegroupmembers');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        $table = new xmldb_table('organizer_slot_appointments');

        $field = new xmldb_field('registrationtime',
            XMLDB_TYPE_INTEGER, '10', null, true, null, '0', 'teacherapplicanttimemodified');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2024041502, 'organizer');
    }

    if ($oldversion < 2024111901) {
        $table = new xmldb_table('organizer');

        // Define field noreregistrations to be added to organizer.
        $field = new xmldb_field('noreregistrations', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'userslotsdailymax');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        upgrade_mod_savepoint(true, 2024111901, 'organizer');
    }

    if ($oldversion < 2025012200) {
        $table = new xmldb_table('organizer');

        // Define field grade to change its type.
        $field = new xmldb_field('grade',
            XMLDB_TYPE_INTEGER, '10', null, true, null, '1', 'enableprintslotuserfields');
        $dbman->change_field_type($table, $field);

        // Define field gradeaggregationmethod to change its default value.
        $field = new xmldb_field('gradeaggregationmethod', XMLDB_TYPE_INTEGER, '4', null, true, null, '1', 'grade');
        $dbman->change_field_default($table, $field);

        // Define field userslotsmin to change its default value.
        $field = new xmldb_field('userslotsmin',
            XMLDB_TYPE_INTEGER, '4', null, false, null, '1', 'enableprintslotuserfields');
        $dbman->change_field_default($table, $field);

        // Define field userslotsmax to change its default value.
        $field = new xmldb_field('userslotsmax',
            XMLDB_TYPE_INTEGER, '4', null, false, null, '1', 'userslotsmin');
        $dbman->change_field_default($table, $field);

        upgrade_mod_savepoint(true, 2025012200, 'organizer');
    }

    return true;
}
