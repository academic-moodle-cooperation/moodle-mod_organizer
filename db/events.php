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
 * Organizer event handler definition.
 *
 * @package mod_organizer
 * @category event
 * @author Simeon Naydenov (moninaydenov@gmail.com)
 * @author Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @copyright 2022 Academic Moodle Cooperation {@link https://www.academic-moodle-cooperation.org/}
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = [

    [
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => 'mod_organizer_observer::user_enrolment_deleted',
    ],

    [
        'eventname'    => '\core\event\group_member_added',
        'callback'     => '\mod_organizer_observer::group_member_added',
        'includefile'  => '/mod/organizer/locallib.php',
        'priority'     => 0,
        'internal'     => false,
    ],
    // We get groupid, userid with this handler.

    [
        'eventname'    => 'core\event\group_member_removed',
        'callback'     => '\mod_organizer_observer::group_member_removed',
        'includefile'  => '/mod/organizer/locallib.php',
        'priority'     => 0,
        'internal'     => false,
    ],
    // We get groupid, userid with this handler.

];

