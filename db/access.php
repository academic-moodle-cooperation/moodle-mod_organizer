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
 * Capability definitions for the organizer module
 *
 * The capabilities are loaded into the database table when the module is
 * installed or updated. Whenever the capability definitions are updated,
 * the module version number should be bumped up.
 *
 * The system has four possible values for a capability:
 * CAP_ALLOW, CAP_PREVENT, CAP_PROHIBIT, and inherit (not set).
 *
 * It is important that capability names are unique. The naming convention
 * for capabilities that are specific to modules and blocks is as follows:
 *   [mod/block]/<plugin_name>:<capabilityname>
 *
 * component_name should be the same as the directory name of the mod or block.
 *
 * Core moodle capabilities are defined thus:
 *    moodle/<capabilityclass>:<capabilityname>
 *
 * Examples: mod/forum:viewpost
 *           block/recent_activity:view
 *           moodle/site:deleteuser
 *
 * The variable name for the capability definitions array is $capabilities
 *
 * @package   mod_organizer
 * @copyright 2011 Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'mod/organizer:addinstance' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:register' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('student' => CAP_ALLOW)),
        'mod/organizer:unregister' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('student' => CAP_ALLOW)),
        'mod/organizer:comment' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('student' => CAP_ALLOW)),
        'mod/organizer:viewallslots' => array('captype' => 'read', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:viewmyslots' => array('captype' => 'read', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:viewregistrations' => array('captype' => 'read', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:viewstudentview' => array('captype' => 'read', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('student' => CAP_ALLOW, 'teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW,
                        'manager' => CAP_ALLOW)),
        'mod/organizer:addslots' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:editslots' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:evalslots' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:deleteslots' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:sendreminders' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:printslots' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)),
        'mod/organizer:receivemessagesstudent' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('student' => CAP_ALLOW)),
        'mod/organizer:receivemessagesteacher' => array('captype' => 'write', 'contextlevel' => CONTEXT_MODULE,
                'legacy' => array('teacher' => CAP_ALLOW, 'editingteacher' => CAP_ALLOW, 'manager' => CAP_ALLOW)));
