<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Tec profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_tec_scenarios_info() {
  return [
    'dfs_tec' => [
      'label' => t('DFS TEC'),
      'description' => t('Demo Framework Scenario for Technology Websites.'),
      'migrations' => _dfs_tec_migrations(),
    ],
  ];
}
