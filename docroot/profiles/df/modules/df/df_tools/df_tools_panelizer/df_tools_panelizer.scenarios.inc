<?php

/**
 * @file
 * Scenarios hooks implemented by the DF Tools Panelizer module.
 */

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate_source_csv\CSVFileObject;
use Symfony\Component\Yaml\Yaml;

/**
 * Implements hook_scenarios_migration_finished().
 */
function df_tools_panelizer_scenarios_migration_finished(Migration $migration) {
  // Initialize variables required to parse migration.
  /** @var Drupal\migrate_source_csv\Plugin\migrate\source\CSV $source */
  $source = $migration->getSourcePlugin();

  /** @var Drupal\migrate_source_csv\CSVFileObject $iterator */
  $iterator = $source->initializeIterator();
  $base_path = $iterator->getPath();
  $filename = $base_path . '/' . $iterator->getFilename();

  $destination = $migration->getDestinationPlugin();
  $entity_type = explode(':', $destination->getPluginId())[1];

  // We only support Nodes at this point.
  // @todo Support more types if necessary.
  if ($entity_type != 'node') {
    return;
  }

  $entity_storage = \Drupal::entityManager()->getStorage($entity_type);

  // Determine the ID required to lookup entities in this migration.
  $id_map = [];
  $ids = array_keys($source->getIds());
  $processes = $migration->getProcess();

  foreach ($processes as $field => $process) {
    if (isset($process[0]['source']) && $process[0]['source'] == $ids[0] && strpos($field, '/') === FALSE) {
      $id_map = [$field, $ids[0]];
    }
  }

  // Grab the processed CSV file using existing CSV methods.
  $file = new CSVFileObject($filename);
  $file->setHeaderRowCount(1);

  $count = $file->count();
  $file->rewind();
  $iterator->rewind();

  if (!empty($id_map)) {
    for ($i = 0; $i < $count; ++$i) {
      // Get the current row.
      $source_row = $iterator->current();

      // Search for the Entity based on its identifier.
      $field = $id_map[0];
      $value = $source_row[$id_map[1]];

      $ids = \Drupal::entityQuery($entity_type)
        ->condition($field, $value)
        ->execute();

      if (!empty($ids)) {
        // Load the Entity, so we can grab field values and add the Panelizer display.
        $id = reset($ids);

        /** @var Drupal\Core\Entity\ContentEntityBase $entity */
        $entity = $entity_storage->load($id);

        // See if a Panelizer config file exists for this Node.
        $panels_file = $base_path . '/panelizer.panels_display.' . $entity_type . '.' . $entity->uuid() . '.yml';

        if (file_exists($panels_file) && isset($entity->panelizer)) {
          $yaml = file_get_contents($panels_file);

          $panels_display = Yaml::parse($yaml);

          // The storage ID isn't based on UUIDs, unfortunately. Hotswap it with current info.
          $panels_display['storage_id'] = $entity_type . ':' . $entity->id() . ':full:' . $entity->getRevisionId();

          // Set the Panels Display to match what's defined in YAML.
          $entity->panelizer->panels_display = $panels_display;

          // Mark that this is an overridden display.
          $entity->panelizer->default = NULL;

          // This is a required field in SQL with no default, but it always "full".
          $entity->panelizer->view_mode = 'full';

          if ($entity instanceof RevisionLogInterface) {
            $entity->setRevisionLogMessage(NULL);
          }

          $entity->save();
        }
      }

      // Move to the next row.
      $file->next();
      $iterator->next();
    }
  }
}
