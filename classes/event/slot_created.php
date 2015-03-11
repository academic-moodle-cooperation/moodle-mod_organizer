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
// If not, see <http://www.gnu.org/licenses/>.

/**
 * event/slot_created.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_organizer\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The slots_created event class.
 **/
class slot_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'organizer_slots';
    }
 
    public static function get_name() {
        return get_string('eventslotcreated', 'mod_organizer');
    }
 
    public function get_description() {
    	return "The user with id {$this->userid} added slots to the organizer activity " .
            "with the course module id {$this->contextinstanceid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/organizer/slots_add.php', array('id' => $this->objectid, 'mode'=>1));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'mod_organizer', 'add',
            $this->objectid, $this->contextinstanceid);
    }
}