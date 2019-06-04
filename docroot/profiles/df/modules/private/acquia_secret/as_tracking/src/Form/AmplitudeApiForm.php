<?php

namespace Drupal\as_tracking\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure Amplitude settings for the site.
 */
class AmplitudeApiForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\as_tracking\Form\AmplitudeApiForm object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    parent::__construct($config_factory);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'amplitude_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('as_tracking.settings');

    $form['api_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('API Key'),
      '#default_value' => $config->get('api_key'),
      '#required' => TRUE,
      '#disabled' => TRUE,
    ];

    $form['user_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('User ID'),
      '#default_value' => $config->get('user_id'),
      '#required' => TRUE,
      '#disabled' => FALSE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('as_tracking.settings');
    $config->set('api_key', $form_state->getValue('api_key'));
    $config->set('user_id', $form_state->getValue('user_id'));
    if ($config->save()) {
      // We need to clear caches to ensure the redirect doesn't fire.
      drupal_flush_all_caches();
      // Rebuild the router too for good measure.
      \Drupal::service("router.builder")->rebuild();
      // Redirect from config form to the front page.
      $form_state->setRedirect('<front>');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['as_tracking.settings'];
  }

}
