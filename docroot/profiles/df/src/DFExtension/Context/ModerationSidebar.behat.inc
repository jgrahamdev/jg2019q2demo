<?php

namespace Acquia\DFExtension\Context;

use Acquia\LightningExtension\AwaitTrait;

use Drupal\DrupalExtension\Context\DrupalSubContextBase;

/**
 * A context with miscellaneous helpers.
 */
class ModerationSidebarContext extends DrupalSubContextBase {

  use AwaitTrait;

  /**
   * Opens the moderation sidebar.
   *
   * @When I open the moderation sidebar
   */
  public function openModerationSidebar() {
    $assert = $this->assertSession();

    // Assert that the tab exists...
    $link = $assert->elementExists(
      'named',
      ['link', 'Tasks'],
      $assert->elementExists('css', '.moderation-sidebar-toolbar-tab')
    );

    // ...but only open it if not already active.
    if ($link->hasClass('sidebar-open') == FALSE) {
      $link->click();
      $this->awaitAjax();
    }
  }

  /**
   * Closes the moderation sidebar.
   *
   * @When I close the moderation sidebar
   */
  public function closeModerationSidebar() {
    $assert = $this->assertSession();

    // Assert that the tab exists...
    $link = $assert->elementExists(
      'named',
      ['link', 'Tasks'],
      $assert->elementExists('css', '.moderation-sidebar-toolbar-tab')
    );

    // ...but only open it if already active.
    if ($link->hasClass('sidebar-open') == TRUE) {
      $link->click();
      $this->awaitAjax();
    }
  }

  /**
   * Selects a moderation sidebar item.
   *
   * @param string $item
   *   The moderation sidebar item to select.
   *
   * @Given I select the :item moderation sidebar button
   */
  public function selectModerationSidebarButton($item) {
    $assert = $this->assertSession();

    // Assert that the tab exists...
    $link = $assert->elementExists(
      'named',
      ['button', $item],
      $assert->elementExists('css', '.moderation-sidebar-container')
    );

    $link->click();
    $this->awaitAjax();
  }

  /**
   * Selects a moderation sidebar item.
   *
   * @param string $item
   *   The moderation sidebar item to select.
   *
   * @Given I select the :item moderation sidebar link
   */
  public function selectModerationSidebarLink($item) {
    $assert = $this->assertSession();

    // Assert that the tab exists...
    $link = $assert->elementExists(
      'named',
      ['link', $item],
      $assert->elementExists('css', '.moderation-sidebar-container')
    );

    $link->click();
    $this->awaitAjax();
  }

}

