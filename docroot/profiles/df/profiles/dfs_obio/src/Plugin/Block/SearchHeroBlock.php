<?php

namespace Drupal\dfs_obio\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\file\Entity\File;
use Drupal\views\Views;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Search hero form' block.
 *
 * @Block(
 *   id = "search_form_hero",
 *   admin_label = @Translation("Search hero form"),
 *   category = @Translation("Forms")
 * )
 */
class SearchHeroBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The responsive image style storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $responsiveImageStyleStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilder $form_builder, EntityRepositoryInterface $entity_repository, EntityStorageInterface $image_style_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->entityRepository = $entity_repository;
    $this->responsiveImageStyleStorage = $image_style_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('entity.repository'),
      $container->get('entity.manager')->getStorage('responsive_image_style')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'uuid' => FALSE,
      'responsive_image_style' => 'responsive_hero',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $responsive_image_styles = $this->responsiveImageStyleStorage->loadMultiple();
    if ($responsive_image_styles && !empty($responsive_image_styles)) {
      foreach ($responsive_image_styles as $machine_name => $responsive_image_style) {
        if ($responsive_image_style->hasImageStyleMappings()) {
          $responsive_image_options[$machine_name] = $responsive_image_style->label();
        }
      }
    }

    /** @var \Drupal\file\Entity\File $old_file */
    $file = $this->entityRepository->loadEntityByUuid('file', $this->configuration['uuid']);

    $form['file'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('Background image'),
      '#default_value' => $file ? [$file->id()] : [],
      '#upload_location' => 'public://',
      '#required' => TRUE,
    ];

    $form['responsive_image_style'] = [
      '#type' => 'select',
      '#title' => $this->t('Image style'),
      '#options' => $responsive_image_options,
      '#default_value' => $this->configuration['responsive_image_style'],
      '#required' => TRUE,
    ];

    // @see https://www.drupal.org/node/2647812#comment-11683961
    $form_state->disableCache();

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $old_configuration = $this->getConfiguration();
    /** @var \Drupal\file\Entity\File $old_file */
    $old_file = $this->entityRepository->loadEntityByUuid('file', $old_configuration['uuid']);
    if ($old_file) {
      $old_file->setTemporary();
      $old_file->save();
    }
    /** @var \Drupal\file\Entity\File $file */
    $file = File::load($form_state->getValue('file')[0]);
    $file->setPermanent();
    $file->save();
    $this->configuration['uuid'] = $file->uuid();
    $this->configuration['responsive_image_style'] = $form_state->getValue('responsive_image_style');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['search_form_container'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['search-form-wrapper'],
      ],
    ];

    $form = $this->formBuilder->getForm('Drupal\dfs_obio\Form\InspirationArticleSearchForm');
    $build['search_form_container']['form'] = $form;

    /** @var \Drupal\file\Entity\File $file */
    $file = $this->entityRepository->loadEntityByUuid('file', $this->configuration['uuid']);
    if ($file) {
      $build['search_hero_container'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['search-hero-wrapper', 'full-width-row'],
        ],
      ];
      $build['search_hero_container']['background'] = [
        '#theme' => 'responsive_image',
        '#uri' => $file->getFileUri(),
        '#item' => $file,
        '#responsive_image_style_id' => $this->configuration['responsive_image_style'],
        '#attributes' => ['class' => ['search-form-background']],
      ];
    }
    return $build;
  }

}
