@quizaccess_quiztimer @view_quiztimer @javascript
Feature: A student visualizes a quiz with a quiztimer configured
  In order to visualize a quiz with a quiztimer configured
  As a student
  I should be enabled to view a quiz in a course.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname | email |
      | student1 | Student | One | student1@example.com |
    And the following "courses" exist:
      | fullname | shortname | format |
      | Test Quiztimer | testquiztimer | topics |
    And the following "course enrolments" exist:
      | user | course | role |
      | student1 | testquiztimer | student |
    And I log in as "admin"
    And I wait "2" seconds
    And I am on "testquiztimer" course homepage
    And I turn editing mode on
    And I click on "Add an activity or resource" "button" in the "General" "section"
    And I click on "Add a new Quiz" "link" in the "Add an activity or resource" "dialogue"
    And I should see "Adding a new Quiz"
    And I set the following fields to these values:
      | Name | Quiz example |
    And I press "Save and display"
    And I wait "2" seconds
    And I click on "Questions" "link"
    And I wait "2" seconds
    And I turn editing mode off
    And I click on "Add" "link"
    And I click on "a new question" "link"
    And I click on "item_qtype_truefalse" "radio"
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    When I set the following fields to these values:
      | Question name | question example1 |
      | Question text  | question example1 |
    And I click on "id_submitbutton" "button"
    And I wait "2" seconds

  Scenario: A student visualizes a quiz with quiztimer configured for section
    Given I click on "Edit heading 'Untitled section'" "icon"
    And I wait "1" seconds
    And I type "section example1"
    And I press the enter key
    And I select "Adjust questions times" from the "id_quiztimer_editviewselector" singleselect
    And I wait "2" seconds
    And I click on the edit icon
    And I wait "2" seconds
    And I press the delete key
    And I press the delete key
    And I wait "1" seconds
    And I type "5"
    And I wait "1" seconds
    And I press the enter key
    And I wait "2" seconds
    And I should see "Total section time:"
    And I should see "5"
    And I should see "minutes"
    And I wait "2" seconds
    And I log in as "student1"
    And I am on "testquiztimer" course homepage
    And I wait "1" seconds
    When I click on "Quiz example" "link" in the "Quiz example" activity
    And I wait "1" seconds
    And I click on "Attempt quiz" "button"
    And I wait "1" seconds
    And I click on "Start attempt" "button"
    And I wait "1" seconds
    Then The "countdown-section" should change his content
    And I should see "question example1"

  Scenario: A student visualizes a quiz with quiztimer configured for question
    Given I click on "Add" "link"
    And I click on "a new question" "link"
    And I wait "1" seconds
    And I click on "item_qtype_truefalse" "radio"
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    And I set the following fields to these values:
      | Question name | question example2 |
      | Question text  | question example2 |
    And I click on "id_submitbutton" "button"
    And I wait "2" seconds
    And I select "Adjust questions times" from the "id_quiztimer_editviewselector" singleselect
    And I wait "1" seconds
    And I select "Time for question" from the "id_quiztimer_quizmodeselector" singleselect
    And I wait "3" seconds
    And I click on the pencil icon to edit the time of the question
    And I wait "1" seconds
    And I press the delete key
    And I press the delete key
    And I wait "1" seconds
    And I type "45"
    And I wait "1" seconds
    And I press the enter key
    And I wait "1" seconds
    And I should see "Total section time:"
    And I should see "1 minutes 45 seconds"
    And I should see "minutes"
    And I wait "2" seconds
    And I log in as "student1"
    And I am on "testquiztimer" course homepage
    And I wait "1" seconds
    When I click on "Quiz example" "link" in the "Quiz example" activity
    And I wait "1" seconds
    And I click on "Attempt quiz" "button"
    And I wait "1" seconds
    And I click on "Start attempt" "button"
    And I wait "3" seconds
    Then The "countdown-question" should change his content
    And I should see "question example1"
    And I click on "Next page" "button"
    And I wait "3" seconds
    And I should see "question example2"
    And The "countdown-question" should change his content
    And I wait "3" seconds

  Scenario: A student visualizes a quiz with quiztimer configured with the section time distributed in pages
    Given I click on "Edit heading 'Untitled section'" "icon"
    And I wait "1" seconds
    And I type "section example1"
    And I press the enter key
    And I click on "Add" "link"
    And I click on "a new question" "link"
    And I click on "item_qtype_truefalse" "radio"
    And I press tab
    And I press the enter key
    And I wait "1" seconds
    And I set the following fields to these values:
      | Question name | question example2 |
      | Question text  | question example2 |
    And I click on "id_submitbutton" "button"
    And I wait "2" seconds
    And I click on "Add page break" "link"
    And I wait "1" seconds
    And I should see "Page 2"
    And I wait "3" seconds
    And I select "Adjust questions times" from the "id_quiztimer_editviewselector" singleselect
    And I wait "3" seconds
    And I select "Distribute the section time in pages" from the "id_quiztimer_quizmodeselector" singleselect
    And I wait "3" seconds
    And I should see "Total section time:"
    And I should see "10"
    And I should see "minutes"
    And I should see "Page 1"
    And I should see "Page 2"
    And I should see "5 minutes"
    And I wait "2" seconds
    And I log in as "student1"
    And I am on "testquiztimer" course homepage
    And I wait "1" seconds
    When I click on "Quiz example" "link" in the "Quiz example" activity
    And I wait "2" seconds
    And I click on "Attempt quiz" "button"
    And I wait "1" seconds
    And I click on "Start attempt" "button"
    And I wait "1" seconds
    Then The "countdown-section" should change his content
    And I should see "question example1"
    And I click on "Next page" "button"
    And I wait "1" seconds
    And I should see "question example2"
    And The "countdown-section" should change his content
    And I wait "3" seconds
