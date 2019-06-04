@dfs_obio
Feature: DFS OBIO: Collections
  In order to prove that collections display and function correctly.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Collections: Page
    Given I am on "/collection/heritage-collection"
    Then I should see "Heritage Collection"
    And I should see "About the Collection"
    And I should see "Features"
    And I should see "Success Stories"
    And I should see "Reviews"
    And I should see "How it Works"

  @api
  Scenario: Collections: Reviews
    Given I am an anonymous user
    When I visit "/collection/heritage-collection"
    Then I should not see "Add a review"

  @api
  Scenario: Collections: Add a Review
    Given I am logged in as a user with the administrator role
    When I visit "/collection/heritage-collection"
    Then I should see "Add a review"
    And I fill in "edit-field-review-0-value" with "Example review text."
    And I select the radio button "" with the id "edit-field-rating-0-value-5"
    And I press "Save"
    Then I should see "Your comment has been posted."
    And I click "Go to page 2"
    And I should see "Example review text."
