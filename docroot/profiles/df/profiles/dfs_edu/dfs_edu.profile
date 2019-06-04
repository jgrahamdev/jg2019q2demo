<?php

/**
 * @file
 * Contains dfs_edu.profile.
 */

// Ensure that the base profile is available.
module_load_include('profile', 'dfs_obio');

/**
 * Implements hook_module_implements_alter().
 */
function dfs_edu_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_blocks',
      'df_tools_slideshow',
      'df_tools_migration',
      'df_tools_user',
      'dfs_obio',
      'dfs_edu',
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
function dfs_edu_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_edu_migrations();
  foreach ($import as $id) {
    $source = &$definitions[$id]['source'];
    $prefix = 'import_dfs_edu';
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
 *   An array of migrations for dfs_edu.
 */
function _dfs_edu_migrations() {
  return [
    'import_dfs_edu_file',
    'import_file_demo_user_pictures',
    'import_user_demo_users',
    'import_dfs_edu_user',
    'import_dfs_edu_image',
    'import_dfs_edu_tags',
    'import_dfs_edu_hero',
    'import_dfs_edu_landing_page',
    'import_dfs_edu_article',
    'import_dfs_edu_collection',
    'import_dfs_edu_review',
    'import_dfs_edu_menu',
  ];
}

/**
 * Implements hook_menu_links_discovered_alter().
 */
function dfs_edu_menu_links_discovered_alter(&$links) {
  $links['dfs_obio.about']['weight'] = '11';
  $links['dfs_obio.about']['title'] = t('Give Today');
  $links['dfs_obio.shop_office']['title'] = t('Academics');
  $links['dfs_obio.inspiration']['title'] = t('Blog');
  $links['dfs_obio.locations']['title'] = t('Research');
  unset($links['dfs_obio.cart']);
  unset($links['dfs_obio.shop_office_footer']);
  $links['dfs_obio.about_footer']['title'] = t('Give Today');
  $links['dfs_obio.inspiration_footer']['title'] = t('Blog');
  $links['dfs_obio.locations_footer']['title'] = t('Research');
}
