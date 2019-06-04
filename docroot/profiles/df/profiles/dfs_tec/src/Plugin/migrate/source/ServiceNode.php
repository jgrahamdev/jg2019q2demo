<?php

/**
 * @file
 * Contains \Drupal\dfs_tec\Plugin\migrate\source\ServiceNode.
 */

namespace Drupal\dfs_tec\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for Service node CSV.
 *
 * @MigrateSource(
 *   id = "service_node"
 * )
 */
class ServiceNode extends CSV {

  public function prepareRow(Row $row) {
    if ($image_path = $row->getSourceProperty('Hero Image')) {
      $path = dirname($this->configuration['path']) . '/images/' . $image_path;

      $data = file_get_contents($path);
      $uri = file_build_uri($image_path);
      $file = file_save_data($data, $uri);

      $row->setSourceProperty('Hero Image', $file);
    }
    if ($value = $row->getSourceProperty('Tags')) {
      $row->setSourceProperty('Tags', explode(',', $value));
    }
  }

}
