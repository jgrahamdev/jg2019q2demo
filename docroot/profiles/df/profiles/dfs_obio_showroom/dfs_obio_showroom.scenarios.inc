<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Obio Showroom profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_obio_showroom_scenarios_info() {
  return [
    'dfs_obio_showroom' => [
      'label' => t('DFS OBIO SHOWROOM'),
      'description' => t('Demo Framework Scenario that extends OBIO, providing a local showroom.'),
      'migrations' => _dfs_obio_showroom_migrations(),
    ],
  ];
}
