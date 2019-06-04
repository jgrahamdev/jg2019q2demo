<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\TestimonialNode.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\AuthorMigrationTrait;
use Drupal\df_tools_migration\Plugin\migrate\source\ImageMigrationTrait;

/**
 * Source for Testimonial node CSV.
 *
 * @MigrateSource(
 *   id = "testimonial_node"
 * )
 */
class TestimonialNode extends CSV {

  use AuthorMigrationTrait;
  use ImageMigrationTrait;

  public function prepareRow(Row $row) {

    $this->setUidProperty($row, null, 'creator');

    if ($value = $row->getSourceProperty('Tags')) {
      $row->setSourceProperty('Tags', explode(',', $value));
    }

    $this->setImageProperty($row, 'Image');

    if ($value = $row->getSourceProperty('Author Image')) {
      $path = dirname($this->configuration['path']) . '/images/' . $value;

      $data = file_get_contents($path);
      $uri = file_build_uri($value);
      $file = file_save_data($data, $uri);

      $row->setSourceProperty('Author Image', $file);
    }
  }
}
