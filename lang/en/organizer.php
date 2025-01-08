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
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['absolutedeadline'] = 'Registration end';
$string['absolutedeadline_help'] = 'Check this to define the time after which no further student actions are possible.';
$string['actionlink_delete'] = 'delete';
$string['actionlink_edit'] = 'edit';
$string['actionlink_eval'] = 'grade';
$string['actionlink_print'] = 'print';
$string['actions'] = 'Action';
$string['actions_help'] = 'Action to take.';
$string['addappointment'] = 'Add appointment';
$string['addslots_placesinfo'] = 'This action will create {$a->numplaces} new possible places, making a total of {$a->totalplaces} possible places for {$a->numstudents} students.';
$string['addslots_placesinfo_group'] = 'This action will create {$a->numplaces} new possible places, making a total of {$a->totalplaces} possible places for {$a->numgroups} groups.';
$string['allowcreationofpasttimeslots'] = 'Past time slots creation';
$string['allowedprofilefieldsprint'] = 'Allowed user profile fields';
$string['allowedprofilefieldsprint2'] = 'Allowed user profile fields for printing single organizer slots';
$string['allowsubmissionsanddescriptionfromdatesummary'] = 'The organizer details and registration form will be available from <strong>{$a}</strong>';
$string['allowsubmissionsfromdate'] = 'Registration start';
$string['allowsubmissionsfromdate_help'] = 'Check this if you want to make this organizer available to students after a certain point in time.';
$string['allowsubmissionsfromdatesummary'] = 'This organizer will accept registrations from <strong>{$a}</strong>';
$string['allowsubmissionstodate'] = 'Registration end';
$string['alwaysshowdescription'] = 'Always show description';
$string['alwaysshowdescription_help'] = 'If disabled, the Assignment Description above will only become visible to students at the \'Registration start\' date.';
$string['applicant'] = 'This is the person that registered the group';
$string['appointment_reminder_student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have an appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have a group appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_student:group:smallmessage'] = 'You have a group appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.';
$string['appointment_reminder_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group appointment reminder';
$string['appointment_reminder_student:smallmessage'] = 'You have an appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location}.';
$string['appointment_reminder_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment reminder';
$string['appointment_reminder_teacher:digest:fullmessage'] = 'Hello {$a->receivername}!

Tomorrow you have the following appointments:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder_teacher:digest:smallmessage'] = 'You have received a digest message of your appointments tomorrow.';
$string['appointment_reminder_teacher:digest:subject'] = 'Appointment digest';
$string['appointment_reminder_teacher:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you have an appointment with students on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['appointment_reminder_teacher:group:digest:fullmessage'] = 'Hello {$a->receivername}!

Tomorrow you have the following appointments:

{$a->digest}

Moodle Messaging System';
$string['appointment_reminder_teacher:group:digest:smallmessage'] = 'You have received a digest message of your appointments tomorrow.';
$string['appointment_reminder_teacher:group:digest:subject'] = 'Appointment digest';
$string['appointment_reminder_teacher:smallmessage'] = 'You have an appointment with students on {$a->date} at {$a->time} in {$a->location}.';
$string['appointment_reminder_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment reminder';
$string['appointmentcomments'] = 'Comments';
$string['appointmentcomments_help'] = 'Additional information about the appointments can be added here.';
$string['appointmentdatetime'] = 'Date & time';
$string['appointmentdeleted_notify_student:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was deleted.';
$string['appointmentdeleted_notify_student:group:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was deleted.';
$string['appointmentdeleted_notify_student:group:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in organizer \'{$a->organizername}\' was deleted.';
$string['appointmentdeleted_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment deleted';
$string['appointmentdeleted_notify_student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in organizer \'{$a->organizername}\' was deleted.';
$string['appointmentdeleted_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment deleted';
$string['assign'] = 'Assign';
$string['assign_notify_student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, an appointment with {$a->slot_teacher} on {$a->date} at {$a->time} has been assigned to you by {$a->sendername}.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Date: {$a->date} at {$a->time}

Moodle Messaging System';
$string['assign_notify_student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, an appointment with {$a->slot_teacher} on {$a->date} at {$a->time} has been assigned to your group {$a->groupname} by {$a->sendername}.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Date: {$a->date} at {$a->time}

Moodle Messaging System';
$string['assign_notify_student:group:smallmessage'] = 'An appointment with {$a->slot_teacher} on {$a->date} at {$a->time} has been assigned to your group {$a->groupname} by {$a->sendername}.';
$string['assign_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment assigned by teacher';
$string['assign_notify_student:smallmessage'] = 'An appointment with {$a->slot_teacher} on {$a->date} at {$a->time} has been assigned to you by {$a->sendername}.';
$string['assign_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment assigned by teacher';
$string['assign_notify_teacher:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, an appointment with {$a->participantname} on {$a->date} at {$a->time} has been assigned to you by {$a->sendername}.

Participant: {$a->participantname}
Location: {$a->slot_location}
Date: {$a->date} at {$a->time}

Moodle Messaging System';
$string['assign_notify_teacher:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, an appointment with group {$a->groupname} on {$a->date} at {$a->time} has been assigned to you by {$a->sendername}.

Group: {$a->groupname}
Location: {$a->slot_location}
Date: {$a->date} at {$a->time}

Moodle Messaging System';
$string['assign_notify_teacher:group:smallmessage'] = 'An appointment with group {$a->groupname} on {$a->date} at {$a->time} has been assigned to you by {$a->sendername}.';
$string['assign_notify_teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment assigned';
$string['assign_notify_teacher:smallmessage'] = 'An appointment with {$a->sendername} on {$a->date} at {$a->time} has been assigned by you by {$a->sendername}';
$string['assign_notify_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment assigned';
$string['assign_title'] = 'Assign appointment';
$string['assignsuccess'] = 'The slot has been assigned successfully and the participant(s) has been notified.';
$string['assignsuccessnotsent'] = 'The slot has been assigned successfully BUT the participant(s) has NOT been notified.';
$string['atlocation'] = 'at';
$string['attended'] = 'attended';
$string['auth'] = 'Authentification method';
$string['availability'] = 'Availability';
$string['availablefrom'] = 'Applications possible from';
$string['availablefrom_help'] = 'Set the timeframe within which students will be allowed to register for these timeslots. Alternatively, check \'Starting now\' to enable registration immediately.';
$string['availablegrouplist'] = 'Available groups';
$string['availableslotsfor'] = 'Available slots for';
$string['back'] = 'Back';
$string['btn_add'] = 'Add new slots';
$string['btn_assign'] = 'Assign slot';
$string['btn_comment'] = 'Edit your comment';
$string['btn_delete'] = 'Remove selected slots';
$string['btn_deleteappointment'] = 'Delete appointment';
$string['btn_deletesingle'] = 'Remove selected slot';
$string['btn_edit'] = 'Edit selected slots';
$string['btn_editsingle'] = 'Edit selected slot';
$string['btn_eval'] = 'Grade selected slots';
$string['btn_eval_short'] = 'Grade';
$string['btn_evalsingle'] = 'Grade selected slot';
$string['btn_exportics'] = 'Export selected slot as an ICS file';
$string['btn_print'] = 'Print selected slots';
$string['btn_printsingle'] = 'Print selected slot';
$string['btn_queue'] = 'Queue';
$string['btn_reeval'] = 'Re-evaluate';
$string['btn_register'] = 'Register';
$string['btn_remind'] = 'Send reminder';
$string['btn_reregister'] = 'Re-register';
$string['btn_save'] = 'Save comment';
$string['btn_send'] = 'Send';
$string['btn_sendall'] = 'Send reminders to all participants without enough appointments:';
$string['btn_start'] = 'Start';
$string['btn_unqueue'] = 'Remove from Queue';
$string['btn_unregister'] = 'Unregister';
$string['calendarsettings'] = 'Calendar settings';
$string['can_reregister'] = 'You can re-registed to another appointment.';
$string['cannot_eval'] = 'Cannot evaluate, the student has a ';
$string['cfg_dontshowidentity'] = 'Hide identity';
$string['cfg_dontshowidentity_desc'] = 'Hide participant\'s identity in slot list';
$string['cfg_limitedwidth'] = 'Smaller content area';
$string['cfg_limitedwidth_desc'] = 'Use smaller moodle 4.x-style content area in organizer. Moodle default is used but can possibly be stretched by long table entries.';
$string['changegradewarning'] = 'This organizer has graded appointments and changing the grade settings will not automatically re-calculate existing grades. You must re-grade all existing appointments, if you wish to change the grade.';
$string['collision'] = 'Warning! Collision detected with following event(s) and/or slot(s):';
$string['configabsolutedeadline'] = 'The default offset of the date and time selector from the current date and time.';
$string['configahead'] = 'ahead';
$string['configallowcreationofpasttimeslots'] = 'Is it allowed to create past time slots?';
$string['configday'] = 'day';
$string['configdays'] = 'day';
$string['configdigest'] = 'Send summary of the next day appointments to the teacher.';
$string['configdigest_label'] = 'Send appointment digest to teachers';
$string['configdontsend'] = 'Don\'t send';
$string['configemailteachers'] = 'Send E-mail notifications to teachers about registration status changes.';
$string['configemailteachers_label'] = 'Send E-mail notifications to teachers';
$string['confighour'] = 'hour';
$string['confighours'] = 'hours';
$string['configintro'] = 'The values you set here define the default values that are used in the settings form when you create a new organizer.';
$string['configlocationlink'] = 'The link to a search engine used to show the way to the location. Place $searchstring in the URL where the query goes.';
$string['configlocationslist'] = 'Locations for autocomplete field';
$string['configlocationslist_desc'] = 'Each location has to be inserted in a separate row!';
$string['configmaximumgrade'] = 'Sets the default value selected in the grade field when creating a new organizer. This is the maximum grade assignable to a student for his appointment.';
$string['configminute'] = 'minute';
$string['configminutes'] = 'minutes';
$string['configmonth'] = 'month';
$string['configmonths'] = 'months';
$string['confignever'] = 'Never';
$string['configrelativedeadline'] = 'The default time ahead of an appointment when the participants should be notified of it.';
$string['configrequiremodintro'] = 'Disable this option if you do not want to force users to enter description of each activity.';
$string['configsingleslotprintfield'] = 'user field to be printed out when single slot is printed';
$string['configweek'] = 'week';
$string['configweeks'] = 'weeks';
$string['configyear'] = 'year';
$string['confirm_conflicts'] = 'Are you sure that you want to ignore the collisions and want to create the time slots?';
$string['confirm_delete'] = 'Delete';
$string['confirm_organizer_remind_all'] = 'Send';
$string['create'] = 'Create';
$string['created'] = 'Created';
$string['createsubmit'] = 'Create time slots';
$string['crontaskname'] = 'Organizer cron job';
$string['datapreviewtitle'] = 'Data Preview';
$string['datapreviewtitle_help'] = 'Click on [+] or [-] for showing or hiding columns.';
$string['datetemplate'] = '%d.%m.%Y';
$string['datetime'] = 'Datetime';
$string['datetime_help'] = 'Date & time of slot.';
$string['day'] = 'day';
$string['day_0'] = 'Monday';
$string['day_1'] = 'Tuesday';
$string['day_2'] = 'Wednesday';
$string['day_3'] = 'Thursday';
$string['day_4'] = 'Friday';
$string['day_5'] = 'Saturday';
$string['day_6'] = 'Sunday';
$string['day_pl'] = 'days';
$string['dbid'] = 'DB ID';
$string['defaultsingleslotprintfields'] = 'Default single print slot user profile fields';
$string['delete_organizer_grades'] = 'Deleting grades of all organizers';
$string['deleteappointmentheader'] = 'Delete this appointment';
$string['deleteheader'] = 'Deleting following slots:';
$string['deletekeep'] = 'The following appointments will be cancelled. Registered students will be notified and the slots will be deleted:';
$string['deletenoslots'] = 'No deletable slots selected';
$string['deleteorganizergrades'] = 'Delete grades from gradebook';
$string['details'] = 'Status details';
$string['details_help'] = 'Current status of this slot.';
$string['downloadfile'] = 'Download file';
$string['duedate'] = 'Due date';
$string['duedateerror'] = 'Absolute deadline cannot be set before the availability date!';
$string['duration'] = 'Duration';
$string['duration_help'] = 'Defines the duration of the appointments. All defined time frames will be divided into slots of the duration defined here. Any remaining time will remain unused (i.e. if the time frame is 40 min long and the duration is set to 15 min, there will be 2 slots in total with 10 unused minutes extra).';
$string['edit_notify_student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the appointment {$a->sendername} on {$a->date} at {$a->time} have been changed.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the group appointment {$a->sendername} on {$a->date} at {$a->time} have been changed.

Teacher: {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments:
{$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_student:group:smallmessage'] = 'The details of the group appointment {$a->sendername} on {$a->date} at {$a->time} have been changed.';
$string['edit_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify_student:smallmessage'] = 'The details of the appointment {$a->sendername} on {$a->date} at {$a->time} have been changed.';
$string['edit_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify_teacher:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.

Teacher(s): {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments: {$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_teacher:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, the details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.

Teacher(s): {$a->slot_teacher}
Location: {$a->slot_location}
Max. participants: {$a->slot_maxparticipants}
Comments: {$a->slot_comments}

Moodle Messaging System';
$string['edit_notify_teacher:group:smallmessage'] = 'The details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.';
$string['edit_notify_teacher:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_notify_teacher:smallmessage'] = 'The details of the time slot on {$a->date} at {$a->time} have been changed by {$a->sendername}.';
$string['edit_notify_teacher:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment details changed';
$string['edit_submit'] = 'Commit changes';
$string['emailteachers'] = 'Send email notifications to teachers';
$string['emailteachers_help'] = 'Notifications for teachers when a student first registrations for a timeslot
    are normally supressed to avoid spamming. Check this to enable the organizer to send such e-mail notifications
    to teachers. Note that the notifications for unregistering and changing slots are always sent.';
$string['enableprintslotuserfields'] = 'Allow change in user profile fields';
$string['enableprintslotuserfieldsdesc'] = 'Controls whether teachers are allowed to change the default selected user profile fields below';
$string['err_availablefromearly'] = 'This date cannot be set later than the start date!';
$string['err_availablefromlate'] = 'This date cannot be set later than the end date!';
$string['err_availablepastdeadline'] = 'This slot cannot be made available past the scheduler\'s deadline ({$a->deadline})!';
$string['err_collision'] = 'This frame is in collision with other frames:';
$string['err_comments'] = 'You must enter a description!';
$string['err_enddate'] = 'End date cannot be set before the start date!';
$string['err_fromto'] = 'End time cannot be set before the start time!';
$string['err_fullminute'] = 'The duration has to be a full minute.';
$string['err_fullminutegap'] = 'The gap has to be a full minute.';
$string['err_isgrouporganizer_app'] = 'Cannot change group mode as there already exist scheduled appointments in this organizer!';
$string['err_location'] = 'You must enter a location!';
$string['err_norecipients'] = 'No recipients were selected!';
$string['err_noslots'] = 'No slots were selected!';
$string['err_posint'] = 'You must enter a positive integer!';
$string['err_startdate'] = 'Start date cannot be set before today\'s date ({$a->now})!';
$string['eval_attended'] = 'Attended';
$string['eval_feedback'] = 'Feedback';
$string['eval_grade'] = 'Grade';
$string['eval_header'] = 'Selected time slots';
$string['eval_link'] = 'new appointment';
$string['eval_no_participants'] = 'This slot had no participants';
$string['eval_not_occured'] = 'This slot has not occurred yet';
$string['eval_notify_newappointment:student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Teachers of the course enable you to re-register to any available slot in the organizer {$a->organizername}.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Teachers of the course enable you to re-register to any available slot in the organizer {$a->coursefullname}.

Moodle Messaging System';
$string['eval_notify_newappointment:student:group:smallmessage'] = 'Your group appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';
$string['eval_notify_newappointment:student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify_newappointment:student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';
$string['eval_notify_newappointment:student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify_student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Moodle Messaging System';
$string['eval_notify_student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group appointment {$a->sendername} on {$a->date} at {$a->time} in {$a->location} has been evaluated.

Moodle Messaging System';
$string['eval_notify_student:group:smallmessage'] = 'Your group appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';
$string['eval_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['eval_notify_student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in {$a->location} has been evaluated.';
$string['eval_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment evaluated';
$string['evaluate'] = 'Evaluate';
$string['event'] = 'Calendar event';
$string['eventappointmentadded'] = 'Student registered to a slot.';
$string['eventappointmentassigned'] = 'Appointment has been assigned by teacher.';
$string['eventappointmentcommented'] = 'Appoinement has been commented.';
$string['eventappointmentdeleted'] = 'Appointment was deleted by teacher.';
$string['eventappointmentevaluated'] = 'Appointment has been evaluated.';
$string['eventappointmentlistprinted'] = 'Appoinement list has been printed.';
$string['eventappointmentremindersent'] = 'Appointment reminder sent.';
$string['eventappointmentremoved'] = 'Student unregistered from a slot.';
$string['eventappwith:group'] = 'Group appointment';
$string['eventappwith:single'] = 'Appointment';
$string['eventnoparticipants'] = 'No participants';
$string['eventqueueadded'] = 'Added to waiting list';
$string['eventqueueremoved'] = 'Removed from waiting list';
$string['eventregistrationsviewed'] = 'Registrations tab viewed.';
$string['eventslotcreated'] = 'New slots created.';
$string['eventslotdeleted'] = 'Slot deleted.';
$string['eventslotupdated'] = 'Slot updated.';
$string['eventslotviewed'] = 'Slots viewed.';
$string['eventteacheranonymous'] = 'an anonymous teacher';
$string['eventtemplate'] = '{$a->courselink} / {$a->organizerlink}: {$a->appwith} {$a->with} {$a->participants}<br />Location: {$a->location}<br />';
$string['eventtemplatecomment'] = 'Comment:<br />{$a}<br />';
$string['eventtemplatewithoutlinks'] = '{$a->coursename} / {$a->organizername}: {$a->appwith} {$a->with} {$a->participants}<br />Location: {$a->location}<br />';
$string['eventtitle'] = '{$a->coursename} / {$a->organizername}: {$a->appwith}';
$string['eventwith'] = 'with';
$string['eventwithout'] = 'with';
$string['exportics'] = 'Export ICS';
$string['exporticsaction'] = 'export ICS';
$string['exportsettings'] = 'Export settings';
$string['filtertable'] = '\'Filterting this table\'';
$string['filtertable_help'] = 'Search these slots for mutual strings here.';
$string['finalgrade'] = 'This value has been set in the gradebook and can not be changed with the organizer.';
$string['font_large'] = 'large';
$string['font_medium'] = 'medium';
$string['font_small'] = 'small';
$string['format'] = 'Format';
$string['format_csv_comma'] = 'CSV (;)';
$string['format_csv_tab'] = 'CSV (tab)';
$string['format_ods'] = 'ODS';
$string['format_pdf'] = 'PDF';
$string['format_xls'] = 'XLS';
$string['format_xlsx'] = 'XLSX';
$string['fulldatelongtemplate'] = '%A %d. %B %Y';
$string['fulldatetemplate'] = '%a %d.%m.%Y';
$string['fulldatetimelongtemplate'] = '%A %d. %B %Y %H:%M';
$string['fulldatetimetemplate'] = '%a %d.%m.%Y %H:%M';
$string['fullname_template'] = '{$a->firstname} {$a->lastname}';
$string['gap'] = 'Gap';
$string['gap_help'] = 'Defines the gap between two appointments.';
$string['grade'] = 'Maximum grade';
$string['grade_help'] = 'Defines the highest amount of points that can be awarded for any appointment that can be graded.';
$string['gradeaggregationmethod'] = 'Aggregation method';
$string['gradeaggregationmethod_help'] = 'The aggregation determines how grades in a category are combined, such as

* Mean of grades - The sum of all grades divided by the total number of grades
* Lowest grade
* Highest grade
* Natural - The sum of all grade values';
$string['grading_desc_grade'] = 'Grading is active.';
$string['grading_desc_nograde'] = 'Grading is not active.';
$string['group_registration_notify:student:queue:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has added your group {$a->groupname} to the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:queue:group:smallmessage'] = '{$a->sendername} has added your group {$a->groupname} to the waiting list of the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group wait-listed';
$string['group_registration_notify:student:register:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:register:group:smallmessage'] = '{$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['group_registration_notify:student:register:smallmessage'] = '{$a->sendername} has registered your group {$a->groupname} to the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['group_registration_notify:student:reregister:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:reregister:group:smallmessage'] = '{$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['group_registration_notify:student:reregister:smallmessage'] = '{$a->sendername} has re-registered your group {$a->groupname} to a new time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['group_registration_notify:student:unqueue:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has removed your group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unqueue:group:smallmessage'] = '{$a->sendername} has removed your group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group removed from waiting list';
$string['group_registration_notify:student:unregister:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, {$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['group_registration_notify:student:unregister:group:smallmessage'] = '{$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['group_registration_notify:student:unregister:smallmessage'] = '{$a->sendername} has unregistered your group {$a->groupname} from the time slot on {$a->date} at {$a->time}.';
$string['group_registration_notify:student:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['group_slot_available'] = 'Slot available';
$string['group_slot_full'] = 'Slot taken';
$string['groupmodeexistingcoursegroups'] = 'Use existing course groups';
$string['groupmodenogroups'] = 'No group appointments';
$string['groupmodeslotgroups'] = 'Group creation per empty slot';
$string['groupmodeslotgroupsappointment'] = 'Group creation per booked slot';
$string['groupoptions'] = 'Group settings';
$string['grouporganizer_desc'] = 'This is a group organizer.';
$string['grouporganizer_desc_novalidgroup'] = 'This is a group organizer. You are not member of a group included in this organizer instance!';
$string['grouporganizer_desc_participant'] = 'This is a group organizer. Clicking the register button will register you and all members of your group {$a->groupname} to this slot. All group members may change and comment the registration.';
$string['grouppicker'] = 'Group picker';
$string['groupwarning'] = 'Check the group options below!';
$string['headerfooter'] = 'Print header/footer';
$string['headerfooter_help'] = 'Print header/footer if checked';
$string['hidecalendar'] = 'Hide calendar';
$string['hidecalendar_help'] = 'Check to hide the calendar in this organizer';
$string['hour'] = 'hr';
$string['hour_pl'] = 'hrs';
$string['id'] = 'ID';
$string['img_title_due'] = 'The slot is bookable';
$string['img_title_evaluated'] = 'The slot is evaluated';
$string['img_title_full'] = 'The slot is full';
$string['img_title_no_participants'] = 'The slot had no participants';
$string['img_title_past_deadline'] = 'The slot is past its deadline';
$string['img_title_pending'] = 'The slot is pending evaluation';
$string['includetraineringroups'] = 'Include trainer in groups';
$string['includetraineringroups_help'] = 'If you check the checkbox not only the slot\'s participants but also its trainers are included in the groups.';
$string['infobox_app_countdown'] = 'Time left to the appointment: {$a->days} days, {$a->hours} hours, {$a->minutes} minutes, {$a->seconds} seconds';
$string['infobox_app_inprogress'] = 'The appointment is in progress.';
$string['infobox_app_occured'] = 'The appointment has already occurred.';
$string['infobox_appointmentsstatus_pl'] = '{$a->tooless} booking(s) is/are due. There are {$a->places} free places in {$a->slots} up-coming slot(s).';
$string['infobox_appointmentsstatus_sg'] = '{$a->tooless} booking(s) is/are due. There is {$a->places} free place in {$a->slots} up-coming slot(s).';
$string['infobox_counter_slotrows'] = 'slots shown.';
$string['infobox_deadline_countdown'] = 'Time left to deadline: {$a->days} days, {$a->hours} hours, {$a->minutes} minutes, {$a->seconds} seconds';
$string['infobox_deadline_passed'] = 'Registration deadline passed. You can no longer change registrations.';
$string['infobox_deadline_passed_slot'] = 'xxx slot(s) could not be created because registration deadline has passed.';
$string['infobox_deadline_passed_slotphp'] = '{$a->slots} slot(s) could not be created because registration deadline has passed.';
$string['infobox_deadlines_title'] = 'Deadlines';
$string['infobox_description_title'] = 'Organizer description';
$string['infobox_feedback_title'] = 'Feedback';
$string['infobox_group'] = 'My group: {$a->groupname}';
$string['infobox_link'] = 'Show/Hide';
$string['infobox_messages_title'] = 'System messages';
$string['infobox_messaging_title'] = '';
$string['infobox_minmax'] = 'Bookings per user: Minimum {$a->min} - Maximum {$a->max}.';
$string['infobox_mycomments_title'] = 'My comments';
$string['infobox_myslot_noslot'] = 'You are not registered to any slot at the time.';
$string['infobox_myslot_title'] = 'My slots';
$string['infobox_myslot_userslots_left'] = 'You have {$a->left} bookings left.';
$string['infobox_myslot_userslots_left_group'] = 'Your group has {$a->left} bookings left.';
$string['infobox_myslot_userslots_max_reached'] = 'You have booked the maximum amount of {$a->max} slot(s).';
$string['infobox_myslot_userslots_max_reached_group'] = 'Your group has booked the maximum amount of {$a->max} slot(s).';
$string['infobox_myslot_userslots_min_not_reached'] = 'You have not booked the required amount of {$a->min} slot(s) yet.';
$string['infobox_myslot_userslots_min_not_reached_group'] = 'Your group has not booked the required amount of {$a->min} slot(s) yet.';
$string['infobox_myslot_userslots_min_reached'] = 'You have successfully booked the required amount of {$a->min} slot(s).';
$string['infobox_myslot_userslots_min_reached_group'] = 'Your group has booked the required amount of {$a->min} bookings.';
$string['infobox_myslot_userslots_status'] = '{$a->booked} of {$a->max} slots booked.';
$string['infobox_organizer_expired'] = 'This organizer expired on {$a->date} at {$a->time}';
$string['infobox_organizer_expires'] = 'This organizer will expire on {$a->date} at {$a->time}.';
$string['infobox_organizer_never_expires'] = 'This organizer does not expire.';
$string['infobox_registrationstatistic_title'] = 'Summary';
$string['infobox_showallparticipants'] = 'Show all participants';
$string['infobox_showfreeslots'] = 'Free slots only';
$string['infobox_showhiddenslots'] = 'Hidden slots';
$string['infobox_showmyslotsonly'] = 'My slots only';
$string['infobox_showregistrationsonly'] = 'Booked slots only';
$string['infobox_showslots'] = 'Also past time slots';
$string['infobox_slotoverview_title'] = 'Slot overview';
$string['infobox_slotsviewoptions'] = 'Special filter options';
$string['infobox_slotsviewoptions_help'] = 'These filter options are combined by AND conjunctions!';
$string['infobox_statistic_maxreached'] = '{$a->maxreached} of {$a->entries} participants booked the maximum of {$a->max} slot(s).';
$string['infobox_statistic_maxreached_group'] = '{$a->maxreached} of {$a->entries} groups booked the maximum of {$a->max} slot(s).';
$string['infobox_statistic_minreached'] = '{$a->minreached} of {$a->entries} participants booked the required amount of {$a->min} slot(s).';
$string['infobox_statistic_minreached_group'] = '{$a->minreached} of {$a->entries} groups reached the required amount of {$a->min} slot(s).';
$string['infobox_title'] = 'Infobox';
$string['introeditor_error'] = 'Organizer description must be given!';
$string['invalidgrouping'] = 'You must select a valid grouping!';
$string['inwaitingqueue'] = 'Waitinglist';
$string['isgrouporganizer'] = 'Group appointments';
$string['isgrouporganizer_help'] = 'Check this if you want this organizer to deal with groups instead of individual users.
\'Use existing groups\': A single groupmember books a slot for the group.
\'Group creation per empty slot\': A course group is created for every new slot.
\'Group creation per booked slot\': A course group is created for every booked slot.';
$string['location'] = 'Location';
$string['location_help'] = 'The location where the slot takes place.';
$string['locationlink'] = 'Location link URL';
$string['locationlink_help'] = 'Type here the full address of the website you want the location link to refer to. This site should at least contain information on how to reach the location. Please type in the full address (including http://)';
$string['locationlinkenable'] = 'location info autolink';
$string['locationmandatory'] = 'Slot Location entry is mandatory';
$string['locationsettings'] = 'Slot location settings';
$string['maillink'] = 'The organizer is available <a href=\'{$a}\'>here</a>.';
$string['maxparticipants'] = 'Max. participants';
$string['maxparticipants_help'] = 'Defines the maximum number of students that can register to these time slots. In case of a group organizer this number is always limited to one.';
$string['message_autogenerated2'] = 'Auto-generated message';
$string['message_custommessage'] = 'Custom message';
$string['message_custommessage_help'] = 'Use this field to enter a personal text into the auto-generated message.';
$string['message_error_action_notallowed'] = 'This action is not possible any more. Please navigate back and refresh your browser!';
$string['message_error_groupsynchronization'] = 'Slotgroup synchronization failed!';
$string['message_error_noactionchosen'] = 'Please choose an action before pressing the Start button.';
$string['message_error_slot_full_group'] = 'This timeslot is already taken!';
$string['message_error_slot_full_single'] = 'This timeslot has no free places left!';
$string['message_error_unknown_unqueue'] = 'Your waiting list entry could not be removed! Unknown error.';
$string['message_error_unknown_unregister'] = 'Your registration could not be removed! Unknown error.';
$string['message_info_appointment_deleted'] = 'The appointment was deleted. The participant has been notified.';
$string['message_info_appointment_deleted_group'] = 'The appointments of one group have been deleted. The participants have been notified.';
$string['message_info_appointment_not_deleted'] = 'A problem occured when deleting the appointment(s).';
$string['message_info_queued'] = 'You was added to a slot\'s waiting list.';
$string['message_info_queued_group'] = 'Your group was added to a slot\'s waiting list';
$string['message_info_registered'] = 'You successfully registered for a slot.';
$string['message_info_registered_group'] = 'Your group successfully registered for a slot.';
$string['message_info_reminders_sent_pl'] = '{$a->count} reminders were sent.';
$string['message_info_reminders_sent_sg'] = '{$a->count} reminder was sent.';
$string['message_info_reregistered'] = 'You successfully reregistered for a slot.';
$string['message_info_reregistered_group'] = 'Your group successfully reregistered for a slot.';
$string['message_info_slots_added_pl'] = '{$a->count} new slots were added.';
$string['message_info_slots_added_sg'] = '{$a->count} new slot was added.';
$string['message_info_slots_deleted_pl'] = '{$a->deleted} slots were deleted. {$a->notified} participant(s) had been notified.';
$string['message_info_slots_deleted_sg'] = 'One slot was deleted. {$a->notified} participant(s) had been notified.';
$string['message_info_slots_edited_pl'] = '{$a->count} slots were edited.';
$string['message_info_slots_edited_sg'] = '{$a->count} slot was edited.';
$string['message_info_slots_evaluated_pl'] = '{$a->count} participants were graded.';
$string['message_info_slots_evaluated_sg'] = '{$a->count} participant was graded.';
$string['message_info_unqueued'] = 'You was removed from a slot\'s waiting list.';
$string['message_info_unqueued_group'] = 'Your group was removed from a slot\'s waiting list.';
$string['message_info_unregistered'] = 'You successfully unregistered from a slot.';
$string['message_info_unregistered_group'] = 'Your group successfully unregistered from a slot.';
$string['message_warning_no_slots_added'] = 'No new slots were added!';
$string['message_warning_no_slots_selected'] = 'You must select at least one slot first!';
$string['message_warning_no_visible_slots_selected'] = 'You must select at least one VISIBLE slot first!';
$string['messageprovider:appointment_reminder_student'] = 'Organizer appointment reminder';
$string['messageprovider:appointment_reminder_teacher'] = 'Organizer appointment reminder (Teacher)';
$string['messageprovider:appointmentdeleted_notify_student'] = 'Organizer appointment deleted';
$string['messageprovider:assign_notify_student'] = 'Organizer assignment by teacher';
$string['messageprovider:assign_notify_teacher'] = 'Organizer assignment';
$string['messageprovider:edit_notify_student'] = 'Organizer changes';
$string['messageprovider:edit_notify_teacher'] = 'Organizer changes (Teacher)';
$string['messageprovider:eval_notify_student'] = 'Organizer evaluation notification';
$string['messageprovider:group_registration_notify_student'] = 'Organizer groupregistration notification';
$string['messageprovider:manual_reminder_student'] = 'Organizer manual appointment reminder';
$string['messageprovider:register_notify_teacher'] = 'Organizer registration notification';
$string['messageprovider:register_notify_teacher_queue'] = 'Organizer queueing notification';
$string['messageprovider:register_notify_teacher_register'] = 'Organizer registration notification';
$string['messageprovider:register_notify_teacher_reregister'] = 'Organizer re-registration notification';
$string['messageprovider:register_notify_teacher_unqueue'] = 'Organizer unqueueing notification';
$string['messageprovider:register_notify_teacher_unregister'] = 'Organizer unsubscription notification';
$string['messageprovider:register_promotion_student'] = 'Organizer promoted from queue notification';
$string['messageprovider:register_reminder_student'] = 'Organizer registration reminder';
$string['messageprovider:slotdeleted_notify_student'] = 'Organizer slots cancelled';
$string['messageprovider:test'] = 'Organizer Test Message';
$string['messages_all'] = 'All registration, re-registrations and unregistrations';
$string['messages_none'] = 'No registration notifications';
$string['messages_re_unreg'] = 'Re-registrations and unregistrations only';
$string['min'] = 'min';
$string['min_pl'] = 'mins';
$string['modformwarningplural'] = 'These fields cannot be edited as there are appointments already made in this organizer!';
$string['modformwarningsingular'] = 'This field cannot be edited as there are appointments already made in this organizer!';
$string['modulename'] = 'Organizer';
$string['modulename_help'] = 'Organizers enable teachers to make appointments with students by creating time slots which students can register to.';
$string['modulenameplural'] = 'Organizers';
$string['monthlyview'] = 'Monthly view';
$string['multimember'] = 'Users cannot belong to multiple course groups!';
$string['multimemberspecific'] = 'User {$a->username} {$a->idnumber} is registered in more than one group! ({$a->groups})';
$string['multipleappointmentenddate'] = 'End date';
$string['multipleappointmentstartdate'] = 'Start date';
$string['mymoodle_app_slot'] = 'Appointment on {$a->date} at {$a->time}';
$string['mymoodle_attended'] = '{$a->attended}/{$a->total} students have completed an appointment';
$string['mymoodle_attended_group'] = '{$a->attended}/{$a->total} groups have completed an appointment';
$string['mymoodle_attended_group_short'] = '{$a->attended} of {$a->total} groups have attended one appointment at least';
$string['mymoodle_attended_short'] = '{$a->attended} of {$a->total} participants have attended one appointment at least';
$string['mymoodle_completed_app'] = 'You completed your appointment on {$a->date} at {$a->time}';
$string['mymoodle_completed_app_group'] = 'Your group {$a->groupname} attended the appointment on at {$a->date} at {$a->time}';
$string['mymoodle_missed_app'] = 'You did not attend the appointment on {$a->date} at {$a->time}';
$string['mymoodle_missed_app_group'] = 'Your group {$a->groupname} did not attend the appointment on {$a->date} at {$a->time}';
$string['mymoodle_next_slot'] = 'Next slot on {$a->date} at {$a->time}';
$string['mymoodle_no_reg_slot'] = 'You have booked {$a->booked} time slots and not reached the minimum of {$a->slotsmin} time slots yet.';
$string['mymoodle_no_reg_slot_group'] = 'Your group {$a->groupname} has booked {$a->booked} time slots and not reached the minimum of {$a->slotsmin} time slots yet.';
$string['mymoodle_no_slots'] = 'No upcoming slots';
$string['mymoodle_organizer_expired'] = 'This organizer expired on {$a->date} at {$a->time}. You can no longer use it';
$string['mymoodle_organizer_expires'] = 'This organizer expires on {$a->date} at {$a->time}';
$string['mymoodle_pending_app'] = 'Your appointment is pending evaluation';
$string['mymoodle_pending_app_group'] = 'Your group {$a->groupname} appointment is pending evaluation';
$string['mymoodle_reg_slot'] = 'You have booked {$a->booked} time slots and therefore reached the minimum of {$a->slotsmin} bookings.';
$string['mymoodle_reg_slot_group'] = 'Your group {$a->groupname} has booked {$a->booked} time slots and therfore reached the minimum of {$a->slotsmin} bookings.';
$string['mymoodle_registered'] = '{$a->registered}/{$a->total} students have registered for an appointment';
$string['mymoodle_registered_group'] = '{$a->registered}/{$a->total} groups have registered for an appointment';
$string['mymoodle_registered_group_short'] = '{$a->registered} of {$a->total} groups have booked the minimum of {$a->slotsmin} slots';
$string['mymoodle_registered_short'] = '{$a->registered} of {$a->total} participants have booked the minimum of {$a->slotsmin} slots';
$string['mymoodle_upcoming_app'] = 'Your appointment will take place on {$a->date} at {$a->time} at {$a->location}';
$string['mymoodle_upcoming_app_group'] = 'Appointment of your group, {$a->groupname}, will take place on {$a->date} at {$a->time} at {$a->location}';
$string['newslot'] = 'Add more slots';
$string['no_due_my_slots'] = 'All of your time slots in this organizer have expired and/or are hidden';
$string['no_due_slots'] = 'All time slots created in this organizer have expired';
$string['no_my_slots'] = 'You have no slots created in this organizer';
$string['no_slots'] = 'There are no time slots created in this organizer';
$string['no_slots_defined'] = 'There are no appointments slots available at the moment.';
$string['no_slots_defined_teacher'] = 'There are no appointments slots present at the moment. Click <a href=\'{$a->link}\'>here</a> to create some now.';
$string['nocalendareventslotcreation'] = 'No calendar events for empty slots';
$string['nocalendareventslotcreation_help'] = 'If you check this option no calendar events will be created when creating slots. Only appointments will create slot calendar events.';
$string['nofreeslots'] = 'No free slots available.';
$string['nogroup'] = 'No group';
$string['nolocationplaceholder'] = '[TBD]';
$string['noparticipants'] = 'No participants';
$string['noreregistrations'] = 'No reregistrations after deadline';
$string['noreregistrations_help'] = 'If a booked slot has reached the deadline it can not be the source of a reregistration anymore.';
$string['norightpage'] = 'You are not allowed to call up this page.';
$string['nosingleslotprintfields'] = 'Printing is not possible. There are no user fields defined. See the organizer settings.';
$string['noslots'] = 'No slots for ';
$string['noslotsselected'] = 'No slots selected!';
$string['notificationtime'] = 'Relative appointment reminder';
$string['notificationtime_help'] = 'Defines how far before the registered appointment the student should be reminded of it.';
$string['novalidparticipants'] = 'No valid participants';
$string['numentries'] = 'Entries shown per page';
$string['numentries_help'] = 'Choose \'optimal\' to optimize the distribution of list entries according to the chosen textsize and page orientation, if there are plenty of participants registered in your course';
$string['organizer'] = 'Organizer';
$string['organizer:addinstance'] = 'Add a new organizer';
$string['organizer:addslots'] = 'Add new time slots';
$string['organizer:assignslots'] = 'Assign time slot to a student';
$string['organizer:comment'] = 'Add comments';
$string['organizer:deleteappointments'] = 'Delete appointments';
$string['organizer:deleteslots'] = 'Delete existing time slots';
$string['organizer:editslots'] = 'Edit existing time slots';
$string['organizer:evalslots'] = 'Grade completed time slots';
$string['organizer:leadslots'] = 'Lead time slots';
$string['organizer:printslots'] = 'Print existing time slots';
$string['organizer:receivemessagesstudent'] = 'Receive messages as sent to students';
$string['organizer:receivemessagesteacher'] = 'Receive messages as sent to teachers';
$string['organizer:register'] = 'Register to a time slot';
$string['organizer:sendreminders'] = 'Send registration reminders to students';
$string['organizer:unregister'] = 'Unregister from a time slot';
$string['organizer:viewallslots'] = 'View all time slots as a teacher';
$string['organizer:viewmyslots'] = 'View own time slots as a teacher';
$string['organizer:viewregistrations'] = 'View status of student registrations';
$string['organizer:viewstudentview'] = 'View all time slots as a student';
$string['organizer_remind_all_no_recepients'] = 'There are no valid recepients.';
$string['organizer_remind_all_recepients_pl'] = 'A total of {$a->count} messages will be sent to the following recepients:';
$string['organizer_remind_all_recepients_sg'] = 'A total of {$a->count} message will be sent to the following recepients:';
$string['organizer_remind_all_title'] = 'Send reminders';
$string['organizercommon'] = 'Organizer settings';
$string['organizername'] = 'Organizer name';
$string['orientationlandscape'] = 'landscape';
$string['orientationportrait'] = 'portrait';
$string['otherheader'] = 'Other';
$string['pageorientation'] = 'Page orientation';
$string['participants'] = 'Participant(s)';
$string['participants_help'] = 'List of participant(s) who have booked this slot.';
$string['pasttimeslotstring'] = 'xxx slots could not be created because the creation of past time slots is not allowed.';
$string['pasttimeslotstringphp'] = '{$a->slots} slots could not be created because the creation of past time slots is not allowed.';
$string['pdf_notactive'] = 'not active';
$string['pdfsettings'] = 'PDF settings';
$string['places_inqueue'] = '{$a->inqueue} on waiting list';
$string['places_inqueue_withposition'] = 'You are {$a->queueposition}. on waiting list';
$string['places_taken_pl'] = '{$a->numtakenplaces}/{$a->totalplaces} places taken';
$string['places_taken_sg'] = '{$a->numtakenplaces}/{$a->totalplaces} place taken';
$string['pluginadministration'] = 'Organizer administration';
$string['pluginname'] = 'Organizer';
$string['position'] = 'Position in queue';
$string['print_return'] = 'Return to slot overview';
$string['printout'] = 'Printout';
$string['printpreview'] = 'Print preview (first 10 entries)';
$string['printslotuserfieldsnotenabled'] = 'The feature Print Slot user Fields is not enabled by the administrator.';
$string['printsubmit'] = 'Display printable table';
$string['privacy:metadata:applicantidappointment'] = 'Identifier of the user who booked this slot for the group.';
$string['privacy:metadata:applicantidqueue'] = 'Identifier of the user who made this entry in the waiting queue for the group.';
$string['privacy:metadata:attended'] = 'Whether or not the user or group attended the slot.';
$string['privacy:metadata:comments'] = 'The trainers comments for this slot.';
$string['privacy:metadata:feedback'] = 'The trainers feedback when grading the slot.';
$string['privacy:metadata:grade'] = 'The grade the user or group received for this slot.';
$string['privacy:metadata:groupidappointment'] = 'Identifier of the usergroup who booked this slot.';
$string['privacy:metadata:groupidqueue'] = 'Identifier of the group who made this entry in the waiting queue for a slot.';
$string['privacy:metadata:organizerslotappointments'] = 'Table in which slot appointments are stored.';
$string['privacy:metadata:organizerslotqueues'] = 'Table in which waiting queue entries for slots are stored.';
$string['privacy:metadata:organizerslottrainer'] = 'Table in which the trainers of a slot are stored.';
$string['privacy:metadata:showfreeslotsonly'] = 'User preference: Shall slot table display free slots only.';
$string['privacy:metadata:showhiddenslots'] = 'User preference: Shall slot table display hidden slots.';
$string['privacy:metadata:showmyslotsonly'] = 'User preference: Shall slot table display my slots only.';
$string['privacy:metadata:showpasttimeslots'] = 'User preference: Shall slot table display past slots as well.';
$string['privacy:metadata:showregistrationsonly'] = 'User preference: Shall slot table display only registrations.';
$string['privacy:metadata:teacherapplicantid'] = 'Identifier of the trainer who assigned this slot to a participant or group.';
$string['privacy:metadata:teacherapplicanttimemodified'] = 'Time when a trainer assigned this slot to a participant or group.';
$string['privacy:metadata:trainerid'] = 'Identifier of a trainer of a slot.';
$string['privacy:metadata:useridappointment'] = 'Identifier of the user who booked this slot.';
$string['privacy:metadata:useridqueue'] = 'Identifier of the user who made this entry in the waiting queue for a slot.';
$string['queue'] = 'Waiting queues';
$string['queue_help'] = 'Waiting queues allow users to register to a time slot even if the maximum number of participants is already reached.
		Users are added to a waiting queue and assigned to the slot (in order) as soon as a slot becomes available.';
$string['recipientname'] = '_recipient name_';
$string['reg_not_occured'] = 'This slot has not occurred yet';
$string['reg_status'] = 'Registration status';
$string['reg_status_not_registered'] = 'Not registered';
$string['reg_status_organizer_expired'] = 'Organizer expired';
$string['reg_status_registered'] = 'Registered';
$string['reg_status_slot_attended'] = 'Attended';
$string['reg_status_slot_available'] = 'Slot available';
$string['reg_status_slot_expired'] = 'Slot expired';
$string['reg_status_slot_full'] = 'Slot full';
$string['reg_status_slot_not_attended'] = 'Did not attend';
$string['reg_status_slot_past_deadline'] = 'Slot past deadline';
$string['reg_status_slot_pending'] = 'Slot pending evaluation';
$string['register_notify_teacher:queue:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has queued for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:queue:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has wait-listed the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:queue:group:smallmessage'] = 'Student {$a->sendername} has wait-listed the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:queue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group wait-listed';
$string['register_notify_teacher:queue:smallmessage'] = 'Student {$a->sendername} has queued for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:queue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student queued';
$string['register_notify_teacher:register:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has registered for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:register:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:register:group:smallmessage'] = 'Student {$a->sendername} has registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:register:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group registered';
$string['register_notify_teacher:register:smallmessage'] = 'Student {$a->sendername} has registered for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:register:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student registered';
$string['register_notify_teacher:reregister:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has re-registered for the new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:reregister:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has re-registered the group {$a->groupname} for the new time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:reregister:group:smallmessage'] = 'Student {$a->sendername} has re-registered the group {$a->groupname} for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:reregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group re-registered';
$string['register_notify_teacher:reregister:smallmessage'] = 'Student {$a->sendername} has re-registered for the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:reregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student re-registered';
$string['register_notify_teacher:unqueue:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has took himself off the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:unqueue:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has removed the group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:unqueue:group:smallmessage'] = 'Student {$a->sendername} has removed the group {$a->groupname} from the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:unqueue:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group removed from waiting list';
$string['register_notify_teacher:unqueue:smallmessage'] = 'Student {$a->sendername} has took himself off the waiting list of the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:unqueue:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student removed from waiting list';
$string['register_notify_teacher:unregister:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has unregistered from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:unregister:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, student {$a->sendername} has unregistered the group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.

Moodle Messaging System';
$string['register_notify_teacher:unregister:group:smallmessage'] = 'Student {$a->sendername} has unregistered the group {$a->groupname} from the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:unregister:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Group unregistered';
$string['register_notify_teacher:unregister:smallmessage'] = 'Student {$a->sendername} has unregistered from the time slot on {$a->date} at {$a->time} in {$a->location}.';
$string['register_notify_teacher:unregister:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Student unregistered';
$string['register_promotion_student:fullmessage'] = 'Your registration for a timeslot has been promoted from status \'waiting list\' to status \'booked\'.';
$string['register_promotion_student:group:fullmessage'] = 'Your group\'s registration for a timeslot has been promoted from status \'waiting list\' to status \'booked\'.';
$string['register_promotion_student:group:smallmessage'] = 'Your group\'s registration for a timeslot has been promoted from status \'waiting list\' to status \'booked\'.';
$string['register_promotion_student:group:subject'] = 'Moodle Organizer: Group promoted from queue';
$string['register_promotion_student:smallmessage'] = 'Your registration for a timeslot has been promoted from status \'waiting list\' to status \'booked\'.';
$string['register_promotion_student:subject'] = 'Moodle Organizer: Promoted from queue';
$string['register_reminder_student:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, you haven\'t registered to the requested amount of time slots yet.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder_student:group:fullmessage'] = 'Hello {$a->receivername}!

As a part of the course {$a->courseid} {$a->coursefullname}, your group {$a->groupname} hasn\'t registered the requested amount of time slots yet.

{$a->custommessage}

Moodle Messaging System';
$string['register_reminder_student:group:smallmessage'] = 'Please register your group to the requested amount of time slots.';
$string['register_reminder_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Registration reminder';
$string['register_reminder_student:smallmessage'] = 'Please register to the requested amounts of time slots.';
$string['register_reminder_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Registration reminder';
$string['relative_deadline_before'] = 'before the appointment';
$string['relative_deadline_now'] = 'Starting now';
$string['relativedeadline'] = 'Relative deadline';
$string['relativedeadline_help'] = 'Sets the deadline for the application for a specific slot specified time ahead. The students will not be able to change register or unregister once the deadline has expired.';
$string['remindall_desc'] = 'Send reminders to all participants without an appointment';
$string['remindallmultiple_desc'] = 'Send reminders to all participants without enough appointments';
$string['requiremodintro'] = 'Require activity description';
$string['reset_organizer_all'] = 'Deleting slots, appointments and related events';
$string['resetorganizerall'] = 'Clear all organizer data (slots & appointments)';
$string['reviewsubmit'] = 'Review time slots';
$string['rewievslotsheader'] = 'Review time slots';
$string['search:activity'] = 'Organizer - activity information';
$string['searchfilter'] = 'Search / Filter';
$string['sec'] = 'sec';
$string['sec_pl'] = 'secs';
$string['select'] = 'Select slots';
$string['select_all_entries'] = 'Select all entries';
$string['select_all_slots'] = 'Select all visible slots';
$string['select_help'] = 'Select one ore more slots you want to work with.';
$string['selectedgrouplist'] = 'Selected groups';
$string['selectedslots'] = 'Selected slots';
$string['showmore'] = 'Show more';
$string['signature'] = 'Signature';
$string['singleslotcommands'] = 'Single slot action';
$string['singleslotcommands_help'] = 'Click an action button to work directly on one slot.';
$string['singleslotprintfield'] = 'Print slot user field';
$string['singleslotprintfield0'] = 'Print slot user field';
$string['singleslotprintfield0_help'] = 'These user fields are used for each participant when a single slot is printed out.';
$string['singleslotprintfields'] = 'Single print slot user profile fields';
$string['singleslotprintfields_help'] = 'In this section you define additional personal fields to be printed out for each participant when a single slot is printed out.';
$string['slot'] = 'Appointment';
$string['slot_anonymous'] = 'Slot anonymous';
$string['slot_slotvisible'] = 'Members only visible if own slot';
$string['slot_visible'] = 'Members of slot always visible';
$string['slotassignedby'] = 'Slot assigned by';
$string['slotdeleted_notify_student:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was cancelled.';
$string['slotdeleted_notify_student:group:fullmessage'] = 'Hello {$a->receivername}!

Your appointment in the course {$a->courseshortname} on {$a->date} at {$a->time} in {$a->location} was cancelled.';
$string['slotdeleted_notify_student:group:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in organizer \'{$a->organizername}\' was cancelled.';
$string['slotdeleted_notify_student:group:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment cancelled';
$string['slotdeleted_notify_student:smallmessage'] = 'Your appointment on {$a->date} at {$a->time} in organizer \'{$a->organizername}\' was cancelled.';
$string['slotdeleted_notify_student:subject'] = '[{$a->courseid}{$a->courseshortname} / {$a->organizername}] - Appointment cancelled';
$string['slotdetails'] = 'Slot details';
$string['slotfrom'] = ' from';
$string['slotlistempty'] = 'No slots were found.';
$string['slotoptionstable'] = '\'Extending this table\'';
$string['slotoptionstable_help'] = 'Show past slots or hidden slots as well.';
$string['slotperiodendtime'] = 'End date';
$string['slotperiodheader'] = 'Generate slots for date range';
$string['slotperiodheader_help'] = 'Specify the starting and the ending date to which the daily time frames (section below) will apply. Specify here as well whether the slot shall be visible to students.';
$string['slotperiodstarttime'] = 'Start date';
$string['slottimeframesheader'] = 'Specific time frames';
$string['slottimeframesheader_help'] = 'This section allows for weekday-based definition of time frames which will be filled with appointment slots with properties specified above. There can be more than one time frame per day. If a time frame on Monday is selected, it will generate time slots for every Monday between the starting and the ending date (inclusive).';
$string['slotto'] = 'to';
$string['status'] = 'Status details';
$string['status_help'] = 'Current status of this slot.';
$string['status_no_entries'] = 'This organizer has no registered students.';
$string['stroptimal'] = 'optimal';
$string['studentcomment_title'] = 'Student comments';
$string['synchronizegroupmembers'] = 'Synchronize group members';
$string['synchronizegroupmembers_help'] = 'If Moodle group members change the changes will be put through to booked slots.';
$string['taballapp'] = 'Appointments';
$string['tabstatus'] = 'Registration status';
$string['tabstud'] = 'Student view';
$string['teacher'] = 'Teacher';
$string['teacher_help'] = 'List of trainers of this slot.';
$string['teacher_unchanged'] = '-- unchanged --';
$string['teachercomment_title'] = 'Teacher comments';
$string['teacherfeedback_title'] = 'Teacher feedback';
$string['teacherid'] = 'Trainer';
$string['teacherid_help'] = 'Select the trainer you want to lead the appointments';
$string['teacherinvisible'] = 'Teacher invisible';
$string['teachervisible'] = 'Teacher visible';
$string['teachervisible_help'] = 'Check this if you want to allow students to see the teacher associated with the timeslot.';
$string['textsize'] = 'Textsize';
$string['th_actions'] = 'Action';
$string['th_appdetails'] = 'Details';
$string['th_attended'] = 'Att.';
$string['th_bookings'] = 'Total Bookings';
$string['th_comments'] = 'Participant Comment';
$string['th_datetime'] = 'Date & time';
$string['th_datetimedeadline'] = 'Date & time';
$string['th_details'] = 'Status';
$string['th_duration'] = 'Duration';
$string['th_email'] = 'Email';
$string['th_evaluated'] = 'Eval';
$string['th_feedback'] = 'Feedback';
$string['th_firstname'] = 'First name';
$string['th_grade'] = 'Grade';
$string['th_group'] = 'Group';
$string['th_groupname'] = 'Group';
$string['th_idnumber'] = 'ID number';
$string['th_lastname'] = 'Last name';
$string['th_location'] = 'Location';
$string['th_participant'] = 'Participant';
$string['th_participants'] = 'Participants';
$string['th_status'] = 'Status';
$string['th_teacher'] = 'Teacher';
$string['th_teachercomments'] = 'Teacher comment';
$string['timeshift'] = 'Shifting absolute deadline';
$string['timeslot'] = 'Organizer Slot';
$string['timetemplate'] = '%H:%M';
$string['title_add'] = 'Add new appointment slots';
$string['title_comment'] = 'Edit your comments';
$string['title_delete'] = 'Delete selected time slots';
$string['title_delete_appointment'] = 'Delete assigned appointment';
$string['title_edit'] = 'Edit selected time slots';
$string['title_eval'] = 'Evaluate selected time slots';
$string['title_print'] = 'Print slots';
$string['totalday'] = 'xxx slots for yyy persons';
$string['totalday_groups'] = 'xxx slots for yyy groups';
$string['totalslots'] = 'from {$a->starttime} to {$a->endtime}, {$a->duration} {$a->unit} each, {$a->totalslots} slot(s) in total';
$string['totaltotal'] = 'Total: xxx slots for yyy persons';
$string['totaltotal_groups'] = 'Total: xxx slots for yyy groups';
$string['trainer'] = 'Trainer';
$string['trainerid'] = 'Teacher';
$string['trainerid_help'] = 'Select the teacher you want to lead the appointments';
$string['unavailableslot'] = 'This slot is available from';
$string['unknown'] = 'Unknown';
$string['userslots_mingreatermax'] = 'Minimum user slots is greater than maximum.';
$string['userslotsdailymax'] = 'Maximum of slots per participant or group per day';
$string['userslotsdailymax_help'] = 'Amount of slots a participant or group is allowed to book per day. \'0\' means there is no daily limit.';
$string['userslotsmax'] = 'Maximum of slots per participant or group';
$string['userslotsmax_help'] = 'Amount of slots a participant or group is allowed to book.';
$string['userslotsmin'] = 'Minimum of slots per participant or group';
$string['userslotsmin_help'] = 'Minimum of slots a participant or group has to book.';
$string['visibility'] = 'Visibility of members - presetting';
$string['visibility_all'] = 'Visible';
$string['visibility_anonymous'] = 'Anonymous';
$string['visibility_help'] = 'Definition of the default visibility option with which a new slot will be created.<br/><b>Anonymous:</b> The members of this slot are always invisible to all.<br/><b>Visible:</b> All members of this slot are always visible to all.<br/><b>Only visible to slot members:</b> Only slot members can see each other.';
$string['visibility_slot'] = 'Only visible to slot members';
$string['visible'] = ' Slot visible';
$string['waitinglists_desc_active'] = 'Waiting lists are activated.';
$string['waitinglists_desc_notactive'] = 'Waiting lists are not activated.';
$string['warning_groupingid'] = 'Group mode enabled. You must select a valid grouping.';
$string['warninggroupmode'] = 'You must enable the group mode and select a grouping to create a group organizer!';
$string['warningtext1'] = 'Selected slots contain different values in this field!';
$string['warningtext2'] = 'WARNING! The contents of this field have been changed!';
$string['weekdaylabel'] = 'Weekday slot';
$string['with'] = 'with';
