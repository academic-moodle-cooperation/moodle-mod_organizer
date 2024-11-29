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
 * settings.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot . '/mod/organizer/locallib.php');

    // Introductory explanation that all the settings are defaults for the add quiz form.
    $settings->add(new admin_setting_heading('organizerintro', '', get_string('configintro', 'organizer')));

    // Maximumgrade.
    $settings->add(new admin_setting_configtext('organizer/maximumgrade',
            get_string('maximumgrade'),
            get_string('configmaximumgrade', 'organizer'), 0, PARAM_INT));

    // E-mail teachers.

    // Appointment digest mode.
    $pickeroptions = [];
    $pickeroptions[ORGANIZER_MESSAGES_NONE] = get_string('messages_none', 'organizer');
    $pickeroptions[ORGANIZER_MESSAGES_RE_UNREG] = get_string('messages_re_unreg', 'organizer');
    $pickeroptions[ORGANIZER_MESSAGES_ALL] = get_string('messages_all', 'organizer');

    $settings->add(
            new admin_setting_configselect('organizer/emailteachers',
                    get_string('configemailteachers_label', 'organizer'),
                    get_string('configemailteachers', 'organizer'), 1, $pickeroptions));

    // Appointment digest time.
    $pickeroptions = [];
    $pickeroptions['never'] = get_string('configdontsend', 'organizer');
    for ($i = 0; $i < 24; $i++) {
        $pickeroptions[$i * 3600] = userdate($i * 3600, get_string('timetemplate', 'organizer'), 0);
    }

    $settings->add(
            new admin_setting_configselect('organizer/digest',
                    get_string('configdigest_label', 'organizer'),
                    get_string('configdigest', 'organizer'), 'never', $pickeroptions));

    // Registration end after regsistration start.
    $abschoices = [];
    $abschoices['never'] = get_string('confignever', 'organizer');
    $abschoices['+1 week'] = '1 ' . get_string('configweek', 'organizer');
    $abschoices['+2 weeks'] = '2 ' . get_string('configweeks', 'organizer');
    $abschoices['+3 weeks'] = '3 ' . get_string('configweeks', 'organizer');
    $abschoices['+1 month'] = '1 ' . get_string('configmonth', 'organizer');
    $abschoices['+2 month'] = '2 ' . get_string('configmonths', 'organizer');
    $abschoices['+3 month'] = '3 ' . get_string('configmonths', 'organizer');
    $abschoices['+4 month'] = '4 ' . get_string('configmonths', 'organizer');
    $abschoices['+5 month'] = '5 ' . get_string('configmonths', 'organizer');
    $abschoices['+6 month'] = '6 ' . get_string('configmonths', 'organizer');
    $abschoices['+1 year'] = '1 ' . get_string('configyear', 'organizer');

    $settings->add(
            new admin_setting_configselect('organizer/absolutedeadline',
                    get_string('absolutedeadline', 'organizer'),
                    get_string('configabsolutedeadline', 'organizer'), 'never', $abschoices));

    // Relative deadline before the slot date for sending remembrance email to students.
    $relchoices = [];
    $relchoices[60 * 1] = '1 ' . get_string('configminute', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[60 * 5] = '5 ' . get_string('configminutes', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[60 * 15] = '15 ' . get_string('configminutes', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[60 * 30] = '30 ' . get_string('configminutes', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 1] = '1 ' . get_string('confighour', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 2] = '2 ' . get_string('confighours', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 3] = '3 ' . get_string('confighours', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 6] = '6 ' . get_string('confighours', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 12] = '12 ' . get_string('confighours', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[3600 * 18] = '18 ' . get_string('confighours', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[86400 * 1] = '1 ' . get_string('configday', 'organizer') . ' ' . get_string('configahead', 'organizer');
    $relchoices[86400 * 2] = '2 ' . get_string('configdays', 'organizer') . ' ' . get_string('configahead', 'organizer');

    $settings->add(
            new admin_setting_configselect('organizer/relativedeadline',
                    get_string('relativedeadline', 'organizer'),
                    get_string('configrelativedeadline', 'organizer'), 86400, $relchoices));

    $yesno = ['0' => get_string('no'), '1' => get_string('yes')];
    $settings->add(
            new admin_setting_configselect('organizer/allowcreationofpasttimeslots',
                    get_string('allowcreationofpasttimeslots', 'organizer'),
                    get_string('configallowcreationofpasttimeslots', 'organizer'), 0, $yesno));

    // Show participant's identity in registration view.
    $settings->add(new admin_setting_configcheckbox('organizer/dontshowidentity', get_string('cfg_dontshowidentity', 'organizer'),
        get_string('cfg_dontshowidentity_desc', 'organizer'), 0));

    // Use moodle 4.x smaller content design.
    $settings->add(new admin_setting_configcheckbox('organizer/limitedwidth', get_string('cfg_limitedwidth', 'organizer'),
        get_string('cfg_limitedwidth_desc', 'organizer'), 0));

    // Synchronize organizer slots with Moodle group changes.
    $settings->add(new admin_setting_configcheckbox('organizer/synchronizegroupmembers',
        get_string('synchronizegroupmembers', 'organizer'),
        get_string('synchronizegroupmembers_help', 'organizer'), 0));

    // Predefine locations for slots, make location mandatory.
    $settings->add(new admin_setting_heading('organizerlocationsettings', '',
        get_string('locationsettings', 'organizer')));

    $settings->add(
        new admin_setting_configcheckbox('organizer/locationmandatory',
            get_string('locationmandatory', 'organizer'), null, 0));

    $settings->add(new admin_setting_configtextarea('mod_organizer/locations', get_string('configlocationslist', 'organizer'),
            get_string('configlocationslist_desc', 'organizer'), '', PARAM_TEXT, '60', '8'));


    // User profile fields for printing single slots.

    $selectableprofilefields = organizer_printslotuserfields();

    // Allowed User profile fields for printing single slots.
    $settings->add(new admin_setting_heading('allowedprofilefieldsprint', '',
        get_string('allowedprofilefieldsprint', 'organizer')));

    $settings->add(
        new admin_setting_configcheckbox('organizer/enableprintslotuserfields',
            get_string('enableprintslotuserfields', 'organizer'),
            get_string('enableprintslotuserfieldsdesc', 'organizer'), 1));
    $settings->add(
        new admin_setting_configmultiselect('organizer/allowedprofilefieldsprint',
           get_string('allowedprofilefieldsprint', 'organizer'),
            get_string('allowedprofilefieldsprint2', 'organizer'),
            array_keys($selectableprofilefields), $selectableprofilefields));


    $settings->add(new admin_setting_heading('organizersingleslotprintfields', '',
        get_string('defaultsingleslotprintfields', 'organizer')));


    $selectableprofilefields = ['' => '--'] + $selectableprofilefields;

    for ($i = 0; $i <= ORGANIZER_PRINTSLOTUSERFIELDS; $i++) {
        $settings->add(
            new admin_setting_configselect('organizer/singleslotprintfield' . $i,
                $i + 1 . '. ' . get_string('singleslotprintfield', 'organizer'),
                null, '', $selectableprofilefields));
    }

}
