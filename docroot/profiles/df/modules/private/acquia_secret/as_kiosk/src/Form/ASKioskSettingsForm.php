<?php

/**
 * @file
 * Contains \Drupal\as_kiosk\Form\ASKioskSettingsForm.
 */

namespace Drupal\as_kiosk\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;

class ASKioskSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['as_kiosk'];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'as_kiosk_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    //Email settings
    $form['as_kiosk_email'] = [
      '#type' => 'details',
      '#title' => t('Email'),
      '#open' => TRUE,
    ];
    $config = \Drupal::config('as_kiosk.settings');
    $contact_forms = \Drupal\contact\Entity\ContactForm::loadMultiple();
    $contact_form_options = [];
    foreach ($contact_forms as $contact_form) {
      $contact_form_options[$contact_form->id()] = $contact_form->label();
    }
    $form['as_kiosk_email']['email_form_id'] = [
      '#type' => 'select',
      '#title' => $this->t('Form'),
      '#description' => $this->t('The form setting will affect'),
      '#options' => $contact_form_options,
      '#default_value' => $config->get('settings.email_form_id'),
      '#required' => TRUE,
    ];
    $form['as_kiosk_email']['email_subject'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail subject'),
      '#description' => $this->t('Add subject for e-mail'),
      '#default_value' => $config->get('settings.email_subject'),
      '#required' => TRUE,
    ];
    $form['as_kiosk_email']['email_from_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail from name'),
      '#description' => $this->t('Add e-mail from name'),
      '#default_value' => $config->get('settings.email_from_name'),
      '#required' => TRUE,
    ];
    $form['as_kiosk_email']['email_from_address'] = [
      '#type' => 'textfield',
      '#title' => $this->t('E-mail from address'),
      '#description' => $this->t('Add e-mail from address'),
      '#default_value' => $config->get('settings.email_from_address'),
      '#required' => TRUE,
    ];
    $form['as_kiosk_email']['email_campaign'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Campaign'),
      '#description' => $this->t('Add campaign for tracking'),
      '#default_value' => $config->get('settings.email_campaign'),
      '#required' => TRUE,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $email_subject = $form_state->getValue('email_subject');
    if (preg_match("/^[<>]*$/", $email_subject)) {
      $form_state->setErrorByName('email_subject', $this->t('The subject you have provided is invalid.'));
    }
    $email_from_name = $form_state->getValue('email_from_name');
    if (preg_match("/^[<>]*$/", $email_from_name)) {
      $form_state->setErrorByName('email_from_name', $this->t('The name you have provided is invalid.'));
    }
    $email_from_address = $form_state->getValue('email_from_address');
    if ($email_from_address == !\Drupal::service('email.validator')->isValid($email_from_address)) {
      $form_state->setErrorByName('email_from_address', t('The email address you have provided is invalid.'));
    }
    $email_campaign = $form_state->getValue('email_campaign');
    if (!preg_match("/^[a-zA-Z0-9-_]*$/", $email_campaign)) {
      $form_state->setErrorByName('email_campaign', $this->t('The campaign name you have provided is invalid.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config = \Drupal::getContainer()->get('config.factory')->getEditable('as_kiosk.settings');
    $config->set('settings.email_form_id', $form_state->getValue('email_form_id'))->save();
    $config->set('settings.email_subject', $form_state->getValue('email_subject'))->save();
    $config->set('settings.email_from_name', $form_state->getValue('email_from_name'))->save();
    $config->set('settings.email_from_address', $form_state->getValue('email_from_address'))->save();
    $config->set('settings.email_campaign', $form_state->getValue('email_campaign'))->save();
    parent::submitForm($form, $form_state);
  }
}
