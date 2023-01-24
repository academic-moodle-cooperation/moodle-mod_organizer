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
 * @author    Thomas Niedermaier (thomas.niedermaier@meduniwien.ac.at)
 * @author    Andreas Windbichler
 * @author    Ivan Šakić
 * @copyright 2014 Academic Moodle Cooperation {@link http://www.academic-moodle-cooperation.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

function organizer_get_slot_user_appointment($slotx, $userid = null, $mergegroupapps = true, $getevaluated = false) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    $organizer = $slotx->get_organizer();

    $paramssql = array('slotid' => $slotx->id, 'userid' => $userid);
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.id = :slotid AND a.userid = :userid" .
        ($getevaluated ? " AND a.attended IS NOT NULL " : " ") .
        "ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);
    $app = reset($apps);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && $app !== false) {
        $paramssql = array('slotid' => $slotx->id, 'groupid' => $app->groupid);
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

function organizer_get_last_user_appointment($organizer, $userid = null, $mergegroupapps = true, $getevaluated = false) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizer));
    }

    $paramssql = array('userid' => $userid, 'organizerid' => $organizer->id);
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
            INNER JOIN {organizer_slots} s ON a.slotid = s.id
            WHERE s.organizerid = :organizerid AND a.userid = :userid" .
            ($getevaluated ? " AND a.attended IS NOT NULL " : " ") .
            "ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);
    $app = reset($apps);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && $app !== false) {
        $paramssql = array('slotid' => $app->slotid, 'organizerid' => $organizer->id);
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

function organizer_get_all_user_appointments($organizer, $userid = null, $mergegroupapps = true) {
    global $DB, $USER;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizer));
    }

    $paramssql = array('userid' => $userid, 'organizerid' => $organizer->id);
    $query = "SELECT a.* FROM {organizer_slot_appointments} a
    INNER JOIN {organizer_slots} s ON a.slotid = s.id
    WHERE s.organizerid = :organizerid AND a.userid = :userid
    ORDER BY a.id DESC";
    $apps = $DB->get_records_sql($query, $paramssql);

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS && $mergegroupapps && count($apps) > 0) {
        foreach ($apps as &$app) {
            $paramssql = array('slotid' => $app->slotid, 'organizerid' => $organizer->id);
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

function organizer_get_next_user_appointment($organizer, $userid = null) {
    global $DB, $USER, $CFG;

    if ($userid == null) {
        $userid = $USER->id;
    }

    if (is_number($organizer) && $organizer == intval($organizer)) {
        $organizer = $DB->get_record('organizer', array('id' => $organizer));
    }

    $todaymidnight = mktime(0, 0, 0, date("m"), date("d"), date("Y"));

    if ($organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
        require_once($CFG->dirroot . '/mod/organizer/locallib.php');
        if ($group = organizer_fetch_user_group($userid, $organizer->id)) {
            $paramssql = array('organizerid' => $organizer->id, 'groupid' => $group->id, 'todaymidnight' => $todaymidnight);
            $query = "SELECT a.*, s.starttime FROM {organizer_slot_appointments} a
                  INNER JOIN {organizer_slots} s ON a.slotid = s.id
                  WHERE s.organizerid = :organizerid AND a.groupid = :groupid AND s.starttime > :todaymidnight
                  ORDER BY s.starttime ASC";
            $apps = $DB->get_records_sql($query, $paramssql);
            $app = reset($apps);
        } else {
            $app = null;
        }
    } else {
        $paramssql = array('organizerid' => $organizer->id, 'userid' => $userid, 'todaymidnight' => $todaymidnight);
        $query = "SELECT a.*, s.starttime FROM {organizer_slot_appointments} a
                  INNER JOIN {organizer_slots} s ON a.slotid = s.id
                  WHERE s.organizerid = :organizerid AND a.userid = :userid AND s.starttime > :todaymidnight
                  ORDER BY s.starttime ASC";
        $apps = $DB->get_records_sql($query, $paramssql);
        $app = reset($apps);
    }

    return $app;
}


class organizer_slot
{

    private $slot;
    private $organizer;
    private $apps;
    private $queue;
    private $queuegroup;

    public function __construct($slot, $lazy = true, $organizer = null) {
        global $DB;

        if (is_number($slot) && $slot == intval($slot)) {
            $this->slot = $DB->get_record('organizer_slots', array('id' => $slot));
        } else {
            $this->slot = $slot;

            if (!isset($this->slot->organizerid)) {
                $this->slot->organizerid = $DB->get_field('organizer_slots', 'organizerid', array('id' => $slot->slotid));
            }

            if (!isset($this->slot->maxparticipants)) {
                $this->slot->maxparticipants = $DB->get_field('organizer_slots', 'maxparticipants', array('id' => $slot->slotid));
            }
        }

        foreach ((array) $this->slot as $key => $value) {
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

    public function get_organizer() {
        $this->load_organizer();
        return $this->organizer;
    }

    public function get_slot() {
        return $this->slot;
    }

    public function has_participants() {
        $this->load_appointments();
        return count($this->apps) != 0;
    }

    public function get_rel_deadline() {
        $this->load_organizer();
        return $this->organizer->relativedeadline;
    }

    public function get_abs_deadline() {
        $this->load_organizer();
        return $this->organizer->duedate;
    }

    public function is_upcoming() {
        return $this->slot->starttime > time();
    }

    public function is_past_deadline() {
        $deadline = $this->get_rel_deadline($this->slot);
        return $this->slot->starttime <= $deadline + time();
    }

    public function is_past_due() {
        return $this->slot->starttime <= time();
    }

    public function is_full() {
        $this->load_organizer();
        $this->load_appointments();
        if ($this->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            return count($this->apps) > 0;
        } else {
            return count($this->apps) >= $this->slot->maxparticipants;
        }
    }

    public function is_available() {
        return ($this->slot->availablefrom == 0) || ($this->slot->starttime - $this->slot->availablefrom <= time());
    }

    public function organizer_expired() {
        $this->load_organizer();
        return isset($this->organizer->duedate) && $this->organizer->duedate - time() < 0;
    }

    public function organizer_unavailable() {
        $this->load_organizer();
        return isset($this->organizer->allowregistrationsfromdate) && $this->organizer->allowregistrationsfromdate - time() > 0;
    }

    public function is_evaluated() {
        $this->load_appointments();

        foreach ($this->apps as $app) {
            if (!isset($app->attended)) {
                return false;
            }
        }
        return count($this->apps) > 0;
    }

    public function organizer_groupmode_user_has_access() {
        $this->load_organizer();
        global $DB;
        if ($this->organizer->isgrouporganizer == ORGANIZER_GROUPMODE_EXISTINGGROUPS) {
            $moduleid = $DB->get_field('modules', 'id', array('name' => 'organizer'));
            $courseid = $DB->get_field(
                'course_modules', 'course',
                array('module' => $moduleid, 'instance' => $this->organizer->id)
            );
            $groups = groups_get_user_groups($courseid);
            $groupingid = $DB->get_field(
                'course_modules', 'groupingid',
                array('module' => $moduleid, 'instance' => $this->organizer->id)
            );
            if (!isset($groups[$groupingid]) || !count($groups[$groupingid])) {
                return false;
            }
        }
        return true;
    }


    /**
     * Waiting list
     * Returns the position of a given user in this slot's queue starting at 1.
     * Returns 0 if the user is not in the queue.
     *
     * @param int $userid The ID of the user.
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
     * @param  Int $groupid The ID of the group.
     * @return Int the group's position in queue
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

    private function load_organizer() {
        global $DB;
        if (!$this->organizer) {
            $this->organizer = $DB->get_record('organizer', array('id' => $this->slot->organizerid));
        }
    }

    private function load_appointments() {
        global $DB;
        if (!$this->apps) {
            $this->apps = $DB->get_records('organizer_slot_appointments', array('slotid' => $this->slot->id));
        }
    }

    private function load_queue() {
        global $DB;
        if (!$this->queue) {
            $this->queue = $DB->get_records('organizer_slot_queues', array('slotid' => $this->slot->id), 'id ASC');
        }
    }

    private function load_queue_group() {
        global $DB;
        if (!$this->queuegroup) {
            $sql = "SELECT q.groupid FROM (SELECT groupid, slotid FROM {organizer_slot_queues} ORDER BY id asc) q
                    WHERE q.slotid = :slotid
                    GROUP BY q.groupid";
            $paramssql = array('slotid' => $this->slot->id);
            $this->queuegroup = $DB->get_records_sql($sql, $paramssql);
        }
    }
}

function organizer_user_has_access($slotid) {
    global $DB;
    $slot = $DB->get_record('organizer_slots', array('id' => $slotid));
    $moduleid = $DB->get_field('modules', 'id', array('name' => 'organizer'));
    $organizer = $DB->get_record('organizer', array('id' => $slot->organizerid));
    $courseid = $DB->get_field('course_modules', 'course', array('module' => $moduleid, 'instance' => $organizer->id));
    $groups = groups_get_user_groups($courseid);
    $groupingid = $DB->get_field(
        'course_modules', 'groupingid',
        array('module' => $moduleid, 'instance' => $organizer->id)
    );
    if (!isset($groups[$groupingid]) || !count($groups[$groupingid])) {
        return false;
    }
    return true;
}

function organizer_get_slot_trainers($slotid, $withname = false) {
    global $DB;

    if ($withname) {
        $paramssql = array('slotid' => $slotid);
        $slotquery = 'SELECT u.id, u.firstname, u.lastname, u.email
				FROM {organizer_slot_trainer} t
				INNER JOIN {user} u ON t.trainerid = u.id
				WHERE t.slotid = :slotid';
        $trainers = $DB->get_records_sql($slotquery, $paramssql);
    } else {
        if ($trainers = $DB->get_fieldset_select(
                'organizer_slot_trainer', 'trainerid', 'slotid = :slotid', array('slotid' => $slotid))) {
            sort($trainers);
        }

    }

    return $trainers;
}
