<?php

namespace Drupal\df_tools_slideshow\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for Slideshow block CSV.
 *
 * @MigrateSource(
 *   id = "slideshow_block"
 * )
 */
class SlideshowBlock extends CSV {

  public function prepareRow(Row $row) {
    if ($slides = $row->getSourceProperty('Slides')) {
      $uuids = explode(',', $slides);
      $references = [];
      foreach ($uuids as $uuid) {
        if ($entity = \Drupal::entityManager()->loadEntityByUuid('block_content', $uuid)) {
          $references[] = [
            'target_id' => $entity->id()
          ];
        }
      }
      $row->setSourceProperty('Slides', $references);
    }
  }
}
