@dfs_fin @fin_search
Feature: DFS FIN: Search
  In order to prove that search displays and functions correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Header Search Block: Search
    Given I am an anonymous user
    And I am on the homepage
    When I fill in "Keywords" with "our story"
    And I press "Search"
    Then I should see "Bayside Associates Financial Group has been helping people with their insurance needs since 1928."
