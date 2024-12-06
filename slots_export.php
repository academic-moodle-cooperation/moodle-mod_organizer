<?php

require_once('../../config.php');
require_once('locallib.php');

function export_ics_file($slot, $activityname, $activitydescription) {
    global $DB, $CFG;
    require_once($CFG->libdir . '/bennu/bennu.class.php');
    
    // Define the timezone
    $timezone = 'Europe/Zurich'; // Adjust the timezone to your region
    $timezoneObject = new DateTimeZone($timezone);

    // Convert start time and end time with Bennu
    $startTime = Bennu::timestamp_to_datetime($slot->starttime);
    $endTime = Bennu::timestamp_to_datetime($slot->starttime + $slot->duration);

    // Set security headers
    header('Content-Type: text/calendar; charset=utf-8');
    //header('Content-Disposition: attachment; filename="slot.ics"');
    header('Content-Disposition: attachment; filename="' . $activityname . " - " . $startTime . '.ics"');

    // Start ICS data
    $icsContent = "BEGIN:VCALENDAR\r\n";
    $icsContent .= "VERSION:2.0\r\n";
    $icsContent .= "PRODID:-//Moodle mod/organizer Slot Export//EN\r\n";

    // Create a single event
    $icsContent .= "BEGIN:VEVENT\r\n";

    // UID for the event (unique for calendar applications)
    $uid = uniqid('moodle-organizer-slot-', true) . "@" . parse_url($CFG->wwwroot)['host'];
    $icsContent .= "UID:$uid\r\n";

    // Set start and end time
    $icsContent .= "DTSTART:$startTime\r\n";
    $icsContent .= "DTEND:$endTime\r\n";

    // Summary of the event
    $summary = isset($activityname) ? $activityname : "Moodle Slot";
    $icsContent .= "SUMMARY:" . escape_ical_text($summary) . "\r\n";

    // Add description and comments
    if (!empty($activitydescription)) {
        $description = convert_to_ical_description($activitydescription);
    }
    if (!empty($slot->comments)) {
        $description .= " " . escape_ical_text($slot->comments);
    }
    if (!empty($description)) {
        $icsContent .= "DESCRIPTION:$description\r\n";
    }

    // Add location and locationlink
    if (!empty($slot->location)) {
        $location = escape_ical_text($slot->location);
    }
    if (!empty($slot->locationlink)) {
        $location .= " " . escape_ical_text($slot->locationlink);
    }
    if (!empty($location)) {
        $icsContent .= "LOCATION:$location\r\n";
    }

    // Add reminder
    if (!empty($slot->notificationtime))
    {
        $notificationtime = escape_ical_text($slot->notificationtime);
        $icsContent .= "BEGIN:VALARM\r\nTRIGGER:-PT" . $notificationtime . "S\r\nACTION:DISPLAY\r\nDESCRIPTION:Reminder\r\nEND:VALARM\r\n";
    }

    // Add timestamp
    $timestamp = gmdate('Ymd\THis\Z');
    $icsContent .= "DTSTAMP:$timestamp\r\n";

    // End the event
    $icsContent .= "END:VEVENT\r\n";

    // Close the calendar
    $icsContent .= "END:VCALENDAR\r\n";

    // Output ICS data
    echo $icsContent;
    exit;
}

function escape_ical_text($text) {
    // Replace actual newlines with the iCalendar \n
    $text = str_replace(["\r\n", "\r", "\n"], "\\n", $text);

    // Escape other special characters
    return addcslashes($text, ",;\\");
}

function convert_to_ical_description($html) {
    // Load the HTML into DOMDocument
    $dom = new DOMDocument();
    libxml_use_internal_errors(true); // Suppress warnings for malformed HTML
    $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    // Extract text content
    $text = $dom->textContent;

    // Escape special characters for iCalendar
    $escaped_text = str_replace(
        ["\\", ",", ";", "\n"], // Characters to escape
        ["\\\\", "\\,", "\\;", "\\n"], // Escaped equivalents
        $text
    );

    // Handle links separately: Replace links in HTML with iCalendar-friendly "<URL>" syntax
    foreach ($dom->getElementsByTagName('a') as $link) {
        $url = $link->getAttribute('href');
        $text_content = $link->textContent;
        $escaped_url = "<" . str_replace(["\\", ",", ";"], ["\\\\", "\\,", "\\;"], $url) . ">";
        $escaped_text = str_replace($text_content, $text_content . " " . $escaped_url, $escaped_text);
    }

    // Replace bullet points and indentation for structured content
    $escaped_text = preg_replace("/^\*\s*/m", "*\t", $escaped_text);

    // Return the final iCalendar-compliant description
    return $escaped_text;
}

// Get course module, course, organizer, and context data
list($cm, $course, $organizer, $context) = organizer_get_course_module_data();
require_login($course, false, $cm);

// Fetch activity data
$activity = $DB->get_record('organizer', ['id' => $cm->instance], '*', MUST_EXIST);
$activity_description = format_module_intro('organizer', $activity, $cm->id);

// Get the slot
$slotid = optional_param('slot', null, PARAM_INT);
$slot = $DB->get_record('organizer_slots', ['id' => $slotid], '*', MUST_EXIST);

// Export the ICS file for the slot
export_ics_file($slot, $cm->name, $activity_description);
