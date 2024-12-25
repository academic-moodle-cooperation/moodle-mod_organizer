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
 * event/appointment_evaluated.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_organizer\event;
use core\event\base;
use moodle_url;

/**
 * The appointment_evaluated event class.
 **/
class appointment_evaluated extends base {
    /**
     * Event init method
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u'; // Options: c (reate), r (ead), u (pdate), d (elete).
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'organizer_slot_appointments';
    }

    /**
     * Get name of event
     * @return \lang_string|string
     * @throws \coding_exception
     */
    public static function get_name() {
        return get_string('eventappointmentevaluated', 'mod_organizer');
    }

    /**
     * Get description of event
     * @return string
     */
    public function get_description() {
        return "The user with id {$this->userid} graded an appointment of organizer activity " .
            "with the course module id {$this->contextinstanceid}.";
    }

    /**
     * Get url of event
     * @return moodle_url
     * @throws \core\exception\moodle_exception
     */
    public function get_url() {
        return new moodle_url('/mod/organizer/view.php', ['id' => $this->objectid]);
    }
}
