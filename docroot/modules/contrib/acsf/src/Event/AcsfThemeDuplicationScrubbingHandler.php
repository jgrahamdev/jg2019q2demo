<?php

namespace Drupal\acsf\Event;

/**
 * Truncates the pending theme notification table.
 */
class AcsfThemeDuplicationScrubbingHandler extends AcsfEventHandler {

  /**
   * Implements AcsfEventHandler::handle().
   */
  public function handle() {
    drush_print(dt('Entered @class', ['@class' => get_class($this)]));
    \Drupal::database()->truncate('acsf_theme_notifications')->execute();
  }

}
