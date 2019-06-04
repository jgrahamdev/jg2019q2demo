<?php

namespace Drupal\df_tools_blocks\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a 'CodeBlock' block.
 *
 * @Block(
 *  id = "code_block",
 *  admin_label = @Translation("Code block"),
 * )
 */
class CodeBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['code' => $this->t('')] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['code'] = [
      '#title' => $this->t('Code'),
      '#type' => 'text_format',
      '#format' => 'full_html',
      '#description' => $this->t('Add javascript or html code.'),
      '#default_value' => $this->configuration['code']['value'],
      '#weight' => '6',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['code'] = $form_state->getValue('code');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    return ['code_block_code' => ['#theme' => 'code_block', '#code' => $this->configuration['code']['value']]];
  }

}

