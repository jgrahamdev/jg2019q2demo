<?php

/**
 * @file
 * Lists available colors and color schemes for the Material Admin theme.
 */

$info = array(
  // Available colors and color labels used in theme.
  // headerbg = base darken-1
  // breadcrumbg = base lighten-1
  // breadcrumbg = base lighten-4
  'fields' => array(
    'headerbg' => t('Header background'),
    'breadcrumbbg' => t('Breadcrumb background'),
    'breadcrumb' => t('Breadcrumb'),
  ),
  // Pre-defined color schemes.
  'schemes' => array(
    'default' => array(
      'title' => t('Blue (default)'),
      'colors' => array(
        'headerbg' => '#1e88e5',
        'breadcrumbbg' => '#42a5f5',
        'breadcrumb' => '#bbdefb',
      ),
    ),
    'amber' => array(
      'title' => t('Amber'),
      'colors' => array(
        'headerbg' => '#ffb300',
        'breadcrumbbg' => '#ffca28',
        'breadcrumb' => '#ffecb3',
      ),
    ),
    'blue_grey' => array(
      'title' => t('Blue - Blue Grey'),
      'colors' => array(
        'headerbg' => '#546e7a',
        'breadcrumbbg' => '#78909c',
        'breadcrumb' => '#cfd8dc',
      ),
    ),
    'light_blue' => array(
      'title' => t('Blue - Light Blue'),
      'colors' => array(
        'headerbg' => '#039be5',
        'breadcrumbbg' => '#29b6f6',
        'breadcrumb' => '#b3e5fc',
      ),
    ),
    'brown' => array(
      'title' => t('Brown'),
      'colors' => array(
        'headerbg' => '#6d4c41',
        'breadcrumbbg' => '#8d6e63',
        'breadcrumb' => '#efebe9',
      ),
    ),
    'cyan' => array(
      'title' => t('Cyan'),
      'colors' => array(
        'headerbg' => '#00acc1',
        'breadcrumbbg' => '#26c6da',
        'breadcrumb' => '#b2ebf2',
      ),
    ),
    'green' => array(
      'title' => t('Green'),
      'colors' => array(
        'headerbg' => '#43a047',
        'breadcrumbbg' => '#66bb6a',
        'breadcrumb' => '#c8e6c9',
      ),
    ),
    'light_green' => array(
      'title' => t('Green - Light Green'),
      'colors' => array(
        'headerbg' => '#7cb342',
        'breadcrumbbg' => '#9ccc65',
        'breadcrumb' => '#dcedc8',
      ),
    ),
    'grey' => array(
      'title' => t('Grey'),
      'colors' => array(
        'headerbg' => '#757575',
        'breadcrumbbg' => '#bdbdbd',
        'breadcrumb' => '#f5f5f5',
      ),
    ),
    'indigo' => array(
      'title' => t('Indigo'),
      'colors' => array(
        'headerbg' => '#3949ab',
        'breadcrumbbg' => '#5c6bc0',
        'breadcrumb' => '#c5cae9',
      ),
    ),
    'lime' => array(
      'title' => t('Lime'),
      'colors' => array(
        'headerbg' => '#c0ca33',
        'breadcrumbbg' => '#d4e157',
        'breadcrumb' => '#f0f4c3',
      ),
    ),
    'orange' => array(
      'title' => t('Orange'),
      'colors' => array(
        'headerbg' => '#fb8c00',
        'breadcrumbbg' => '#ffa726',
        'breadcrumb' => '#ffe0b2',
      ),
    ),
    'deep_orange' => array(
      'title' => t('Orange - Deep Orange'),
      'colors' => array(
        'headerbg' => '#f4511e',
        'breadcrumbbg' => '#ff7043',
        'breadcrumb' => '#ffccbc',
      ),
    ),
    'pink' => array(
      'title' => t('Pink'),
      'colors' => array(
        'headerbg' => '#d81b60',
        'breadcrumbbg' => '#ec407a',
        'breadcrumb' => '#f8bbd0',
      ),
    ),
    'purple' => array(
      'title' => t('Purple'),
      'colors' => array(
        'headerbg' => '#8e24aa',
        'breadcrumbbg' => '#ab47bc',
        'breadcrumb' => '#e1bee7',
      ),
    ),
    'deep_purple' => array(
      'title' => t('Purple - Deep Purple'),
      'colors' => array(
        'headerbg' => '#5e35b1',
        'breadcrumbbg' => '#7e57c2',
        'breadcrumb' => '#d1c4e9',
      ),
    ),
    'red' => array(
      'title' => t('Red'),
      'colors' => array(
        'headerbg' => '#e53935',
        'breadcrumbbg' => '#ef5350',
        'breadcrumb' => '#ffcdd2',
      ),
    ),
    'teal' => array(
      'title' => t('Teal'),
      'colors' => array(
        'headerbg' => '#00897b',
        'breadcrumbbg' => '#26a69a',
        'breadcrumb' => '#b2dfdb',
      ),
    ),
    'yellow' => array(
      'title' => t('Yellow'),
      'colors' => array(
        'headerbg' => '#fdd835',
        'breadcrumbbg' => '#ffee58',
        'breadcrumb' => '#fff9c4',
      ),
    ),
  ),

  // CSS files (excluding CSS @imports) to rewrite with new color scheme.
  'css' => array(
    'css/material_admin.css',
  ),

  // //Preview files.
  'preview_library' => 'material_admin/color.preview',
  'preview_html' => 'color/preview.html',
);
