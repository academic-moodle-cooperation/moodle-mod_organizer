<?php
$functions = [
    'mod_organizer_get_organizers_by_courses' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'get_organizers_by_courses',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => "Gets information on organizers in the provided courses (all courses if no ids are provided).",
        'type' => 'read',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_get_organizer' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'get_organizer',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => "Gets information of an organizer with the given id",
        'type' => 'read',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_get_slots' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'get_slots',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => " Gets all appointment slots of the organizer with the provided id.",
        'type' => 'read',
        'capabilities' => 'mod/organizer:viewstudentview',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_register_appointment' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'register_appointment',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => 'Registers the current user for the slot with the provided id. If applicable, the user is added to the slot queue instead.',
        'type' => 'write',
        'capabilities' => 'mod/organizer:register',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_unregister_appointment' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'unregister_appointment',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => 'Unregisters the current user for the slot with the provided id. If applicable, the user is removed from the slot queue instead.',
        'type' => 'write',
        'capabilities' => 'mod/organizer:unregister',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_reregister_appointment' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'reregister_appointment',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => 'Reregisters the current user for the slot with the provided id. This is applicable, if the user is presently registered for another slot in the same organizer.',
        'type' => 'write',
        'capabilities' => 'mod/organizer:unregister, mod/organizer:register',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

    'mod_organizer_get_appointment_status' => [
        'classname' => 'mod_organizer_external',
        'methodname' => 'get_appointment_status',
        'classpath' => 'mod/organizer/externallib.php',
        'description' => 'Gets information on the current registered appointment for the given organizer, if available. The onlyattended parameter indicates whether the function only consider attended appointments.',
        'type' => 'read',
        'capabilities' => 'mod/organizer:viewstudentview',
        'services'      => [MOODLE_OFFICIAL_MOBILE_SERVICE],
    ],

];