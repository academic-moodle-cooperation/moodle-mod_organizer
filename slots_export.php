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

require_once('../../config.php');
require_once('locallib.php');

function export_ics_file($slot, $activityname, $activitydescription) {
    global $CFG;
    require_once($CFG->libdir . '/bennu/bennu.class.php');

    // Set the timezone to the web server's default timezone.
    core_date::set_default_server_timezone();

    // Convert start time and end time with Bennu
    $starttime = Bennu::timestamp_to_datetime($slot->starttime);
    $endtime = Bennu::timestamp_to_datetime($slot->starttime + $slot->duration);

    // Set security headers
    header('Content-Type: text/calendar; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $activityname . " - " . $starttime . '.ics"');

    // Start ICS data.
    $icscontent = "BEGIN:VCALENDAR\r\n";
    $icscontent .= "VERSION:2.0\r\n";
    $icscontent .= "PRODID:-//Moodle mod/organizer Slot Export//EN\r\n";

    // Create a single event.
    $icscontent .= "BEGIN:VEVENT\r\n";

    // UID for the event (unique for calendar applications).
    $uid = uniqid('moodle-organizer-slot-', true) . "@" . parse_url($CFG->wwwroot)['host'];
    $icscontent .= "UID:$uid\r\n";

    // Set start and end time.
    $icscontent .= "DTSTART:$starttime\r\n";
    $icscontent .= "DTEND:$endtime\r\n";

    // Summary of the event
    $summary = isset($activityname) ? $activityname : "Moodle Slot";
    $icscontent .= "SUMMARY:" . escape_ical_text($summary) . "\r\n";

    // Add description and comments
    if (!empty($activitydescription)) {
        $description = convert_to_ical_description($activitydescription);
    }
    if (!empty($slot->comments)) {
        $description .= " " . escape_ical_text($slot->comments);
    }
    if (!empty($description)) {
        $icscontent .= "DESCRIPTION:$description\r\n";
    }

    // Add location and locationlink
    if (!empty($slot->location)) {
        $location = escape_ical_text($slot->location);
    }
    if (!empty($slot->locationlink)) {
        $location .= " " . escape_ical_text($slot->locationlink);
    }
    if (!empty($location)) {
        $icscontent .= "LOCATION:$location\r\n";
    }

    // Add reminder
    if (!empty($slot->notificationtime))
    {
        $notificationtime = escape_ical_text($slot->notificationtime);
        $icscontent .= "BEGIN:VALARM\r\nTRIGGER:-PT" . $notificationtime . "S\r\nACTION:DISPLAY\r\nDESCRIPTION:Reminder\r\nEND:VALARM\r\n";
    }

    // Add timestamp
    $timestamp = gmdate('Ymd\THis\Z');
    $icscontent .= "DTSTAMP:$timestamp\r\n";

    // End the event
    $icscontent .= "END:VEVENT\r\n";

    // Close the calendar
    $icscontent .= "END:VCALENDAR\r\n";

    // Output ICS data
    echo $icscontent;
    exit;
}

function escape_ical_text($text) {
    // Replace actual newlines with the iCalendar \n
    $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);

    // Escape other special characters
    return addcslashes($text, ",;\\");
}

function convert_to_ical_description($html) {
    // Ensure the HTML is in UTF-8 encoding
    $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

    // Load the HTML into DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Extract text content and decode HTML entities
    $text = $dom->textContent;
    $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8');

    // Escape special characters for iCalendar
    $escapedtext = str_replace(
        ["\\", ",", ";", "\n"], // Characters to escape
        ["\\\\", "\\,", "\\;", "\\n"], // Escaped equivalents
        $text
    );

    // Handle links separately: Replace links in HTML with iCalendar-friendly "<URL>" syntax
    foreach ($dom->getElementsByTagName('a') as $link) {
        $url = $link->getAttribute('href');
        $textcontent = $link->textContent;

        // Format the link as "Link Text <URL>"
        $formatted_link = $textcontent . " <" . str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $url) . ">";

        // Replace the text content of the link in the escaped text with the formatted link
        $escapedtext = str_replace($textcontent, $formatted_link, $escapedtext);
    }

    // Replace bullet points and indentation for structured content
    $escapedtext = preg_replace("/^\*\s*/m", "*\t", $escapedtext);

    // Remove all newlines and extra spaces to produce a single line
    $escapedtext = preg_replace('/\s+/', ' ', $escapedtext);

    // Return the final iCalendar-compliant description
    return $escapedtext;
}

// Get course module, course, organizer, and context data.
[$cm, $course, $organizer, $context] = organizer_get_course_module_data();
require_login($course, false, $cm);

// Fetch activity data.
$activity = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
$activitydescription = format_module_intro('organizer', $activity, $cm->id);

// Get the slot.
$slotid = optional_param('slot', null, PARAM_INT);
$slot = $DB->get_record('organizer_slots', ['id' => $slotid], '*', MUST_EXIST);

// Export the ICS file for the slot.
export_ics_file($slot, $cm->name, $activitydescription);
