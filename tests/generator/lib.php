<?php

defined('MOODLE_INTERNAL') || die();

class mod_organizer_generator extends testing_module_generator {

    /**
     * Generator method creating a mod_organizer instance.
     *
     *
     * @param array|stdClass $record (optional) Named array containing instance settings
     * @param array $options (optional) general options for course module. Can be merged into $record
     * @return stdClass record from module-defined table with additional field cmid (corresponding id in course_modules table)
     */
    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $timecreated = time();

        $defaultsettings = [
            'name' => 'Organizer',
            'intro' => 'Introtext',
            'introformat' => 1,
            'timemodified' => $timecreated,
            'isgrouporganizer' => 0,
            'emailteachers' => 0,
            'allowregistrationsfromdate' => $timecreated,
            'duedate' => null,
            'alwaysshowdescription' => 1,
            'relativedeadline' => 86400,
            'grade' => 0,
            'queue' => 0,
            'visibility' => 2,
            'hidecalendar' => 1,
            'nocalendareventslotcreation' => 1,
            'locationfieldmandatory' => 0,
            'includetraineringroups' => 0,
            'singleslotprintfield0' => '',
            'singleslotprintfield1' => '',
            'singleslotprintfield2' => '',
            'singleslotprintfield3' => '',
            'singleslotprintfield4' => '',
            'singleslotprintfield5' => '',
            'singleslotprintfield6' => '',
            'singleslotprintfield7' => '',
            'singleslotprintfield8' => '',
            'singleslotprintfield9' => '',
        ];

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
