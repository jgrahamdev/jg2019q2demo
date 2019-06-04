<?php

namespace Drupal\dfs_obio_commerce_cart;

use Drupal\commerce_product\Entity\ProductVariationInterface;
use Drupal\Component\Utility\Html;
use Drupal\commerce_product\ProductVariationFieldRenderer;

/**
 * Custom product variation filed renderer.
 *
 * This one uses an additional replace CSS selector item which makes possible
 * to replace only those fields which are rendered with the current view mode.
 */
class ObioProductVariationFieldRenderer extends ProductVariationFieldRenderer {

  /**
   * {@inheritdoc}
   */
  public function renderField($field_name, ProductVariationInterface $variation, $display_options = []) {
    $ajax_class = $this->buildAjaxReplacementClass($field_name, $variation);
    $content = $this->variationViewBuilder->viewField($variation->get($field_name), $display_options);
    $content['#attributes']['class'][] = $ajax_class;
    $content['#ajax_replace_class'] = $ajax_class . '.' . Html::getClass('fvm-' . $display_options);

    return $content;
  }

}
