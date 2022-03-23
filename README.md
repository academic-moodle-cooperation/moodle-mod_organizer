Organizer Module
================

This file is part of the mod_organizer plugin for Moodle - <http://moodle.org/>

*Author:*    Thomas Niedermaier, Ivan Sakic, Katarzyna Potocka

*Copyright:* 2020 [Academic Moodle Cooperation](http://www.academic-moodle-cooperation.org)

*License:*   [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The Organizer module allows participants to subscribe to events, which can be created easily and
efficiently by teachers.

The Organizer consists of three tabs:

* **Events:** provides an overview of all available and past events and allows teachers to create
  new events, delete and edit existing events, assess events in which people have enrolled and
  print all selected events.

* **Students' view:** shows a simplified view of the enrolment page as seen by participants.

* **Enrolment status:** provides a detailed overview of participants' enrolments. This tab allows
  teachers to send reminders to students who have not enrolled yet.

Teachers have access to all three tabs, whereas students can only access the second tab "students'
view".

**Group organizer:**

If a group organizer was created, a grouping must be selected in the settings for which the
organizer is to be used. As soon as one participant enrols, all other group members are enrolled as
well and are notified about time and place of the event.

Note: Students can be enrolled in only one group of this grouping.


Example
-------

Create events for group meetings for submitting a project to allow students to enrol in an event of
their choice. The events should, for example, be created for the two upcoming weeks to take place
from Tuesdays to Thursdays, 8 a.m. to 12 p.m., at 15 minutes each. Enrolments, changes and
unenrolments should be possible up to two days prior to the event, or at least by a specific date.
Students' attendance and grades are to be documented.


Requirements
------------

The plugin is available for Moodle 2.5+. This version is for Moodle 3.9.


Installation
------------

* Copy the module code directly to the mod/organizer directory.

* Log into Moodle as administrator.

* Open the administration area (http://your-moodle-site/admin) to start the installation
  automatically.


Admin Settings
--------------

As an administrator you can set the default values instance-wide on the settings page for
administrators in the organizer module:

* Best grade (text field)
* E-mail notification (drop down)
* Send a summary of events (drop down)
* Final deadline (drop down)
* Relative deadline (drop down)


Documentation
-------------

You can find a cheat sheet for the plugin on the [AMC
website](https://www.academic-moodle-cooperation.org/en/module/organizer/) and a video tutorial in
german only in the [AMC YouTube Channel](https://www.youtube.com/c/AMCAcademicMoodleCooperation).


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](<https://github.com/academic-moodle-cooperation/moodle-mod_organizer/issues). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
