@dfs_fin
Feature: DFS FIN: Menus
  In order to prove that dfs_fin was enabled correctly
  As a developer
  I need to check for menu items

  @api
  Scenario: Primary Menu
    Given I am on the homepage
      Then I should see "Products"
        And I should see "Auto"
        And I should see "Disability"
        And I should see "Home"
        And I should see "Life"
      And I should see "Resources"
        And I should see "Articles"
        And I should see "Frequently Asked Questions"
      And I should see "Our Company"
        And I should see "About"
        And I should see "History"
        And I should see "Our Story"
        And I should see "Terms & Conditions"
      And I should see "Contact Us"
      And I should see "Get Started"
        And I should see "Get A Quote"
        And I should see "Find A Location"

  Scenario: Secondary Menus
    Given I am on the homepage
      Then I should see "Products"
        And I should see "Auto Insurance"
        And I should see "Home Insurance"
        And I should see "Life Insurance"
        And I should see "Disability Insurance"
      And I should see "Our Company"
        And I should see "History"
        And I should see "Terms & Conditions"
        And I should see "About"
        And I should see "Our Story"
        And I should see "Agent License"
      And I should see "Get Started"
        And I should see "Need to speak to someone?"
        And I should see "LOCATIONS"
        And I should see "CONTACT US"
