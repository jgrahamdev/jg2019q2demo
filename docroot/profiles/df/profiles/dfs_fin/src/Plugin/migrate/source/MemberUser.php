<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\MemberUser.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;

/**
 * Source for multiple user roles via CSV.
 *
 * @MigrateSource(
 *   id = "member_user"
 * )
 */
class MemberUser extends CSV {
  public function prepareRow(Row $row) {
    // Set the default demo user password for all members.
    if ($value = $row->getSourceProperty('Pass')) {
      $password = \Drupal::service('password')->hash($value);
      $row->setSourceProperty('Pass', $password);
    }

    // Set roles adding hard-coded 'Member' role.
    $roles[] = 'member';
    // Use any additional existing roles assigned from the source.
    if ($value = $row->getSourceProperty('Roles')) {
      $import_roles = explode(', ', $value);
      if (!empty($import_roles)) {
        $roles = array_merge($roles, $import_roles);
      }
    }
    $row->setSourceProperty('Roles', $roles);
    // Use the main agent.
    $agents = [
      'Bud Mortenson, CPA',
    ];
    $row->setSourceProperty('Title', array($agents[0]));
  }
}
