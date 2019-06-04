@dfs_tec @api
Feature: DFS TEC: Services page
  In order to prove that dfs_tec was enabled correctly
  As a developer
  I need to check for the services page and services view

  @68063523
  Scenario: Services: Default
    Given I am at "/services"
    Then I should see the heading "Jump Start Your Business"
    And I should see "Digital Consulting"
    And I should see "Business Transformation"
    And I should see "System Integration"
    And I should see "Research Analysis"
