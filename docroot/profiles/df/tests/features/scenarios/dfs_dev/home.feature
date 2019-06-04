@dfs_dev @api
Feature: DFS DEV: Homepage
  In order to prove that dfs_dev was enabled correctly
  As a developer
  I need to check for elements on the front page

  @bf41f474
  Scenario: Block: Example Slideshow
    Given I am on the homepage
    Then I should see "Example Slideshow"
    And I should see "Who likes blocks?"

  @61ecc9b8
  Scenario: Block: Example Hero
    Given I am on the homepage
    Then I should see "Example Hero"
    And I should see "We like blocks"

  @9a461c09
  Scenario: Block: Example Map
    Given I am on the homepage
    Then I should see "Example Map"

  @55c4a0a8
  Scenario: Block: Basic
    Given I am on the homepage
    Then I should see "Block vero eos et"
    And I should see "Quis aute Block"

  @5cb84acd
  Scenario: Block: User login
    Given I am on the homepage
    Then I should see "User login"

  @f34d54c6
  Scenario: Block: Powered by Drupal
    Given I am on the homepage
    Then I should see "Powered by Drupal"
