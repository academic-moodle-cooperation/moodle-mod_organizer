CHANGELOG
=========

4.5.1 (2025-03-06)
------------------
* [FIXED] Error message after deactivating "hide calendar" instance option

4.5.0 (2025-01-22)
------------------
* [FEATURE] Deleting an organizer instance: notify participants of future appointments
* [FEATURE] Groupmode: Groupings not mandatory anymore
* [FEATURE] Added action to export slot as ics calendar file
* [FEATURE] Add github workflow
* [FIXED] Remove differences in database schema
* [FIXED] Fix Codechecker warnings
* Moodle 4.5 compatible version

4.4.2 (2024-07-11)
------------------
* [FEATURE] New setting: Limit the daily possible bookings for participants
* [UPDATE]  New organizer icon for Moodle 4.4 (once again)
* [FIXED]   Enable assignment of expired slots
* [FIXED]   Synchonize moodle group member changes in organizer slots

4.4.1 (2024-05-23)
------------------
* [FIXED]   Fix organizer cron job (digest, teacherid)
* [FIXED]   Fix registration view bug when no students in course or groups in groupings

4.4.0 (2024-04-15)
------------------
* [UPDATE]  New moodle 4.4 plugin icon
* [FIXED]   Remove console debug messages
* [FIXED]   Fix calendar block (three month view deprecated)
* Moodle 4.4 compatible version

4.3.1 (2024-02-23)
------------------
* [FEATURE] Allow deleting of appointments in registration view for holders of the new right deleteappointments
* [FIXED]   Fixed deleting of appointments error
* [FIXED]   Email message: fix typo with empty comments
* [FIXED]   Appointments view: No action button for grading if grading is not active
* [FIXED]   Show participants with bookings in reg view regardless of access restrictions
* [FIXED]   Moodle 4 third navigation select field instead of tab row
* [FIXED]   Take restricted access into account also on appointmentstatus bar in reg view and dont count empty groups

4.3.0 (2024-02-08)
------------------
* [FIXED]   Student view: Fix "Show all participants" option, remove the broken "Show only my slots"-filter option
* [FIXED]   Registration view: Do not list groups which have no members
* [FIXED]   Do not send group messages to group trainers
* [FIXED]   Fix dynamic property warnings in php 8.2
* [FIXED]   Admin setting "hide identity" fixed
* [FIXED]   Registration view: Do not check disabled checkboxes when "Checking all"
* [FIXED]   After grading: return to calling page
* [FEATURE] Make all icons font-awesome 4.7
* [FEATURE] New Admin setting: Content width optionally in Moodle 4.x style (=smaller)
* [FEATURE] Bulk-actions: Start-buttons not enabled unless there is at least one checked checkbox
* [FEATURE] Improve design of Grading slots-panel
* [FEATURE] Registration view: Add the possibility to send reminders to a manually selected list of users
* [FEATURE] Registration view: Disable send reminders- and assign-buttons if there is no free place or the booking's maximum is reached
* [FEATURE] Registration view: If organizer instance is in moodle group-mode a group selector can be used to filter the participants list
* [FEATURE] Registration view: Only participants who's access is not restricted by trainer defined instance restrictions are listed. 
* Moodle 4.3 compatible version

4.2.3 (2023-11-17)
------------------
* [FIXED] #7721: error message when deleting enrollments
* [FIXED] #7746: don't allow not-students to be graded (in group mode)
* [FIXED] #7745: don't show suspended users in registration view
* [FIXED] #7778: include statement does not work using Bulk user actions - delete
* [FEATURE] #7744: new admin setting: don't show participant's ID in slot list

4.2.2 (2023-09-22)
------------------
* [FIXED] #7670: course fullname instead of the shortname in PAGE heading
* [FIXED] #7697: print single slot: error message when special userprofile fields are filled
* [FIXED] #7705: incorrect success message when creating just one slot
* [FEATURE] #7701: No calendar events for empty slots-option only editable when creating an organizer instance
* [FEATURE] #7706: managers should not have leadslots permission by default

