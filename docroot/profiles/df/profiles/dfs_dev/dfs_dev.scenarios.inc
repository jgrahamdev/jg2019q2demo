<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Dev profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_dev_scenarios_info() {
  return [
    'dfs_dev' => [
      'label' => t('DFS DEV'),
      'description' => t('Demo Framework Scenario for Development Testing.'),
      'migrations' => _dfs_dev_migrations(),
    ],
  ];
}
