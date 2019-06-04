<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\AgentUser.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\ImageMigrationTrait;


/**
 * Source for multiple user roles via CSV.
 *
 * @MigrateSource(
 *   id = "agent_user"
 * )
 */
class AgentUser extends CSV {

  use ImageMigrationTrait;

  public function prepareRow(Row $row) {
    if ($value = $row->getSourceProperty('Title')) {
      $strings = explode(' ', $value);
      $first_name = $strings[0];
      $last_name = str_replace(',', '', $strings[1]);
      $password = 'password';
      $row->setSourceProperty('Name', $first_name . $last_name);
      $row->setSourceProperty('First', $first_name);
      $row->setSourceProperty('Last', $last_name);
      $row->setSourceProperty('Pass', \Drupal::service('password')->hash($password));
      $row->setSourceProperty('Status', 1);
    }
    // Hard code the role.
    $row->setSourceProperty('Roles', array('agent'));
    // Unless its Bud, the special Agent creator.
    if ($first_name == 'Bud') {
      $row->setSourceProperty('Roles', array('agent', 'creator'));
    }
    // Use existing email column.
    if ($value = $row->getSourceProperty('Email')) {
      $row->setSourceProperty('Mail', $value);
    }  
    // Use existing Image for picture.
    if ($value = $row->getSourceProperty('Image')) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->loadByProperties(array('filename' => $value));
      $row->setSourceProperty('Picture', $file);
    }
  }
}
