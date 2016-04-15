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
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * xmldb_organizer_upgrade
 *
 * @param int $oldversion
 * @return bool
 */
function xmldb_organizer_upgrade($oldversion) {

    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012081404) {

        // Changing precision of field grade on table organizer to (10, 5).
        $table = new xmldb_table('organizer_slot_appointments');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null,
                'attended');

        // Launch change of precision, sign and the default value for field grade.
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_unsigned($table, $field);
        $dbman->change_field_default($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2012081404, 'organizer');
    }

    if ($oldversion < 2012081401) {

        // Changing precision of field grade on table organizer to (10, 5).
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0',
                'relativedeadline');

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
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null,
                'attended');

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
        $index = new xmldb_index('slots_organizerid', XMLDB_INDEX_NOTUNIQUE, array('organizerid'));

        // Conditionally launch drop index slots_eventid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index slots_eventid (not unique) to be added to organizer_slots.
        $index = new xmldb_index('slots_eventid', XMLDB_INDEX_NOTUNIQUE, array('eventid'));

        // Conditionally launch add index slots_eventid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define key organizer (foreign) to be added to organizer_slots.
        $key = new xmldb_key('organizer', XMLDB_KEY_FOREIGN, array('organizerid'), 'organizer', array('id'));

        // Launch add key organizer.
        $dbman->add_key($table, $key);

        // Define index slot_appointments_slotid (not unique) to be dropped form organizer_slot_appointments.
        $table = new xmldb_table('organizer_slot_appointments');
        $index = new xmldb_index('slot_appointments_slotid', XMLDB_INDEX_NOTUNIQUE, array('slotid'));

        // Conditionally launch drop index slot_appointments_slotid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Define index slot_appointments_eventid (not unique) to be added to organizer_slot_appointments.
        $index = new xmldb_index('slot_appointments_eventid', XMLDB_INDEX_NOTUNIQUE, array('eventid'));

        // Conditionally launch add index slot_appointments_eventid.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Define key slot (foreign) to be added to organizer_slot_appointments.
        $key = new xmldb_key('slot', XMLDB_KEY_FOREIGN, array('slotid'), 'organizer_slots', array('id'));

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

        $field = new xmldb_field('duedate', XMLDB_TYPE_INTEGER, '10',
                XMLDB_UNSIGNED, false, null, '0', 'allowregistrationsfromdate');
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('allowregistrationsfromdate', XMLDB_TYPE_INTEGER,
                '10', XMLDB_UNSIGNED, false, null, '0', 'emailteachers');
        $dbman->change_field_notnull($table, $field);

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2013112901, 'organizer');
    }

    if ($oldversion < 2013122300) {
        $DB->execute('UPDATE mdl_log SET url = RIGHT(url, LOCATE("/", REVERSE(url))-1) ' .
                'WHERE module="organizer" and url LIKE "http://%"');

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2013122300, 'organizer');
    }

    if ($oldversion < 2014032400) {

        $table = new xmldb_table('organizer');

        $field = new xmldb_field('duedate', XMLDB_TYPE_INTEGER,
                '10', XMLDB_UNSIGNED, false, null, '0', 'allowregistrationsfromdate');
        $dbman->change_field_notnull($table, $field);

        $field = new xmldb_field('allowregistrationsfromdate', XMLDB_TYPE_INTEGER,
                '10', XMLDB_UNSIGNED, false, null, '0', 'emailteachers');
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


    if ($oldversion < 2016041500) {

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
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('slot', XMLDB_KEY_FOREIGN, array('slotid'), 'organizer_slots', array('id'));

        // Adding indexes to table organizer_slot_queues.
        $table->add_index('slot_queue_userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        $table->add_index('slot_queue_groupid', XMLDB_INDEX_NOTUNIQUE, array('groupid'));
        $table->add_index('slot_queue_eventid', XMLDB_INDEX_NOTUNIQUE, array('eventid'));

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

        // Define field calendar to be added to organizer.
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('calendar', XMLDB_TYPE_INTEGER, '4', null, null, null, '0', 'visibility');

        // Conditionally launch add field calender.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Organizer savepoint reached.
        upgrade_mod_savepoint(true, 2016041500, 'organizer');
    }

    return true;
}