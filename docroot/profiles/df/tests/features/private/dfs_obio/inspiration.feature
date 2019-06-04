@dfs_obio
Feature: DFS OBIO: Inspiration
  In order to prove that the inspiration page displays and functions correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Inspiration: Page
    Given I am on "/inspiration"
    Then I should see "The Obio Inspiration Blog"

  @api
  Scenario: Inspiration: Search
    Given I am logged in as a user with the administrator role
    And I am on "/inspiration"
    When I enter "3 Office Design Trends Every Founder Must Know" for "nid"
    And I press "Search"
    Then I should see "1. Privacy in an Open-Office World"

  @api
  Scenario: Inspiration: Filter
    Given I am on "/inspiration"
    When I fill in "edit-field-tags-target-id" with "heritage"
    And I press "edit-submit-article-collection"
    And I should see "Meet Thomas and See How this Collection Works for His Business"
    And I should not see "3 Reasons to Never Get Complacent about Your Office's Design"
