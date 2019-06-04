<?php

namespace Drupal\df_tools_migration\Plugin\migrate\process;

use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;

/**
 * This plugin replaces node UUIDs in links to relative IDs.
 *
 * @MigrateProcessPlugin(
 *   id = "uuid_link"
 * )
 */
class UUIDLink extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $split = explode(':node/', $value);
    if (isset($split[1])) {
      $nodes = \Drupal::entityTypeManager()
        ->getStorage('node')
        ->loadByProperties(['uuid' => $split[1]]);
      if (!empty($nodes)) {
        reset($nodes);
        $nid = key($nodes);
        $value = str_replace($split[1], $nid, $value);
      }
    }
    return $value;
  }

}
