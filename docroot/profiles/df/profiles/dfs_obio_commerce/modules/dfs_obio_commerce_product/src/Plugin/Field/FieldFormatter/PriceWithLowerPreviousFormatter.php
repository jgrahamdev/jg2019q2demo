<?php

namespace Drupal\dfs_obio_commerce_product\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\commerce_price\Plugin\Field\FieldFormatter\PriceDefaultFormatter;

/**
 * Plugin implementation of the 'price_with_lower_previous' formatter.
 *
 * @FieldFormatter(
 *   id = "price_with_lower_previous",
 *   label = @Translation("Price with lower previous"),
 *   field_types = {
 *     "commerce_price"
 *   }
 * )
 */
class PriceWithLowerPreviousFormatter extends PriceDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $entity = $items->getEntity();
    $previousPriceElements = [];
    $previousPriceItems = $entity->hasField('field_price_previous') && !$entity->field_price_previous->isEmpty() ? $entity->field_price_previous : [];

    if (!empty($previousPriceItems)) {
      $previousPriceElements = parent::viewElements($previousPriceItems, $langcode);
    }

    foreach ($elements as $delta => $element) {
      if (empty($elements[$delta])) {
        break 1;
      }

      $elements[$delta] = [
        'current' => [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#attributes' => ['class' => ['price-current', 'text-bold']],
          '#value' => $element['#markup'],
        ],
        '#cache' => $element['#cache'],
      ];

      if (!empty($previousPriceItems[$delta]) && ($items[$delta]->getValue()['number'] < $previousPriceItems[$delta]->getValue()['number'])) {
        $elements[$delta]['previous'] = [
          '#type' => 'html_tag',
          '#tag' => 'span',
          '#attributes' => ['class' => ['price-previous']],
          '#value' => $previousPriceElements[$delta]['#markup'],
        ];
      }
    }

    return $elements;
  }

}
