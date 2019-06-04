<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Obio Acm profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_obio_acm_scenarios_info() {
  return [
    'dfs_obio_acm' => [
      'label' => t('DFS OBIO ACM'),
      'description' => t('Demo Framework Scenario extending OBIO, providing Acquia commerce integration.'),
      'migrations' => _dfs_obio_acm_migrations(),
    ],
  ];
}
