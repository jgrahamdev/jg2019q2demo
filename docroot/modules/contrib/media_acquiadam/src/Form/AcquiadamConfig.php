<?php

namespace Drupal\media_acquiadam\Form;

use cweagans\webdam\Client as WebdamClient;
use cweagans\webdam\Exception\InvalidCredentialsException;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AcquiadamConfig.
 *
 * @package Drupal\media_acquiadam\Form
 */
class AcquiadamConfig extends ConfigFormBase {

  /**
   * The Guzzle client to use for communication with the DAM API.
   *
   * @var \GuzzleHttp\ClientInterface
   *   A guzzle http client.
   */
  protected $httpClient;

  /**
   * A user data object to retrieve API keys from.
   *
   * @var UserDataInterface
   */
  protected $userData;

  /**
   * The current user.
   *
   * @var AccountProxyInterface
   */
  protected $currentUser;

  /**
   * AcquiadamConfig constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ClientInterface $http_client) {
    $this->configFactory = $config_factory;
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'acquiadam_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'media_acquiadam.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('media_acquiadam.settings');

    $form['authentication'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Authentication details'),
    ];

    $form['authentication']['username'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Username'),
      '#default_value' => $config->get('username'),
      '#description' => $this->t('The username of the Acquia DAM account to use for API access.'),
      '#required' => TRUE,
    ];

    $form['authentication']['password'] = [
      '#type' => 'password',
      '#title' => $this->t('Password'),
      '#default_value' => $config->get('password'),
      '#description' => $this->t('The password of the Acquia DAM account to use for API access. Note that this field will appear blank even if you have previously saved a value.'),
    ];

    $form['authentication']['client_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('client_id'),
      '#description' => $this->t('API Client ID to use for API access. Contact the Acquia DAM support team to get one assigned.'),
      '#required' => TRUE,
    ];

    $form['authentication']['secret'] = [
      '#type' => 'password',
      '#title' => $this->t('Client secret'),
      '#default_value' => $config->get('secret'),
      '#description' => $this->t('API Client Secret to use for API access. Contact the Acquia DAM support team to get one assigned. Note that this field will appear blank even if you have previously saved a value.'),
    ];

    $form['cron'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Cron Settings'),
    ];

    $form['cron']['sync_interval'] = [
      '#type' => 'select',
      '#title' => $this->t('Asset refresh interval'),
      '#options' => [
        '-1' => 'Every cron run',
        '3600' => 'Every hour',
        '7200' => 'Every 2 hours',
        '10800' => 'Every 3 hours',
        '14400' => 'Every 4 hours',
        '21600' => 'Every 6 hours',
        '28800' => 'Every 8 hours',
        '43200' => 'Every 12 hours',
        '86400' => 'Every 24 hours',
      ],
      '#default_value' => empty($config->get('sync_interval')) ? 3600 : $config->get('sync_interval'),
      '#description' => $this->t('How often should Acquia DAM assets saved in this site be synced with Acquia DAM (this includes asset metadata as well as the asset itself)?'),
      '#required' => TRUE,
    ];

    $form['image'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Image configuration'),
    ];

    $form['image']['size_limit'] = [
      '#type' => 'select',
      '#title' => $this->t('Image size limit'),
      '#description' => $this->t('Limit the source size used when importing image assets. The largest available size up to the selected will be used.'),
      '#options' => [
        -1 => $this->t('Original size'),
        100 => 100,
        150 => 150,
        220 => 220,
        310 => 310,
        550 => 550,
        1280 => 1280,
      ],
      '#default_value' => empty($config->get('size_limit')) ? -1 : $config->get('size_limit'),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    try {
      // We set the client data array with the values from form_state.
      $username = $form_state->getValue('username');
      $password = $this->getFieldValue($form_state, 'password');
      $form_state->setValue('password', $password);
      $client_id = $form_state->getValue('client_id');
      $client_secret = $this->getFieldValue($form_state, 'secret');
      $form_state->setValue('secret', $client_secret);

      // Try to call checkCredentials() with details from form_state.
      $acquiadam_client = new WebdamClient($this->httpClient, $username, $password, $client_id, $client_secret);
      $acquiadam_client->getAccountSubscriptionDetails();
    }
    // If checkCredentials() throws an exception,
    // we catch it here and display the error message to the user.
    catch (InvalidCredentialsException $e) {
      $form_state->setErrorByName('authentication', $e->getMessage());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('media_acquiadam.settings')
      ->set('username', $form_state->getValue('username'))
      ->set('password', $form_state->getValue('password'))
      ->set('client_id', $form_state->getValue('client_id'))
      ->set('secret', $form_state->getValue('secret'))
      ->set('sync_interval', $form_state->getValue('sync_interval'))
      ->set('size_limit', $form_state->getValue('size_limit'))
      ->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Gets a form value from stored config.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param string $field_name
   *   The key of the field in the simple config.
   *
   * @return mixed
   *   The value for the given form field, or NULL.
   */
  protected function getFormValueFromConfig(FormStateInterface $form_state, $field_name) {
    $config_name = $this->getEditableConfigNames();
    $value = $this->config(reset($config_name))->get($field_name);
    return $value;
  }

  /**
   * Gets a form field value, either from the form or from config.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state object.
   * @param string $field_name
   *   The key of the field in config. (This may differ from form field key).
   *
   * @return mixed
   *   The value for the given form field, or NULL.
   */
  protected function getFieldValue(FormStateInterface $form_state, $field_name) {
    // If the user has entered a value use it, if not check config.
    $value = $form_state->getValue($field_name) ?: $this->getFormValueFromConfig($form_state, $field_name);
    return $value;
  }

}
