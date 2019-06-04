@dfs_obio
Feature: DFS OBIO: Shop
  In order to prove that the shop page displays and functions correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Shop: Page
    Given I am on "/shop/office"
    Then I should see "Shop by Collection"
    And I should see the link "Eco-friendly Collection"
    And I should see the link "Technology Collection"
    And I should see the link "Minimalist Collection"
    And I should see the link "Heritage Collection"
