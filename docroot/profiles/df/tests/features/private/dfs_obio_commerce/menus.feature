@dfs_obio_commerce
Feature: DFS OBIO: Menus
  In order to prove that the menu displays and functions correctly.
  As a developer
  I need to check for menu items

  @api
  Scenario: Menus: Secondary Menu
    Given I am logged in as a user with the administrator role
    And I am on the homepage
    Then I should see a "commerce_cart" block
    And I should see a "user_dropdown" block
