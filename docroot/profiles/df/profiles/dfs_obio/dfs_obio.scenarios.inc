<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Obio profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_obio_scenarios_info() {
  return [
    'dfs_obio' => [
      'label' => t('DFS OBIO'),
      'description' => t('Demo Framework Scenario for online retailers.'),
      'migrations' => _dfs_obio_migrations(),
    ],
  ];
}

/**
 * Implements hook_scenarios_migration_finished().
 */
function dfs_obio_scenarios_migration_finished(\Drupal\migrate\Plugin\MigrationInterface $migration) {
  if ($migration->id() === 'import_dfs_obio_article') {
    // Create new revisions of certain content to fill out the dashboard.
    $revisions = [
      'c8317f4e-0a58-4922-8095-4a848bfd2f3e' => [
        'moderation_state' => 'draft',
        'title' => 'Meet Shawn and See How The Eco-Friendly Collection Worked for His Business',
      ],
      '51d9f3f2-cbc7-4713-9fbb-f2e062165115' => [
        'moderation_state' => 'draft',
        'field_image' => reset(\Drupal::entityQuery('file')->condition('filename', 'design-consultation.jpg')->execute()),
      ],
      '3f2d7116-00e9-44da-abb0-c27bbdb752c6' => [
        'moderation_state' => 'review',
        'field_image' => reset(\Drupal::entityQuery('file')->condition('filename', 'collection-iot-3.jpg')->execute()),
      ],
      '6e67a931-4c13-4c0a-bcea-d5cb1cda64b2' => [
        'moderation_state' => 'review',
        'title' => 'Meet a Young Designer Who\'s Changing the Industry',
      ],
    ];

    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');

    foreach ($revisions as $uuid => $fields) {
      /** @var \Drupal\node\NodeInterface $node */
      if ($node = $entity_repository->loadEntityByUuid('node', $uuid)) {
        // Content cannot directly transition from 'published' to 'review'. It
        // must first go through the draft state.
        if ($fields['moderation_state'] == 'review') {
          $node->set('moderation_state', 'draft');
          $node->setNewRevision(TRUE);
          $node->setRevisionLogMessage('');
          $node->save();
        }

        foreach ($fields as $field_id => $value) {
          $node->set($field_id, $value);
        }

        $node->setNewRevision(TRUE);
        $node->setRevisionLogMessage('');
        $node->save();
      }
    }
  }
}
