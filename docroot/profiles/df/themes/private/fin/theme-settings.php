<?php

/**
 * Implements hook_form_FORM_ID_alter().
 *
 * @param $form
 *   The form.
 * @param $form_state
 *   The form state.
 */
function fin_form_system_theme_settings_alter(&$form, &$form_state) {

  $form['page_elements']['fin_page_site_logo_reversed'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show reversed logo in footer'),
    '#description' => t('Determines if the hard-coded site reversed color logo should be displayed.'),
    '#default_value' => theme_get_setting('fin_page_site_logo_reversed'),
  );

  $form['page_elements']['fin_page_desktop_mobile_menu_icon'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show mobile menu icon on desktop'),
    '#description' => t('Determines if the mobile menu (hamburger) icon should be shown on desktop.'),
    '#default_value' => theme_get_setting('fin_page_desktop_mobile_menu_icon'),
  );
  
  $form['page_elements']['fin_page_last_menu_as_cta'] = array(
    '#type' => 'checkbox',
    '#title' => t('Show last menu item as CTA button'),
    '#description' => t('Change the last menu item to look like a CTA button'),
    '#default_value' => theme_get_setting('fin_page_last_menu_as_cta'),
  );

}
