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
 * db/messages.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$messageproviders = [
        'register_notify_teacher' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_notify_teacher_register' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_notify_teacher_reregister' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_notify_teacher_unregister' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_notify_teacher_queue' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_notify_teacher_unqueue' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'appointment_reminder_teacher' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'slotdeleted_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'appointment_reminder_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_reminder_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'eval_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'group_registration_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'edit_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'edit_notify_teacher' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'assign_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'assign_notify_teacher' => ['capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'register_promotion_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
        'appointmentdeleted_notify_student' => ['capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => ['popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_ENABLED, 'email' => MESSAGE_FORCED]],
];

