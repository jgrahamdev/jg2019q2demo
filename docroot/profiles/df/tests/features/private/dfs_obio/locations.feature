@dfs_obio
Feature: DFS OBIO: Locations
  In order to prove that the location page displays and functions correctly.
  As a developer
  I need to check for store locations.

  @api
  Scenario: Locations: Page
    Given I am on "/locations"
    Then I should see "Our Locations"
    And I should see "San Francisco"
    And I should see "Washington D.C."

  @api
  Scenario: Individual location
    Given I am on "/location/san-francisco"
    Then the response status code should be 200
    And I should see "San Francisco"
    And I should see "Nestled on the edge of the financial district"
