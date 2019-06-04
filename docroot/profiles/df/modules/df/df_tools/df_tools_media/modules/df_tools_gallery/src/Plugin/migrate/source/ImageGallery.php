<?php

namespace Drupal\df_tools_gallery\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for Image Gallery CSV.
 *
 * @MigrateSource(
 *   id = "image_gallery"
 * )
 */
class ImageGallery extends CSV {

  public function prepareRow(Row $row) {
    if ($images = $row->getSourceProperty('Images')) {
      $image_names = explode(',', $images);
      $files = [];
      foreach ($image_names as $image_name) {
        $path = dirname($this->configuration['path']) . '/images/' . $image_name;
        $data = file_get_contents($path);
        $uri = file_build_uri($image_name);
        $files[] = file_save_data($data, $uri);
      }
      $row->setSourceProperty('Images', $files);
    }
  }

}
