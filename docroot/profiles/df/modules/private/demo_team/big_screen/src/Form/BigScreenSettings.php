<?php

namespace Drupal\big_screen\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Extension\ThemeHandler;
use Drupal\Core\Extension\InfoParser;
use Drupal\Core\Config\ConfigFactory;
Use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class BigScreenSettings.
 *
 * @package Drupal\big_screen\Form
 */
class BigScreenSettings extends FormBase {

  /**
   * @var ThemeHandler $theme_handler
   */
  protected $themeHandler;

  /**
   * @var InfoParser $info_parser
   */
  protected $infoParser;

  public function __construct(ThemeHandler $theme_handler, InfoParser $info_parser) {
    $this->themeHandler = $theme_handler;
    $this->infoParser = $info_parser;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('theme_handler'),
      $container->get('info_parser')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'big_screen_settings';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $options = [];
    $config = $this->config('big_screen.settings');
    $theme_list = $this->themeHandler->listInfo();
    foreach ($theme_list as $theme) {
      $theme_info = $this->infoParser->parse($theme->getPathname());
      $options[$theme->getName()] = $this->t($theme_info['name']);
    }
    $form['big_screen_theme'] = [
      '#type' => 'select',
      '#title' => $this->t('Big Screen Theme'),
      '#description' => $this->t('Select the theme to use for Big Screen mode.'),
      '#options' => $options,
      '#default_value' => $config->get('theme'),
      '#size' => 5
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => t('Submit')
    ];
    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $theme = $form_state->getValue('big_screen_theme');
    $config = $this->configFactory()->getEditable('big_screen.settings');
    if ($config->set('theme', $theme)->save()) {
      drupal_set_message('Big Screen settings saved.');
    }
  }

}
