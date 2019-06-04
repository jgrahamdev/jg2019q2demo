<?php

namespace Drupal\dfs_obio_commerce_individual_product_search\Plugin\facets\widget;

use Drupal\Core\Link;
use Drupal\facets\FacetInterface;
use Drupal\facets_range_widget\Plugin\facets\widget\RangeSliderWidget;

/**
 * The range slider widget.
 *
 * @FacetsWidget(
 *   id = "dfs_range_slider",
 *   label = @Translation("DFS Range slider"),
 *   description = @Translation("A widget that shows a range slider with a reset button."),
 * )
 */
class DfsRangeSliderWidget extends RangeSliderWidget {

  /**
   * {@inheritdoc}
   */
  public function build(FacetInterface $facet) {
    $build = parent::build($facet);

    $request = \Drupal::service('request_stack')->getMasterRequest();
    $query_params = $request->query->all();

    $url_alias = $facet->getUrlAlias();
    $filter_key = $facet->getFacetSourceConfig()->getFilterKey() ?: 'f';

    if (isset($query_params[$filter_key])) {
      foreach ($query_params[$filter_key] as $delta => $param) {
        if (strpos($param, $url_alias . ':') !== FALSE) {
          unset($query_params[$filter_key][$delta]);
        }
      }

      if (!$query_params[$filter_key]) {
        unset($query_params[$filter_key]);
      }
    }

    $results = $facet->getResults();

    /** @var \Drupal\Core\Url $first_item_url */
    $first_item_url = reset($results)->getUrl();
    $first_item_url->setOptions(['query' => $query_params]);

    $item = (new Link($this->t('Reset'), $first_item_url))->toRenderable();
    $build['reset_button'] = $item;

    return $build;
  }

}
