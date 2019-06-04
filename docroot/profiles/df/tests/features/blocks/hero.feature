@df @df_tools @df_tools_blocks @blocks @hero @api
Feature: Demo Framework: Hero Block

  @fcbbb7d6
  Scenario: Hero workflow
    Given I am logged in as a user with the "administrator" role
    When I go to "/block/add/hero"
    Then I fill in "edit-info-0-value" with "Homepage Hero"
    And I fill in "edit-field-first-line-0-value" with "Check out"
    And I fill in "edit-field-second-line-0-value" with "This stuff"
    And I fill in "edit-field-hero-link-0-uri" with "<front>"
    And I fill in "edit-field-hero-link-0-title" with "Click me"
    And I press "Save"
    Then I should see "Configure block"
    When I select "content" from "edit-region"
    And I press "Save block"
    Then I should see "The block configuration has been saved"
    When I go to "/"
    Then I should see "Homepage Hero"
    And I should see "Check out"
    And I should see "This stuff"
    And I should see "Click me"
    And I go to "/admin/structure/block/manage/homepagehero/delete"
    And I press "Remove"
    Then I should see "The block Homepage Hero has been removed."
