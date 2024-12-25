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
 * A scheduled task for organizer cron.
 *
 * @package   mod_organizer
 * @copyright 2016 Thomas Niedermaier <thomas.niedermaier@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_organizer\task;

use core\task\scheduled_task;

/**
 * Cron task class.
 */
class cron_task extends scheduled_task {

    /**
     * Get name function
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public function get_name() {
        // Shown in admin screens.
        return get_string('crontaskname', 'mod_organizer');
    }

    /**
     * Class e  xecute function
     * @return void
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function execute() {
        global $CFG;
        include_once($CFG->dirroot . '/mod/organizer/lib.php');
        organizer_cron();
    }

}
