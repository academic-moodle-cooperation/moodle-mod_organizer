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
 * lang/en/organizer.php
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

$string['modulename'] = 'Organizer';
$string['modulenameplural'] = 'Organizers';
$string['modulename_help'] = 'Organizers enable the teacher to make appointments with students by creating time slots which students can register to.';
$string['organizername'] = 'Organizer name';
$string['organizer'] = 'Organizer';
$string['pluginadministration'] = 'Organizer administration';
$string['pluginname'] = 'Organizer';
$string['isgrouporganizer'] = 'Group appointments';
$string['isgrouporganizer_help'] = "Check this if you want this organizer to deal with groups instead of individual users. Note that, if left unchecked, the organizer still allows more users to attend the same appointment.";
$string['appointmentdatetime'] = 'Date & time';
$string['multipleappointmentstartdate'] = 'Start date';
$string['multipleappointmentenddate'] = 'End date';
$string['appointmentcomments'] = 'Comments';
$string['appointmentcomments_help'] = 'Additional information about the appointments can be added here.';
$string['duration'] = 'Duration';
$string['duration_help'] = 'Defines the duration of the appointments. All defined time frames will be divided into slots of the duration defined here. Any remaining time will remain unused (i.e. if the time frame is 40 min long and the duration is set to 15 min, there will be 2 slots in total with 10 unused minutes extra).';
$string['gap'] = 'Gap';
$string['gap_help'] = 'Defines the gap between two appointments.';
$string['location'] = 'Location';
$string['location_help'] = 'Type the name of the location the appointments will take place at';
$string['introeditor_error'] = 'Organizer description must be given!';
$string['allowsubmissionsfromdate'] = 'Registration start';
$string['allowsubmissionsfromdate_help'] = "Check this if you want to make this organizer available to students after a certain point in time.";
$string['allowsubmissionsfromdatesummary'] = 'This organizer will accept registrations from <strong>{$a}</strong>';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The organizer details and registration form will be available from <strong>{$a}</strong>';
$string['duedate'] = 'Due date';
$string['availability'] = 'Availability';

$string['eventslotviewed'] = 'Appointments tab viewed.';
$string['eventregistrationsviewed'] = 'Registrations tab viewed.';
$string['eventslotcreated'] = 'New slots created.';
$string['eventslotupdated'] = 'Slot updated.';
$string['eventslotdeleted'] = 'Slot deleted.';
$string['eventslotviewed'] = 'Slots viewed.';
$string['eventappointmentadded'] = 'Student registered to a slot.';
$string['eventappointmentcommented'] = 'Appoinement has been commented.';
$string['eventappointmentevaluated'] = 'Appointment has been evaluated.';
$string['eventappointmentremoved'] = 'Student unregistered from a slot.';
$string['eventappointmentremindersent'] = 'Appointment reminder sent.';
$string['eventappointmentlistprinted'] = 'Appoinement list has been printed.';

$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the Assignment Description above will only become visible to students at the "Registration start" date.';
$string['warning_groupingid'] = 'Group mode enabled. You must select a valid grouping.';
$string['addappointment'] = 'Add appointment';
$string['taballapp'] = 'Appointments';
$string['tabstud'] = 'Student view';
$string['maxparticipants'] = 'Max. participants';
$string['maxparticipants_help'] = 'Defines the maximum number of students that can register to these time slots. In case of a group organizer this number is always limited to one.';
$string['emailteachers'] = 'Send email notifications to teachers';
$string['emailteachers_help'] = "Notifications for teachers when a student first registrations for a timeslot
    are normally supressed to avoid spamming. Check this to enable the organizer to send such e-mail notifications
    to teachers. Note that the notifications for unregistering and changing slots are always sent.";
