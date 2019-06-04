<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\InsuranceProductNode.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\AuthorMigrationTrait;
use Drupal\df_tools_migration\Plugin\migrate\source\ImageMigrationTrait;

/**
 * Source for the Insurance Product node CSV.
 *
 * @MigrateSource(
 *   id = "insurance_product_node"
 * )
 */
class InsuranceProductNode extends CSV {

  use AuthorMigrationTrait;
  use ImageMigrationTrait;

  public function prepareRow(Row $row) {

    // Set a random author.
    $this->setUidProperty($row, null, 'creator');

    foreach (['Image'] as $key) {
      $this->setImageProperty($row, $key);
    }

    if ($column = $row->getSourceProperty('Associated testimonials')) {
      $titles = explode(',', $column);
      $references = [];
      foreach ($titles as $title) {
        $query = \Drupal::entityQuery('node')
          ->condition('title', $title)
          ->execute();
        if ($query && count($query) > 0) {
          $references[] = [
            'target_id' => reset($query)
          ];
        }
      }
      $row->setSourceProperty('Associated testimonials', $references);
    }

    foreach (['Hero reference', 'Hero promo reference'] as $key) {
      if ($column = $row->getSourceProperty($key)) {
        $uuids = explode(',', $column);
        $references = [];
        foreach ($uuids as $uuid) {
          if ($entity = \Drupal::entityManager()->loadEntityByUuid('block_content', $uuid)) {
            $references[] = [
              'target_id' => $entity->id()
            ];
          }
        }
        $row->setSourceProperty($key, $references);
      }
    }
  }

}
