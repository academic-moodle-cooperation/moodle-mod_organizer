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
 * slotlib.php
 *
 * @package   mod_organizer
 * @author    Andreas Hruska (andreas.hruska@tuwien.ac.at)
 * @author    Katarzyna Potocka (katarzyna.potocka@tuwien.ac.at)
 * @author    Thomas Niedermaier (thomas.niedermaier@gmail.com)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


/**
 * Class organizer_slot
 *
 * Represents a slot in the organizer module for Moodle.
 * Handles loading, validations, and operations related to slots in the organizer.
 *
 * @package   mod_organizer
 * @author    Andreas Hruska
 * @author    Katarzyna Potocka
 * @author    Thomas Niedermaier
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class organizer_slot {

    /**
     * The slot object associated with this instance, representing a record from the 'organizer_slots' table.
     *
     * @var stdClass
     */
    private $slot;

    /**
     * The organizer object associated with this slot, representing a record from the 'organizer' table.
     *
     * @var stdClass|null
     */
    private $organizer;

    /**
     * Holds the list of appointments associated with this slot.
     *
     * @var array|null
     */
    private $apps;

    /**
     * Holds the queue related to this slot.
     *
     * @var array|null
     */
    private $queue;

    /**
     * Holds the group queue associated with this slot.
     *
     * @var array|null
     */
    private $queuegroup;

    /**
     * The availability status and details of the organizer slot.
     *
     * @var bool|array|null
     */
    private $availability;

    /**
     * organizer_slot constructor.
     *
     * Initializes an instance of the organizer_slot class. It loads a slot record from the database
     * based on a given slot ID or stdClass object. Additional associated data such as organizer,
     * appointments, queue, and group queue can be loaded depending on the constructor parameters.
     *
     * @param int|stdClass $slot The slot ID or slot object to initialize the instance with.
     * @param bool $lazy If true, delays loading organizer, appointments, queue, and group queue until needed.
     * @param stdClass|null $organizer An optional organizer object to associate with the slot.
     * @throws dml_exception
     */
    public function __construct($slot, $lazy = true, $organizer = null) {
        global $DB;

        if (is_number($slot) && $slot == intval($slot)) {
            $this->slot = $DB->get_record('organizer_slots', ['id' => $slot]);
        } else {
            $this->slot = $slot;

            if (!isset($this->slot->organizerid)) {
                $this->slot->organizerid = $DB->get_field('organizer_slots', 'organizerid', ['id' => $slot->slotid]);
            }

            if (!isset($this->slot->maxparticipants)) {
                $this->slot->maxparticipants = $DB->get_field('organizer_slots', 'maxparticipants', ['id' => $slot->slotid]);
            }

            if (!isset($this->slot->id)) {
                $this->slot->id = $slot->slotid;
            }
        }

        foreach (get_object_vars($this->slot) as $key => $value) {
            $this->$key = $value;
        }

        if (!$lazy) {
            $this->load_organizer();
            $this->load_appointments();
            if ($this->organizer->queue) {
                $this->load_queue();
                $this->load_queue_group();
            }
        }
        if ($organizer) {
            $this->organizer = $organizer;
        }
    }

    /**
     * Retrieves the organizer instance associated with this slot.
     *
     * This method ensures that the organizer information is loaded before returning it.
     * If the data has not been loaded yet, it will call `load_organizer()` to fetch the
     * required information from the database.
     *
     * @return stdClass|null The organizer object associated with this slot, or null if not available.
     * @throws dml_exception
     */
    public function get_organizer() {
        $this->load_organizer();
        return $this->organizer;
    }

    /**
     * Returns the slot instance.
     *
     * This function returns the slot object representing the organizer slot.
     * The slot contains information retrieved from the database or initialized
     * when the class instance was created.
     *
     * @return stdClass The slot object with details about the organizer slot.
     */
    public function get_slot() {
        return $this->slot;
    }

    /**
     * Checks whether this slot has participants.
     *
     * This method verifies if there are any appointments (participants)
     * associated with this slot by loading its appointments and checking
     * their count.
     *
     * @return bool True if the slot has participants, false otherwise.
     * @throws dml_exception
     */
    public function has_participants() {
        $this->load_appointments();
        return count($this->apps) != 0;
    }

    /**
     * Retrieves the relative deadline for this slot.
     *
     * This method ensures that the organizer is loaded before accessing its
     * `relativedeadline` property. The relative deadline is a time offset
     * (in seconds) calculated based on the organizer's configuration.
     *
     * @return int|null The relative deadline in seconds, or null if not set.
     * @throws dml_exception
     */
    public function get_rel_deadline() {
        $this->load_organizer();
        return $this->organizer->relativedeadline;
    }

    /**
     * Retrieves the absolute deadline for this slot.
     *
     * This method ensures that the organizer is loaded before accessing its
     * `duedate` property. The absolute deadline represents the final due date
     * defined by the organizer for this slot.
     *
     * @return int|null The absolute deadline timestamp, or null if not set.
     * @throws dml_exception
     */
    public function get_abs_deadline() {
        $this->load_organizer();
        return $this->organizer->duedate;
    }

    /**
     * Checks if the slot is upcoming.
     *
     * This method determines whether the slot's start time is in the future
     * compared to the current time.
     *
     * @return bool True if the slot's start time is in the future, false otherwise.
     */
    public function is_upcoming() {
        return $this->slot->starttime > time();
    }

    /**
     * Determines if the current slot is past its relative deadline.
     *
     * This method calculates whether the current slot's start time is beyond
     * its relative deadline. The relative deadline is retrieved using
     * `get_rel_deadline()` and is added to the current time to determine the
     * cut-off.
     *
     * @return bool True if the slot is past the relative deadline, false otherwise.
     */
    public function is_past_deadline() {
        $deadline = $this->get_rel_deadline($this->slot);
        return $this->slot->starttime <= $deadline + time();
    }

    /**
     * Checks if the slot's start time is past the relative deadline.
     *
     * This method retrieves the slot's relative deadline using `get_rel_deadline()`
     * and determines whether the slot's start time has surpassed that deadline.
     *
     * @return bool True if the slot is past the relative deadline, false otherwise.
     */
    public function is_past_due() {
        return $this->slot->starttime <= time();
    }

    /**
     * Checks if the slot is full.
     *
     * This method determines if the slot has reached its maximum capacity of participants.
     * For group organizers, the slot is considered full if there is at least one appointment.
     * For individual organizers, the slot is full when the number of appointments reaches the
     * maximum number of participants allowed (`maxparticipants`).
     *
     * @return bool True if the slot is full, false otherwise.
     * @throws dml_exception
     */
    public function is_full() {
        $this->load_organizer();
        $this->load_appointments();
        if ($this->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            return count($this->apps) > 0;
        } else {
            return count($this->apps) >= $this->slot->maxparticipants;
        }
    }

    /**
     * Determines if the slot is available for registration or bookings.
     *
     * The slot is available if it does not have a specific "available from" time,
     * or if the defined "available from" time has already passed when compared
     * to the current time.
     *
     * @return bool True if the slot is available, false otherwise.
     */
    public function is_available() {
        return ($this->slot->availablefrom == 0) || ($this->slot->starttime - $this->slot->availablefrom <= time());
    }

    /**
     * Checks if the organizer has expired.
     *
     * This method determines whether the organizer's due date has passed
     * by comparing the current time with the organizer's defined `duedate`.
     *
     * @return bool True if the organizer has expired, false otherwise.
     * @throws dml_exception
     */
    public function organizer_expired() {
        $this->load_organizer();
        return isset($this->organizer->duedate) && $this->organizer->duedate - time() < 0;
    }

    /**
     * Checks if the organizer is unavailable for registrations.
     *
     * This method determines if the organizer is currently unavailable for
     * registrations by checking the `allowregistrationsfromdate` property.
     * If the current time is earlier than the `allowregistrationsfromdate`,
     * the organizer is considered unavailable.
     *
     * @return bool True if the organizer is unavailable, false otherwise.
     * @throws dml_exception
     */
    public function organizer_unavailable() {
        $this->load_organizer();
        return isset($this->organizer->allowregistrationsfromdate) && $this->organizer->allowregistrationsfromdate - time() > 0;
    }

    /**
     * Checks if all appointments in the slot have been evaluated.
     *
     * This method iterates through all appointments within the slot
     * to ensure each has been marked as attended or not attended.
     * A slot is considered evaluated only when all appointments have
     * an attended status (>= 0) and there is at least one appointment.
     *
     * @return bool True if all appointments are evaluated, false otherwise.
     * @throws dml_exception
     */
    public function is_evaluated() {
        $this->load_appointments();

        foreach ($this->apps as $app) {
            if (($app->attended ?? -1) < 0) {
                return false;
            }
        }
        return count($this->apps) > 0;
    }

    /**
     * Checks if the user has access to the organizer in group mode.
     *
     * This method verifies if the user has access to the organizer when it is in
     * group mode. For existing groups, it checks if the user belongs to a group
     * within the required grouping.
     *
     * @return bool True if the user has access, false otherwise.
     * @throws dml_exception
     */
    public function organizer_groupmode_user_has_access() {
        $this->load_organizer();
        global $DB;
        if ($this->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $moduleid = $DB->get_field('modules', 'id', ['name' => 'organizer']);
            if ($groupingid = $DB->get_field('course_modules', 'groupingid',
                ['module' => $moduleid, 'instance' => $this->organizer->id])) {
                $groups = groups_get_user_groups($this->organizer->course);
                if (!isset($groups[$groupingid]) || !count($groups[$groupingid])) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Determines if grading is active for the organizer.
     *
     * This method checks whether the grading functionality
     * is enabled for the current organizer by verifying
     * the `grade` property.
     *
     * @return mixed The grade setting of the organizer.
     * @throws dml_exception
     */
    public function gradingisactive() {
        $this->load_organizer();
        return $this->organizer->grade;
    }


    /**
     * Waiting list
     * Returns the position of a given user in this slot's queue starting at 1.
     * Returns 0 if the user is not in the queue.
     *
     * @param int $userid The ID of the user.
     * @throws dml_exception
     */
    public function is_user_in_queue($userid) {
        $result = 0;
        $this->queue = null;
        $this->load_organizer();
        // The organizer should exists. Otherwise we are in a pathological state.
        if ($this->organizer->queue) {
            $this->load_queue();
            // The queue might be empty though.
            if ($this->queue) {
                $position = 0;
                foreach ($this->queue as $entry) {
                    $position++;
                    if ($entry->userid == $userid) {
                        $result = $position;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Waiting list
     *  Returns the position of a given group in this slot's queue starting at 1.
     *  Returns 0 if the group is not in the queue.
     *
     * @param Int $groupid The ID of the group.
     * @return Int the group's position in queue
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function is_group_in_queue($groupid = 0) {
        $result = 0;
        $position = 0;

        if ($groupid == 0) {
            $group = organizer_fetch_my_group();
            $groupid = $group ? $group->id : 0;
        }

        $this->load_organizer();
        // The organizer should exists. Otherwise we are in a pathological state.
        if ($this->organizer->queue) {
            $this->load_queue_group();
            // The queue might be empty though.
            if ($this->queuegroup) {
                foreach ($this->queuegroup as $entry) {
                    $position++;
                    if ($entry->groupid == $groupid) {
                        $result = $position;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /**
     * Waiting list
     *  Returns the next entry of the waiting queue for this slot if the queue is not empty,
     *  null otherwise.
     *
     * @return Ambigous <NULL, mixed>
     * @throws dml_exception
     */
    public function get_next_in_queue() {
        $result = null;
        $this->load_organizer();

        if ($this->organizer->queue) {
            $this->load_queue();
            if ($this->queue) {
                $result = array_shift($this->queue);
            }
        }
        return $result;
    }

    /**
     * Retrieves the next entry in the waiting queue for the group in this slot.
     * If the queue is not empty, the next entry is returned; otherwise, null is returned.
     *
     * @return mixed|null The next entry in the group queue, or null if the queue is empty.
     * @throws dml_exception
     */
    public function get_next_in_queue_group() {
        $result = null;
        $this->load_organizer();

        if ($this->organizer->queue) {
            $this->load_queue_group();
            if ($this->queuegroup) {
                $result = array_shift($this->queuegroup);
            }
        }
        return $result;
    }

    /**
     * Loads the organizer record associated with the current slot.
     *
     * Retrieves the organizer data from the database using the organizer ID
     * from the current slot and assigns it to the `$this->organizer` property.
     * If the organizer data is already loaded, this method does nothing.
     *
     * @return void
     * @throws dml_exception
     */
    private function load_organizer() {
        global $DB;
        if (!$this->organizer) {
            $this->organizer = $DB->get_record('organizer', ['id' => $this->slot->organizerid]);
        }
    }

    /**
     * Loads the appointments associated with the current slot.
     *
     * Retrieves appointment records from the database for the slot ID
     * of the current organizer slot and assigns them to the `$this->apps` property.
     * Does nothing if the appointments are already loaded.
     *
     * @return void
     * @throws dml_exception If there is an error executing the database query.
     */
    private function load_appointments() {
        global $DB;
        if (!$this->apps) {
            $this->apps = $DB->get_records('organizer_slot_appointments', ['slotid' => $this->slot->id]);
        }
    }

    /**
     * Loads the queue for the current organizer slot.
     *
     * Retrieves queue records from the database for the slot ID
     * of the current organizer slot and assigns them to the `$this->queue` property.
     * If the queue data is already loaded, this method does nothing.
     *
     * @return void
     * @throws dml_exception If there is an error executing the database query.
     */
    private function load_queue() {
        global $DB;
        if (!$this->queue) {
            $this->queue = $DB->get_records('organizer_slot_queues', ['slotid' => $this->slot->id], 'id ASC');
        }
    }

    /**
     * Loads the group queue for the current organizer slot.
     *
     * This method retrieves queue records grouped by group ID from the database
     * for the slot ID of the current organizer slot. The retrieved data is
     * assigned to the `$this->queuegroup` property, which holds the grouped
     * queue information. If the queue group data is already loaded, this method
     * does nothing.
     *
     * @return void
     * @throws dml_exception If there is an error executing the database query.
     */
    private function load_queue_group() {
        global $DB;
        if (!$this->queuegroup) {
            $sql = "SELECT q.groupid FROM (SELECT groupid, slotid FROM {organizer_slot_queues} ORDER BY id asc) q
                    WHERE q.slotid = :slotid
                    GROUP BY q.groupid";
            $paramssql = ['slotid' => $this->slot->id];
            $this->queuegroup = $DB->get_records_sql($sql, $paramssql);
        }
    }

    /**
     * Magic setter to set the value of inaccessible or undefined properties.
     *
     * This method allows dynamic setting of properties that are either private, protected,
     * or not explicitly defined in the class.
     *
     * @param string $name The name of the property to set.
     * @param mixed $value The value to assign to the property.
     * @return void
     */
    public function __set(string $name, mixed $value): void {

    }

    /**
     * Magic setter to set the value of inaccessible or undefined properties.
     *
     * This method allows dynamic setting of properties that are either private, protected,
     * or not explicitly defined in the class. It assigns the given value to the specified
     * property name.
     *
     * @param string $name The name of the property to set.
     * @param mixed $value The value to assign to the property.
     * @return void
     */
    public function get_id() {
        return $this->slot->id;
    }
}

/**
 * Retrieves a user's appointment in a specific slot of an organizer.
 *
 * This function fetches the appointment data for a user's specific slot.
 * If the organizer is a group organizer and group appointments should
 * be merged (`$mergegroupapps` is true), it also retrieves the appointments
 * for all group members and adjusts the attended status accordingly.
 *
 * @param object $slotx The slot object to retrieve the appointment for.
 * @param int|null $userid The ID of the user whose appointment is to be retrieved
 *                         (defaults to the current user's ID if not provided).
 * @param bool $mergegroupapps Whether to include group member appointments (default: true).
 * @param bool $getevaluated Whether to include only evaluated appointments (default: false).
 * @return object|false The user's appointment object or false if no appointment is found.
 * @throws dml_exception If there is an error executing the database queries.
 */
function organizer_get_slot_user_appointment($slotx, $userid = null, $mergegroupapps = true, $getevaluated = false) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    $organizer = $slotx->get_organizer();

    $paramssql = ['slotid' => $slotx->get_id(), 'userid' => $userid];
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.id = :slotid AND a.userid = :userid" .
        ($getevaluated ? " AND a.attended IS NOT NULL " : " ") .
        "ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);
    $app = reset($apps);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && $app !== false) {
        $paramssql = ['slotid' => $slotx->get_id(), 'groupid' => $app->groupid];
        $query = "SELECT a.* FROM {organizer_slot_appointments} a
                INNER JOIN {organizer_slots} s ON a.slotid = s.id
                WHERE s.id = :slotid AND a.groupid = :groupid
                ORDER BY a.id DESC";
        $groupapps = $DB->get_records_sql($query, $paramssql);

        $appcount = 0;
        $someoneattended = false;
        foreach ($groupapps as $groupapp) {
            if ($groupapp->userid == $userid) {
                $app = $groupapp;
            }
            if (isset($groupapp->attended)) {
                $appcount++;
                if ($groupapp->attended == 1) {
                    $someoneattended = true;
                }
            }
        }

        if ($app) {
            $app->attended = ($appcount == count($groupapps)) ? $someoneattended : null;
        }
    }

    return $app;
}

/**
 * Gets the last booked slot for a user before the organizer's deadline.
 *
 * This function determines whether a specific user has a booked slot
 * in the given organizer where the slot's deadline has not yet passed.
 * The check is useful for validating if the user still has valid,
 * upcoming appointments for the organizer.
 *
 * @param int $organizerid The ID of the organizer to check.
 * @param int|null $userid The ID of the user whose slots are being checked
 *                         (defaults to the current user's ID if not provided).
 * @return bool True if a slot exists that the user booked before the deadline, otherwise false.
 * @throws dml_exception
 */
function organizer_get_last_user_appointment($organizer, $userid = null, $mergegroupapps = true, $getevaluated = false) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', ['id' => $organizer]);
    }

    $paramssql = ['userid' => $userid, 'organizerid' => $organizer->id];
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid" .
            ($getevaluated ? " AND a.attended IS NOT NULL " : " ") .
            "ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);
    $app = reset($apps);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && $app !== false) {
        $paramssql = ['slotid' => $app->slotid, 'organizerid' => $organizer->id];
        $query = "SELECT a.* FROM {organizer_slot_appointments} a
                INNER JOIN {organizer_slots} s ON a.slotid = s.id
                WHERE s.organizerid = :organizerid AND s.id = :slotid
                ORDER BY a.id DESC";
        $groupapps = $DB->get_records_sql($query, $paramssql);

        $appcount = 0;
        $someoneattended = false;
        foreach ($groupapps as $groupapp) {
            if ($groupapp->userid == $userid) {
                $app = $groupapp;
            }
            if (isset($groupapp->attended)) {
                $appcount++;
                if ($groupapp->attended == 1) {
                    $someoneattended = true;
                }
            }
        }

        if ($app) {
            $app->attended = ($appcount == count($groupapps)) ? $someoneattended : null;
        }
    }

    return $app;
}

/**
 * Checks if a user has a booked slot before the deadline in the given organizer.
 *
 * This function retrieves all slots booked by the user for the specified organizer
 * and determines if any of the slots have not exceeded the deadline. It returns
 * true if such a slot exists; otherwise, returns false.
 *
 * @param int $organizerid The ID of the organizer to check.
 * @param int|null $userid The ID of the user whose slots are being checked. Defaults to null for the current user.
 * @return bool True if a slot was booked before the deadline, otherwise false.
 * @throws dml_exception Thrown when there is an issue with database access.
 */
function organizer_exist_bookedslotbeforedeadline($organizerid, $userid) {
    global $DB;

    $paramssql = ['userid' => $userid, 'organizerid' => $organizerid];
    $query = "SELECT s.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid";
    $slots = $DB->get_records_sql($query, $paramssql);

    $exists = false;
    foreach ($slots as $slot) {
        $slotx = new organizer_slot($slot);
        if (!$slotx->is_past_deadline()) {
            $exists = true;
            break;
        }
    }

    return $exists;
}

/**
 * Retrieves all appointments related to a specific user for the given organizer.
 *
 * This function fetches all appointments linked to the provided organizer and user.
 * If the organizer operates in group mode (ORGANIZER_GROUPMODE_EXISTINGGROUPS), it
 * optionally merges group appointments and calculates whether the user attended.
 *
 * @param mixed $organizer The organizer object or organizer ID.
 * @param int|null $userid The ID of the user whose appointments are being fetched. Defaults to null for the current user.
 * @param bool $mergegroupapps Whether to merge group appointments in group mode. Defaults to true.
 * @return array An array of appointment records related to the user for the given organizer.
 * @throws dml_exception Thrown when there is an issue with database access.
 */
function organizer_get_all_user_appointments($organizer, $userid = null, $mergegroupapps = true) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', ['id' => $organizer]);
    }

    $paramssql = ['userid' => $userid, 'organizerid' => $organizer->id];
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
    INNER JOIN {organizer_slots} s ON a.slotid = s.id
    WHERE s.organizerid = :organizerid AND a.userid = :userid
    ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && count($apps) > 0) {
        foreach ($apps as &$app) {
            $paramssql = ['slotid' => $app->slotid, 'organizerid' => $organizer->id];
            $query = "SELECT a.* FROM {organizer_slot_appointments} a
                        INNER JOIN {organizer_slots} s ON a.slotid = s.id
                        WHERE s.organizerid = :organizerid AND s.id = :slotid
                        ORDER BY a.id DESC";
            $groupapps = $DB->get_records_sql($query, $paramssql);
            $appcount = 0;
            $someoneattended = false;
            foreach ($groupapps as $groupapp) {
                if (isset($groupapp->attended)) {
                    $appcount++;
                    if ($groupapp->attended == 1) {
                        $someoneattended = true;
                    }
                }
            }
            $app->attended = ($appcount == count($groupapps)) ? $someoneattended : null;
        }
    }

    return $apps;
}

/**
 * Retrieves all group appointments for a specific group and organizer.
 *
 * This function fetches all appointment records associated with the given
 * organizer and group ID. Appointments are ordered in descending order
 * by their ID.
 *
 * @param mixed $organizer The organizer object or organizer ID.
 * @param int $groupid The ID of the group for which appointments are being fetched.
 * @return array An array of appointment records related to the group for the given organizer.
 * @throws dml_exception Thrown when there is an issue with database access.
 */
function organizer_get_all_group_appointments($organizer, $groupid) {
    global $DB;
    $params = ['groupid' => $groupid, 'organizerid' => $organizer->id];
    $groupapps = $DB->get_records_sql(
        'SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE a.groupid = :groupid AND s.organizerid = :organizerid
            ORDER BY a.id DESC', $params
    );

    return $groupapps;
}

/**
 * Retrieves a list of trainers associated with a specific slot.
 *
 * This function fetches trainers assigned to the given slot. If `$withname` is set
 * to true, it retrieves detailed trainer information (ID, first name, last name, email);
 * otherwise, it only returns a sorted list of trainer IDs.
 *
 * @param int $slotid The ID of the slot for which trainers are being fetched.
 * @param bool $withname Whether to fetch detailed trainer information. Defaults to false.
 * @return array An array of trainer records. If `$withname` is true, each record contains
 *               detailed information (id, firstname, lastname, and email). If false, it
 *               returns an array of trainer IDs.
 * @throws dml_exception Thrown when there is an issue with database access.
 */
function organizer_get_slot_trainers($slotid, $withname = false) {
    global $DB;

    if ($withname) {
        $paramssql = ['slotid' => $slotid];
        $slotquery = 'SELECT u.id, u.firstname, u.lastname, u.email
				FROM {organizer_slot_trainer} t
				INNER JOIN {user} u ON t.trainerid = u.id
				WHERE t.slotid = :slotid';
        $trainers = $DB->get_records_sql($slotquery, $paramssql);
    } else {
        if ($trainers = $DB->get_fieldset_select(
                'organizer_slot_trainer', 'trainerid', 'slotid = :slotid', ['slotid' => $slotid])) {
            sort($trainers);
        }

    }

    return $trainers;
}
