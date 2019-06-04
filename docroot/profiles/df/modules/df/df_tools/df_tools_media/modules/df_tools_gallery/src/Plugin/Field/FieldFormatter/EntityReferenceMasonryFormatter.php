<?php

namespace Drupal\df_tools_gallery\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Plugin implementation of the 'entity_reference_masonry' formatter.
 *
 * @FieldFormatter(
 *   id = "entity_reference_masonry",
 *   label = @Translation("Rendered as Masonry grid"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class EntityReferenceMasonryFormatter extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      // Implement default settings.
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return array(
      // Implement settings form.
    ) + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $lightbox_id = Html::getUniqueId('df-tools-gallery');
    foreach ($elements as &$element) {
      if (isset($element['#file']) && $element['#file']->bundle() === 'image') {
        $url = UrlHelper::filterBadProtocol((file_create_url($element['#file']->getFileUri())));
        $element['#prefix'] = "<a href=\"$url\" data-lightbox=\"$lightbox_id\">";
        $element['#suffix'] = '</a>';
      }
      $element['#attributes']['class'][] = 'grid-item';
    }
    $elements['#attached']['library'][] = 'df_tools_gallery/view';
    $elements['#attributes']['class'][] = 'df-masonry-gallery';
    return $elements;
  }

}
