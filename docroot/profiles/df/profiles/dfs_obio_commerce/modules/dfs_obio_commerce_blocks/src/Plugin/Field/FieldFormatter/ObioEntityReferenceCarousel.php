<?php

namespace Drupal\dfs_obio_commerce_blocks\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\slick\Plugin\Field\FieldFormatter\SlickEntityFormatterBase;

/**
 * @\Drupal\Core\Field\Annotation\FieldFormatter(
 *   id = "obio_entity_reference_carousel",
 *   label = @\Drupal\Core\Annotation\Translation("Obio Carousel"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ObioEntityReferenceCarousel extends SlickEntityFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // Get back field wrapper, and make qe functional again.
    $slick = parent::viewElements($items, $langcode);
    return [$slick];
  }

}
