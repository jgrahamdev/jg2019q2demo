<?php

/**
 * @file
 * Contains dfs_obio_showroom.profile.
 */

// Ensure that the base profile is available.
module_load_include('profile', 'dfs_obio');

/**
 * Implements hook_module_implements_alter().
 */
function dfs_obio_showroom_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_blocks',
      'df_tools_slideshow',
      'df_tools_migration',
      'df_tools_user',
      'dfs_obio',
      'dfs_obio_showroom',
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
function dfs_obio_showroom_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_obio_showroom_migrations();
  foreach ($import as $id) {
    $source = &$definitions[$id]['source'];
    $prefix = 'import_dfs_obio_showroom';
    if (substr($id, 0, strlen($prefix)) === $prefix) {
      $source['path'] = str_replace('..', dirname(__FILE__), $source['path']);
      if (isset($source['constants']) && isset($source['constants']['source_base_path'])) {
        $source['constants']['source_base_path'] = str_replace('..', dirname(__FILE__), $source['constants']['source_base_path']);
      }
    }
  }
}

/**
 * Helper function to return a list of migrations.
 *
 * @return array
 *   An array of migrations for dfs_obio_showroom.
 */
function _dfs_obio_showroom_migrations() {
  return [
    'import_file_demo_user_pictures',
    'import_user_demo_users',
    'import_dfs_obio_media_tags',
    'import_dfs_obio_image',
    'import_dfs_obio_tags',
    'import_dfs_obio_gallery_media',
    'import_dfs_obio_showroom_media_tags',
    'import_dfs_obio_showroom_image',
    'import_dfs_obio_showroom_tags',
    'import_dfs_obio_showroom_location',
    'import_dfs_obio_showroom_article',
    'import_dfs_obio_showroom_basic',
    'import_dfs_obio_showroom_hero',
    'import_dfs_obio_showroom_landing_page',
  ];
}

/**
 * Implements hook_menu_links_discovered_alter().
 */
function dfs_obio_showroom_menu_links_discovered_alter(&$links) {
  unset($links['dfs_obio.about']);
  unset($links['dfs_obio.shop_office']);
  unset($links['dfs_obio.inspiration']); 
  unset($links['dfs_obio.locations']);
  unset($links['dfs_obio.cart']);
}

/**
 * Implements hook_preprocess_HOOK().
 */
function dfs_obio_showroom_preprocess_html(&$variables) {
    $variables['attributes']['class'][] = 'dfs-obio-showroom';
}
