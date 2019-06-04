<?php

namespace Drupal\df_tools_blocks\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for Hero block CSV.
 *
 * @MigrateSource(
 *   id = "hero_block"
 * )
 */
class HeroBlock extends CSV {

  public function prepareRow(Row $row) {
    if ($image_path = $row->getSourceProperty('Image')) {
      $path = dirname($this->configuration['path']) . '/images/' . $image_path;

      $data = file_get_contents($path);
      $uri = file_build_uri($image_path);
      $file = file_save_data($data, $uri);

      $row->setSourceProperty('Image', $file);
    }

    if ($blocks = $row->getSourceProperty('Nested Blocks')) {
      $uuids = explode(',', $blocks);
      if (!empty($uuids)) {
        $references = [];
        foreach ($uuids as $uuid) {
          if (!empty($uuid)) {
            if ($entity = \Drupal::entityManager()->loadEntityByUuid('block_content', $uuid)) {
              $references[] = [
                'target_id' => $entity->id()
              ];
            }
          }
        }
        $row->setSourceProperty('Nested Blocks', $references);
      }
    }
  }

}
