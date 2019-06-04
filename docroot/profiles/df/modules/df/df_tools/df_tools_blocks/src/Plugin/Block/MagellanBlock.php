<?php

namespace Drupal\df_tools_blocks\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;

/**
 * Provides the "Magellan" block.
 *
 * @Block(
 *   id = "magellan",
 *   admin_label = @Translation("Magellan"),
 *   category = @Translation("Menus")
 * )
 */
class MagellanBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [
      '#markup' =>
        '<div class="df-tools-magellan-block hide-for-small-only full-width-row sticky-container">' .
        '  <div data-sticky-light>' .
        '    <nav data-magellan-light>' .
        '      <ul class="horizontal menu nav expanded"></ul>' .
        '    </nav>' .
        '  </div>' .
        '</div>',
    ];

    $build['#attached']['library'][] = 'df_tools_blocks/magellan';

    return $build;
  }

}
