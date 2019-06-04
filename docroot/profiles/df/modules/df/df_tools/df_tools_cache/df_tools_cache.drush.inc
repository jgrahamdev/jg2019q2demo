<?php

/**
 * @file
 * Provides drush utility commands for managing persistent cache objects.
 * Fork of https://github.com/lcatlett/cache_tools
 */

/**
 * Implements hook_drush_command().
 */
function df_tools_cache_drush_command() {
  return [
    'df-tools-cache-purge' => [
      'description' => dt('Purges data from persistent cache tables.'),
      'aliases' => ['cache-nuke'],
    ],
 
  ];
}

/**
 * Action callback for cache-tools-purge command.
 */
function drush_df_tools_cache_purge() {

  drush_print(dt('Preparing to truncate persistent cache tables... '));

  // Clear all caches to purge expired objects first.
  drupal_flush_all_caches();
  drush_log(dt('Cleared caches.'), 'ok');

  // Trigger a rebuild of router paths to discover changes.
  \Drupal::service("router.builder")->rebuild();

  // Persistent cache bin stores to truncate.
  $bins = [
    'bootstrap',
    'config',
    'data',
    'default',
    'discovery',
    'dynamic_page_cache',
    'entity',
    'menu',
    'migrate',
    'render',
    'rest',
    'toolbar',
  ];
  foreach ($bins as $bin) {
    if (\Drupal::hasService("cache.$bin")) {
      \Drupal::cache($bin)->deleteAll();
    }
  }
}