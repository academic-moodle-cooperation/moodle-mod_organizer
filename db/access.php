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
 * db/access.php
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

$capabilities = array(
        'mod/organizer:addinstance' => array('captype'      => 'write',
                                             'contextlevel' => CONTEXT_MODULE,
                                             'legacy'       => array('editingteacher' => CAP_ALLOW,
                                                                     'manager'        => CAP_ALLOW)),

        'mod/organizer:register' => array('captype'      => 'write',
                                          'contextlevel' => CONTEXT_MODULE,
                                          'legacy'       => array('student' => CAP_ALLOW)),

        'mod/organizer:unregister' => array('captype'      => 'write',
                                            'contextlevel' => CONTEXT_MODULE,
                                            'legacy'       => array('student' => CAP_ALLOW)),

        'mod/organizer:comment' => array('captype'      => 'write',
                                         'contextlevel' => CONTEXT_MODULE,
                                         'legacy'       => array('student' => CAP_ALLOW)),

        'mod/organizer:viewallslots' => array('captype'      => 'read',
                                              'contextlevel' => CONTEXT_MODULE,
                                              'legacy'       => array('teacher'        => CAP_ALLOW,
                                                                      'editingteacher' => CAP_ALLOW,
                                                                      'manager'        => CAP_ALLOW)),

        'mod/organizer:viewmyslots' => array('captype'      => 'read',
                                             'contextlevel' => CONTEXT_MODULE,
                                             'legacy'       => array('teacher'        => CAP_ALLOW,
                                                                     'editingteacher' => CAP_ALLOW,
                                                                     'manager'        => CAP_ALLOW)),

        'mod/organizer:viewregistrations' => array('captype'      => 'read',
                                                   'contextlevel' => CONTEXT_MODULE,
                                                   'legacy'       => array('teacher'        => CAP_ALLOW,
                                                                           'editingteacher' => CAP_ALLOW,
                                                                           'manager'        => CAP_ALLOW)),

        'mod/organizer:viewstudentview' => array('captype'      => 'read',
                                                 'contextlevel' => CONTEXT_MODULE,
                                                 'legacy'       => array('student'        => CAP_ALLOW,
                                                                         'teacher'        => CAP_ALLOW,
                                                                         'editingteacher' => CAP_ALLOW,
                                                                         'manager'        => CAP_ALLOW)),
        'mod/organizer:addslots' => array('captype'      => 'write',
                                          'contextlevel' => CONTEXT_MODULE,
                                          'legacy'       => array('teacher'        => CAP_ALLOW,
                                                                  'editingteacher' => CAP_ALLOW,
                                                                  'manager'        => CAP_ALLOW)),

        'mod/organizer:editslots' => array('captype'      => 'write',
                                           'contextlevel' => CONTEXT_MODULE,
                                           'legacy' => array('teacher'        => CAP_ALLOW,
                                                             'editingteacher' => CAP_ALLOW,
                                                             'manager' => CAP_ALLOW)),

        'mod/organizer:evalslots' => array('captype' => 'write',
                                           'contextlevel' => CONTEXT_MODULE,
                                           'legacy' => array('teacher'        => CAP_ALLOW,
                                                             'editingteacher' => CAP_ALLOW,
                                                             'manager'        => CAP_ALLOW)),

        'mod/organizer:deleteslots' => array('captype' => 'write',
                                             'contextlevel' => CONTEXT_MODULE,
                                             'legacy' => array('teacher'        => CAP_ALLOW,
                                                               'editingteacher' => CAP_ALLOW,
                                                               'manager'        => CAP_ALLOW)),

        'mod/organizer:assignslots' => array('captype'      => 'write',
                                               'contextlevel' => CONTEXT_MODULE,
                                               'legacy' => array('teacher'        => CAP_ALLOW,
                                                                 'editingteacher' => CAP_ALLOW,
                                                                 'manager'        => CAP_ALLOW)),

        'mod/organizer:sendreminders' => array('captype'      => 'write',
                                               'contextlevel' => CONTEXT_MODULE,
                                               'legacy' => array('teacher'        => CAP_ALLOW,
                                                                 'editingteacher' => CAP_ALLOW,
                                                                 'manager'        => CAP_ALLOW)),

        'mod/organizer:printslots' => array('captype'      => 'write',
                                            'contextlevel' => CONTEXT_MODULE,
                                            'legacy'       => array('teacher' => CAP_ALLOW,
                                                                    'editingteacher' => CAP_ALLOW,
                                                                    'manager' => CAP_ALLOW)),

        'mod/organizer:receivemessagesstudent' => array('captype'      => 'write',
                                                        'contextlevel' => CONTEXT_MODULE,
                                                        'legacy'       => array('student' => CAP_ALLOW)),

        'mod/organizer:receivemessagesteacher' => array('captype'      => 'write',
                                                        'contextlevel' => CONTEXT_MODULE,
                                                        'legacy'       => array('teacher' => CAP_ALLOW,
                                                                                'editingteacher' => CAP_ALLOW,
                                                                                'manager' => CAP_ALLOW)));
