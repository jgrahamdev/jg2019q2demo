@dfs_edu
Feature: DFS EDU: Menus
  In order to prove that dfs_edu was enabled correctly
  As a developer
  I need to check for menu items

  @api
  Scenario: Primary Menu
    Given I am on the homepage
      Then I should not see "Cart"
