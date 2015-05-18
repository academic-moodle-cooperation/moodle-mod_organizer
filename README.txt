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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * README.txt
 * @version       2014-02-24
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

# ---------------------------------------------------------------
# FOR Moodle 2.5+
# ---------------------------------------------------------------

Organizer module
===============

OVERVIEW
================================================================================
    The Organizer module is used to create timeslots for students to register to.

    An organizer consists of three views:
    1) Appointments view, which allows teachers to create, remove, modify, grade 
        and print timeslots and made appointments
    2) Student view, which allows students to register to time slots created by 
        teachers. Teachers have limited use of this view as they cannot register
        to time slots.
    3) Registration status view, which provides a concise overview over students'
        registration status. Here a teacher can send reminders to students that 
        haven't had and completed an appointment yet.

    Teachers have access to all three views, while students can access only the 
    student view (no. 2).

    If a group organizer is created, an existing grouping MUST be provided. 
    Additionally, all groups contained in the grouping MUST be disjunct (i.e. 
    no student can be in more than one group). Registration of a group can be 
    done by any student belonging to it.

REQUIREMENTS
================================================================================
    Moodle 2.5 or later

INSTALLATION 
================================================================================
    To install, extract the contents of the archive to the mod/ folder in the moodle
    root folder, and all of the archive's contents will be properly placed into the 
    folder structure. The module and all of its files is located in mod/organizer 
    folder and require no other files or folders.

    The langfiles can be put into the folder mod/organizer/lang normally.
    All languages should be encoded with utf-8.

    After it you have to run the admin-page of moodle (http://your-moodle-site/admin)
    in your browser. You have to loged in as admin before.
    The installation process will be displayed on the screen. All the data necessary
    for a proper install is contained in the help files displayed on screen.
    
CHANGELOG
================================================================================
    *) 23.9.2013 Bugs fixed
    *) 23.9.2013 compatibility for Moodle 2.5
    *) 24.5.2012 Version 2012052400 released.
