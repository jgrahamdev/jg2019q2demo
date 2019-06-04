<?php

/**
 * @file
 * Scenarios hooks implemented by the DF Tools Translation module.
 */

use Drupal\Core\Entity\RevisionLogInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\migrate\MigrateExecutable;
use Drupal\migrate\MigrateMessage;
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\Migration;
use Drupal\migrate_source_csv\CSVFileObject;
use Drupal\migrate_tools\DrushLogMigrateMessage;

/**
 * Implements hook_scenarios_migration_finished().
 *
 * @todo Can we refactor this to use derived migration plugins?
 */
function df_tools_translation_scenarios_migration_finished(Migration $migration) {
  // The Locale module splits its translation functions into separate include
  // files, based on utility.
  // To ensure that each function we require is available, load its respective
  // include file.
  \Drupal::moduleHandler()->loadInclude('locale', 'bulk.inc');

  // Initialize variables required to parse migration.
  /** @var Drupal\migrate_source_csv\Plugin\migrate\source\CSV $source */
  $source = $migration->getSourcePlugin();

  /** @var Drupal\migrate_source_csv\CSVFileObject $iterator */
  $iterator = $source->initializeIterator();
  $filename = $iterator->getPath() . '/' . $iterator->getFilename();

  /** @var \Drupal\migrate\Plugin\migrate\destination\EntityContentBase $destination_plugin */
  $destination_plugin = $migration->getDestinationPlugin();
  $entity_type = explode(':', $destination_plugin->getPluginId())[1];
  $storage = \Drupal::entityTypeManager()->getStorage($entity_type);

  // Determine the ID required to lookup entities in this migration.
  $id_map = [];
  $ids = array_keys($source->getIds());
  $processes = $migration->getProcess();

  // Prefer UUIDs to other fields.
  if (in_array('UUID', $ids, TRUE)) {
    $id_map = ['uuid', 'UUID'];
  }
  else {
    foreach ($processes as $field => $process) {
      if (isset($process[0]['source']) && $process[0]['source'] == $ids[0]) {
        $id_map = [$field, $ids[0]];
        break;
      }
    }
  }

  // Get a list of all currently installed languages.
  $languageManager = \Drupal::languageManager();
  // We only care about langcodes.
  $langcodes = array_keys($languageManager->getLanguages());

  // Create a migrate executable, which we use later to set row values.
  if (function_exists('drush_log')) {
    $log = new DrushLogMigrateMessage();
  }
  else {
    $log = new MigrateMessage();
  }

  $executable = new MigrateExecutable($migration, $log);

  // Process translations for each langcode.
  foreach ($langcodes as $langcode) {
    $new_filename = str_replace('.csv', ".$langcode.csv", $filename);

    if (file_exists($new_filename)) {
      $file = df_tools_translation_initialize_csv($new_filename);
      $count = $file->count();
      $file->rewind();
      $iterator->rewind();

      // Update translations for each Entity.
      for ($i = 0; $i < $count; ++$i) {
        // Get the current row.
        $source_row = $iterator->current();
        $row = new Row($file->current(), $source->getIds());

        // Search for the Entity based on its identifier.
        $field = $id_map[0];

        // We use the source file here as a UUID is not always used, and field
        // values (title) may be translated into different languages.
        $value = $source_row[$id_map[1]];
        $ids = \Drupal::entityQuery($entity_type)
          ->condition($field, $value)
          ->execute();

        // If a migration failed the Entity won't exist, check for that.
        if (empty($ids)) {
          continue;
        }

        // Load the Entity, so we can grab field values and add a translation.
        $id = reset($ids);

        /** @var Drupal\Core\Entity\ContentEntityBase $entity */
        $entity = $storage->load($id);

        if (!$entity->hasTranslation($langcode)) {
          $entity_values = $entity->toArray();
          $translation = $entity->addTranslation($langcode, $entity_values);
        }
        else {
          $translation = $entity->getTranslation($langcode);
        }

        // Properly set the content translation metadata, if applicable.
        if ($translation->hasField('content_translation_source')) {
          /** @var \Drupal\content_translation\ContentTranslationManager $manager */
          $manager = \Drupal::service('content_translation.manager');
          $manager->getTranslationMetadata($translation)
            ->setSource($entity->language()->getId());
        }

        // Collect a list of process plugins that either have no source, or
        // have a source that our translated CSV also has.
        $process_plugins = [];

        foreach ($migration->getProcess() as $field_name => $process) {
          $untranslatable = ['uuid', 'type', 'path'];

          if (!in_array($field_name, $untranslatable)) {
            if (!isset($process[0]['source']) || $row->hasSourceProperty($process[0]['source'])) {
              $process_plugins[$field_name] = $process;
            }
          }
        }
        // Process the row, then use its destination values as field values
        // for our translation.
        $executable->processRow($row, $process_plugins);

        foreach ($row->getDestination() as $field_name => $values) {
          $field = $translation->$field_name;

          if ($field instanceof TypedDataInterface) {
            $field->setValue($values);
          }
        }

        // Save the translation.
        if ($translation instanceof RevisionLogInterface) {
          $translation->setRevisionLogMessage(NULL);
        }

        $translation->save();

        // Move to the next row.
        $file->next();
        $iterator->next();
      }
    }
  }

  locale_translate_delete_translation_files();
}

