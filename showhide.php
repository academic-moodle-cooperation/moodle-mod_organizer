<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../config.php');
require_once('../../lib/moodlelib.php');

$showpasttimeslots = optional_param('showpastslots', -1, PARAM_INT);
$showmyslotsonly = optional_param('showmyslotsonly', -1, PARAM_INT);

if ($showpasttimeslots != -1) {
    set_user_preference('mod_organizer_showpasttimeslots', $showpasttimeslots);
}

if ($showmyslotsonly != -1) {
    set_user_preference('mod_organizer_showmyslotsonly', $showmyslotsonly);
}