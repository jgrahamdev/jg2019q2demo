<?php

namespace Drupal\as_lift\Plugin\Block;

use Acquia\LiftClient\Entity\Slot;
use Acquia\LiftClient\Entity\Visibility;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the "Lift Slot" block.
 *
 * @Block(
 *   id = "lift_slot",
 *   admin_label = @Translation("Lift Slot"),
 *   category = @Translation("Lift")
 * )
 */
class LiftSlotBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'lift_slot_id' => '',
      'lift_full_width' => 0,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['lift_existing_slot'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Use existing slot'),
      '#default_value' => !empty($this->configuration['lift_slot_id']),
    ];

    $form['lift_new_slot'] = [
      '#type' => 'container',
      '#states' => [
        'invisible' => [
          ':input[name*="lift_existing_slot"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['lift_new_slot']['lift_slot_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('New Slot label'),
      '#states' => [
        'required' => [
          ':input[name*="lift_existing_slot"]' => ['checked' => FALSE],
        ],
      ],
    ];

    $form['lift_new_slot']['lift_slot_description'] = [
      '#type' => 'textfield',
      '#title' => $this->t('New Slot description'),
    ];

    $client = _as_lift_get_client();
    $slot_manager = $client->getSlotManager();
    $options = [];
    foreach ($slot_manager->query() as $slot) {
      if ($slot->getStatus()) {
        $options[$slot->getId()] = $slot->getLabel() . ' (' . implode(',', $slot->getVisibility()->getPages()) . ')';
      }
    }

    $form['lift_slot_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Existing Slot ID'),
      '#options' => !empty($options) ? $options : ['none' => $this->t('No slots found')],
      '#disabled' => empty($options),
      '#states' => [
        'invisible' => [
          ':input[name*="lift_existing_slot"]' => ['checked' => FALSE],
        ],
        'required' => [
          ':input[name*="lift_existing_slot"]' => ['checked' => TRUE],
        ],
      ],
      '#default_value' => $this->configuration['lift_slot_id'],
    ];

    $form['lift_full_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full width row'),
      '#default_value' => $this->configuration['lift_full_width'],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    if (!$form_state->getValue('lift_existing_slot') && empty($form_state->getValue(['lift_new_slot', 'lift_slot_label']))) {
      $form_state->setErrorByName('lift_slot_label', 'The slot label is required.');
    }
    if ($form_state->getValue('lift_existing_slot') && empty($form_state->getValue('lift_slot_id'))) {
      $form_state->setErrorByName('lift_slot_id', 'No slot selected.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // Create a new Slot if an existing ID was not passed.
    if (!$form_state->getValue('lift_existing_slot')) {
      $slot = new Slot();
      $slot_id = \Drupal::service('uuid')->generate();
      $slot->setLabel($form_state->getValue(['lift_new_slot', 'lift_slot_label']));
      $slot->setDescription($form_state->getValue(['lift_new_slot', 'lift_slot_description']));
      $slot->setId($slot_id);
      $slot->setStatus(TRUE);

      $visibility = new Visibility();
      $visibility->setCondition('show');
      $visibility->setPages(['*']);
      $slot->setVisibility($visibility);

      $client = _as_lift_get_client();
      $slot_manager = $client->getSlotManager();
      $slot = $slot_manager->add($slot);
      $this->configuration['lift_slot_id'] = $slot->getId();

      // Track that a new slot is added.
      _as_tracking_event('lift_slot_added');
    }
    else {
      $this->configuration['lift_slot_id'] = $form_state->getValue('lift_slot_id');
    }

    $this->configuration['lift_full_width'] = $form_state->getValue('lift_full_width', 0);
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $lift_slot_id = $this->configuration['lift_slot_id'];
    $classes = $this->configuration['lift_full_width'] ? ['full-width-row'] : [];
    $build = [
      '#type' => 'container',
      '#attributes' => [
        'class' => $classes,
        'data-lift-slot' => $lift_slot_id,
      ],
    ];
    return $build;
  }

}
