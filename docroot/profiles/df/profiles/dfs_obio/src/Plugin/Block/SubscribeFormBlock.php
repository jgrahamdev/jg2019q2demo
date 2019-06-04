<?php

namespace Drupal\dfs_obio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the "Newsletter Form" block.
 *
 * @Block(
 *   id = "subscribe_form",
 *   admin_label = @Translation("Subscribe Form"),
 *   category = @Translation("Forms")
 * )
 */
class SubscribeFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

 public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

     // Retrieve existing configuration for this block.
    $config = $this->getConfiguration();

     $form['newsletter_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Intro Text:'),
      '#default_value' => isset($config['newsletter_description'])? $config['newsletter_description'] : t('Sign up now and get 10% off!'),
      ];

      return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilder $form_builder) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);
    $values = $form_state->getValues();
    $this->configuration['newsletter_description'] = $values['newsletter_description'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();
    $news_description = isset($config['newsletter_description']) ? $config['newsletter_description'] : t('Sign up now and get 10% off!');

    // Get the Subscribe form.
    $build = [];
    $build['newsletter_description'] = [
      '#type' => 'markup',
      '#markup' => '<div class="newsletter-description"><p>' . $news_description . '</p></div>',
    ];
    $build['form'] = $this->formBuilder->getForm('\Drupal\dfs_obio\Form\SubscribeForm');
    return $build;
  }

}
