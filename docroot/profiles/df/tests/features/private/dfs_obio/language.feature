@dfs_obio
Feature: DFS OBIO: Language
  In order to prove that content can be displayed and functions correctly in multiple languages.
  As a developer
  I need to check for page elements.

  @api
  Scenario: Language: French
    Given I am an anonymous user
    And I am on "/fr"
    And I should see "faire de l'espace."
    And I should see "Abonnez-vous"

  @api
  Scenario: Language: Spanish
    Given I am an anonymous user
    And I am on "/es"
    And I should see "hacer un poco de espacio."
    And I should see "Suscribirse y Guardar"

#  @api
#  Scenario: Language: Switcher Block
#    Given I am an anonymous user
#    And I am on the homepage
#    Then I should see a "language_block:language_interface" block
#    And I should see the link "English"
#    And I should see the link "Français"
#    And I should see the link "Español"
#    When I click "Français"
#    Then I should see "faire de l'espace."
#    When I click "Español"
#    Then I should see "hacer un poco de espacio."
#    When I click "English"
#    Then I should see "make some space."

#  @api
#  Scenario: Language: Switching Languages
#    Given I am an anonymous user
#    And I am on "/collection/technology-collection"
#    Then I should see "Technology Collection"
#    And I should see "About the Collection"
#    And I should see "Features"
#    And I should see "Success Stories"
#    And I should see "Reviews"
#    And I should see "Refresh your office decor regularly"
#    And I should see "How it Works"
#    When I click "Français"
#    Then I should see "Collection de la technologie"
#    And I should see "À propos de la Collection"
#    And I should see "Caractéristiques"
#    And I should see "Réussites"
#    And I should see "Avis"
#    And I should see "Rafraîchissez votre décor de bureau régulièrement"
#    And I should see "Comment cela Fonctionne"
