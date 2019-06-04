<?php

namespace Drupal\dfs_obio_commerce_cart\Plugin\Field\FieldWidget;

use Drupal\commerce_product\Entity\ProductVariation;
use Drupal\commerce_product\Event\ProductEvents;
use Drupal\commerce_product\Event\ProductVariationAjaxChangeEvent;
use Drupal\commerce_product\Plugin\Field\FieldWidget\ProductVariationAttributesWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of 'obio_commerce_cart_product_variation_attributes' widget.
 *
 * @FieldWidget(
 *   id = "obio_commerce_cart_product_variation_attributes",
 *   label = @Translation("Obio Product variation attributes"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class ObioProductVariationAttributesWidget extends ProductVariationAttributesWidget {

  /**
   * {@inheritdoc}
   */
  public static function ajaxRefresh(array $form, FormStateInterface $form_state) {
    // Using the view mode from the storage instead of the form_display for
    // rendering variation fields.
    /** @var \Drupal\Core\Render\MainContent\MainContentRendererInterface $ajax_renderer */
    $ajax_renderer = \Drupal::service('main_content_renderer.ajax');
    $request = \Drupal::request();
    $route_match = \Drupal::service('current_route_match');
    /** @var \Drupal\Core\Ajax\AjaxResponse $response */
    $response = $ajax_renderer->renderResponse($form, $request, $route_match);

    $variation = ProductVariation::load($form_state->get('selected_variation'));
    /** @var \Drupal\dfs_obio_commerce_cart\ObioProductVariationFildRendererInterface $variation_field_renderer */
    $variation_field_renderer = \Drupal::service('dfs_obio_commerce_cart.variation_field_renderer');
    $view_mode = $form_state->getStorage()['view_mode'];
    $variation_field_renderer->replaceRenderedFields($response, $variation, $view_mode);

    // Allow modules to add arbitrary ajax commands to the response.
    $event = new ProductVariationAjaxChangeEvent($variation, $response, $view_mode);
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch(ProductEvents::PRODUCT_VARIATION_AJAX_CHANGE, $event);

    return $response;
  }

}
