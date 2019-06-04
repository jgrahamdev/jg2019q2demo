<?php

namespace Drupal\big_screen\Controller;

use Drupal\node\Entity\Node;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityDisplayRepository;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Extension\ModuleHandler;

/**
 * Class BigScreen.
 *
 * @package Drupal\big_screen\Controller
 */
class BigScreen extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityDisplayRepository definition.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepository
   */
  protected $entityDisplayRepository;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Extension\ModuleHandler;
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityDisplayRepository $entity_display_repository, EntityTypeManager $entity_type_manager, ModuleHandler $module_handler) {
    $this->entityDisplayRepository = $entity_display_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_display.repository'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * @param \Drupal\node\Entity\Node $node
   * @return \Drupal\node\Entity\Node
   */
  public function moderationCheck(Node $node) {
    if (isset($node->moderation_state->target_id) && $this->moduleHandler()->moduleExists('content_moderation')) {
      $node = \Drupal::service('content_moderation.moderation_information')->getLatestRevision('node', $node->id());
    }
    return $node;
  }

  /**
   * @param $entity
   * @param $data
   * @return array
   */
  public function buildView($entity, $data) {
    $type = $entity->getEntityTypeId();
    $render = $this->entityTypeManager->getViewBuilder($type)->view($entity);
    $render['#attributes']['data-big-screen'] = $data;
    return $render;
  }

  /**
   * View Output.
   *
   * @return array
   */
  public function viewOutput(Node $node) {
    return $this->buildView($node, 'view');
  }

  /**
   * Preview Output (shows latest revision).
   *
   * @return array
   */
  public function previewOutput(Node $node) {
    // Check for moderation state and load latest revision.
    $node = $this->moderationCheck($node);
    return $this->buildView($node, 'preview');
  }

}
