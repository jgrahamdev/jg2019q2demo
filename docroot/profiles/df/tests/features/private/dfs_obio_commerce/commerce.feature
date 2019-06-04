@dfs_obio_commerce @api
Feature: DFS OBIO: Commerce
  In order to prove that commerce products display and function correctly.
  As a developer
  I need to purchase products.

  Scenario: Commerce: Product Purchasing
    Given I am on "/collection/heritage-collection"
    Then I should see "Build Your Office"
    When I select the radio button "Small Office, 100 - 1000 square feet"
    And I press "Add to cart"
    Then I should see "Small Office, Silver Ambiance added to your cart."
    And I should see "1 item"
    When I visit "/cart"
    Then I should see "Heritage Collection"
    And I should see "Small Office, Silver Ambiance"
    When I visit "/collection/minimalist-collection"
    Then I should see "Build Your Office"
    When I select the radio button "Medium Office, 1000 - 2500 square feet"
    And I press "Add to cart"
    Then I should see "Medium Office, Silver Ambiance added to your cart."
    And I should see "2 items"
    When I visit "/cart"
    Then I should see "Minimalist Collection"
    And I should see "Medium Office, Silver Ambiance"
    When I press "Checkout"
    Then I should see "Returning Customer"
    And I should see "Guest Checkout"
    When I press "Continue as Guest"
    Then I should see "Contact information"
    And I should see "Order Summary"
    When I fill in "Email" with "customer@example.com"
    And I press "Continue to review"
    Then I should see "Contact information"
    And I should see "customer@example.com"
    And I should see "Order Summary"
    When I press "Complete purchase"
    Then I should see "You can view your order on your account page when logged in."

  Scenario: Commerce: Cart Redirection
    Given I am on "/collection/minimalist-collection"
    Then I should see "Build Your Office"
    When I select the radio button "Small Office, 100 - 1000 square feet"
    And I press "Add to cart"
    Then I should be on "/cart"
