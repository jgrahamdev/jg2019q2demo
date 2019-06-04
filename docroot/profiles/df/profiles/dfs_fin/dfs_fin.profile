<?php

/**
 * @file
 * Contains dfs_fin.profile.
 */

/**
 * Implements hook_module_implements_alter().
 */
function dfs_fin_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_user',
      'df_tools_blocks',
      'df_tools_slideshow',
      'dfs_fin',
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
function dfs_fin_migration_plugins_alter(&$definitions) {
  // Copy the demo user migration to create additional demo/member users.
  $migration =_df_migration_copy($definitions['import_user_demo_users'], 'dfs_fin_demo', 'user', 'user', null, FALSE);
  $migration['source']['plugin'] = 'member_user';
  $migration['process']['field_user_agent_location'] = [
    'plugin' => 'migration',
    'migration' => 'import_dfs_fin_agent_location',
    'source' => 'Title',
  ];
  $dependencies = ['import_file_demo_user_pictures', 'import_dfs_fin_agent_location', 'import_dfs_fin_agent_user'];
  $migration['migration_dependencies'] = ['required' => $dependencies];
  $definitions[$migration['id']] = $migration;

  // Update source references in migrations.
  $import = _dfs_fin_migrations();
  foreach ($import as $id) {
    if (substr($id, 0, 14) === 'import_dfs_fin') {
      $definitions[$id]['source']['path'] = str_replace('..', dirname(__FILE__), $definitions[$id]['source']['path']);
    }
  }
}

/**
 * Helper function to return a list of migrations.
 *
 * @return array
 *   An array of migrations for dfs_fin.
 */
function _dfs_fin_migrations() {
  return [
    'import_dfs_fin_tags',
    'import_dfs_fin_basic',
    'import_dfs_fin_image',
    'import_dfs_fin_media_image',
    'import_dfs_fin_gallery_media',
    'import_dfs_fin_hero',
    'import_dfs_fin_slideshow',
    'import_dfs_fin_areas_of_focus',
    'import_dfs_fin_agent_location',
    'import_dfs_fin_agent_user',
    'import_file_demo_user_pictures',
    'import_dfs_fin_demo_user',
    'import_dfs_fin_testimonial',
    'import_dfs_fin_insurance_product',
    'import_dfs_fin_article',
    'import_dfs_fin_comment',
    'import_dfs_fin_question',
    'import_dfs_fin_answer',
    'import_dfs_fin_vin',
    'import_dfs_fin_landing_page',
    'import_dfs_fin_page',
  ];
}

/**
 * Implements hook_mail().
 */
function dfs_fin_mail($key, &$message, $params) {
  // Check if this is an email type we can handle
  if ($key == 'sign-up') {
    // Base the current site url based on SERVER_NAME
    $url = 'http://' . $_SERVER['SERVER_NAME'];

    // Use HTML formatting for this email so we can use utm_* parameters
    $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed';

    $site_name = \Drupal::configFactory()->get('system.site')->get('site_name');

    // Format the subject
    $message['subject'] = t('@site - Newsletter Subscription Confirmation', array('@site' => $site_name));

    // Format the body
    $body = array();
    $body[] = t('Thank you for signing up for the @site monthly newsletter!', array('@site' => $site_name));
    $body[] = t('For more information on our company, please visit ') . "<a href=\"$url?utm_source=newsletter&utm_medium=email\">$url</a>";
    $message['body'][] = implode('<br />', $body);
  }
}
