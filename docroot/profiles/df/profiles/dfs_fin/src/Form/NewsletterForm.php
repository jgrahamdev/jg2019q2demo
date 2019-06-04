<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Form\NewsletterForm.
 */

namespace Drupal\dfs_fin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;

/**
 * Provides a newsletter sign up form.
 */
class NewsletterForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_fin_newsletter_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    // Define Foundation wrappers for form elements
    $form['#attributes']['class'] = ['row medium-unstack'];
    $form['left'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['large-4 large-offset-1 medium-6 small-2 small-centered columns']]
    ];

    $form['right'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['align-middle small-10 large-6 columns']]
    ];

    // Add some text prefixing the newletter form
    $form['left']['header'] = [
      '#markup' => '<i class="fa fa-envelope-o fa-3x newsletter-icon hide-for-small-only"></i> <div class="newsletter-header">' . t('Sign up for our newsletter') . '</div>'
    ];

    $form['left']['subheader'] = [
      '#markup' => '<div class="newsletter-subheader hide-for-small-only">' . t('We will keep your address safe') . ' - <em>' . t('we promise!') . '</em></div>'
    ];

    $form['right']['email'] = [
      '#prefix' => '<div class="row medium-unstuck small-collapse"><div class="columns small-8">',
      '#suffix' =>'</div>',
      '#type' => 'textfield',
      '#attributes' => ['class' => ['email-form-textbox'], 'placeholder' => t('your email address')],
      '#size' => 80,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    $form['right']['submit'] = [
      '#prefix' => '<div class="columns small-4">',
      '#type' => 'submit',
      '#attributes' => ['class' => ['subscribe-submit']],
      '#value' => t('Subscribe'),
      '#suffix' =>'</div></div>',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Send an email message to the given address
    $address = $form_state->getValue('email');

    /** @var \Drupal\Core\Mail\MailManager $mail_manager */
    $mail_manager = \Drupal::service('plugin.manager.mail');

    $message = $mail_manager->mail('dfs_fin', 'sign-up', $address, LanguageInterface::LANGCODE_NOT_APPLICABLE);

    // Check for success
    if ($message['result']) {
      // Let our theme know that this message should be opened in a fancy modal.
      $_SESSION['fin_modal'] = t('Thanks for signing up! An email confirmation has been sent to @address', array('@address' => $address));
    }
  }

}
