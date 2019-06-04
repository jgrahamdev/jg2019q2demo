<?php

namespace Drupal\entity_block\Plugin\Block;

use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the block for similar articles.
 *
 * @Block(
 *   id = "entity_block",
 *   admin_label = @Translation("Entity block"),
 *   deriver = "Drupal\entity_block\Plugin\Derivative\EntityBlock"
 * )
 */
class EntityBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
    * The entity repository.
    *
    * @var \Drupal\Core\Entity\EntityRepositoryInterface
    */
  protected $entityRepository;

  /**
   * The entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * The name of our entity type.
   *
   * @var string
   */
  protected $entityTypeName;

  /**
   * The entity type manager.
   *
   * @var EntityTypeManagerInterface
   */
  public $entityTypeManager;

  /**
   * The entity storage for our entity type.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface;
   */
  protected $entityStorage;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepositoryInterface $entityRepository, EntityTypeManagerInterface $entityTypeManager, EntityDisplayRepositoryInterface $entityDisplayRepository) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Allows entities to be loaded by UUID.
    $this->entityRepository = $entityRepository;

    // Allows view modes to be retrieved.
    $this->entityDisplayRepository = $entityDisplayRepository;

    // Determine what entity type we are referring to.
    $this->entityTypeName = $this->getDerivativeId();

    // Load various utilities related to our entity type.
    $this->entityTypeManager = $entityTypeManager;
    $this->entityStorage = $entityTypeManager->getStorage($this->entityTypeName);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('entity.manager'),
      $container->get('entity_display.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'entity' => 0,
      'entity_uuid' => '',
      'view_mode' => 'full',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);
    $config = $this->configuration;
    $options = $this->entityDisplayRepository->getViewModeOptions($this->entityTypeName);

    $form['entity'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Entity'),
      '#target_type' => $this->entityTypeName,
      '#default_value' => $this->getEntity(),
      '#required' => TRUE,
    ];

    $form['entity_uuid'] = array(
      '#type' => 'value',
      '#value' => $config['entity_uuid'],
    );

    $form['view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('View mode'),
      '#options' => $options,
      '#default_value' => isset($options[$config['view_mode']]) ? $config['view_mode'] : reset($options),
      '#access' => (count($options) > 1),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Hide default block form fields that are undesired in this case.
    $form['admin_label']['#access'] = FALSE;
    $form['label']['#access'] = FALSE;
    $form['label_display']['#access'] = FALSE;

    // Hide the block title by default.
    $form['label_display']['#value'] = FALSE;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    parent::blockSubmit($form, $form_state);

    $this->configuration['entity'] = $form_state->getValue('entity');
    $this->configuration['view_mode'] = $form_state->getValue('view_mode');

    if ($entity = $this->entityStorage->load($form_state->getValue('entity'))) {
      if ($uuid = $entity->uuid()) {
        $this->configuration['entity_uuid'] = $uuid;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    if ($this->getEntity()) {
      return $this->getEntity()->access('view', $account, TRUE);
    }
    return AccessResult::forbidden();
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    if ($entity = $this->getEntity()) {
      return $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId())->view($entity, $this->configuration['view_mode']);
    }
    else {
      return [
        '#markup' => $this->t('Unable to load %type entity %id.', array('%type' => $this->entityTypeName, '%id' => $this->configuration['entity'])),
      ];
    }
  }

  /**
   * Loads the entity from configuration by UUID or ID.
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   *   The entity object, or NULL if there is no entity with the given ID/UUID.
   */
  protected function getEntity() {
    $config = $this->configuration;

    if (Uuid::isValid($config['entity_uuid'])) {
      $entity = $this->entityRepository->loadEntityByUuid($this->entityTypeName, $config['entity_uuid']);
    }
    else {
      $entity = $this->entityStorage->load($config['entity']);
    }

    return $entity;
  }
}
