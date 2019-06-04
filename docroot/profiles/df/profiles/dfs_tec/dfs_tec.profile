<?php

/**
 * @file
 * Contains dfs_tec.profile.
 */

/**
 * Implements hook_module_implements_alter().
 */
function dfs_tec_module_implements_alter(&$implementations, $hook) {
  // Ensure migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_user',
      'df_tools_blocks',
      'df_tools_slideshow',
      'dfs_tec',
    ];
    foreach ($preferred_order as $module) {
      if (isset($implementations[$module])) {
        unset($implementations[$module]);
        $implementations[$module] = $module;
      }
    }
  }
}

/**
 * Implements hook_migration_plugins_alter().
 */
function dfs_tec_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_tec_migrations();
  foreach ($import as $id) {
    $definitions[$id]['source']['path'] = str_replace('..', dirname(__FILE__), $definitions[$id]['source']['path']);
  }
}

/**
 * Helper function to return a list of migrations.
 *
 * @return array
 *   An array of migrations for dfs_tec.
 */
function _dfs_tec_migrations() {
  return [
    'import_file_demo_user_pictures',
    'import_user_demo_users',
    'import_dfs_tec_tags',
    'import_dfs_tec_article',
    'import_dfs_tec_basic',
    'import_dfs_tec_employee',
    'import_dfs_tec_image',
    'import_dfs_tec_page',
    'import_dfs_tec_service',
    'import_dfs_tec_hero',
    'import_dfs_tec_slideshow',
  ];
}
