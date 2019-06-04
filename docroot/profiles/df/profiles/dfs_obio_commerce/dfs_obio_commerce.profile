<?php

/**
 * @file
 * Contains dfs_obio_commerce.profile.
 */

use Drupal\Core\Layout\LayoutDefinition;
use Drupal\Core\Render\Element;
use Drupal\Core\Template\Attribute;

// Ensure that the base profile is available.
module_load_include('profile', 'dfs_obio');

/**
 * Implements hook_module_implements_alter().
 */
function dfs_obio_commerce_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_user',
      'df_tools_blocks',
      'df_tools_slideshow',
      'df_tools_migration',
      'dfs_obio',
      'dfs_obio_commerce',
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
function dfs_obio_commerce_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_obio_commerce_migrations();
  foreach ($import as $id) {
    $source = &$definitions[$id]['source'];
    $prefix = 'import_dfs_obio_commerce';
    if (substr($id, 0, strlen($prefix)) === $prefix) {
      $source['path'] = str_replace('..', dirname(__FILE__), $source['path']);
      if (isset($source['constants']) && isset($source['constants']['source_base_path'])) {
        $source['constants']['source_base_path'] = str_replace('..', dirname(__FILE__), $source['constants']['source_base_path']);
      }
    }
    $migrations_using_parent_csv = ['import_dfs_obio_commerce_review', 'import_dfs_obio_commerce_collections'];
    if (in_array($id, $migrations_using_parent_csv)) {
      $source['path'] = str_replace('profiles/dfs_obio_commerce/', 'profiles/dfs_obio/', $source['path']);
      if (isset($source['constants']) && isset($source['constants']['source_base_path'])) {
        $source['constants']['source_base_path'] = str_replace('profiles/dfs_obio_commerce/', 'profiles/dfs_obio/', $source['constants']['source_base_path']);
      }
    }
  }
}

/**
 * Helper function to return a list of migrations.
 *
 * @return array
 *   An array of migrations for dfs_obio_commerce.
 */
function _dfs_obio_commerce_migrations() {
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
    'import_dfs_obio_product_types',
    'import_dfs_obio_hero',
    'import_dfs_obio_basic',
    'import_dfs_obio_media_block',
    'import_dfs_obio_landing_page',
    'import_dfs_obio_article',
    'import_dfs_obio_location',
    'import_dfs_obio_gallery_media',
    //'import_dfs_obio_commerce_landing_page',
    'import_dfs_obio_commerce_block_content_basic',
    'import_dfs_obio_commerce_image',
    'import_dfs_obio_commerce_ambiance_package',
    'import_dfs_obio_commerce_office_size',
    'import_dfs_obio_commerce_store',
    'import_dfs_obio_commerce_office',
    'import_dfs_obio_commerce_collection',
    'import_dfs_obio_commerce_collections',
    'import_dfs_obio_commerce_review',
    'import_dfs_obio_commerce_color',
    'import_dfs_obio_commerce_variation_individual',
    'import_dfs_obio_commerce_product_individual',
    'import_dfs_obio_commerce_review_product',
    'import_dfs_obio_menu',
    'import_dfs_obio_commerce_menu',
  ];
}

/**
 * Implements hook_preprocess().
 */
function dfs_obio_commerce_preprocess(&$variables, $hook) {
  $variables['img_path'] = file_create_url(drupal_get_path('profile', 'dfs_obio_commerce') . '/images/wendy.jpg');
}

/**
 * Implements hook_preprocess_HOOK() for layout.
 */
function dfs_obio_commerce_preprocess_layout(&$variables) {
  $layout = $variables['content']['#layout'];
  $layout_additional = $layout instanceof LayoutDefinition ? $variables['content']['#layout']->get('additional') : NULL;

  if ($layout_additional && !empty($layout_additional['dfs_obio_base'])) {
    // Creating attributes form the configuration.
    $dfs_defaults = $variables['content']['#settings']['dfs_defaults'];
    $dfs_regions = $variables['content']['#settings']['dfs_regions'];

    // Clean up raw settings.
    unset($variables['settings']['dfs_defaults']);
    unset($variables['settings']['dfs_regions']);

    foreach ($dfs_defaults as $default_key => $value) {
      if (!empty($value)) {
        $attr_name = str_replace('classes_', 'attributes_default_', $default_key);
        $variables[$attr_name] = new Attribute(['class' => explode(' ', $value)]);
      }
    }

    foreach ($dfs_regions as $region_key => $value) {
      if (!empty($value)) {
        $attr_name = str_replace('classes_', 'attributes_region_', $region_key);
        $variables[$attr_name] = new Attribute(['class' => explode(' ', $value)]);
      }
    }

    // Both Panels StandardDisplayBuilder and Page Manager's
    // PageBlockDisplayVariant adds a div wrapper around regions with the class
    // "block-region-[regionName]".
    // We dont want to have them in our custom layouts.
    if (!empty($variables['content']) && is_array($variables['content'])) {
      foreach (Element::children($variables['content']) as $region_name) {
        unset($variables['content'][$region_name]['#prefix']);
        unset($variables['content'][$region_name]['#suffix']);
      }
    }
  }
}
