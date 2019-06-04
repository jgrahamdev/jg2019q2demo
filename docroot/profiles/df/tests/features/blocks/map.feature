@df @df_tools @df_tools_blocks @blocks @hero @api
Feature: Demo Framework: Hero Block

  @a6c1005e
  Scenario: Map workflow
    Given I am logged in as a user with the "administrator" role
    When I go to "/block/add/map"
    Then I fill in "Block description" with "Homepage Map"
    And I fill in "Street address" with "1120 SW 5th Ave"
    And I fill in "City" with "Portland"
    And I select "Oregon" from "State"
    And I fill in "Zip code" with "97210"
    And I press "Save"
    Then I should see "Configure block"
    When I select "content" from "edit-region"
    And I press "Save block"
    Then I should see "The block configuration has been saved"
    When I go to "/"
    Then I should see "Homepage Map"
    And I should see "Address"
    And I should see "1120 SW 5th Ave"
    And I go to "/admin/structure/block/manage/homepagemap/delete"
    And I press "Remove"
    Then I should see "The block Homepage Map has been removed."
