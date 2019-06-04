<?php

/**
 * @file
 * Lists available colors and color schemes for the FIN theme.
 */

$info = array(
  // Available colors and color labels used in theme.
  'fields' => array(
    'bg' => t('Main Background'),
    'primary' => t('Primary Color'),
    'link' => t('Primary Hover Color'),
    'text' => t('Dark Background Text Color'),
    'metanav' => t('Meta Nav background'),
    'headline' => t('Headers H1-H6 - Primary Nav Link'),
    'bodytext' => t('Default Body Text'),
    'footer' => t('Footer Background'),
    'dropdownbg' => t('Dropdown Background'),
  ),
  // Pre-defined color schemes.
  'schemes' => array(
    'default' => array(
      'title' => t('FIN'),
      'colors' => array(
        'bg' => '#fbfcfc',
        'primary' => '#2199e8',
        'link' => '#0c4d78',
        'text' => '#ffffff',
        'metanav' => '#f3f3f3',
        'headline' => '#353535',
        'bodytext' => '#0a0a0a',
        'footer' => '#181a24',
        'dropdownbg' => '#2c304c',
      ),
    ),
  ),

  // CSS files (excluding @import) to rewrite with new color scheme.
  'css' => array(
    'css/colors.css',
  ),

  // Files to copy.
  'copy' => array(
    'logo.svg',
  ),

  //Preview files.
  'preview_library' => 'fin/color.preview',
  'preview_html' => 'color/preview.html',

  // Attachments.
  '#attached' => [
    'drupalSettings' => [
      'color' => [
        // Put the logo path into JavaScript for the live preview.
        'logo' => theme_get_setting('logo.url', 'fin'),
      ],
    ],
  ],
);
