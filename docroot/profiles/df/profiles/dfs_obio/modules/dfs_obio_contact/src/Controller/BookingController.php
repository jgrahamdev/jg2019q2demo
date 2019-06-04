<?php

namespace Drupal\dfs_obio_contact\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Provides route response for the Booking form.
 */
class BookingController extends ControllerBase {

  /**
   * Returns a simple page.
   *
   * @return array
   *   A simple renderable array.
   */
  public function confirmPage() {
    $element = array(
      '#markup' => 'Your appointment has been confirmed. You will be hearing from us shortly. Thanks!',
    );
    return $element;
  }

}
