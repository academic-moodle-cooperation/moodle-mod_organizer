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
 * slotsviewoptions.php
 *
 * Saves user changes of the filter options in slots view. Called by ajax.
 *
 * @package   mod_organizer
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @copyright 2022 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../config.php');

require_login();
// Check access.
if (!confirm_sesskey()) {
    throw new moodle_exception('invalidsesskey', 'error');
}

// Get the params.
$slotsviewoptions = required_param('slotsviewoptions', PARAM_ALPHANUM);  // Present slots view options.
$userid = required_param('userid', PARAM_INT);  // The ID of the user.

// Update database entry.

/*
 * Legend of slotsviewoptions string
 *
 * Values: 0 for not active, 1 for active
 *
 * Position 0: show_my_slots_only
 * Position 1: show_free_slots_only
 * Position 2: show_hidden_slots
 * Position 3: show_past_slots
 * Position 4: show_registrations_only
 * Position 5: show_all_participants
 */

set_user_preference('mod_organizer_slotsviewoptions', $slotsviewoptions, $userid);

echo "1";
