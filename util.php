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
 * util.php
 *
 * @package       mod_organizer
 * @author        Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author        Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author        Andreas Windbichler
 * @author        Ivan Šakić
 * @copyright     2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license       http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('ORGANIZER_APP_STATUS_EVALUATED', 1);
define('ORGANIZER_APP_STATUS_PENDING', 2);
define('ORGANIZER_APP_STATUS_ATTENDED', 4);
define('ORGANIZER_APP_STATUS_REAPPOINTMENT_ALLOWED', 8);

define('ORGANIZER_APP_STATUS_INVALID', 0);
define('ORGANIZER_APP_STATUS_ATTENDED_REAPP', ORGANIZER_APP_STATUS_ATTENDED & ORGANIZER_APP_STATUS_REAPPOINTMENT_ALLOWED);
define('ORGANIZER_APP_STATUS_REGISTERED', ORGANIZER_APP_STATUS_PENDING);
define('ORGANIZER_APP_STATUS_NOT_ATTENDED', 4);
define('ORGANIZER_APP_STATUS_NOT_ATTENDED_REAPP', 5);
define('ORGANIZER_APP_STATUS_NOT_REGISTERED', 6);

function organizer_get_appointment_status($app) {
    global $DB;

    if (is_number($app) && $app == intval($app)) {
        $app = $DB->get_record('organizer_slot_appointmentss', array('id' => $app));
    }
    if (!$app) {
        return 0;
    }

    $slot = $DB->get_record('organizer_slots', array('id' => $app->slotid));

    $evaluated = isset($app->attended);
    $attended = $evaluated && ((int)$app->attended === 1);
    $pending = !$evaluated && $slot->stattime < time();
    $reapp = $app->allownewappointments;

    return ($evaluated) & ($attended << 1) & ($pending << 2) & ($reapp << 3);
}

function organizer_check_appointment_status($app, $status) {
    return $status & organizer_get_appointment_status($app);
}