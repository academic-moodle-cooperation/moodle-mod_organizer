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
 * view_action.php
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

use mod_organizer\event\slot_deleted;

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once(dirname(__FILE__) . '/view_action_form_delete.php');
require_once(dirname(__FILE__) . '/view_lib.php');
require_once(dirname(__FILE__) . '/messaging.php');

$mode = optional_param('mode', null, PARAM_INT);
$action = optional_param('action', null, PARAM_ALPHANUMEXT);
$slot = optional_param('slot', null, PARAM_INT);
$slots = organizer_get_param_slots();

[$cm, $course, $organizer, $context, $redirecturl] = organizer_slotpages_header();

$params['limitedwidth'] = organizer_get_limitedwidth();

require_login($course, false, $cm);

$logurl = 'view_action.php?id=' . $cm->id . '&mode=' . $mode . '&action=' . $action;

require_capability('mod/organizer:deleteslots', $context);

$infoboxmessage = "";
if (!$slots) {
    $infoboxmessage .= $OUTPUT->notification(get_string('message_warning_no_slots_selected', 'organizer'),
        'error');
    redirect($redirecturl);
}

$mform = new organizer_delete_slots_form(null, ['id' => $cm->id, 'mode' => $mode, 'slots' => $slots]);

if ($data = $mform->get_data()) {
    $a = new stdClass();
    if (isset($slots)) {
        $notified = 0;
        foreach ($slots as $slotid) {
            if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPSLOT ||
                    $organizer->isgrouporganizer == ORGANIZER_GROUPMODE_NEWGROUPBOOKING) {
                organizer_delete_coursegroup(null, $slotid);
            }
            $notified += organizer_delete_appointment_slot($slotid);
        }
        $a->notified = $notified;
        $a->deleted = count($slots);

        $event = slot_deleted::create(['objectid' => $PAGE->cm->id,
                'context' => $PAGE->context]);
        $event->trigger();

        if ($a->deleted == 1) {
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_slots_deleted_sg', 'organizer', $a),
                'success');
        } else {
            $infoboxmessage .= $OUTPUT->notification(get_string('message_info_slots_deleted_pl', 'organizer', $a),
                'success');
        }
    }
    if ($infoboxmessage) {
        $_SESSION["infoboxmessage"] = $infoboxmessage;
    }
    redirect($redirecturl);
} else if ($mform->is_cancelled()) {
    redirect($redirecturl);
} else {
    organizer_display_form($mform, get_string('title_delete', 'organizer'));
}
throw new coding_exception('If you see this, something went wrong with delete action!');

die;
