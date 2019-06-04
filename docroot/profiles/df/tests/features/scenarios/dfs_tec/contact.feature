@dfs_tec @api
Feature: DFS TEC: Contact page
  In order to prove that dfs_tec was enabled correctly
  As a developer
  I need to check for the services page and services view

  @80289074
  Scenario: Contact: Default
    Given I am at "/contact"
    Then I should see the heading "Contact Our Team"
