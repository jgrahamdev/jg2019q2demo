@dfs_tec @api
Feature: DFS TEC: Employee node
  In order to prove that dfs_tec was enabled correctly
  As a developer
  I need to be able to create nodes of the Employee content type

  @4677e302
  Scenario: Create an employee node
    Given I am viewing an "employee" content with the title "Dries"
    Then I should see the heading "Dries"
    And I should not see the heading "Tom"

#  Scenario: Create an employee node with fields
#    Given "employee" content:
#      | title       | field_title | field_employee_name | field_biography | field_department | field_email    | field_title | field_phone  | field_location |
#      | Thad Martin | Salesperson | Test                | A cool dude     | Sales            | thad@sales.org | Sales guy   | 555-555-5555 | boston         |
#    When I visit "/thad"
#    Then I should see the heading "Thad Martin, Sales guy"
#    And I should see the text "Salesperson"
#    And I should see the text "Sales guy"
#    And I should see the text "Sales"
#    And I should see the text "A cool dude"
#    And I should see the text "Boston, Massachusetts"
#    And I should see the text "555-555-5555"
