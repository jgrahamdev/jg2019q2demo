<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Fin profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_fin_scenarios_info() {
  return [
    'dfs_fin' => [
      'label' => t('DFS FIN'),
      'description' => t('Demo Framework Scenario for Financial Services.'),
      'migrations' => _dfs_fin_migrations(),
    ],
  ];
}
