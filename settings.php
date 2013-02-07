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

/**
 * Administration settings definitions for the organizer module.
 *
 * @package    mod
 * @subpackage organizer
 * @copyright  2011 Ivan Sakic
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/organizer/lib.php');
require_once($CFG->dirroot . '/mod/organizer/locallib.php');

// First get a list of quiz reports with their own settings pages. If there none,
// we use a simpler overall menu structure.
$reportsbyname = array();
if ($reports = get_plugin_list('organizer')) {
    foreach ($reports as $report => $reportdir) {
        if (file_exists("$reportdir/settings.php")) {
            $strreportname = get_string($report . 'report', 'organizer_' . $report);
            // Deal with reports which are lacking the language string
            if ($strreportname[0] == '[') {
                $textlib = textlib_get_instance();
                $strreportname = $textlib->strtotitle($report . ' report');
            }
            $reportsbyname[$strreportname] = $report;
        }
    }
    ksort($reportsbyname);
}

// Create the organizer settings page.
if (empty($reportsbyname)) {
    $pagetitle = get_string('modulename', 'organizer');
} else {
    $pagetitle = get_string('generalsettings', 'admin');
}
$organizersettings = new admin_settingpage('modsettingorganizer', $pagetitle, 'moodle/site:config');

// Introductory explanation that all the settings are defaults for the add quiz form.
$organizersettings->add(new admin_setting_heading('organizerintro', '', get_string('configintro', 'organizer')));

// Maximum grade
$organizersettings->add(
        new admin_setting_configtext('organizer/maximumgrade', get_string('maximumgrade'),
                get_string('configmaximumgrade', 'organizer'), 10, PARAM_INT));

// E-mail teachers
$pickeroptions = array();
$pickeroptions[ORGANIZER_MESSAGES_NONE] = get_string('messages_none', 'organizer');
$pickeroptions[ORGANIZER_MESSAGES_RE_UNREG] = get_string('messages_re_unreg', 'organizer');
$pickeroptions[ORGANIZER_MESSAGES_ALL] = get_string('messages_all', 'organizer');

// Appointment digest
$organizersettings->add(
        new admin_setting_configselect('organizer/emailteachers', get_string('configemailteachers_label', 'organizer'),
                get_string('configemailteachers', 'organizer'), 1, $pickeroptions));

$pickeroptions = array();
$pickeroptions['never'] = 'Don\'t send';
for ($i = 0; $i < 24; $i++) {
    $pickeroptions[$i * 3600] = userdate($i * 3600, get_string('timetemplate', 'organizer'), 0);
}

// Appointment digest
$organizersettings->add(
        new admin_setting_configselect('organizer/digest', get_string('configdigest_label', 'organizer'),
                get_string('configdigest', 'organizer'), 'never', $pickeroptions));

$abschoices = array();
$abschoices['never'] = get_string('never');
$abschoices['+1 week'] = "1 week";
$abschoices['+2 weeks'] = "2 weeks";
$abschoices['+3 weeks'] = "3 weeks";
$abschoices['+1 month'] = "1 month";
$abschoices['+2 month'] = "2 months";
$abschoices['+3 month'] = "3 months";
$abschoices['+4 month'] = "4 months";
$abschoices['+5 month'] = "5 months";
$abschoices['+6 month'] = "6 months";
$abschoices['+1 year'] = "1 year";

$organizersettings->add(
        new admin_setting_configselect('organizer/absolutedeadline', get_string('absolutedeadline', 'organizer'),
                get_string('configabsolutedeadline', 'organizer'), 'never', $abschoices));

$relchoices = array();
$relchoices[60 * 1] = "1 minute ahead";
$relchoices[60 * 5] = "5 minutes ahead";
$relchoices[60 * 15] = "15 minutes ahead";
$relchoices[60 * 30] = "30 minutes ahead";
$relchoices[3600 * 1] = "1 hour ahead";
$relchoices[3600 * 2] = "2 hours ahead";
$relchoices[3600 * 3] = "3 hours ahead";
$relchoices[3600 * 6] = "6 hours ahead";
$relchoices[3600 * 12] = "12 hours ahead";
$relchoices[3600 * 18] = "18 hours ahead";
$relchoices[86400 * 1] = "1 day ahead";
$relchoices[86400 * 2] = "2 days ahead";

$organizersettings->add(
        new admin_setting_configselect('organizer/relativedeadline', get_string('relativedeadline', 'organizer'),
                get_string('configrelativedeadline', 'organizer'), 86400, $relchoices));

// Now, depending on whether any reports have their own settings page, add
// the organizer setting page to the appropriate place in the tree.
if (empty($reportsbyname)) {
    $ADMIN->add('modsettings', $organizersettings);
} else {
    $ADMIN->add('modsettings',
            new admin_category('modsettingsquizcat', get_string('modulename', 'quiz'), !$module->visible));
    $ADMIN->add('modsettingsquizcat', $organizersettings);

    // Add the report pages for the settings.php files in sub directories of mod/quiz/report
    foreach ($reportsbyname as $strreportname => $report) {
        $reportname = $report;

        $settings = new admin_settingpage('modsettingsquizcat' . $reportname, $strreportname, 'moodle/site:config',
                !$module->visible);
        if ($ADMIN->fulltree) {
            include($CFG->dirroot . "/mod/quiz/report/$reportname/settings.php");
        }
        $ADMIN->add('modsettingsquizcat', $settings);
    }
}

$settings = null; // we do not want standard settings link
