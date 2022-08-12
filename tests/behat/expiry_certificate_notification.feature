@mod @mod_coursecertificate @moodleworkplace @javascript
Feature: See certificates that are about to expire
    In order to know when my certificate expires in advance
    As a student
    I need to see the certificates about to expire notification

Background:
    Given the following "users" exist:
        | username | firstname | lastname | email                |
        | teacher1 | Teacher   | 1        | teacher1@example.com |
        | student1 | Student   | 1        | student1@example.com |
        | manager1 | Manager   | 1        | manager1@example.com |
    And the following "courses" exist:
        | fullname | shortname | format |
        | Course 1 | C1        | topics |
    And the following "course enrolments" exist:
        | user     | course | role           |
        | teacher1 | C1     | editingteacher |
        | student1 | C1     | student        |
    And the following certificate templates exist:
        | name        | shared |
        | Template 01 | 1      |
    And the following "activities" exist:
        | activity          | name           | intro             | course | idnumber           | template    | groupmode | expirydatetype | expirydateoffset | expirynotificationenabled | expirynotificationdateoffset |
        | coursecertificate | My certificate | Certificate intro | C1     | coursecertificate1 | Template 01 | 1         | 2              | 1209600          | 1                         | 86400                        |
    And the following certificate issues exist:
        | template    | user     | course | component             | code  | timecreated |
        | Template 01 | student1 | C1     | mod_coursecertificate | code1 | 1009882800  |

Scenario: See expiry notification when the certificate is about to expire
    Then I log in as "student1"
    And I am on "Course 1" course homepage
    And I follow "My certificate"
    And I press the "back" button in the browser
    And I click on ".popover-region-notifications" "css_element"
    And I should see "Your certificate is abpout to expire!"