$string['absolutedeadline'] = 'Registration end';
$string['locationlinkenable'] = 'location info autolink';
$string['locationlink'] = 'Location link URL';
$string['locationlink_help'] = 'Type here the full address of the website you want the location link to refer to. This site should at least contain information on how to reach the location. Please type in the full address (including http://)';
$string['absolutedeadline_help'] = "Check this to define the time after which no further student actions are possible.";
$string['relativedeadline'] = 'Relative deadline';
$string['relativedeadline_help'] = "Sets the deadline for the application for a specific slot specified time ahead. The students will not be able to change register or unregister once the deadline has expired.";
$string['grade'] = 'Maximum grade';
$string['grade_help'] = 'Defines the highest amount of points that can be awarded for any appointment that can be graded.';
$string['changegradewarning'] = 'This organizer has graded appointments and changing the grade will not automatically re-calculate existing grades. You must re-grade all existing appointments, if you wish to change the grade.';
$string['groupoptions'] = 'Group settings';
$string['organizercommon'] = 'Organizer settings';
$string['grouppicker'] = 'Group picker';
$string['availablegrouplist'] = 'Available groups';
$string['selectedgrouplist'] = 'Selected groups';
$string['slotperiodstarttime'] = 'Start date';
$string['slotperiodendtime'] = 'End date';
$string['slotperiodheader'] = 'Generate slots for date range';
$string['slotperiodheader_help'] = 'Specify the starting and the ending date to which the daily time frames (section below) will apply.';
$string['slottimeframesheader'] = 'Specific time frames';
$string['slottimeframesheader_help'] = 'This section allows for weekday-based definition of time frames which will be filled with appointment slots with properties specified above. There can be more than one time frame per day. If a time frame on Monday is selected, it will generate time slots for every Monday between the starting and the ending date (inclusive).';
$string['slotdetails'] = 'Slot details';
$string['back'] = 'Back';
$string['teacherid'] = 'Teacher';
$string['teacherid_help'] = 'Select the teacher you want to lead the appointments';
$string['teacher'] = 'Teacher';
$string['otherheader'] = 'Other';
$string['day_0'] = 'Monday';
$string['day_1'] = 'Tuesday';
$string['day_2'] = 'Wednesday';
$string['day_3'] = 'Thursday';
$string['day_4'] = 'Friday';
$string['day_5'] = 'Saturday';
$string['day_6'] = 'Sunday';
$string['day'] = 'day';
$string['hour'] = 'hr';
$string['min'] = 'min';
$string['sec'] = 'sec';
$string['day_pl'] = 'days';
$string['hour_pl'] = 'hrs';
$string['min_pl'] = 'mins';
$string['sec_pl'] = 'secs';
$string['slotfrom'] = ' from';
$string['newslot'] = 'Add another slot';
$string['slotto'] = 'to';
$string['btn_comment'] = 'Edit comment';
$string['confirm_delete'] = 'Delete';
$string['err_enddate'] = 'End date cannot be set before the start date!';
$string['err_startdate'] = 'Start date cannot be set before today\'s date ({$a->now})!';
$string['err_fromto'] = 'End time cannot be set before the start time!';
$string['err_collision'] = 'This frame is in collision with other frames:';
$string['err_location'] = 'You must enter a location!';
$string['err_posint'] = 'You must enter a positive integer!';
$string['err_fullminute'] = 'The duration has to be a full minute.';
$string['err_fullminutegap'] = 'The gap has to be a full minute.';
$string['create'] = 'Create';
$string['availablefrom'] = 'Applications possible from';
$string['availablefrom_help'] = 'Set the timeframe within which students will be allowed to register for these timeslots. Alternatively, check "Starting now" to enable registration immediately.';
$string['err_availablefromearly'] = 'This date cannot be set later than the start date!';
$string['err_availablefromlate'] = 'This date cannot be set later than the end date!';
$string['teachervisible'] = 'Teacher visible';
$string['teachervisible_help'] = 'Check this if you want to allow students to see the teacher associated with the timeslot.';
$string['notificationtime'] = 'Relative appointment reminder';
$string['notificationtime_help'] = 'Defines how far before the registered appointment the student should be reminded of it.';
$string['title_add'] = 'Add new appointment slots';
$string['title_comment'] = 'Edit your comments';
$string['title_delete'] = 'Delete selected time slots';
$string['createsubmit'] = 'Create time slots';
$string['confirm_conflicts'] = 'Are you sure that you want to ignore the collisions and want to create the time slots?';
$string['reviewsubmit'] = 'Review time slots';
$string['edit_submit'] = 'Commit changes';
$string['rewievslotsheader'] = 'Review time slots';
$string['noslots'] = 'No slots for ';
$string['err_noslots'] = 'No slots were selected!';
$string['err_comments'] = 'You must enter a description!';
$string['visibility'] = 'Visibility of members - presetting';
$string['visibility_help'] = 'Definition of the default visibility option with which a new slot will be created.<br/><b>Anonymous:<b/> The members of this slot are always invisible to all.<br/><b>Visible:</b> All members of this slot are always visible to all.<br/><b>Only visible to slot members:</b> Only slot members can see each other.';
$string['remindall_desc'] = 'Send reminders to all students without an appointment';
$string['infobox_showmyslotsonly'] = 'Show my slots only';
$string['th_datetime'] = 'Date & time';
$string['th_duration'] = 'Duration';
$string['th_location'] = 'Location';
$string['th_teacher'] = 'Teacher';
$string['th_participants'] = 'Participants';
$string['th_participant'] = 'Participant';
$string['th_email'] = 'Email';
$string['th_attended'] = 'Att.';
$string['th_grade'] = 'Grade';
$string['th_feedback'] = 'Feedback';
$string['th_details'] = 'Status';
$string['th_idnumber'] = 'ID number';
$string['th_firstname'] = 'First name';
$string['th_lastname'] = 'Last name';
$string['th_actions'] = 'Action';
$string['th_status'] = 'Status';
$string['th_comments'] = 'Comments';
$string['th_appdetails'] = 'Details';
$string['th_group'] = 'Group';
$string['th_datetimedeadline'] = 'Date & time';
$string['th_evaluated'] = 'Eval';
$string['th_status'] = 'Status';
$string['th_groupname'] = 'Group';
$string['teacher_unchanged'] = '-- unchanged --';
$string['warningtext1'] = 'Selected slots contain different values in this field!';
$string['warningtext2'] = 'WARNING! The contents of this field have been changed!';
$string['teacher'] = 'Teacher';
$string['tabstatus'] = 'Registration status';
$string['title_edit'] = 'Edit selected time slots';
$string['select_all_slots'] = 'Select all visible slots';

