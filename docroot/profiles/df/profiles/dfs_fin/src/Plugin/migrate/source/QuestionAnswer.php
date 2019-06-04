<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\QuestionAnswer.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\AuthorMigrationTrait;

/**
 * Source for node Comment CSV.
 *
 * @MigrateSource(
 *   id = "question_answer"
 * )
 */
class QuestionAnswer extends CSV {

  use AuthorMigrationTrait;

  public function prepareRow(Row $row) {

    // Set authors.
    $this->setUidProperty($row, null, 'creator');

    // Provide the Default comment settings.
    $row->setDestinationProperty('entity_type', 'node');
    $row->setDestinationProperty('field_name', 'field_answer');
  }

}
