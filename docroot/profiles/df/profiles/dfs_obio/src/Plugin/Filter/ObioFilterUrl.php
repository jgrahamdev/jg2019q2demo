<?php

namespace Drupal\dfs_obio\Plugin\Filter;

use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Unicode;

/**
 * Provides a filter to add 'use-ajax' class to internal, 'ajaxable' URLs.
 *
 * @Filter(
 *   id = "obio_filter_url",
 *   title = @Translation("Process ajaxable internal links"),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_MARKUP_LANGUAGE
 * )
 */
class ObioFilterUrl extends FilterBase {

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    $html_dom = Html::load($text);
    $anchors = $html_dom->getElementsByTagName('a');

    foreach ($anchors as $anchor) {
      if ($anchor->hasAttribute('href')) {
        $href = $anchor->getAttribute('href') ? trim($anchor->getAttribute('href')) : '';
        if (
          !empty($href) &&
          Unicode::strpos($href, '/') === 0 &&
          Unicode::strpos($href, '//') !== 0 &&
          Unicode::strpos($href, 'nojs') !== FALSE
        ) {
          $class = ($anchor->getAttribute('class') ? trim($anchor->getAttribute('class')) . ' ' : '');
          $anchor->setAttribute('class', $class . 'use-ajax');
        }
      }
    }
    $text = Html::serialize($html_dom);
    $result = new FilterProcessResult($text);
    $result->setAttachments([
      'library' => ['core/drupal.dialog.ajax'],
    ]);
    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Internal links with "nojs" stub get the "use-ajax" class.');
  }

}