$string['no_slots'] = 'There are no time slots created in this organizer';
$string['no_due_slots'] = 'All time slots created in this organizer have expired';
$string['no_my_slots'] = 'You have no slots created in this organizer';
$string['no_due_my_slots'] = 'All of your time slots in this organizer have expired';

$string['id'] = 'ID';

$string['cannot_eval'] = 'Cannot evaluate, the student has a ';
$string['eval_link'] = 'new appointment';
$string['title_eval'] = 'Evaluate selected time slots';
$string['eval_attended'] = 'Attended';
$string['eval_grade'] = 'Grade';
$string['eval_feedback'] = 'Feedback';
$string['eval_allow_new_appointments'] = 'Allow reappointment';

$string['evaluate'] = 'Evaluate';
$string['eval_header'] = 'Selected time slots';

$string['collision'] = 'Warning! Collision detected with following event(s):';

$string['btn_add'] = 'Add new slots';
$string['btn_edit'] = 'Edit selected slots';
$string['btn_delete'] = 'Remove selected slots';
$string['btn_eval'] = 'Grade selected slots';
$string['btn_print'] = 'Print selected slots';
$string['printsubmit'] = 'Display printable table';
$string['title_print'] = 'Print slots';

$string['btn_register'] = 'Register';
$string['btn_unregister'] = 'Unregister';
$string['btn_reregister'] = 'Re-register';
$string['btn_queue'] = 'Queue';
$string['btn_unqueue'] = 'Remove from Queue';
$string['btn_reeval'] = 'Re-evaluate';
$string['btn_eval_short'] = 'Grade';
$string['btn_remind'] = 'Send reminder';
$string['btn_save'] = 'Save comment';
$string['btn_send'] = 'Send';

$string['downloadfile'] = 'Download file';
$string['teacherinvisible'] = 'Teacher invisible';

$string['img_title_evaluated'] = 'The slot is evaluated';
$string['img_title_pending'] = 'The slot is pending evaluation';
$string['img_title_no_participants'] = 'The slot had no participants';
$string['img_title_past_deadline'] = 'The slot is past its deadline';
$string['img_title_due'] = 'The slot is due';

$string['no_slots_defined'] = 'There are no appointments slots available at the moment.';
$string['no_slots_defined_teacher'] = 'There are no appointments slots present at the moment. Click <a href="{$a->link}">here</a> to create some now.';

$string['err_availablepastdeadline'] = 'This slot cannot be made available past the scheduler\'s deadline ({$a->deadline})!';
$string['err_isgrouporganizer_app'] = 'Cannot change group mode as there already exist scheduled appointments in this organizer!';

$string['places_taken_pl'] = '{$a->numtakenplaces}/{$a->totalplaces} places taken';
$string['places_taken_sg'] = '{$a->numtakenplaces}/{$a->totalplaces} place taken';
$string['places_inqueue'] = '{$a->inqueue} on waiting list';
$string['places_inqueue_withposition'] = '{$a->queueposition}. position on waiting list';
$string['group_slot_available'] = "Slot available";
$string['group_slot_full'] = "Slot taken";
$string['slot_anonymous'] = "Slot anonymous";
$string['slot_slotvisible'] = "Members only visible if own slot";
$string['slot_visible'] = "Members of slot always visible";

$string['addslots_placesinfo'] = 'This action will create {$a->numplaces} new possible places, making a total of {$a->totalplaces} possible places for {$a->numstudents} students.';
$string['addslots_placesinfo_group'] = 'This action will create {$a->numplaces} new possible places, making a total of {$a->totalplaces} possible places for {$a->numgroups} groups.';

$string['mymoodle_registered'] = '{$a->registered}/{$a->total} students have registered for an appointment';
$string['mymoodle_attended'] = '{$a->attended}/{$a->total} students have completed an appointment';
$string['mymoodle_registered_group'] = '{$a->registered}/{$a->total} groups have registered for an appointment';
$string['mymoodle_attended_group'] = '{$a->attended}/{$a->total} groups have completed an appointment';

$string['mymoodle_registered_short'] = '{$a->registered}/{$a->total} students registered';
$string['mymoodle_attended_short'] = '{$a->attended}/{$a->total} students completed';
$string['mymoodle_registered_group_short'] = '{$a->registered}/{$a->total} groups registered';
$string['mymoodle_attended_group_short'] = '{$a->attended}/{$a->total} groups completed';

