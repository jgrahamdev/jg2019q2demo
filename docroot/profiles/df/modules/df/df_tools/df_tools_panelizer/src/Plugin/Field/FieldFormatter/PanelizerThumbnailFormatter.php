<?php

namespace Drupal\df_tools_panelizer\Plugin\Field\FieldFormatter;

use Drupal\block_content\Plugin\Block\BlockContentBlock;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\ctools_block\Plugin\Block\EntityField;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;
use Drupal\panelizer\PanelizerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Renders a thumbnail based on a given Panelizer field.
 *
 * @FieldFormatter(
 *   id = "panelizer_thumbnail",
 *   label = @Translation("Panelizer Thumbnail"),
 *   field_types = {
 *     "panelizer"
 *   }
 * )
 */
class PanelizerThumbnailFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * How deep this formatter should look for a file.
   *
   * @var int
   */
  const DEPTH_LIMIT = 5;

  /**
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $responsiveImageStyleStorage;

  /**
   * @var \Drupal\panelizer\PanelizerInterface
   */
  protected $panelizer;

  /**
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * Constructs a PanelizerThumbnailFormatter object.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Any third party settings.
   * @param \Drupal\Core\Entity\EntityStorageInterface $responsive_image_style_storage
   *   The responsive image style storage.
   * @param \Drupal\panelizer\PanelizerInterface $panelizer
   *   The panelizer service.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, EntityStorageInterface $responsive_image_style_storage, PanelizerInterface $panelizer, ImageFactory $image_factory, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);

    $this->responsiveImageStyleStorage = $responsive_image_style_storage;
    $this->panelizer = $panelizer;
    $this->imageFactory = $image_factory;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('entity.manager')->getStorage('responsive_image_style'),
      $container->get('panelizer'),
      $container->get('image.factory'),
      $container->get('entity.repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'responsive_image_style' => '',
        'image_link' => FALSE,
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $responsive_image_style = $this->responsiveImageStyleStorage->load($this->getSetting('responsive_image_style'));
    if ($responsive_image_style) {
      $summary[] = $this->t('Responsive image style: @responsive_image_style', ['@responsive_image_style' => $responsive_image_style->label()]);

      if ($this->getSetting('image_link')) {
        $summary[] = $this->t('Thumbnail is linked to entity');
      }
    }
    else {
      $summary[] = $this->t('Please select a responsive image style.');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $responsive_image_options = [];
    $responsive_image_styles = $this->responsiveImageStyleStorage->loadMultiple();
    if ($responsive_image_styles && !empty($responsive_image_styles)) {
      foreach ($responsive_image_styles as $machine_name => $responsive_image_style) {
        if ($responsive_image_style->hasImageStyleMappings()) {
          $responsive_image_options[$machine_name] = $responsive_image_style->label();
        }
      }
    }

    $elements['responsive_image_style'] = [
      '#title' => $this->t('Responsive image style'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('responsive_image_style'),
      '#required' => TRUE,
      '#options' => $responsive_image_options,
    ];

    $elements['image_link'] = [
      '#title' => $this->t('Link image to content'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('image_link'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();
    if ($file = $this->getFileFromPanelizedEntity($entity)) {
      if (($image = $this->imageFactory->get($file->getFileUri())) && $image->isValid()) {
        $item = new \stdClass();
        $item->width = $image->getWidth();
        $item->height = $image->getHeight();
        $item->alt = '';
        $item->title = $file->getFilename();
        $item->entity = $file;

        $url = NULL;
        if ($this->getSetting('image_link')) {
          $entity = $items->getEntity();
          if (!$entity->isNew()) {
            $url = $entity->toUrl();
          }
        }

        $elements = [
          '#theme' => 'responsive_image_formatter',
          '#item' => $item,
          '#responsive_image_style_id' => $this->getSetting('responsive_image_style'),
          '#url' => $url,
        ];
      }
    }

    return $elements;
  }

  /**
   * Finds the first file from a given Panelized entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|FieldableEntityInterface $entity
   *   A Panelized entity.
   * @return \Drupal\file\FileInterface|bool
   *   The first file displayed in a given display, or FALSE if none was found.
   */
  protected function getFileFromPanelizedEntity($entity) {
    if ($display = $this->panelizer->getPanelsDisplay($entity, 'full')) {
      $regions = $display->getRegionAssignments();
      foreach ($regions as $region => $blocks) {
        foreach ($blocks as $block) {
          if ($block instanceof BlockContentBlock) {
            $uuid = $block->getDerivativeId();
            /** @var \Drupal\block_content\BlockContentInterface $block_content */
            $block_content = $this->entityRepository->loadEntityByUuid('block_content', $uuid);
            if ($file = $this->getFileFromEntity($block_content)) {
              return $file;
            }
          }
          else if ($block instanceof EntityField) {
            list (, $field_name) = explode(':', $block->getDerivativeId());
            if ($entity->hasField($field_name) && $field = $entity->get($field_name)) {
              if ($file = $this->getFileFromField($field)) {
                return $file;
              }
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Gets the first file present in a given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface|FieldableEntityInterface $entity
   *   The entity you want to find a file for.
   * @return \Drupal\file\FileInterface|bool
   *   The first file found for a given entity, or FALSE
   */
  protected function getFileFromEntity(FieldableEntityInterface $entity) {
    foreach ($entity->getFields() as $field) {
      if ($file = $this->getFileFromField($field)) {
        return $file;
      }
    }

    return FALSE;
  }

  /**
   * Gets the first file from a given field.
   *
   * If the field is a Media reference, recursion is used to find nested
   * file fields.
   *
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   * @return \Drupal\file\FileInterface|bool
   */
  protected function getFileFromField(FieldItemListInterface $field) {
    static $depth = 0;
    $depth++;

    $return = FALSE;

    if ($field instanceof EntityReferenceFieldItemList) {
      $referenced_entities = $field->referencedEntities();
      $referenced_entity = reset($referenced_entities);
      if ($referenced_entity instanceof FileInterface) {
        $return = $referenced_entity;
      }
      else if ($referenced_entity instanceof MediaInterface) {
        if ($depth < self::DEPTH_LIMIT && $file = $this->getFileFromEntity($referenced_entity)) {
          $return =  $file;
        }
      }
    }

    $depth--;
    return $return;
  }

}
