<?php
/**
 * @file
 * Add custom theme settings to the ZURB Foundation sub-theme.
 */

use Drupal\Core\Form\FormStateInterface;

/**
 * Implements hook_form_FORM_ID_alter().
 * @param $form
 * @param \Drupal\Core\Form\FormStateInterface $form_state
 */
function dfs_base_form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
  $form['theme_ui']['dfs_base_callout_auto_close'] = array(
    '#type' => 'checkbox',
    '#title' => t('Automatically close status messages'),
    '#default_value' => theme_get_setting('dfs_base_callout_auto_close'),
  );
}
