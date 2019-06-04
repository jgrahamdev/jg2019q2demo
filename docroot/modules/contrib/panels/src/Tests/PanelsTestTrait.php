<?php

namespace Drupal\panels\Tests;

/**
 * Provides common functionality for the Panels test classes.
 */
trait PanelsTestTrait {

  /**
   * Generate random CSS properties (HTML Id., classes, style).
   *
   * @param int $class_count
   *   Class item counter.
   *
   * @return array
   *   Keyed array with generated CSS properties.
   */
  public function generateCSSProperties($class_item_count = 5) {
    $result = [];
    $result['html_id'] = strtolower($this->randomMachineName());
    $css_classes_array = [];
    for ($i = 0; $i < $class_item_count; $i++) {
      $css_classes_array[] = strtolower($this->randomMachineName());
    }
    $result['css_classes'] = implode(' ', $css_classes_array);
    $result['css_style'] = strtolower($this->randomMachineName() . ': ' . $this->randomMachineName());
    return $result;
  }
}
