<?php

namespace Drupal\as_journey\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure AS Journey settings for the site.
 */
class ASJourneySettingsForm extends ConfigFormBase {

  /**
   * Constructs a \Drupal\as_journey\Form\ASJourneySettingsForm object.
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
    return 'as_journey_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $config = $this->config('as_journey.settings');

    $form['obio_subscribe_pixel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Subscribe pixel URL'),
      '#default_value' => $config->get('obio_subscribe_pixel'),
      '#required' => TRUE,
    ];

    $form['obio_purchase_pixel'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Purchase pixel URL'),
      '#default_value' => $config->get('obio_purchase_pixel'),
      '#required' => TRUE,
    ];

    $form['obio_facebook_profile'] = [
      '#type' => 'select',
      '#title' => $this->t('Facebook Profile (found at /demo/facebook)'),
      '#options' => [
        'amy' => $this->t('Amy (Healthcare)'),
        'mike' => $this->t('Mike (Default)'),
      ],
      '#default_value' => $config->get('obio_facebook_profile'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    $config = $this->config('as_journey.settings');
    $config->set('obio_subscribe_pixel', $form_state->getValue('obio_subscribe_pixel'));
    $config->set('obio_purchase_pixel', $form_state->getValue('obio_purchase_pixel'));
    $config->set('obio_facebook_profile', $form_state->getValue('obio_facebook_profile'));
    $config->save();
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['as_journey.settings'];
  }

}