4.2.1 (2023-08-10)
------------------
* [FIXED] #7660: fix typo in observer.php

4.2.0 (2023-05-31)
------------------
* [FIXED] #7444: remove double module description
* [FIXED] #7518: fix grading error with postgres
* [FIXED] #7519: fix mktime php 8.1 error
* [FIXED] #7520: introduce new moodle 4.x compatible icons
* [FIXED] #7522: fix evaluation form group mode error message
* [FIXED] #7637: fix calendar bug
* Moodle 4.2 compatible version

3.11.7 (2023-03-16)
------------------
* [FEATURE] #7506: Make minimum and maximum of bookable slots per instance always changeable

3.11.6 (2023-03-10)
------------------
* [FIXED] #7485: Github #112 - registration view does not work when there are no participants

3.11.5 (2023-02-10)
------------------
* [FIXED] #7431: sort participants by lastname, firstname in grading form
* [FIXED] #7449: wrong forecast if dateto time is less than datefrom time in adding slots form
* [FIXED] #7462: no loading add slots page anymore after pressing enter in search field of slot overview
* [FIXED] #7464: dont loose slot options user preferences when changing to registration overview page
* [FEATURE] #7446: multiple slots registrations per user/group in one single organizer instance
* [FEATURE] #7458: better grouping of slot overview options
* [FEATURE] #7459: deleting an appointment
* [FEATURE] #7460: in-app notifications in Moodle style

3.11.4 (2022-10-31)
------------------
* [FIXED] #6383: slot edit leads to shortened location string
* [FIXED] #6954: cancelled slot grading not removable
* [FIXED] #7181: calendar export shows no organizer entries
* [FIXED] #7215: debug messages when clicking the gear icon of a slot
* [FIXED] #7219: slot creation: make the time input field a select field
* [FIXED] #7232: slot creation: warning message when using a gap
* [FEATURE] #7233: backup/copy: copy slots as well
* [FIXED] #7248: slot edit form: label "Relative appointment reminder" is shown two times
* [FIXED] #7249: slot view: Display/hide slot options settings are not saved
* [FIXED] #7278: show completion status in view.php
* [FIXED] #7380: slot edit form: cancel button does not work if there is a location entry

3.11.3 (2022-04-25)
------------------
* [FEATURE]  Provide activity dates for display in course overview

3.11.2 (2022-01-04)
------------------
* [BUG]  #6506 - fix participation when unenrolled from course - github issue #59

3.11.1 (2022-01-03)
-------------------
* [FIXED] #6968 - fix db discrepancies caused by wrong upgrade.php - github issue #98

3.11.0 (2021-06-23)
-------------------
* Moodle 3.11 compatible version

3.10.1 (2021-04-14)
------------------
* [FIXED] #6848: github #90 - fix request uri too long when creating/editing many slots
* [FIXED] #6849: github #84 - fix usage of undefined function fullusername
* [FEATURE] #6882: github #96 - use message API to send messages (Philipp Hager)

3.10.0 (2020-12-01)
------------------
* Moodle 3.10 compatible version

3.9.1 (2020-10-02)
------------------
* [FIXED] #6760: fix confusing slots filtering options
* [FIXED] #6762: Temporary hotfix for slow calendar events fetching

3.9.0 (2020-08-25)
------------------
* Moodle 3.9 compatible version


3.8.3 (2020-07-22)
------------------
* [FEATURE] #6067: github #43 Data leak with feature "Print slot user fields" part two

3.8.2 (2020-05-12)
------------------

* [FIXED] #6627 - show only relevant calendar entries for slots and appointments

3.8.1 (2020-02-17)
------------------

* [FEATURE] #6109: github #44 Multi language processing of custom user profile fields is missing
* [FEATURE] #6067: github #43 Data leak with feature "Print slot user fields"

3.8.0 (2020-02-05)
------------------

