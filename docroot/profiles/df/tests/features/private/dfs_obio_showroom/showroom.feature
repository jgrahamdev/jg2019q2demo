@dfs_obio_showroom
Feature: DFS OBIO: Showroom
  In order to prove that the showroom scenario displays and functions correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Showroom: Homepage
    Given I am on the homepage
    Then I should see a "block_content:79067cfc-3ecb-49a2-9614-d9cbf2253e82" block
    Then I should see "Downtown Boston"
    And I should see the link "Visit Us"
    And I should see "Seeing is believing."
    And I should see a "block_content:13e54264-d983-4fdd-a5f7-9858241a9c2c" block
    And I should see "Hours / Location"
    And I should see "What's New"
    And I should see a "views_block:latest_articles-block_1" block
    And I should see "Boston Galleries"
    And I should see a "views_block:latest_galleries-boston_galleries" block
    And I should see "Stay Informed"
