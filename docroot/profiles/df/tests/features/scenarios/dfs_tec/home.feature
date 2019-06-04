@dfs_tec @api
Feature: DFS TEC: Homepage
  In order to prove that dfs_tec was enabled correctly
  As a developer
  I need to check for elements on the front page

  @2868e474
  Scenario: Block: Slideshow
    Given I am on the homepage
    Then I should see "Insight that makes sense of it all"
    And I should see "Strong providers in innovation."
    And I should see "The perspective that affects your bottom line."

  @2f45dff7
  Scenario: Block: Services Promo
    Given I am on the homepage
    Then I should see "Bayside Consulting is your global business consulting partner."
    And I should see "Digital Consulting"
    And I should see "Business Transformation"
    And I should see "Research Analysis"

  @0e25aa3d
  Scenario: Block: Recent Article
    Given I am on the homepage
    Then I should see "Latest articles"
    And I should see "Planning for success"
    And I should see "The Lost Art of Creativity"
    And I should see "Scalable Personalization"

  @201a9fc7
  Scenario: Block: Footer contact
    Given I am on the homepage
    Then I should see "Address"
    And I should see "Press/PR"
    And I should see "Social"
