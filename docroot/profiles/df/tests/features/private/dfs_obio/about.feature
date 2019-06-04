@dfs_obio
Feature: DFS OBIO: About
  In order to prove that the about page displays and functions correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: About: Page
    Given I am on "/about"
    Then I should see "About Us"
    And I should see "The Right Space, The Right Time"
    And I should see "Our Founding Team"
    And I should see "Lauren Stevens"
    And I should see "Monica Smith"
    And I should see "Todd Brooks"
    And I should see "Our Locations"
