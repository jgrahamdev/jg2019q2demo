<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Obio Commerce profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_obio_commerce_scenarios_info() {
  return [
    'dfs_obio_commerce' => [
      'label' => t('DFS OBIO COMMERCE'),
      'description' => t('Demo Framework Scenario that extends OBIO, providing Drupal commerce integration.'),
      'migrations' => _dfs_obio_commerce_migrations(),
    ],
  ];
}
