<?php

use mod_organizer\local\tests\base;

defined('MOODLE_INTERNAL') || die();

global $CFG;

require_once($CFG->dirroot . '/webservice/tests/helpers.php');
require_once($CFG->dirroot . '/mod/organizer/externallib.php');

/**
 * External mod organizer functions unit tests
 */
class mod_organizer_external_testcase extends externallib_advanced_testcase {
    
    /**
     * Test if the user only gets organizer for enrolled courses
     */
    public function test_get_organizers_by_courses() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course1 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse1',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course1->id);

        $course2 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse2',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course2->id);

        $course3 = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse3',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $organizer1 = self::getDataGenerator()->create_module('organizer', [
            'course' => $course1->id,
            'name' => 'Organizer Module 1',
            'intro' => 'Organizer module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $organizer2 = self::getDataGenerator()->create_module('organizer', [
            'course' => $course2->id,
            'name' => 'Organizer Module 2',
            'intro' => 'Organizer module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $organizer3 = self::getDataGenerator()->create_module('organizer', [
            'course' => $course3->id,
            'name' => 'Organizer Module 3',
            'intro' => 'Organizer module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_organizer_external::get_organizers_by_courses([]);

        // user is enrolled only in course1 and course2, so the third organizer module in course3 should not be included
        $this->assertEquals(2, count($result->organizers));
    }


    /**
     * Test if the user gets a valid organizer from the endpoint
     */
    public function test_get_organizer() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $organizer = self::getDataGenerator()->create_module('organizer', [
            'course' => $course->id,
            'name' => 'Organizer Module',
            'intro' => 'Organizer module for automated php unit tests',
            'introformat' => FORMAT_HTML,
        ]);

        $this->setUser($user);

        $result = mod_organizer_external::get_organizer($organizer->id);

        // organizer name should be equal to 'Organizer Module'
        $this->assertEquals('Organizer Module', $result->organizer->name);

        // Course id in organizer should be equal to the id of the course
        $this->assertEquals($course->id, $result->organizer->course);
    }


    /**
     * Test if the user gets an exception when the organizer is hidden in the course
     */
    public function test_get_organizer_hidden() {
        global $CFG, $DB, $USER;

        $this->resetAfterTest(true);

        $user = $this->getDataGenerator()->create_user();

        $course = $this->getDataGenerator()->create_course([
            'fullname' => 'PHPUnitTestCourse',
            'summary' => 'Test course for automated php unit tests',
            'summaryformat' => FORMAT_HTML
        ]);

        $this->getDataGenerator()->enrol_user($user->id, $course->id);

        $organizer = self::getDataGenerator()->create_module('organizer', [
            'course' => $course->id,
            'name' => 'Hidden Organizer Module',
            'intro' => 'Organizer module for automated php unit tests',
            'introformat' => FORMAT_HTML,
            'visible' => 0,
        ]);

        $this->setUser($user);

        // Test should throw require_login_exception
        $this->expectException(require_login_exception::class);

        $result = mod_organizer_external::get_organizer($organizer->id);

    }

}