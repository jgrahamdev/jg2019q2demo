<?php

/**
 * @file
 * Contains dfs_obio.profile.
 */

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Url;

/**
 * Implements hook_module_implements_alter().
 */
function dfs_obio_module_implements_alter(&$implementations, $hook) {
  // Ensure dynamic migrations get set in the right order.
  if ($hook == 'migration_plugins_alter') {
    $preferred_order = [
      'import',
      'df_tools_user',
      'df_tools_blocks',
      'df_tools_slideshow',
      'df_tools_migration',
      'dfs_obio',
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
function dfs_obio_migration_plugins_alter(&$definitions) {
  // Update source references in migrations.
  $import = _dfs_obio_migrations();
  foreach ($import as $id) {
    $source = &$definitions[$id]['source'];
    $prefix = 'import_dfs_obio';
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
 *   An array of migrations for dfs_obio.
 */
function _dfs_obio_migrations() {
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
    'import_dfs_obio_collections',
    'import_dfs_obio_review',
    'import_dfs_obio_menu',
    'import_dfs_obio_product_types',
  ];
}

/**
 * Implements hook_mail().
 */
function dfs_obio_mail($key, &$message, $params) {
  // Check if this is an email type we can handle
  if ($key == 'sign-up') {
    // Use HTML formatting for this email so we can use utm_* parameters
    $message['headers']['Content-Type'] = 'text/html; charset=UTF-8; format=flowed';

    $site_name = \Drupal::configFactory()->get('system.site')->get('name');

    // Format the subject.
    $message['subject'] = t('@site newsletter confirmation', ['@site' => $site_name]);
    $message['from'] = '"' . $site_name . '" <' . $message['from'] . '>';

    $query_options = [
      'utm_source' => 'subscription',
      'utm_medium' => 'email',
    ];

    if (isset($_COOKIE['tc_ptid'])) {
      $query_options['identity'] = $message['to'];
      $query_options['identityType'] = 'email';
    }

    $parameters = new MenuTreeParameters();
    $parameters->setTopLevelOnly();
    $header_links = [];
    $menu = \Drupal::menuTree()->load('main', $parameters);
    foreach ($menu as $id => $tree) {
      if ($id !== 'menu_link_content:34f752f4-a59a-4f13-b953-73369cfab82a') {
        $url = $tree->link->getUrlObject();
        $url->setOption('query', $query_options);
        $header_links[] = [
          'url' => $url->setAbsolute(TRUE)->toString(),
          'title' => $tree->link->getTitle(),
        ];
      }
    }

    $url = new Url('<front>');
    $url->setOption('query', $query_options);

    $location_url = Url::fromUri('internal:/locations');
    $location_url->setOption('query', $query_options);

    $logo = DRUPAL_ROOT . '/' . theme_get_setting('logo.url');
    $mime = \Drupal::service('file.mime_type.guesser')->guess($logo);
    $allowed_mime = ['image/svg+xml', 'image/png', 'image/jpeg', 'image/gif'];
    if (in_array($mime, $allowed_mime, TRUE)) {
      $data = base64_encode(file_get_contents($logo));
      $logo_url = 'data: ' . $mime . ';base64,' . $data;
    }
    else {
      $logo_url = FALSE;
    }

    // @todo Figure out how to inline images using Content IDs with Drupal.
    $logo_url = FALSE;

    $palette = color_get_palette('obio');

    $template = file_get_contents(drupal_get_path('theme', 'obio') . '/inky_templates/compiled/newsletter.html.twig');
    $template = str_replace(["'{{","}}'"], ['{{','}}'], $template);

    // Render the body.
    $build = [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'site' => $site_name,
        'palette' => $palette,
        'logo_url' => $logo_url,
        'url' => $url->setAbsolute(TRUE)->toString(),
        'location_url' => $location_url->setAbsolute(TRUE)->toString(),
        'header_links' => $header_links,
      ],
    ];
    $message['body'][] = render($build);
  }
}
