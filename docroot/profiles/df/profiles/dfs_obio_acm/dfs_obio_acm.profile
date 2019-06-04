<?php

/**
 * @file
 * Contains dfs_obio_acm.profile.
 */

// Ensure that the base profile is available.
module_load_include('profile', 'dfs_obio');

/**
 * Implements hook_module_implements_alter().
 */
function dfs_obio_acm_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_blocks',
      'df_tools_slideshow',
      'df_tools_migration',
      'df_tools_user',
      'dfs_obio',
      'dfs_obio_acm',
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
function dfs_obio_acm_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_obio_acm_migrations();
  foreach ($import as $id) {
    $source = &$definitions[$id]['source'];
    $prefix = 'import_dfs_obio_acm';
    if (substr($id, 0, strlen($prefix)) === $prefix) {
      $source['path'] = str_replace('..', dirname(__FILE__), $source['path']);
      if (isset($source['constants']) && isset($source['constants']['source_base_path'])) {
        $source['constants']['source_base_path'] = str_replace('..', dirname(__FILE__), $source['constants']['source_base_path']);
      }
    }
    $migrations_using_parent_csv = ['import_dfs_obio_acm_collections'];
    if (in_array($id, $migrations_using_parent_csv)) {
      $source['path'] = str_replace('profiles/dfs_obio_acm/', 'profiles/dfs_obio/', $source['path']);
      if (isset($source['constants']) && isset($source['constants']['source_base_path'])) {
        $source['constants']['source_base_path'] = str_replace('profiles/dfs_obio_acm/', 'profiles/dfs_obio/', $source['constants']['source_base_path']);
      }
    }
  }
}

/**
 * Helper function to return a list of migrations.
 *
 * @return array
 *   An array of migrations for dfs_obio_acm.
 */
function _dfs_obio_acm_migrations() {
  return [
    'import_dfs_obio_file',
    'import_file_demo_user_pictures',
    'import_user_demo_users',
    'import_dfs_obio_user',
    'import_dfs_obio_media_tags',
    'import_dfs_obio_image',
    'import_dfs_obio_video',
    'import_dfs_obio_instagram',
    'import_dfs_obio_twitter',
    'import_dfs_obio_local_video',
    'import_dfs_obio_tags',
    'import_dfs_obio_hero',
    'import_dfs_obio_basic',
    'import_dfs_obio_media_block',
    'import_dfs_obio_landing_page',
    'import_dfs_obio_article',
    'import_dfs_obio_location',
    'import_dfs_obio_gallery_media',
    'import_dfs_obio_acm_collections',
    'import_dfs_obio_review',
    'import_dfs_obio_menu',
  ];
}
