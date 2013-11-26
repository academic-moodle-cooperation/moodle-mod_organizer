<?php
//tscpr: adapt file header "This file is made for Moodle" + doctype-filecomment (with description, author, copyright, etc.)
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

$messageproviders = array(
        'register_notify:teacher' => array('capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'appointment_reminder:teacher' => array('capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'slotdeleted_notify:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                		'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),         
        'appointment_reminder:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'register_reminder:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'eval_notify:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'group_registration_notify:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'edit_notify:student' => array('capability' => 'mod/organizer:receivemessagesstudent',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
        'edit_notify:teacher' => array('capability' => 'mod/organizer:receivemessagesteacher',
                'defaults' => array('popup' => MESSAGE_PERMITTED + MESSAGE_DEFAULT_LOGGEDIN, 'email' => MESSAGE_FORCED)),
);
