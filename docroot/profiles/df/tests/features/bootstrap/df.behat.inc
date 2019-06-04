<?php

/**
 * @file
 * Contains \DFSubContext.
 */

use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Hook\Scope\AfterScenarioScope;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Exception\ExpectationException;
use Behat\Mink\Exception\UnsupportedDriverActionException;
use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * Sub context for DF step definitions.
 */
class DFSubContext extends DrupalSubContextBase {

  /**
   * The Mink context.
   *
   * @var \Drupal\DrupalExtension\Context\MinkContext
   */
  protected $minkContext;

  /**
   * Start time for each scenario.
   *
   * @var int
   */
  protected $startTime;

  /**
   * Pre-scenario hook.
   *
   * @BeforeScenario
   */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();

    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
  }

  /**
   * Store current time.
   *
   * @BeforeScenario
   */
  public function setStartTime() {
    $this->startTime = time();
  }

  /**
   * Check for errors since the scenario started.
   *
   * @AfterScenario ~@errors
   */
  public function checkWatchdog(AfterScenarioScope $scope) {
    // Bypass the error checking if the scenario has the @errors tag.
    if (in_array('errors', $scope->getScenario()->getTags())) {
      return;
    }

    $db = \Drupal::database();
    if ($db->schema()->tableExists('watchdog')) {
      $log = $db->select('watchdog', 'w')
        ->fields('w')
        ->condition('w.type', 'php', '=')
        ->condition('w.timestamp', $this->startTime, '>=')
        ->execute()
        ->fetchAll();

      if (!empty($log)) {
        // TODO: Move this stuff to its own exception class in Lightning
        // Extension for greater clarity.
        foreach ($log as $error) {
          // Make the substitutions easier to read in the log.
          $error->variables = unserialize($error->variables);
          print_r($error);
        }
        throw new \Exception('PHP errors logged to watchdog in this scenario.');
      }
    }
  }

  /**
   * Checks, that page contains specified title
   * Example: Then I should see the page title "Who is the Batman?"
   * Example: And I should see the page title "Who is the Batman?"
   *
   * @Then /^(?:|I )should see the page title "(?P<text>(?:[^"]|\\")*)"$/
   */
  public function assertPageContainsTitle($title) {
    $this->assertSession()->elementContains('css', 'h1', $title);
  }

  /**
   * Checks, that page doesn't contains specified title
   * Example: Then I should not see the page title "Who is the Batman?"
   * Example: And I should not see the page title "Who is the Batman?"
   *
   * @Then /^(?:|I )should not see the page title "(?P<text>(?:[^"]|\\")*)"$/
   */
  public function assertPageNotContainsTitle($title) {
    $this->assertSession()->elementNotContains('css', 'h1', $title);
  }

  /**
   * @Then I should see the :tag element with the color :value
   */
  public function assertElementColorValue($tag, $value) {
    // JS script that makes the CSS assertion in the browser.
    $script = <<<JS
      (function(){
        return jQuery("$tag").css('color') === "$value";
      })();
JS;

    if (!$this->getSession()->evaluateScript($script)) {
      throw new Exception();
    }
  }

}
