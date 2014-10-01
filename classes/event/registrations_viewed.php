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
 * The registrations_viewed event.
 *
 * @package    mod_organizer
 * @copyright  Andreas Windbichler
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_organizer\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The registrations_viewed event class.
 *
 * @property-read array $other {
 *      Extra information about event.
 *
 *      - PUT INFO HERE
 * }
 *
 * @since     Moodle MOODLEVERSION
 * @copyright 2014 YOUR NAME
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class registrations_viewed extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'organizer';
    }
 
    public static function get_name() {
        return get_string('eventregistrationsviewed', 'mod_organizer');
    }
 
    public function get_description() {
        return "The user with id {$this->userid} viewed registrations tab with the course module id {$this->objectid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/mod/organizer/view.php', array('id' => $this->objectid, 'mode'=>3));
    }
 
    public function get_legacy_logdata() {
        // Override if you are migrating an add_to_log() call.
        return array($this->courseid, 'mod_organizer', 'statusview',
            $this->objectid, $this->contextinstanceid);
    }
}