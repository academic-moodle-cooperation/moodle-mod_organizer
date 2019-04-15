@mod @block_semester_sortierung
Feature: Change WS and SS
   
  @javascript
  Scenario: Change WS and SS
    Given the following "users" exist:
        | username | firstname | lastname | email |
        | teacher1 | Teacher | 1 | teacher1@asd.com |
    And the following "courses" exist:
        | fullname | shortname | category | startdate |
        | Course 1 | C1 | 0 | 1460386247 |
        | Course 2 | C2 | 0 | 1460386247 |
    And the following "course enrolments" exist:
        | user | course | role |
        | teacher1 | C1 | editingteacher |
        | teacher1 | C2 | editingteacher |
 
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I turn editing mode on
    And I add a "Organizer" to section "1" and I fill the form with:
      | Organizer name | Test organizer name |
      | Description | Test description |
    And I follow "Test organizer name" 
    And I set the following fields to these values:
        | Location | Karlsplatz |
        | Location link URL | https://en.wikipedia.org/wiki/Karlsplatz |
    And I press "Save changes"
    And I press "Show legend"
    Then I should see "Status icons"
    And I press "Add new slots"
    And I set the following fields to these values:
        | Location | Rathaus | 
        | Location link URL | https://en.wikipedia.org/wiki/Vienna_City_Hall |
        | Comments | Organizer scenarios? |
    And I press "Save changes"
    And I press "Start"
    And I am on "Course 1" course homepage
    And I add a "Page" to section "2" and I fill the form with:
      | Name | Test Page |
      | Description | Test description |
      | Page content | https://upload.wikimedia.org/wikipedia/commons/thumb/2/29/Nanga_Parbat_The_Killer_Mountain.jpg/1200px-Nanga_Parbat_The_Killer_Mountain.jpg |
    And I follow "Download center"
    Then I should see "Test Page"
    And I set the following fields to these values:
        | General | 0 |
        | Topic 1 | 0 |
        | Topic 2 | 0 |
        | Topic 3 | 0 |
        | Topic 4 | 0 |
      And I set the following fields to these values:
        | General | 1 |
        | Topic 1 | 1 |
        | Topic 2 | 1 |
        | Topic 3 | 1 |
        | Topic 4 | 1 |
    Then I press "Create ZIP archive"
    And I press "OK"
    