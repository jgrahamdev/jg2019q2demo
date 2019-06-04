<?php

namespace Drupal\dfs_obio_commerce_product\Plugin\Field\FieldFormatter;

use Drupal\comment\CommentManagerInterface;
use Drupal\comment\Plugin\Field\FieldType\CommentItemInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Provides average rating and anchor formatter.
 *
 * @FieldFormatter(
 *   id = "comment_average_rating_and_anchor",
 *   label = @Translation("Average rating from comments and anchor"),
 *   field_types = {
 *     "comment"
 *   },
 *   quickedit = {
 *     "editor" = "disabled"
 *   }
 * )
 */
class CommentAverageRatingFormatter extends FormatterBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The comment storage.
   *
   * @var \Drupal\comment\CommentStorageInterface
   */
  protected $storage;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

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
      $container->get('current_user'),
      $container->get('entity.manager'),
      $container->get('language_manager')
    );
  }

  /**
   * Constructs a new CommentDefaultFormatter.
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
   *   Third party settings.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The current user.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, AccountInterface $current_user, EntityManagerInterface $entity_manager, LanguageManagerInterface $language_manager) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->storage = $entity_manager->getStorage('comment');
    $this->currentUser = $current_user;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $field_name = $this->fieldDefinition->getName();
    $entity = $items->getEntity();
    $status = $items->status;
    $type = $this->getSetting('type');
    $formatType = $this->formatTypes($this->getSetting('type'));
    $rating = [
      'sum' => 0,
      'count' => 0,
    ];
    $minMax = [
      'min' => 0,
      'max' => 0,
    ];

    if ($status != CommentItemInterface::HIDDEN && empty($entity->in_preview) &&
      !in_array($this->viewMode, ['search_result', 'search_index'])) {
      $elements = [
        0 => [
          '#comment_type' => '_rating_from_comments',
          '#comment_display_mode' => CommentManagerInterface::COMMENT_MODE_FLAT,
          'comments' => [
            '#theme_wrappers' => [
              'container' => ['#attributes' => ['class' => ['dfs-rating']]],
            ],
          ],
          'comment_form' => [],
        ],
        '#cache' => ['contexts' => ['user.permissions']],
      ];

      if (($this->currentUser->hasPermission('access comments') || $this->currentUser->hasPermission('administer comments'))
      &&
      ($entity->get($field_name)->comment_count || $this->currentUser->hasPermission('administer comments'))) {
        $comments = $this->storage->loadThread($entity, $field_name, CommentManagerInterface::COMMENT_MODE_FLAT);
        if ($comments) {
          $ratingDataTypes = ['integer', 'decimal', 'float'];
          foreach ($comments as $comment) {
            if ($comment->hasField('field_rating') && !$comment->field_rating->isEmpty() && in_array($comment->field_rating->getFieldDefinition()->getType(), $ratingDataTypes)) {
              $rating['sum'] += (float) $comment->field_rating->first()->getString();
              $rating['count']++;
            }
          }

          // If we had at least one comment and it had a rating field,
          // We need 'max' rating later.
          if (isset($comment) && $rating['count']) {
            $commentRatingFieldSettings = $comment->field_rating->getItemDefinition()->getSettings();

            if (!empty($commentRatingFieldSettings['min'] && !empty($commentRatingFieldSettings['max']))) {
              $min = (int) round($commentRatingFieldSettings['min']);
              $max = (int) round($commentRatingFieldSettings['max']);
              if ($min > 0) {
                $minMax['min'] = $min;
                $minMax['max'] = $min;
              }
              if ($max > $minMax['max']) {
                $minMax['max'] = $max;
              }
            }
          }
        }
      }

      $average = $rating['count'] ? (float) $rating['sum'] / (int) $rating['count'] : 0;
      $message = $rating['count'] ?
      $this->formatPlural((int) $rating['count'], 'Review', 'Reviews', [], ['context' => 'user feedback']) :
        $this->t('Not reviewed yet');

      if ($type === 'star') {
        $message = $rating['count'] ?
        $this->formatPlural((int) $rating['count'], '@average of @max from 1 review', '@average of @max from @count reviews', [
          '@average' => $average,
          '@max' => $minMax['max'],
        ], ['context' => 'user feedback']) :
        $this->t('Not reviewed yet.');
      }

      // Provide link only if there are comments or the current user is allowed
      // to add new comment.
      /* @var \Drupal\Core\Url $urlToComments */
      $urlToComments = !empty($this->getSetting('anchor')) && ($rating['count'] && $this->currentUser->hasPermission('access comments') || $this->currentUser->hasPermission('post comments')) ?
        $entity->toUrl()->mergeOptions([
          'fragment' => 'comments-' . $entity->id(),
          'language' => $this->languageManager->getCurrentLanguage(),
        ]) :
        NULL;

      $elements[0]['comments']['average_rating'] = [
        '#theme' => $formatType['hook'],
        '#min' => $minMax['min'],
        '#max' => $minMax['max'],
        '#value' => $average,
        '#count' => $rating['count'],
        '#message' => $message,
        '#link' => $urlToComments,
      ];
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      // Rating field name is hardcoded.
      'anchor' => FALSE,
      'type' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = [];
    $element['anchor'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Provide a conditional anchor link to comments'),
      '#default_value' => $this->getSetting('anchor'),
    ];
    $typeOptions = [];
    foreach ($this->formatTypes() as $type => $value) {
      $typeOptions[$type] = $value['label'];
    }

    $element['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Rating output format'),
      '#options' => $typeOptions,
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $type = $this->formatTypes($this->getSetting('type'));

    $summary[] = $this->t('Rating output format: @format-label', [
      '@format-label' => $type['label'],
    ]);

    $summary[] = empty($this->getSetting('anchor')) ? $this->t('Without anchor') : $this->t('With anchor @anchor', [
      '@anchor' => '#' . $this->getSetting('anchor'),
    ]);

    return $summary;
  }

  /**
   * Returns properties of every or a specific format type.
   *
   * @param string|bool $type
   *   The name of the type, or null if you need them all.
   *
   * @return array
   *   If $type param provided, returns the type properties of that type if
   *   it's defined or the 'default'.
   *   Properties:
   *   - label: the label of the specified type.
   *   - hook: which theme hook schould be used.
   *   If no $type provided, returns the array of properties keyed by
   *   their name.
   */
  public function formatTypes($type = FALSE) {
    $types = [
      'default' => [
        'label' => $this->t('Default'),
        'hook' => 'rating_average',
      ],
      'stars' => [
        'label' => $this->t('Stars'),
        'hook' => 'rating_average__stars',
      ],
    ];

    if (!empty($type)) {
      return empty($types[$type]) ? $types['default'] : $types[$type];
    }

    return $types;
  }

}
