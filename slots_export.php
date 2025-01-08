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
 * slots_export.php
 *
 * @package   mod_organizer
 * @author    Ramon
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../config.php');
require_once('locallib.php');

/**
 * Exports an iCalendar (ICS) file for a given slot with activity details.
 *
 * @param stdClass $slot An object containing details of the slot, including start time, duration,
 *                       location, comments, and notification time.
 * @param string $activityname The name of the activity to be used as the event summary.
 * @param string $activitydescription The description of the activity to be included in the ICS file.
 *
 * @return void This function outputs an ICS file to the browser and exits the script.
 */
function export_ics_file($slots, $activityname, $activitydescription) {
    global $CFG;
    require_once($CFG->libdir . '/bennu/bennu.class.php');

    // Set the timezone to the web server's default timezone.
    core_date::set_default_server_timezone();

    // Set security headers.
    header('Content-Type: text/calendar; charset=utf-8');

    // Start ICS data.
    $icscontent = "BEGIN:VCALENDAR\r\n";
    $icscontent .= "VERSION:2.0\r\n";
    $icscontent .= "PRODID:-//Moodle mod/organizer Slot Export//EN\r\n";

    $headertitle = false;
    foreach ($slots as $slot) {
        // Convert start time and end time with Bennu.
        $starttime = Bennu::timestamp_to_datetime($slot->starttime);
        $endtime = Bennu::timestamp_to_datetime($slot->starttime + $slot->duration);

        // Write headertitle with startdate of first slot.
        if (!$headertitle) {
            header('Content-Disposition: attachment; filename="' . $activityname . " - " . $starttime . '.ics"');
            $headertitle = true;
        }
        // Create a single event.
        $icscontent .= "BEGIN:VEVENT\r\n";

        // UID for the event (unique for calendar applications).
        $uid = uniqid('moodle-organizer-slot-', true) . "@" . parse_url($CFG->wwwroot)['host'];
        $icscontent .= "UID:$uid\r\n";

        // Set start and end time.
        $icscontent .= "DTSTART:$starttime\r\n";
        $icscontent .= "DTEND:$endtime\r\n";

        // Summary of the event.
        $summary = isset($activityname) ? $activityname : get_string('timeslot', 'mod_organizer');
        $icscontent .= "SUMMARY:" . escape_ical_text($summary) . "\r\n";

        // Add description and comments.
        $description = '';
        if (!empty($activitydescription)) {
            $description = convert_to_ical_description($activitydescription);
        }
        if (!empty($slot->comments)) {
            $description .= " " . escape_ical_text($slot->comments);
        }
        if (!empty($description)) {
            $icscontent .= "DESCRIPTION:$description\r\n";
        }

        // Add location and locationlink.
        if (!empty($slot->location)) {
            $location = escape_ical_text($slot->location);
        }
        if (!empty($slot->locationlink)) {
            $location .= " " . escape_ical_text($slot->locationlink);
        }
        if (!empty($location)) {
            $icscontent .= "LOCATION:$location\r\n";
        }

        // Add reminder.
        if (!empty($slot->notificationtime)) {
            $notificationtime = escape_ical_text($slot->notificationtime);
            $icscontent .= "BEGIN:VALARM\r\nTRIGGER:-PT" . $notificationtime .
                "S\r\nACTION:DISPLAY\r\nDESCRIPTION:Reminder\r\nEND:VALARM\r\n";
        }

        // Add timestamp.
        $timestamp = gmdate('Ymd\THis\Z');
        $icscontent .= "DTSTAMP:$timestamp\r\n";

        // End the event.
        $icscontent .= "END:VEVENT\r\n";

    }

    // Close the calendar.
    $icscontent .= "END:VCALENDAR\r\n";

    // Output ICS data.
    echo $icscontent;
    exit;
}

/**
 * Escapes special characters in text to make it compatible with iCalendar specifications.
 *
 * This function replaces actual newlines with the iCalendar newline character (\n)
 * and escapes special characters such as commas, semicolons, and backslashes.
 *
 * @param string $text The text to be escaped for iCalendar compatibility.
 *
 * @return string The escaped text.
 */
function escape_ical_text($text) {
    // Replace actual newlines with the iCalendar \n.
    $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);

    // Escape other special characters.
    return addcslashes($text, ",;\\");
}

/**
 * Converts an HTML input to a sanitized, escaped description suitable for an iCalendar entry.
 *
 * This function ensures the input HTML is properly encoded, sanitizes it,
 * extracts its text content, and escapes special characters according to
 * the iCalendar specification. Additionally, it processes hyperlinks in the
 * HTML, formatting them in an iCalendar-compatible format.
 *
 * @param string $html The HTML content to be converted and escaped.
 *
 * @return string The sanitized and escaped iCalendar-compliant description.
 */
function convert_to_ical_description($html) {
    // Ensure the HTML is in UTF-8 encoding.
    $html = html_entity_decode($html, ENT_QUOTES, 'UTF-8');

    // Load the HTML into DOMDocument.
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings for malformed HTML.
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Extract text content and decode HTML entities.
    $text = $dom->textContent;
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // Escape special characters for iCalendar.
    $escapedtext = str_replace(
        ["\\", ",", ";", "\n"], // Characters to escape.
        ["\\\\", "\\,", "\\;", "\\n"], // Escaped equivalents.
        $text
    );

    // Handle links separately: Replace links in HTML with iCalendar-friendly "<URL>" syntax.
    foreach ($dom->getElementsByTagName('a') as $link) {
        $url = $link->getAttribute('href');
        $textcontent = $link->textContent;

        // Format the link as "Link Text <URL>".
        $formattedlink = $textcontent . " <" . str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $url) . ">";

        // Replace the text content of the link in the escaped text with the formatted link.
        $escapedtext = str_replace($textcontent, $formattedlink, $escapedtext);
    }

    // Replace bullet points and indentation for structured content.
    $escapedtext = preg_replace("/^\*\s*/m", "*\t", $escapedtext);

    // Remove all newlines and extra spaces to produce a single line.
    $escapedtext = preg_replace('/\s+/', ' ', $escapedtext);

    // Return the final iCalendar-compliant description.
    return $escapedtext;
}

// Get course module, course, organizer, and context data.
[$cm, $course, $organizer, $context] = organizer_get_course_module_data();
require_login($course, false, $cm);

// Fetch activity data.
$activity = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
$activitydescription = format_module_intro('organizer', $activity, $cm->id);

// Get the slots.
$slots = optional_param('slots', null, PARAM_TEXT);
$arrslots = explode(',', $slots);
[$slotselect, $slotparams] = $DB->get_in_or_equal($arrslots, SQL_PARAMS_NAMED);
$rslots = $DB->get_records_select('organizer_slots', 'id ' . $slotselect, $slotparams);

// Export the ICS file for the slot.
export_ics_file($rslots, $cm->name, $activitydescription);
