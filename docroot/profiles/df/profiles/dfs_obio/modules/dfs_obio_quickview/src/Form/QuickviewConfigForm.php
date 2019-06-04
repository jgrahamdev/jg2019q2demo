<?php

namespace Drupal\dfs_obio_quickview\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class QuickviewConfigForm.
 */
class QuickviewConfigForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['dfs_obio_quickview.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_obio_quickview_config_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('dfs_obio_quickview.settings');

    $form['main'] = [
      '#type' => 'details',
      '#title' => $this->t('Width and Height'),
      '#open' => TRUE,
    ];
    $form['main']['width'] = [
      '#type' => 'number',
      '#title' => $this->t('Width'),
      '#description' => $this->t('Width of the modal in pixels. Defaults to <code>300</code>'),
      '#default_value' => $config->get('modal.width') ?: NULL,
      '#min' => 100,
      '#step' => 1,
    ];
    $form['main']['height'] = [
      '#type' => 'number',
      '#title' => $this->t('Height'),
      '#description' => $this->t('Height of the modal in pixels. Defaults to <code>auto</code>'),
      '#default_value' => $config->get('modal.height') ?: NULL,
      '#min' => 100,
      '#step' => 1,
    ];
    $form['main']['resizable'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Resizable modal.'),
      '#description' => $this->t('Make modal resizable instead of the default, fixed position'),
      '#default_value' => $config->get('modal.resizable') ?: FALSE,
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $formState) {
    parent::submitForm($form, $formState);
    $modalPropertyOptions = [
      'width',
      'height',
    ];
    $nonNullableModalOptions = [
      'resizable',
    ];

    foreach (array_merge($modalPropertyOptions, $nonNullableModalOptions) as $modalOption) {
      $modalOptionValue = $formState->getValue($modalOption);

      if (!empty($modalOptionValue) || in_array($modalOption, $nonNullableModalOptions)) {
        $this->config('dfs_obio_quickview.settings')->set('modal.' . $modalOption, $modalOptionValue)->save();
      }
      else {
        $this->config('dfs_obio_quickview.settings')->clear('modal.' . $modalOption)->save();
      }
    }
  }

}