$string['mymoodle_next_slot'] = 'Next slot on {$a->date} at {$a->time}';
$string['mymoodle_no_slots'] = 'No upcoming slots';
$string['mymoodle_completed_app'] = 'You completed your appointment on {$a->date} at {$a->time}';
$string['mymoodle_completed_app_group'] = 'Your group {$a->groupname} attended the appointment on at {$a->date} at {$a->time}';
$string['mymoodle_missed_app'] = 'You did not attend the appointment on {$a->date} at {$a->time}';
$string['mymoodle_missed_app_group'] = 'Your group {$a->groupname} did not attend the appointment on {$a->date} at {$a->time}';
$string['mymoodle_organizer_expires'] = 'This organizer expires on {$a->date} at {$a->time}';
$string['mymoodle_pending_app'] = 'Your appointment is pending evaluation';
$string['mymoodle_pending_app_group'] = 'Your group {$a->groupname} appointment is pending evaluation';
$string['mymoodle_upcoming_app'] = 'Your appointment will take place on {$a->date} at {$a->time} at {$a->location}';
$string['mymoodle_upcoming_app_group'] = 'Appointment of your group, {$a->groupname}, will take place on {$a->date} at {$a->time} at {$a->location}';
$string['mymoodle_organizer_expired'] = 'This organizer expired on {$a->date} at {$a->time}. You can no longer use it';
$string['mymoodle_no_reg_slot'] = 'You have not registered to a time slot yet';
$string['mymoodle_no_reg_slot_group'] = 'Your group {$a->groupname} has not registered to a time slot yet';

$string['infobox_title'] = 'Infobox';
$string['infobox_myslot_title'] = 'My slot';
$string['infobox_mycomments_title'] = 'My comments';
$string['infobox_messaging_title'] = 'Messaging options';
$string['infobox_deadlines_title'] = 'Deadlines';
$string['infobox_legend_title'] = 'Legend';
$string['infobox_organizer_expires'] = 'This organizer will expire on {$a->date} at {$a->time}.';
$string['infobox_organizer_never_expires'] = 'This organizer doesn\'t expire.';
$string['infobox_organizer_expired'] = 'This organizer expired on {$a->date} at {$a->time}';
$string['infobox_deadline_countdown'] = 'Time left to deadline: {$a->days} days, {$a->hours} hours, {$a->minutes} minutes, {$a->seconds} seconds';
$string['infobox_deadline_passed'] = 'Registration deadline passed. You can no longer change registrations.';
$string['infobox_app_countdown'] = 'Time left to the appointment: {$a->days} days, {$a->hours} hours, {$a->minutes} minutes, {$a->seconds} seconds';
$string['infobox_app_occured'] = 'The appointment has already occurred.';
$string['infobox_feedback_title'] = 'Feedback';
$string['infobox_group'] = 'My group: {$a->groupname}';
$string['infobox_deadlines_title'] = 'Deadlines';

$string['fullname_template'] = '{$a->firstname} {$a->lastname}';

$string['infobox_showfreeslots'] = 'Show free slots only';
$string['infobox_showslots'] = 'Show past time slots';
$string['infobox_showlegend'] = 'Show legend';
$string['infobox_hidelegend'] = 'Hide legend';
$string['infobox_slotoverview_title'] = 'Slot overview';
$string['infobox_description_title'] = 'Organizer description';
$string['infobox_messages_title'] = 'System messages';
$string['message_warning_no_slots_selected'] = 'You must select at least one slot first!';
$string['message_info_slots_added_sg'] = '{$a->count} new slot was added.';
$string['message_info_slots_added_pl'] = '{$a->count} new slots were added.';
$string['message_warning_no_slots_added'] = 'No new slots were added!';
$string['message_info_slots_deleted'] = 'The following slots were deleted:<br/>
{$a->deleted} slots deleted.<br/>
{$a->notified} users have been notified.';
$string['message_info_slots_deleted_group'] = 'The following slots were deleted:<br/>
{$a->deleted} slots deleted.<br/>
{$a->notified} users have been notified.';

$string['message_autogenerated2'] = 'Auto-generated message';
$string['message_custommessage'] = 'Custom message';
$string['message_custommessage_help'] = 'Use this field to enter a personal text into the auto-generated message.';

$string['message_info_available'] = 'There are {$a->freeslots} empty slots available for {$a->notregistered} users without any appointment.';
$string['message_info_available_group'] = 'There are {$a->freeslots} empty slots available for {$a->notregistered} groups without any appointment.';

$string['message_warning_available'] = '<span style="color:red;">Warning</span> There are {$a->freeslots} empty slots available for {$a->notregistered} users without any appointment.';
$string['message_warning_available_group'] = '<span style="color:red;">Warning</span> There are {$a->freeslots} empty slots available for {$a->notregistered} groups without any appointment.';

$string['grouporganizer_desc_nogroup'] = 'This is a group organizer. Students can register their groups to appointments. All group members may change and comment the registration.';
$string['grouporganizer_desc_hasgroup'] = 'This is a group organizer. Clicking the register button will register you and all members of your group {$a->groupname} to this slot. All group members may change and comment the registration.';
$string['status_no_entries'] = 'This organizer has no registered students.';
$string['infobox_link'] = 'Show/Hide';
$string['eval_not_occured'] = 'This slot has not occurred yet';
$string['eval_no_participants'] = 'This slot had no participants';

