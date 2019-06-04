@dfs_fin
Feature: DFS FIN: Homepage
  In order to prove that dfs_fin was enabled correctly
  As a developer
  I need to check for elements on the front page

  @api
  Scenario: Block: Slideshow
    Given I am on the homepage
    Then I should see "Bayside Associates Financial Group"
    And I should see "What kind of insurance are you looking for?"
    And I should see "Why Go Alone?"

  Scenario: Block: Intro CTAs
    Given I am on the homepage
    Then I should see "Do you have the proper coverage"
    And I should see "protecting with auto insurance coverage"
    And I should see "Protect Your Assets"
    And I should see "For over 87 years"
    And I should see "See How"
    And I should see "Managing Risk"
    And I should see "Diversification is key"
    And I should see "Tailored Advice"
    And I should see "like a lighthouse"
    And I should see "Get Advice"

  Scenario: Block: Member Services
    Given I am on the homepage
    Then I should see "For Existing Customers"
    And I should see "Login to our secure portal"
    And I should see "Make a payment"
    And I should see "Start a new quote"

  Scenario: Block: Our History
    Given I am on the homepage
    Then I should see "Our Company"
    And I should see "Daniel Smith and Jim Snow in San Francisco"
    And I should see "READ MORE"

  Scenario: Block: Testimonials
    Given I am on the homepage
    Then I should see "What our customers are saying"
