<?php

/**
 * @file
 * Scenarios hooks implemented by the DF Tools Migration module.
 */

/**
 * Implements hook_scenarios_migration_finished().
 *
 * Replaces instances of "sites/default/files" in fields with the current file
 * path.
 */
function df_tools_migration_scenarios_migration_finished(Drupal\migrate\Plugin\Migration $migration) {
  $destination = $migration->getDestinationPlugin();
  $entity_type = explode(':', $destination->getPluginId())[1];
  $entities = \Drupal::entityTypeManager()
    ->getStorage($entity_type)
    ->loadMultiple();

  foreach ($entities as $entity) {
    if ($entity instanceof \Drupal\Core\Entity\ContentEntityInterface) {
      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      foreach ($entity->getFields() as $field_name => $field) {
        if (!$field->isEmpty()) {
          $value = $field->get(0)->getValue();
          if (isset($value['value']) && is_string($value['value'])
            && strpos($value['value'], 'sites/default/files') !== FALSE) {
            $file_path = \Drupal\Core\Site\Settings::get('file_public_path', 'sites/default/files');
            $value['value'] = str_replace('sites/default/files', $file_path, $value['value']);
            $entity->{$field_name}->get(0)->setValue($value);
            $entity->save();
          }
        }
      }
    }
  }
}
