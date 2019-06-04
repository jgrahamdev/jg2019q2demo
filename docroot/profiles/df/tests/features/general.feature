@df @general @api
Feature: Demo Framework
  Verifies that basic site functionality is working.

  @d451c44b
  Scenario: Verify that anonymous users can visit the site.
    Given I am an anonymous user
    And I am on the homepage
    Then the response status code should be 200

  @228e5a01
  Scenario: Verify that authenticated users can visit the site.
    Given I am logged in as a user with the "administrator" role
    And I am on the homepage
    Then the response status code should be 200

  @a8de9751
  Scenario: Ensure that database logging is available.
    Given I am logged in as a user with the "administrator" role
    And I am on "/admin/reports/dblog"
    Then I should see "Recent log messages"

  @0e983e34
  Scenario: Ensure that the admin theme is used on user pages
    Given I am an anonymous user
    When I am on "/user"
    Then The page should be an admin page
    When I am on "/user/login"
    Then The page should be an admin page
    When I am on "/user/password"
    Then The page should be an admin page
