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
 * This file keeps track of upgrades to the organizer module
 *
 * Sometimes, changes between versions involve alterations to database
 * structures and other major things that may break installations. The upgrade
 * function in this file will attempt to perform all the necessary actions to
 * upgrade your older installation to the current version. If there's something
 * it cannot do itself, it will tell you what you need to do.  The commands in
 * here will all be database-neutral, using the functions defined in DLL libraries.
 *
 * @package   mod_organizer
 * @copyright 2010 Your Name
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

        // Changing precision of field grade on table organizer to (10, 5)
        $table = new xmldb_table('organizer_slot_appointments');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, null, null, null,
                'attended');

        // Launch change of precision, sign and the default value for field grade
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_unsigned($table, $field);
        $dbman->change_field_default($table, $field);

        // organizer savepoint reached
        upgrade_mod_savepoint(true, 2012081404, 'organizer');
    }

    if ($oldversion < 2012081401) {

        // Changing precision of field grade on table organizer to (10, 5)
        $table = new xmldb_table('organizer');
        $field = new xmldb_field('grade', XMLDB_TYPE_NUMBER, '10, 5', null, XMLDB_NOTNULL, null, '0',
                'relativedeadline');

        // Launch change of precision, sign and the default value for field grade
        $dbman->change_field_precision($table, $field);
        $dbman->change_field_unsigned($table, $field);
        $dbman->change_field_default($table, $field);

        // organizer savepoint reached
        upgrade_mod_savepoint(true, 2012081401, 'organizer');
    }

    return true;
}
