<?php

namespace Drupal\dfs_obio_commerce_cart\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Render\Element\PathElement;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the 'Continue Shopping' block.
 *
 * @Block(
 *   id = "obio_commerce_cart_continue_shopping",
 *   admin_label = @Translation("Continue Shopping Link"),
 *   category = @Translation("Commerce")
 * )
 */
class ObioCommerceContinueShoppingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The path validator.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * Constructs a new Continue Shopping Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin ID for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, PathValidatorInterface $pathValidator) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->pathValidator = $pathValidator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('path.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'path' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $formState) {
    $config = $this->configuration;

    $form['path'] = [
      '#type' => 'path',
      '#title' => $this->t('Uri of the ”Continue Shopping” link'),
      '#description' => $this->t('Only internal urls are allowed'),
      '#default_value' => $config['path'],
      '#convert_path' => PathElement::CONVERT_NONE,
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $formState) {
    $this->configuration['path'] = $formState->getValue('path');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var \Drupal\Core\Url $url */
    if ($url = $this->pathValidator->getUrlIfValid($this->configuration['path'])) {
      $url->setOption('attributes', [
        'class' => ['obio-back', 'button', 'transparent'],
      ]);
      $build['link'] = Link::fromTextAndUrl($this->t('Continue Shopping'), $url)->toRenderable();
    }

    return $build;
  }

}
