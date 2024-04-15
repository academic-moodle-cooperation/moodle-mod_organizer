@mod @mod_organizer
Feature: Create organizer instance

  @javascript
  Scenario: Create organizer instance in course1
    Given the following "users" exist:
      | username | firstname | lastname | email            |
      | teacher1 | Teacher   | 1        | teacher1@asd.com |
    And the following "courses" exist:
      | fullname | shortname | category | startdate |
      | Course 1 | C1        | 0        | 1460386247 |
      | Course 2 | C2        | 0        | 1460386247 |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |

    When I log in as "teacher1"
    And I add a organizer activity to course "Course 1" section "1" and I fill the form with:
      | Organizer name | Test organizer name |
      | Description    | Test description    |
    And I am on the "Test organizer name" "organizer activity" page
    And I set the field "Location" to "Karlsplatz"
    And I set the field "id_newslots_0_day" to "Monday"
    Then the field "id_newslots_0_dayto" matches value "Monday"
    Then the field "id_newslots_0_toh" matches value "08"
    Then the field "id_newslots_0_tom" matches value "15"
    And I press "Save changes"
