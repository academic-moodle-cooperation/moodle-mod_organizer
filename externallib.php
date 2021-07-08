<?php

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/user/externallib.php");
require_once("$CFG->dirroot/mod/organizer/locallib.php");


class mod_organizer_external extends external_api
{
    /**
     * Get definition of the parameters for the get_organizers_by_courses function
     *
     * @return external_function_parameters
     */
    public static function get_organizers_by_courses_parameters() {
        return new external_function_parameters(
            [
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids (all enrolled courses if empty array)', VALUE_DEFAULT, []
                ),
            ]
        );
    }

    /**
     * Get definition of the return value of the get_organizers_by_courses function
     *
     * @return external_single_structure
     */
    public static function get_organizers_by_courses_returns() {
        return new external_single_structure(
            [
                'organizers' => new external_multiple_structure(self::organizer_structure(), 'organizer info'),
                'warnings' => new external_warnings('warnings')
            ]
        );
    }

    /**
     * Gets information on organizers in the provided courses (all courses if no ids are provided).
     *
     * @param $courseids
     * @return stdClass
     * @throws coding_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_organizers_by_courses($courseids) {
        $warnings = [];

        $params = [
            'courseids' => $courseids,
        ];
        $params = self::validate_parameters(self::get_organizers_by_courses_parameters(), $params);

        $mycourses = [];
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }
        $returned_organizers = [];
        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = external_util::validate_courses($params['courseids'], $mycourses);

            $organizer_instances = get_all_instances_in_courses("organizer", $courses);
            foreach ($organizer_instances as $organizer_instance) {
                list(, , $organizer,) = organizer_get_course_module_data(null, $organizer_instance->id);
                $returned_organizers[] = self::export_organizer($organizer);
            }
        }
        $result = new stdClass();
        $result->organizers = $returned_organizers;
        $result->warnings = $warnings;
        return $result;
    }

    /**
     * Returns description of the get_organizer parameters
     *
     * @return external_function_parameters
     */
    public static function get_organizer_parameters() {
        return new external_function_parameters(
            [
            'organizerid' => new external_value(PARAM_INT, 'The id of the organizer'),
            ]
        );
    }

    /**
     * Returns description of the get_organizer result value
     *
     * @return external_single_structure
     */
    public static function get_organizer_returns() {
        return new external_single_structure(
            [
            'organizer' => self::organizer_structure(),
            ]
        );
    }

    /**
     * Returns the organizer for the given id
     *
     * @param $id
     */
    public static function get_organizer($organizerid) {
        $params = self::validate_parameters(self::get_organizer_parameters(), ['organizerid' => $organizerid]);

        list(, , $organizer, $context) = organizer_get_course_module_data(null, $params['organizerid']);
        self::validate_context($context);

        $result = new stdClass();
        $result->organizer = self::export_organizer($organizer);
        return $result;
    }

    /**
     * Get definition of the parameters for the get_slots function
     *
     * @return external_function_parameters
     */
    public static function get_slots_parameters() {
        return new external_function_parameters(
            [
                'organizerid' => new external_value(PARAM_INT, 'organizer id'),
            ]
        );
    }

    /**
     * Get definition of the return value of the get_slots function
     *
     * @return external_single_structure
     */
    public static function get_slots_returns() {
        return new external_single_structure(
            [
                'organizer' => self::organizer_structure(),
                'slots' => new external_multiple_structure(self::slot_structure(), 'array of slots'),
            ]
        );
    }

    /**
     * Gets all appointment slots of the organizer with the provided id.
     *
     * @param $organizerid
     * @return stdClass
     * @throws invalid_parameter_exception
     */
    public static function get_slots($organizerid) {

        $params = self::validate_parameters(self::get_slots_parameters(), [
            'organizerid' => $organizerid,
        ]);

        list(, , $organizer, $context) = organizer_get_course_module_data(null, $params['organizerid']);
        self::validate_context($context);
        $slots = organizer_fetch_allslots($params['organizerid']);
        $returned_slots = [];

        if (time() > $organizer->allowregistrationsfromdate) {
            foreach ($slots as $slot) {
                if ($slot->visible) {
                    $returned_slots[] = self::export_slot(new organizer_slot($slot, false));
                }
            }
        }

        $result = new stdClass();
        $result->organizer = self::export_organizer($organizer);
        $result->slots = $returned_slots;
        return $result;
    }

    /**
     * Get definition of the parameters for the register_appointment function
     *
     * @return external_function_parameters
     */
    public static function register_appointment_parameters() {
        return new external_function_parameters(
            [
                'slotid' => new external_value(PARAM_INT, 'slot id'),
            ]
        );
    }

    /**
     * Get definition of the return value of the register_appointment function
     *
     * @return external_single_structure
     */
    public static function register_appointment_returns() {
        return new external_single_structure(
            [
                'appointmentid' => new external_value(PARAM_INT, "id of the new appointment (0 if not created)"),
                'queueid' => new external_value(PARAM_INT, 'id of slot queue placement (0 if not created)'),
                'status' => new external_value(PARAM_INT, 'indicator whether success (added to queue or appointment) or failure'),
            ]
        );
    }

    /**
     * Registers the current user for the slot with the provided id. If applicable, the user is added to the slot queue
     * instead.
     *
     * @param $slotid
     * @return stdClass
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function register_appointment($slotid) {

        $params = self::validate_parameters(self::register_appointment_parameters(), [
            'slotid' => $slotid,
        ]);
        $slot = new organizer_slot($params['slotid']);

        $organizer = $slot->get_organizer();
        list($cm) = organizer_get_course_module_data(null, $organizer->id);
        $_GET['id'] = $cm->id; //Further calls, such as organizer_is_group_mode, rely on this setting.

        $group = organizer_fetch_my_group();
        $groupid = $group ? $group->id : 0;
        $queue = $slot->is_full();

        if ($queue) {
            $action = ORGANIZER_ACTION_QUEUE;
        } else {
            $action = ORGANIZER_ACTION_REGISTER;
        }

        if (!self::organizer_organizer_student_action_allowed($action, $params['slotid'])) {
            print_error('Cannot execute registration/queue action!');
        }

        $success = organizer_register_appointment($params['slotid'], $groupid);
        $result = new stdClass();
        $result->appointmentid = 0;
        $result->queueid = 0;

        if (!$success) {
            $result->status = 0;
        } else {
            $result->status = 1;
            if ($queue) {
                $result->queueid = $success;
            } else {
                $result->appointmentid = $success;
            }
        }

        return $result;
    }

    /**
     * Get definition of the parameters for the unregister_appointment function
     *
     * @return external_function_parameters
     */
    public static function unregister_appointment_parameters() {
        return new external_function_parameters(
            [
                'slotid' => new external_value(PARAM_INT, 'Course id')
            ]
        );
    }

    /**
     * Get definition of the return value of the unregister_appointment function
     *
     * @return external_single_structure
     */
    public static function unregister_appointment_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_INT, 'status indicator whether unregistering/dequeuing was successful'),
                'isunregister' => new external_value(PARAM_INT, '1 if unregistering, 0 if dequeueing applies'),
            ]
        );
    }

    /**
     * Unregisters the current user for the slot with the provided id. If applicable, the user is removed from the slot
     * queue instead.
     *
     * @param $slotid
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function unregister_appointment($slotid) {
        global $USER;

        $params = self::validate_parameters(self::unregister_appointment_parameters(), [
            'slotid' => $slotid,
        ]);
        $slot = new organizer_slot($params['slotid']);

        $organizer = $slot->get_organizer();
        list($cm) = organizer_get_course_module_data(null, $organizer->id);
        $_GET['id'] = $cm->id; //Further calls, such as organizer_is_group_mode, rely on this setting.

        $group = organizer_fetch_my_group();
        $groupid = $group ? $group->id : 0;
        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $isinqueue = $slot->is_group_in_queue();
        } else {
            $isinqueue = $slot->is_user_in_queue($USER->id);
        }
        if (!$isinqueue) {
            if (!self::organizer_organizer_student_action_allowed(ORGANIZER_ACTION_UNREGISTER, $params['slotid'])) {
                print_error('Cannot execute unregistration action!');
            }
            $success = organizer_unregister_appointment($params['slotid'], $groupid, $organizer->id);
        } else {
            if (!self::organizer_organizer_student_action_allowed(ORGANIZER_ACTION_UNQUEUE, $params['slotid'])) {
                print_error('Inconsistent state: Cannot execute unqueue action!');
            }
            $success = organizer_delete_from_queue($params['slotid'], $USER->id, $groupid);
        }
        $result = new stdClass();
        $result->isunregister = (int)!$isinqueue;
        $result->status = $success;
        return $result;
    }

    /**
     * Get definition of the parameters for the get_organizers_by_courses function
     *
     * @return external_function_parameters
     */
    public static function reregister_appointment_parameters() {
        return new external_function_parameters(
            [
                'slotid' => new external_value(PARAM_INT, 'slot id')
            ]
        );
    }

    /**
     * Get definition of the return value of the get_organizers_by_courses function
     *
     * @return external_single_structure
     */
    public static function reregister_appointment_returns() {
        return new external_function_parameters(
            [
                'status' => new external_value(PARAM_INT, 'status indicator whether reregistering was successful'),
            ]
        );
    }

    /**
     * Reregisters the current user for the slot with the provided id. This is applicable, if the user is presently
     * registered for another slot in the same organizer.
     *
     * @param $slotid
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function reregister_appointment($slotid) {

        $params = self::validate_parameters(self::reregister_appointment_parameters(), [
            'slotid' => $slotid,
        ]);
        $slot = new organizer_slot($params['slotid']);

        $organizer = $slot->get_organizer();
        list($cm) = organizer_get_course_module_data(null, $organizer->id);
        $_GET['id'] = $cm->id; //Further calls, such as organizer_is_group_mode, rely on this setting.

        if (!self::organizer_organizer_student_action_allowed(ORGANIZER_ACTION_REREGISTER, $params['slotid'])) {
            print_error('Cannot execute reregistration action!');
        }

        $group = organizer_fetch_my_group();
        $groupid = $group ? $group->id : 0;
        $result = new stdClass();
        $result->status = organizer_reregister_appointment($params['slotid'], $groupid);;
        return $result;
    }

    /**
     * Get definition of the parameters for the get_appointment_status function
     *
     * @return external_function_parameters
     */
    public static function get_appointment_status_parameters() {
        return new external_function_parameters(
            [
                'organizerid' => new external_value(PARAM_INT, 'organizer id'),
                'onlyattended' => new external_value(PARAM_INT, 'indicator, whether to only consider attended appointments', VALUE_DEFAULT, 0)
            ]
        );
    }

    /**
     * Get definition of the return value of the get_appointment_status function
     *
     * @return external_single_structure
     */
    public static function get_appointment_status_returns() {
        return new external_single_structure(
            [
                'hasappointment' => new external_value(PARAM_INT, 'indicator whether user has an appointment'),
                'appointment' => self::appointment_structure(),
            ]
        );
    }

    /**
     * Gets information on the current registered appointment for the given organizer, if available.
     * The onlyattended parameter indicates whether the function only consider attended appointments.
     *
     * @param $organizerid
     * @param $onlyattended
     * @return stdClass
     * @throws invalid_parameter_exception
     * @throws moodle_exception
     */
    public static function get_appointment_status($organizerid, $onlyattended) {

        $params = self::validate_parameters(self::get_appointment_status_parameters(), [
            'organizerid' => $organizerid,
            'onlyattended' => $onlyattended
        ]);

        $result = new stdClass();

        if ($appointment = organizer_get_last_user_appointment($params['organizerid'], null, true, $params['onlyattended'])) {
            $result->hasappointment = 1;
            $result->appointment = self::export_appointment($appointment);
        } else {
            $result->hasappointment = 0;
        }

        return $result;
    }

    /**
     * Structure of an organizer, used in return value definitions.
     *
     * @return external_single_structure
     */
    public static function organizer_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'organizer id'),
                'course' => new external_value(PARAM_INT, 'course id the organizer belongs to'),
                'name' => new external_value(PARAM_TEXT, 'organizer name'),
                'intro' => new external_value(PARAM_RAW, 'organizer introduction text.', VALUE_OPTIONAL),
                'introformat' => new external_format_value('intro', VALUE_OPTIONAL),
                'isgrouporganizer' => new external_value(PARAM_INT, 'Whether the organizer is a group organizer', VALUE_OPTIONAL),
                'queue' => new external_value(PARAM_INT, 'organizer introduction text.', VALUE_OPTIONAL),
                'allowregistrationsfromdate' => new external_value(PARAM_INT, 'start of registration period', VALUE_OPTIONAL),
                'duedate' => new external_value(PARAM_INT, 'end of registration period', VALUE_OPTIONAL),
                'relativedeadline' => new external_value(PARAM_INT, 'deadline for registering relative to a specific slot', VALUE_OPTIONAL)
            ], 'organizer information'
        );
    }

    /**
     * Transforms the given organizer object, such that it fits organizer_strucutre.
     *
     * @param $organizer
     * @return stdClass
     */
    public static function export_organizer($organizer) {
        $exported_organizer = new StdClass();
        $exported_organizer->id = $organizer->id;
        $exported_organizer->course = $organizer->course;
        $exported_organizer->name = $organizer->name;
        $exported_organizer->intro = $organizer->intro;
        $exported_organizer->introformat = $organizer->introformat;
        $exported_organizer->isgrouporganizer = $organizer->isgrouporganizer;
        $exported_organizer->queue = $organizer->queue;
        $exported_organizer->allowregistrationsfromdate = $organizer->allowregistrationsfromdate;
        $exported_organizer->duedate = $organizer->duedate;
        $exported_organizer->relativedeadline = $organizer->relativedeadline;

        return $exported_organizer;
    }

    /**
     * Structure of a slot, used in return value definitions.
     *
     * @return external_single_structure
     */
    public static function slot_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'slot id'),
                'organizerid' => new external_value(PARAM_INT, 'organizer id'),
                'starttime' => new external_value(PARAM_INT, 'slot start time'),
                'duration' => new external_format_value(PARAM_INT, 'slot duration'),
                'location' => new external_value(PARAM_RAW, 'slot location'),
                'locationlink' => new external_value(PARAM_RAW, 'link to slot location'),
                'maxparticipants' => new external_value(PARAM_INT, 'maximum number of allowed participants (1 if groupmode)'),
                'availablefrom' => new external_value(PARAM_INT, 'time relative to starttime when registration becomes possible', VALUE_OPTIONAL),
                'teachervisible' => new external_value(PARAM_INT, 'whether the teachers assigned to the slot are visible'),
                'comments' => new external_value(PARAM_RAW, 'slot comments', VALUE_OPTIONAL),
                'isavailable' => new external_value(PARAM_INT, 'indicator whether the slot is available for registration', VALUE_OPTIONAL),
                'isfull' => new external_value(PARAM_INT, 'indicator whether slot is full', VALUE_OPTIONAL),
                'teachers' => new external_multiple_structure(self::trainer_structure(), 'trainers (empty if not teachervisible)'),

            ], 'organizer slot information'
        );
    }

    /**
     * Transforms the given slot object, such that it fits slot_structure.
     *
     * @param $organizer
     * @return stdClass
     */
    public static function export_slot($slot) {
        $exported_slot = new stdClass();
        $exported_slot->id = $slot->id;
        $exported_slot->organizerid = $slot->organizerid;
        $exported_slot->starttime = $slot->starttime;
        $exported_slot->duration = $slot->duration;
        $exported_slot->location = $slot->location;
        $exported_slot->locationlink = $slot->locationlink;
        $exported_slot->maxparticipants = $slot->maxparticipants;
        $exported_slot->availablefrom = $slot->availablefrom;
        $exported_slot->teachervisible = $slot->teachervisible;
        $exported_slot->comments = $slot->comments;
        $exported_slot->isavailable = (int)$slot->is_available();
        $exported_slot->isfull = (int)$slot->is_full();
        $exported_slot->teachers = [];
        if ($slot->teachervisible) {
            $exported_slot->teachers = organizer_get_slot_trainers($slot->id, true);
        }
        return $exported_slot;
    }

    /**
     * Structure of an appointment, used in return value definitions. This is an optional value.
     *
     * @return external_single_structure
     */
    private static function appointment_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'appointment id'),
                'slotid' => new external_value(PARAM_INT, 'slot id'),
                'groupid' => new external_value(PARAM_INT, 'group id (0 if not group organizer)'),
                'attended' => new external_value(PARAM_INT, 'indicator whether user attended the appointment'),
                'grade' => new external_value(PARAM_RAW, 'grade received'),
                'feedback' => new external_value(PARAM_RAW, 'feedback for the appointment'),
                'comments' => new external_value(PARAM_RAW, 'appointment comments'),
                'allownewappointments' => new external_value(PARAM_INT, 'whether re-registration is allowed'),
            ], 'organizer appointment information', VALUE_OPTIONAL
        );
    }

    /**
     * Transforms the given appointment object, such that it fits appointment_structure.
     *
     * @param $appointment
     * @return stdClass
     */
    private static function export_appointment($appointment) {
        $exported_appointment = new stdClass();
        $exported_appointment->id = $appointment->id;
        $exported_appointment->slotid = $appointment->slotid;
        $exported_appointment->groupid = $appointment->groupid;
        $exported_appointment->attended = $appointment->attended;
        $exported_appointment->grade = $appointment->grade;
        $exported_appointment->feedback = $appointment->feedback;
        $exported_appointment->comments = $appointment->comments;
        $exported_appointment->allownewappointments = $appointment->allownewappointments;
        return $exported_appointment;
    }

    /**
     * Structure of a trainer, used in return value definitions.
     *
     * @return external_single_structure
     */
    public static function trainer_structure() {
        return new external_single_structure(
            [
                'id' => new external_value(PARAM_INT, 'user id'),
                'firstname' => new external_value(PARAM_RAW, 'trainer firstname'),
                'lastname' => new external_value(PARAM_RAW, 'trainer lastname'),
                'email' => new external_value(PARAM_RAW, 'trainer email')
            ], 'organizer trainer information'
        );
    }

    /**
     * This function is copied from 'view_action.php'. While uncommon, this is in keeping with this plugin's already
     * existing codebase.
     * See 'slots_print.php' and 'slots_printdetail.php' for variants of this function that are duplicated.
     */
    private static function organizer_organizer_student_action_allowed($action, $slot) {
        global $DB, $USER;

        if (!$DB->record_exists('organizer_slots', ['id' => $slot])) {
            return false;
        }

        $slotx = new organizer_slot($slot);

        list($cm, $course, $organizer, $context) = organizer_get_course_module_data();

        $canregister = has_capability('mod/organizer:register', $context, null, false);
        $canunregister = has_capability('mod/organizer:unregister', $context, null, false);
        $canreregister = $canregister && $canunregister;

        $myapp = organizer_get_last_user_appointment($organizer);
        if ($myapp) {
            $regslot = $DB->get_record('organizer_slots', ['id' => $myapp->slotid]);
            if (isset($regslot)) {
                $regslotx = new organizer_slot($regslot);
            }
        }

        $myslotexists = isset($regslot);
        $organizerdisabled = $slotx->organizer_unavailable() || $slotx->organizer_expired();
        $slotdisabled = $slotx->is_past_due() || $slotx->is_past_deadline();
        $myslotpending = $myslotexists && $regslotx->is_past_deadline() && !$regslotx->is_evaluated();
        $ismyslot = $myslotexists && ($slotx->id == $regslot->id);
        $slotfull = $slotx->is_full();

        $disabled = $myslotpending || $organizerdisabled ||
            $slotdisabled || !$slotx->organizer_user_has_access() || $slotx->is_evaluated();

        $isalreadyinqueue = false;
        if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $isalreadyinqueue = $slotx->is_group_in_queue();
        } else {
            $isalreadyinqueue = $slotx->is_user_in_queue($USER->id);
        }

        $isqueueable = $organizer->queue && !$isalreadyinqueue && !$myslotpending && !$organizerdisabled
            && !$slotdisabled && $slotx->organizer_user_has_access() && !$slotx->is_evaluated();

        if ($myslotexists) {
            if (!$slotdisabled) {
                if ($ismyslot) {
                    $disabled |= !$canunregister
                        || (isset($regslotx) && $regslotx->is_evaluated() && !$myapp->allownewappointments);
                } else {
                    $disabled |= $slotfull || !$canreregister
                        || (isset($regslotx) && $regslotx->is_evaluated() && !$myapp->allownewappointments);
                }
            }
            $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REREGISTER;
        } else {
            $disabled |= $slotfull || !$canregister || $ismyslot;
            $allowedaction = $ismyslot ? ORGANIZER_ACTION_UNREGISTER : ORGANIZER_ACTION_REGISTER;
        }

        $result = !$disabled && ($action == $allowedaction);
        if (!$result && $isqueueable && $action == ORGANIZER_ACTION_QUEUE) {
            $result = true;
        }
        if (!$result && $isalreadyinqueue && $action == ORGANIZER_ACTION_UNQUEUE) {
            $result = true;
        }

        return $result;
    }
}