* Moodle 3.8.0 compatible version
* [FIXED] #6382: Undefined variable: regslotx in view_lib.php on line 2459
* [FIXED] #6423: Remove header in csv export file
* [FIXED] #6489: Student can see identity field
* [FIXED] #6499: Fix slot ID error when fetching trainers
* [FIXED] #6502: Readme.md wrong_URL
* [FIXED] #6373: Make all events organizer events

3.7.2 (2020-01-28)
------------------

* [FIXED] #6490: Warning when student registers for slot
* [FIXED] #6488: Notice: Undefined variable: regslotx in view_lib.php on line 2459
* [FIXED] #6486: Fatal regression: Impossible to ungregister from slot with version v3.7.1

3.7.1 (2019-10-30)
------------------

* [FIXED] #6366: Fatal error when creating an organizer instance with groups and a student in multiple groups
* [FEATURE] #6083: Print slots infos: Print participants slot comments as well

3.7.0 (2019-08-05)
------------------

* [FIXED] #6232: Debug message when booking slot that has no trainer
* [FIXED] #6216: Calendar view: when organizer instance option "No calendar events for empty slots" is deactivated
and a student books a slot, the calendar message changes the language
* [FIXED] #6186: Debug message registration view when first column not unique id
* [FIXED] #6211: SLOTS PRINT Print Preview: Cell-lines not visible anymore, table does not scroll to the right i
content is overflowing, table does not use the full width that is usable
* [FIXED] #6210: Add slots: The "Required" icon at the trainer option should be removed
* [FIXED] #6191: Adding/Editing a new organizer instance: new groupmodes are not selectable
* [FIXED] #6190: Disarranged appointment view
* [FIXED] #6189: "Add new slot" button does not work anymore
* [FEATURE] #6112: Registration view: Changes to the infobox
* [FEATURE] #6111: Student view: Changes to the infobox
* [FEATURE] #6110: Appointments view: Changes to the infobox
* [FIXED] #6174: [github #55/pull request #56] PHP warning when running cronjob
* [FIXED] #6157: Creation of past time slots not possible because of newly programmed consideration of the
booking deadline
* [FIXED] #6172: Reregister is not possible anymore
* [FIXED] #6156: Debug message undefined mail variable
* [FIXED] #6158: Edit a single slot: changes are not saved
* [FEATURE] #6152: Instance settings: Selectfield "grouping" does not appear when choosing organizer groupmode
"use existing groups"
* [FEATURE] #5937: Privacy API - implement deletion
* [FEATURE] #6137: Change settings: "Hide calendar" default, move grade section over the Print slot user fields section
* [FEATURE] #3779: Move student view to the third place in the tab row
* [FIXED] #6127: a teacher's  userpreference "show my slots only" hides all slots in a different organizer instance
where the same user is student.
* [FEATURE] #5784: Make registration view sortable by time/date
* [FEATURE] #6107: LOCATION: Edit a slot - location field not mandatory any more and an input field if suggestion list
is empty


3.6.2 (2019-05-14)
------------------

* [HOTFIX] #6114: After reregistration the trainers of the newly booked slot are wrongly added to the slot booked before
* [HOTFIX] #6106: Re-assign student to other slot not possible

3.6.2 (2019-05-14)
------------------

* [HOTFIX] #6114: After reregistration the trainers of the newly booked slot are wrongly added to the slot booked before
* [HOTFIX] #6106: Re-assign student to other slot not possible

3.6.1 (2019-03-29)
------------------

* [FIXED] #6064: Error when choosing groupmode "use existing course groups" for an organizer instance
* [FIXED] #6063: Function "organizer_fetch_user_group" not found
* [FIXED] #6066: Trainer is able to assign more than one slot to specific student
* [PULL REQUEST] github #49: fix tablename in organizer_send_message_from_trainer()

3.6.0 (2019-02-08)
------------------

* [FIXED] #5899: Organizer debug message if group is missing
* [FIXED] #5749: Waiting list entries does not move up when the slot's participants maximum is increased
* [FIXED] #5886: slots_print.php: Columns Email and Participants not sortable for the moment
* [FIXED] #5878: Double scrollbar in print preview table
* [FIXED] #5869: Too many calendar events for bookings when moodle group is involved
* [FIXED] #5830: cronjob throws error when global search is activated
* [FIXED] #5727: Only instance calendar events as course level events, deploy filters on event name
* [FEATURE] #5756: Privacy API: Implement new provider (mandatory)
* Moodle 3.6.1 compatible version

