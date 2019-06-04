<?php

namespace Drupal\dfs_obio_commerce_checkout\Plugin\Commerce\CheckoutPane;

use Drupal\Core\Form\FormStateInterface;
use Drupal\commerce_checkout\Plugin\Commerce\CheckoutPane\CheckoutPaneBase;

/**
 * Provides the completion message pane.
 *
 * @CommerceCheckoutPane(
 *   id = "dfs_obio_commerce_checkout_completion_message",
 *   label = @Translation("OBIO Completion message"),
 *   default_step = "complete",
 * )
 */
class CompletionMessage extends CheckoutPaneBase {

  /**
   * {@inheritdoc}
   */
  public function buildPaneForm(array $pane_form, FormStateInterface $form_state, array &$complete_form) {
    $pane_form['message'] = [
      '#theme' => 'dfs_obio_commerce_checkout_completion_message',
      '#order' => $this->order,
    ];

    return $pane_form;
  }

}
