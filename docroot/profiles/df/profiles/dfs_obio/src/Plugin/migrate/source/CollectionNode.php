<?php

namespace Drupal\dfs_obio\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\AuthorMigrationTrait;

/**
 * Source for Collection node via CSV with a coded Creator author.
 *
 * @MigrateSource(
 *   id = "collection_node"
 * )
 */
class CollectionNode extends CSV {

  use AuthorMigrationTrait;

  public function prepareRow(Row $row) {
    $this->setUidProperty($row, null, 'creator');
  }

}