3.5.6 (2019-05-14)
------------------

* [HOTFIX] #6114: After reregistration the trainers of the newly booked slot are wrongly added to the slot booked before
* [HOTFIX] #6106: Re-assign student to other slot not possible

3.5.5 (2019-03-27)
------------------

* [FIXED] #6066: Trainer is able to assign more than one slot to specific student
* [PULL REQUEST] github #49: fix tablename in organizer_send_message_from_trainer()
* [FIXED] #6064: Error when choosing groupmode "use existing course groups" for an organizer instance
* [FIXED] #6063: Function "organizer_fetch_user_group" not found
* [FIXED] #5869: Too many calendar events for bookings when moodle group is involved
* [FIXED] #5878: Double scrollbar in print preview table
* [FIXED] #5886: slots_print.php: Columns Email and Participants not sortable for the moment.
* [FIXED] #5749: Waiting list entries does not move up when the slot's participants maximum is increased
* [FIXED] #5899: Organizer debug message if group is missing

3.5.2 (2018-09-26)
------------------

* [FIXED] #5732: Creating a new slot or register for a new slot with new groupmode ends up with an error

3.5.1 (2018-09-05)
------------------

* [FIXED] #5721: Slot-creation: Error with daylight saving time

3.5.0 (2018-08-10)
------------------

* [FIXED] #5569: Wrong deadline and missing singleslotprintfields when importing organizer instance backup
* [FIXED] #5561: Restore and update collision checking
* [FIXED] #5567: Groupmember enrollment message for teacher does not contain the correct groupname
* [FIXED] #5562: Clicking link of organizer system message leads to an error & Rewrite function
organizer_get_eventaction_student
* [FIXED] #5565: lang strings teacherid and teacherid_help missing in en
* [FIXED] #5564: Event of deleted slot remains in student calendar AND event name improvements for students
appointment events
* [FIXED] #5558: slot evaluation - form elements in disorder
* [FIXED] #5554: Debug Messages because of missing lang-strings
* [FIXED] #5506: Add new slots: Change to-date time from 00:00:00 to 23:59:59
* [FIXED] #5496: Counting the slots to be created does not work after adding additional empty slots
* [FEATURE] #3260: Rebuild slot overview - helpicons in column headers
* [FEATURE] #5383: Implement Privacy API (without deletion of data)
* [FEATURE] #5118: Remove german langstring-File from master
* [PULL REQUEST] Merge pull request #40 from edaktik
* [FEATURE] #3777: Redesign table - tooltipps replace legend, filter slots, sort only sortable fields
* [FEATURE] edaktik#3269 Adding past slots now possible

3.4.24 (2018-04-26)
------------------

* [FIXED] #5418: Error when writing groupmode in course_modules after an organizer instance was restored

3.4.2 (2018-02-12)
------------------

* [FIXED] #5173: Decription text not processed by filters

3.4.1 (2018-01-29)
------------------

* Moodle 3.4.1 compatible version
* [FIXED] #4518: Values overwritten in gradebook of type scala are not shown in organizer
* [FIXED] #4919: Overwritten grading value of 0 points is not shown in organizer
* [FIXED] #5177: Error message in grouptool when importing new participant manually
* [FIXED] #5231: Getting wrong user group when messaging in group mode
* [FEATURE] #4899: Display forecast of the number of slots to be created in slots add page
* [FEATURE] #3811: Rewrite YUI to JQuery/Javascript
* [FEATURE] #3778: Students identity information (ID-number or email) not visible for other students
* [FEATURE] #3883 Hide slots from students

3.4.0 (2017-11-29)
------------------

