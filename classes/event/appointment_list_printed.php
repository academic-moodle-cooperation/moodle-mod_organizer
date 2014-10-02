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
 * The appointment_list_printed event.
 *
 * @package    mod_organizer
 * @copyright  2014 Andreas Windbichler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_organizer\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The appointment_list_printed event class.
 **/
class appointment_list_printed extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'organizer_slot_appointments';
    }
 
    public static function get_name() {
        return get_string('eventappointmentlistprinted', 'mod_organizer');
    }
 
    public function get_description() {
    	return "The user with id {$this->userid} printed the appointments from the organizer activity " .
            "with the course module id {$this->contextinstanceid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/organizer/view_action.php', array('id' => $this->objectid, 'mode'=>1));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'mod_organizer', 'print',
            $this->objectid, $this->contextinstanceid);
    }
}