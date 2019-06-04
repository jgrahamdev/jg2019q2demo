<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\VIN.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\ImageMigrationTrait;

/**
 * Source for Testimonial VIN CSV.
 *
 * @MigrateSource(
 *   id = "vin"
 * )
 */
class VIN extends CSV {

  use ImageMigrationTrait;

  public function prepareRow(Row $row) {
    $this->setImageProperty($row, 'Image');
  }

}