* Moodle 3.4 compatible version
* [FIXED] #4971: When sending notification mails triggered by slot assignment placeholders are not replaced by value
* [FIXED] #4967: When sending notification emails triggered by changed slot data not all placeholders are replaced
* [FIXED] #4923: Adding a slot - weekday names only in english
* [FIXED] #4928: Warning when loading slot overview page with calendar
* [FIXED] #4922: Organizer: lang strings are rendered twice in pages slots_add and slots_edit
* [FIXED] #4674: Dashboard error in group mode
* [FEATURE] #3883 Hide slots from students

3.3.1 (2017-08-09)
------------------

* [FIXED] #4649: DB-Error after group applies for a slot

3.3.0 (2017-07-19)
------------------

* Moodle 3.3 compatible version
* [FEATURE] #4500 Replace modulename_print_overview()-notifications by calendar entries with action events
* [FEATURE] #3112 Better recognizable icons (svg)
* [FIXED] #4481 Error message when selecting no action option on slot overview page
* [FEATURE] #3780 Participantslist in slot overview table newly formatted
* [FIXED] #3318 Message provider names now according to the moodle standards
* [FEATURE] #3259 Module description in moodle layout
* [FEATURE] #3257 Add a slot: only one page
* [FIXED] #4405 Location gets lost when creating a slot


3.2.1 (2017-03-28)
------------------

* [FIXED] #4350 Messages sent to group members although group mode is not activated
* [FIXED] #4367 Page "Registration status" in goupmode lacks groups without registration


3.2.0 (2017-01-31)
------------------

* Moodle 3.2 compatible version
* [FEATURE] #3595 Enable Global Search for organizer
* [CHANGE] #3675 Replace cron job with Task API
* [CHANGE] #3889 Use message class for message object instead of stdClass
* [CHANGE] #3890 Add course-ID to message objects


3.1.5 (2016-11-15)
------------------

* [FIXED] #3826 Slots without registration can not be printed


3.1.4 (2016-10-21)
------------------

* [FIXED] #3771 Expired organizer can not be graded and slots can not be printed


3.1.3 (2016-09-09)
------------------

* [FIXED] #3754 PostgreSQL database error on tab registration status


3.1.2 (2016-08-30)
------------------

* [FIXED] #3691 Fails on new installation


3.1.0 (2016-07-21)
------------------

* Moodle 3.1 compatible version
* [FEATURE] #3556 Enhanced legend
* [FEATURE] #3254 After creating a new organizer the user will be sent to the "Add a new
  slot" page immediately
* [FEATURE] #3114 Action-buttons in up-to-date moodle style
* [FEATURE] #3118 Improve wording of email messages (location)
* [FEATURE] #3132 Show participants in the waiting list of a slot to teachers
* [FEATURE] #2788 Capability for the responsibility for time slots
* [FEATURE] #3128 Add student to slot manually
* [FIXED] #3602 CSS
* [FIXED] #3349 Reminder e-mail not sent to group
* [FIXED] #3333 Wrong person cited in email on assignment of a slot
* [FIXED] #3286 Import of gradings
* [FIXED] #3325 Assignment of group slot by teacher
* [FIXED] #3311 Teachers can not be set to unvisible after application time expired
* [FIXED] #3313 Slots with duration > 36000 seconds (9h10m) are not possible
* [FIXED] #3322 Time specification in PDF


3.0.0 (2016-04-18)
------------------

* Moodle 3.0 compatible version
* [FEATURE] #3135 Make grading suppressible
* [FEATURE] #2778 New visibility statuses of slots for students
* [FEATURE] #2678 Create a waiting list for time slots that are fully booked
* [FIXED] #3116 Group appointments mode without grouping possible
* [FIXED] #3234 Students get notified constantly after the slot appointment is over, delete waiting
  queue entries when instance not queueable anymore


2.9 (2016-01-28)
----------------

* Moodle 2.9 compatible version


2.8 (2015-07-17)
----------------

* Moodle 2.8 compatible version


2.7 (2015-01-21)
----------------

* Moodle 2.7 compatible version


2.6 (2014-03-31)
----------------

* First release for Moodle 2.6
