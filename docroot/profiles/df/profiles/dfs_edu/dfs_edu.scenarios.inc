<?php

/**
 * @file
 * Scenarios hooks implemented by the DFS Edu profile.
 */

/**
 * Implements hook_scenarios_info().
 */
function dfs_edu_scenarios_info() {
  return [
    'dfs_edu' => [
      'label' => t('DFS EDU'),
      'description' => t('Demo Framework Scenario for higher education.'),
      'migrations' => _dfs_edu_migrations(),
    ],
  ];
}