$string['infobox_myslot_noslot'] = 'You are not registered to any slot at the time.';
$string['resetorganizerall'] = 'Clear all organizer data (slots & appointments)';
$string['deleteorganizergrades'] = 'Delete grades from gradebook';

$string['configintro'] = 'The values you set here define the default values that are used in the settings form when you create a new organizer.';
$string['requiremodintro'] = 'Require activity description';
$string['configrequiremodintro'] = 'Disable this option if you do not want to force users to enter description of each activity.';
$string['configmaximumgrade'] = 'Sets the default value selected in the grade field when creating a new organizer. This is the maximum grade assignable to a student for his appointment.';
$string['configabsolutedeadline'] = 'The default offset of the date and time selector from the current date and time.';
$string['configrelativedeadline'] = 'The default time ahead of an appointment when the participants should be notified of it.';
$string['configdigest'] = 'Send summary of the next day appointments to the teacher.';
$string['configemailteachers'] = 'Send E-mail notifications to teachers about registration status changes.';
$string['configlocationlink'] = 'The link to a search engine used to show the way to the location. Place $searchstring in the URL where the query goes.';
$string['confignever'] = 'Never';
$string['configdontsend'] = 'Don\'t send';
$string['configminute'] = 'minute';
$string['configminutes'] = 'minutes';
$string['confighour'] = 'hour';
$string['confighours'] = 'hours';
$string['configday'] = 'day';
$string['configdays'] = 'day';
$string['configweek'] = 'week';
$string['configweeks'] = 'weeks';
$string['configmonth'] = 'month';
$string['configmonths'] = 'months';
$string['configyear'] = 'year';
$string['configahead'] = 'ahead';

$string['configemailteachers_label'] = 'Send E-mail notifications to teachers';
$string['configdigest_label'] = 'Send appointment digest to teachers';

$string['organizer:addinstance'] = 'Add a new organizer';
$string['organizer:comment'] = 'Add comments';
$string['organizer:addslots'] = 'Add new time slots';
$string['organizer:leadslots'] = 'Lead time slots';
$string['organizer:editslots'] = 'Edit existing time slots';
$string['organizer:evalslots'] = 'Grade completed time slots';
$string['organizer:deleteslots'] = 'Delete existing time slots';
$string['organizer:sendreminders'] = 'Send registration reminders to students';
$string['organizer:printslots'] = 'Print existing time slots';
$string['organizer:receivemessagesstudent'] = 'Receive messages as sent to students';
$string['organizer:receivemessagesteacher'] = 'Receive messages as sent to teachers';
$string['organizer:register'] = 'Register to a time slot';
$string['organizer:unregister'] = 'Unregister from a time slot';
$string['organizer:viewallslots'] = 'View all time slots as a teacher';
$string['organizer:viewmyslots'] = 'View own time slots as a teacher';
$string['organizer:viewstudentview'] = 'View all time slots as a student';
$string['organizer:viewregistrations'] = 'View status of student registrations';

$string['messageprovider:test'] = 'Organizer Test Message';
$string['messageprovider:appointment_reminder:student'] = 'Organizer appointment reminder';
$string['messageprovider:appointment_reminder:teacher'] = 'Organizer appointment reminder (Teacher)';
$string['messageprovider:manual_reminder:student'] = 'Organizer manual appointment reminder';
$string['messageprovider:eval_notify:student'] = 'Organizer evaluation notification';
$string['messageprovider:register_notify:teacher'] = 'Organizer registration notification';
$string['messageprovider:group_registration_notify:student'] = 'Organizer groupregistration notification';
$string['messageprovider:register_reminder:student'] = 'Organizer registration reminder';
$string['messageprovider:edit_notify:student'] = 'Organizer changes';
$string['messageprovider:edit_notify:teacher'] = 'Organizer changes (Teacher)';
$string['messageprovider:slotdeleted_notify:student'] = 'Organizer slots cancled';

$string['reg_status_organizer_expired'] = 'Organizer expired';
$string['reg_status_slot_expired'] = 'Slot expired';
$string['reg_status_slot_pending'] = 'Slot pending evaluation';
$string['reg_status_slot_not_attended'] = 'Did not attend';
$string['reg_status_slot_attended'] = 'Attended';
$string['reg_status_slot_not_attended_reapp'] = 'Did not attend, reappointment allowed';
$string['reg_status_slot_attended_reapp'] = 'Attended, reappointment allowed';
$string['reg_status_slot_past_deadline'] = 'Slot past deadline';
$string['reg_status_slot_full'] = 'Slot full';
$string['reg_status_slot_available'] = 'Slot available';
$string['reg_status_registered'] = 'Registered';
$string['reg_status_not_registered'] = 'Not registered';

$string['legend_evaluated'] = 'Slot evaluated';
$string['legend_pending'] = 'Slot pending evaluation';
$string['legend_no_participants'] = 'Slot had no participants';
$string['legend_past_deadline'] = 'Slot past deadline';
$string['legend_due'] = 'Slot due';
$string['legend_organizer_expired'] = 'Organizer expired';
$string['legend_anonymous'] = 'Anonymous slot';

