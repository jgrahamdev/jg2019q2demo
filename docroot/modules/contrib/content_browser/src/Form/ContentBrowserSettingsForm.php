<?php

namespace Drupal\content_browser\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Content Browser settings for the site.
 */
class ContentBrowserSettingsForm extends ConfigFormBase {

  /**
   * @var \Drupal\Core\Routing\RouteBuilder
   */
  private $routeBuilder;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Routing\RouteBuilderInterface $route_builder
   *   The route builder service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, RouteBuilderInterface $route_builder) {
    parent::__construct($config_factory);
    $this->routeBuilder = $route_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('router.builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'content_browser_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('content_browser.settings');

    $form['use_frontend_theme'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use frontend theme when rendering Content Browser'),
      '#default_value' => $config->get('use_frontend_theme'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('content_browser.settings');
    $config->set('use_frontend_theme', $form_state->getValue('use_frontend_theme'));
    $config->save();
    $this->routeBuilder->rebuild();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['content_browser.settings'];
  }

}
