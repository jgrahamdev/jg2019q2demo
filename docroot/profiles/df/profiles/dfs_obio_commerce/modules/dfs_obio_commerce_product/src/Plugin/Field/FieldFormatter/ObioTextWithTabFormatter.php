<?php

namespace Drupal\dfs_obio_commerce_product\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\df_tools_tabs\Plugin\Field\FieldFormatter\TextWithTabFormatter;

/**
 * Plugin implementation of the 'obio_text_with_tab' formatter.
 *
 * Returns [] if field is empty.
 *
 * @FieldFormatter(
 *   id = "obio_text_with_tab",
 *   label = @Translation("Obio text with tab"),
 *   field_types = {
 *     "text_with_tab",
 *   },
 *   quickedit = {
 *     "editor" = "form"
 *   }
 * )
 */
class ObioTextWithTabFormatter extends TextWithTabFormatter {

  /**
   * {@inheritdoc}
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    if ($items->isEmpty()) {
      return [];
    }

    return parent::view($items, $langcode);
  }

}
