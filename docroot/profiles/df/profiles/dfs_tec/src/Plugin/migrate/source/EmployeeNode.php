<?php

/**
 * @file
 * Contains \Drupal\dfs_tec\Plugin\migrate\source\EmployeeNode.
 */

namespace Drupal\dfs_tec\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for Employee node CSV.
 *
 * @MigrateSource(
 *   id = "employee_node"
 * )
 */
class EmployeeNode extends CSV {

  public function prepareRow(Row $row) {
    if ($image_path = $row->getSourceProperty('Headshot')) {
      $path = dirname($this->configuration['path']) . '/images/' . $image_path;

      $data = file_get_contents($path);
      $uri = file_build_uri($image_path);
      $file = file_save_data($data, $uri);

      $row->setSourceProperty('Headshot', $file);
    }
    if ($value = $row->getSourceProperty('Links')) {
      $links_raw = explode(',', $value);
      $links = [];
      foreach ($links_raw as $link) {
          list($uri,$title) = explode('|', $link);
          $links[] = [
            'uri' => $uri,
            'title' => $title,
            'options' => array()
          ];
      }
      $row->setSourceProperty('Links', $links);
    }
  }

}
