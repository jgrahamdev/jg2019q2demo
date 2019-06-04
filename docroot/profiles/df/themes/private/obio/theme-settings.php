<?php

/**
 * @file
 * Add custom theme settings to Obio.
 */

/**
 * Implements hook_form_FORM_ID_alter().
 */
function obio_form_system_theme_settings_alter(&$form, \Drupal\Core\Form\FormStateInterface $form_state) {
  $form['page_elements']['obio_page_site_logo_reversed'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show reversed logo & tagline in footer'),
    '#description' => t('Determines if the hard-coded site reversed color logo & tagline should be displayed.'),
    '#default_value' => theme_get_setting('obio_page_site_logo_reversed'),
  );
}