$string['legend_group_applicant'] = 'Applicant of the group';
$string['legend_comments'] = 'Student/Teacher comments';
$string['legend_feedback'] = 'Teacher feedback';
$string['legend_not_occured'] = 'Appointment has not occured yet';

$string['legend_section_status'] = 'Status icons';
$string['legend_section_details'] = 'Slot details icons';

$string['reg_status'] = 'Registration status';

$string['recipientname'] = '&lt;recipient name&gt;';

$string['modformwarningsingular'] = 'This field cannot be edited as there are appointments already made in this organizer!';
$string['modformwarningplural'] = 'These fields cannot be edited as there are appointments already made in this organizer!';

/* Message templates following.
 * Please note that the following strings are available:
 *     sendername, receivername, courseshortname, courselongname, courseid, organizername,
 *     date, time, location, groupname, courselink
 * If more strings are required, add them to the $strings object in messaging.php
 */

// slotdeleted student

$string['slotdeleted_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment cancled';
$string['slotdeleted_notify:student:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was cancelled.
Note that you have no longer an appointment in the organizer {$a->organizername}.
Please follow the link to make a new appointment: {$a->courselink}';
$string['slotdeleted_notify:student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->organizername} was cancled.';

// slotdeleted student group

$string['slotdeleted_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment cancled';
$string['slotdeleted_notify:student:group:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was cancelled.
Note that you have no longer an appointment in the organizer {$a->organizername}.
Please follow the link to make a new appointment: {$a->courselink}';
$string['slotdeleted_notify:student:group:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->organizername} was cancled.';

// register teacher register

$string['register_notify:teacher:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student registered';
$string['register_notify:teacher:register:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has registered for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:register:smallmessage'] = 'Student {$a->sendername} has registered for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher queue

$string['register_notify:teacher:queue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student queued';
$string['register_notify:teacher:queue:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has queued for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:queue:smallmessage'] = 'Student {$a->sendername} has queued for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher reregister

$string['register_notify:teacher:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student re-registered';
$string['register_notify:teacher:reregister:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has re-registered for the new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:reregister:smallmessage'] = 'Student {$a->sendername} has re-registered for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher unregister

$string['register_notify:teacher:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student unregistered';
$string['register_notify:teacher:unregister:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has unregistered from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:unregister:smallmessage'] = 'Student {$a->sendername} has unregistered from the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher unqueue

$string['register_notify:teacher:unqueue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student removed from waiting list';
$string['register_notify:teacher:unqueue:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has took himself off the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:unqueue:smallmessage'] = 'Student {$a->sendername} has took himself off the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher register group

$string['register_notify:teacher:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['register_notify:teacher:register:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:register:group:smallmessage'] = 'Student {$a->sendername} has registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher queue group

$string['register_notify:teacher:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group wait-listed';
$string['register_notify:teacher:queue:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has wait-listed the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:queue:group:smallmessage'] = 'Student {$a->sendername} has wait-listed the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher reregister group

$string['register_notify:teacher:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['register_notify:teacher:reregister:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has re-registered the group {$a->groupname} for the new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:reregister:group:smallmessage'] = 'Student {$a->sendername} has re-registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher unregister group

$string['register_notify:teacher:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['register_notify:teacher:unregister:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has unregistered the group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:unregister:group:smallmessage'] = 'Student {$a->sendername} has unregistered the group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.';

// register teacher unqueue group

$string['register_notify:teacher:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group removed from waiting list';
$string['register_notify:teacher:unqueue:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has removed the group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify:teacher:unqueue:group:smallmessage'] = 'Student {$a->sendername} has removed the group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.';

// eval student

$string['eval_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify:student:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Moodle Messaging System';

$string['eval_notify:student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';

// eval newappointment student

$string['eval_notify_newappointment:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';

$string['eval_notify_newappointment:student:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Teachers of the course enable you to re-register to any available slot in the organizer {$a->organizername}.

Moodle Messaging System';

$string['eval_notify_newappointment:student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';

// eval student group

$string['eval_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify:student:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Moodle Messaging System';
$string['eval_notify:student:group:smallmessage'] = 'Your group appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';

// eval newappointment student group

$string['eval_notify_newappointment:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify_newappointment:student:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Teachers of the course enable you to re-register to any available slot in the organizer {$a->coursefullname}.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:smallmessage'] = 'Your group appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';

// edit student

$string['edit_notify:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify:student:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the appointment with {$a->sendername} on {$a->date} at {$a->time} have been changed.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:student:smallmessage'] = 'The details of the appointment with {$a->sendername} on {$a->date} at {$a->time} have been changed.';

// edit student group

$string['edit_notify:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify:student:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the group appointment with {$a->sendername} on {$a->date} at {$a->time} have been changed.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:student:group:smallmessage'] = 'The details of the group appointment with {$a->sendername} on {$a->date} at {$a->time} have been changed.';

// edit teacher

$string['edit_notify:teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify:teacher:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:teacher:smallmessage'] = 'The details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.';

// edit teacher group

$string['edit_notify:teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify:teacher:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify:teacher:group:smallmessage'] = 'The details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.';

// register reminder student

$string['register_reminder:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Registration reminder';
$string['register_reminder:student:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you either still haven\'t registered to any time slot, or you\'ve missed the one you did register to.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:student:smallmessage'] = 'Please register to a new time slot.';

// register reminder ??? group

$string['register_reminder:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Registration reminder';
$string['register_reminder:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group {$a->groupname} either still hasn\'t registered to any time slot, or you\'ve missed the one you did register to.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:group:smallmessage'] = 'Please register your group to a new time slot.';

// register reminder student group

$string['register_reminder:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Registration reminder';
$string['register_reminder:student:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group {$a->groupname} either still hasn\'t registered to any time slot, or you\'ve missed the one you did register to.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder:student:group:smallmessage'] = 'Please register your group to a new time slot.';

// group registration student register group

$string['group_registration_notify:student:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['group_registration_notify:student:register:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:smallmessage'] = '{$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time}.';

// group registration student queue group

$string['group_registration_notify:student:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group wait-listed';
$string['group_registration_notify:student:queue:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has added your group {$a->groupname} to the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:queue:group:smallmessage'] = '{$a->sendername} has added your group {$a->groupname} to the waiting list of the time slot on {$a->date} at {$a->time}.';

// group registration student reregister group

$string['group_registration_notify:student:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['group_registration_notify:student:reregister:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:smallmessage'] = '{$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time}.';

// group registration student unregister group

$string['group_registration_notify:student:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['group_registration_notify:student:unregister:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:smallmessage'] = '{$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time}.';

// group registration student unqueue group

$string['group_registration_notify:student:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group removed from waiting list';
$string['group_registration_notify:student:unqueue:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has removed your group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unqueue:group:smallmessage'] = '{$a->sendername} has removed your group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time}.';

// group registration student register

$string['group_registration_notify:student:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['group_registration_notify:student:register:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:register:smallmessage'] = '{$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time}.';

// group registration student reregister

$string['group_registration_notify:student:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['group_registration_notify:student:reregister:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:smallmessage'] = '{$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time}.';

// group registration student unregister

$string['group_registration_notify:student:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['group_registration_notify:student:unregister:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:smallmessage'] = '{$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time}.';

// appointment reminder teacher

$string['appointment_reminder:teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment reminder';
$string['appointment_reminder:teacher:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have an appointment with students on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:teacher:smallmessage'] = 'You have an appointment with students on {$a->date} at {$a->time} in {$a->location}.';

// appointment reminder teacher digest

$string['appointment_reminder:teacher:digest:subject'] = 'Appointment digest';
$string['appointment_reminder:teacher:digest:fullmessage'] =
'Hello {$a->receivername}!

Tomorrow you have the following appointments:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder:teacher:digest:smallmessage'] = 'You have received a digest message of your appointments tomorrow.';

// appointment reminder teacher group digest

$string['appointment_reminder:teacher:group:digest:subject'] = 'Appointment digest';
$string['appointment_reminder:teacher:group:digest:fullmessage'] =
'Hello {$a->receivername}!

Tomorrow you have the following appointments:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder:teacher:group:digest:smallmessage'] = 'You have received a digest message of your appointments tomorrow.';

// appointment reminder student

$string['appointment_reminder:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment reminder';
$string['appointment_reminder:student:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have an appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:student:smallmessage'] = 'You have an appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.';

// appointment reminder student group

$string['appointment_reminder:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group appointment reminder';
$string['appointment_reminder:student:group:fullmessage'] =
'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have a group appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder:student:group:smallmessage'] = 'You have a group appointment with {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.';

$string['teachercomment_title'] = 'Teacher comments';
$string['studentcomment_title'] = 'Student comments';
$string['teacherfeedback_title'] = 'Teacher feedback';
$string['relative_deadline_before'] = 'before the appointment';
$string['relative_deadline_now'] = 'Starting now';

$string['nogroup'] = 'No group';
$string['noparticipants'] = 'No participants';
$string['unavailableslot'] = 'This slot is available from';
$string['applicant'] = 'This is the person that registered the group';

$string['deleteheader'] = 'Deleting following slots:';
$string['deletenoslots'] = 'No deletable slots selected';
$string['deletekeep'] = 'The following appointments will be cancelled. Registered students will be notified and the slots will be deleted:';

$string['atlocation'] = 'at';

$string['exportsettings'] = 'Export settings';
$string['format'] = 'Format';
$string['format_pdf'] = 'PDF';
$string['format_xls'] = 'XLS';
$string['format_xlsx'] = 'XLSX';
$string['format_ods'] = 'ODS';
$string['format_csv_tab'] = 'CSV (tab)';
$string['format_csv_comma'] = 'CSV (;)';
$string['pdfsettings'] = 'PDF settings';
$string['numentries'] = 'Entries shown per page';
$string['numentries_help'] = 'Choose "optimal" to optimize the distribution of list entries according to the chosen textsize and page orientation, if there are plenty of participants registered in your course';
$string['stroptimal'] = 'optimal';
$string['textsize'] = 'Textsize';
$string['pageorientation'] = 'Page orientation';
$string['orientationportrait'] = 'portrait';
$string['orientationlandscape'] = 'landscape';
$string['pdf_notactive'] = 'not active';

$string['headerfooter'] = 'Print header/footer';
$string['headerfooter_help'] = 'Print header/footer if checked';
$string['printpreview'] = 'Print preview (first 10 entries)';

$string['datapreviewtitle'] = "Data Preview";
$string['datapreviewtitle_help'] = "Click on [+] or [-] for showing or hiding columns in the print-preview.";

$string['unknown'] = 'Unknown';

$string['totalslots'] = 'from {$a->starttime} to {$a->endtime}, {$a->duration} {$a->unit} each, {$a->totalslots} slot(s) in total';

$string['warninggroupmode'] = 'You must enable the group mode and select a grouping to create a group organizer!';

$string['multimember'] = 'Users cannot belong to multiple groups within the same grouping!';
$string['multimemberspecific'] = 'User {$a->username} {$a->idnumber} is registered in more than one group! ({$a->groups})';
$string['groupwarning'] = 'Check the group options below!';

$string['duedateerror'] = 'Absolute deadline cannot be set before the availability date!';
$string['invalidgrouping'] = 'You must select a valid grouping!';

$string['maillink'] = 'The organizer is available <a href="{$a}">here</a>.';

// Section.

$string['eventnoparticipants'] = 'no participants';
$string['eventteacheranonymous'] = 'an anonymous teacher';

$string['eventwith'] = 'with';
$string['eventwithout'] = 'with';

$string['eventappwith:single'] = 'Appointment';
$string['eventappwith:group'] = 'Group appointment';

$string['eventtitle'] = '{$a->coursename} / {$a->organizername}: {$a->appwith}';

$string['eventtemplate'] = '{$a->courselink} / {$a->organizerlink}: {$a->appwith} {$a->with} {$a->participants}<br />Location: {$a->location}<br />';
$string['eventtemplatecomment'] = 'Comment:<br />{$a}<br />';

$string['fulldatetimelongtemplate'] = '%A %d. %B %Y %H:%M';
$string['fulldatetimetemplate'] = '%a %d.%m.%Y %H:%M';
$string['fulldatelongtemplate'] = '%A %d. %B %Y';
$string['fulldatetemplate'] = '%a %d.%m.%Y';
$string['datetemplate'] = '%d.%m.%Y';
$string['timetemplate'] = '%H:%M';

$string['font_small'] = 'small';
$string['font_medium'] = 'medium';
$string['font_large'] = 'large';

$string['printout'] = 'Printout';
$string['confirm_organizer_remind_all'] = 'Send';

$string['message_info_reminders_sent_sg'] = '{$a->count} reminder was sent.';
$string['message_info_reminders_sent_pl'] = '{$a->count} reminders were sent.';

$string['organizer_remind_all_recepients_sg'] = 'A total of {$a->count} message will be sent to the following recepients:';
$string['organizer_remind_all_recepients_pl'] = 'A total of {$a->count} messages will be sent to the following recepients:';

$string['organizer_remind_all_no_recepients'] = 'There are no valid recepients.';

$string['print_return'] = 'Return to slot overview';

$string['message_error_slot_full_single'] = 'This timeslot has no free places left!';
$string['message_error_slot_full_group'] = 'This timeslot is already taken!';

$string['message_error_unknown_unqueue'] = 'Your waiting list entry could not be removed! Unknown error.';
$string['message_error_unknown_unregister'] = 'Your registration could not be removed! Unknown error.';

$string['organizer_remind_all_title'] = 'Send reminders';
$string['can_reregister'] = 'You can re-registed to another appointment.';

$string['messages_none'] = 'No registration notifications';
$string['messages_re_unreg'] = 'Re-registrations and unregistrations only';
$string['messages_all'] = 'All registration, re-registrations and unregistrations';

$string['reset_organizer_all'] = 'Deleting slots, appointments and related events';
$string['delete_organizer_grades'] = 'Deleting grades of all organizers';
$string['timeshift'] = 'Shifting absolute deadline';

$string['queue'] = 'Waiting queues';
$string['queue_help'] = 'Waiting queues allow users to register to a time slot even if the maximum number of participants is already reached.
		Users are added to a waiting queue and assigned to the slot (in order) as soon as a slot becomes available.';
$string['queuesubject'] = 'Moodle Organizer: Promoted from queue';
$string['queuebody'] = 'Your registration for a timeslot has been promoted from status "waiting list" to status "booked".';
$string['eventqueueadded'] = 'Added to waiting list';
$string['eventqueueremoved'] = 'Removed from waiting list';

$string['visibility_anonymous'] = 'Anonymous';
$string['visibility_slot'] = 'Only visible to slot members';
$string['visibility_all'] = 'Visible';

$string['hidecalendar'] = 'Hide calendar';
$string['hidecalendar_help'] = 'Check to hide the calendar in this organizer';
