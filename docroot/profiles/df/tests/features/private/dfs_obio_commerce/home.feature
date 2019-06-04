@dfs_obio_commerce @api
Feature: DFS OBIO: Commerce Home
  In order to prove that commerce products show on the homepage.
  As an end-user
  I need to see invidiual products on the homepage.

  Scenario: Commerce: Homepage Featured Blocks
    Given I am on "/home"
    Then I should see "Featured products"
    When I click "Quick View"
    Then I should see "SKU"
    And I should see "Exclusively by Obio"
