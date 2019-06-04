<?php

namespace Drupal\dfs_obio\Form;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\HtmlCommand;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Egulias\EmailValidator\EmailValidator;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a subscription sign up form.
 */
class SubscribeForm extends FormBase {

  /**
   * The email validator.
   *
   * @var \Egulias\EmailValidator\EmailValidator
   */
  protected $emailValidator;

  /**
   * Whether form has submitted.
   *
   * @var bool
   */
  protected $submitted;

  /**
   * Submit message.
   *
   * @var TranslatableMarkup|null
   */
  protected $message;

  /**
   * Constructs a new UpdateSettingsForm.
   *
   * @param \Egulias\EmailValidator\EmailValidator $email_validator
   *   The email validator.
   */
  public function __construct(EmailValidator $email_validator) {
    $this->emailValidator = $email_validator;
    $this->submitted = FALSE;
    $this->message = NULL;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('email.validator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_obio_subscribe_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $js = 'nojs') {
    $form = [];

    $form['form'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['row']]
    ];

    $form['form']['email'] = [
      '#prefix' => '<div class="columns medium-9 small-12">',
      '#suffix' =>'</div>',
      '#type' => 'textfield',
      '#attributes' => ['class' => ['email-form-textbox'], 'placeholder' => t('Enter Email Address')],
      '#size' => 80,
      '#maxlength' => 128,
      '#required' => TRUE,
    ];

    $form['form']['submit'] = [
      '#prefix' => '<div class="columns medium-3 small-12">',
      '#suffix' =>'</div>',
      '#type' => 'submit',
      '#attributes' => ['class' => ['subscribe-submit']],
      '#value' => t('Sign Up'),
      '#id' => 'submit-newsletter',
    ];

    if ($js === 'ajax') {
      $form['#prefix'] = '<div id="' . $this->getFormId() . '-ajax-wrapper">';
      $form['#suffix'] = '</div>';
      $form['form']['submit']['#submit'] = [];
      $form['form']['submit']['#ajax'] = [
        'callback' => '::submitForm',
        'event' => 'click',
      ];
      $form['#attached']['library'][] = 'core/drupal.dialog.ajax';
      $form['#attached']['library'][] = 'core/drupalSettings';
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Validate the email address.
    $address = $form_state->getValue('email');

    if (!$this->emailValidator->isValid($address)) {
      $form_state->setErrorByName('email', $this->t('%address is an invalid email address.', [
        '%address' => $address,
      ]));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $build_info = $form_state->getBuildInfo();
    $ajax_submission = !empty($build_info['args']) && in_array('ajax', $build_info['args']);
    $errors = $form_state->hasAnyErrors();

    if ($ajax_submission) {
      $response = new AjaxResponse();

      if ($errors) {
        unset($form['#prefix'], $form['#suffix']);
        $form['status_messages'] = [
          '#type' => 'status_messages',
          '#weight' => -10,
        ];
        $response->addCommand(new HtmlCommand('#' . $this->getFormId() . '-ajax-wrapper', $form));
      }
      else {
        $this->realSubmit($form, $form_state);
        if (!empty($this->message)) {
          $response->setAttachments([
            'drupalSettings' => ['obio' => ['modal' =>$this->message]],
          ]);
        }
        $response->addCommand(new CloseModalDialogCommand());
      }

      return $response;
    }

    $this->realSubmit($form, $form_state);
    if (!empty($this->message)) {
      $_SESSION['obio_modal'] = $this->message;
    }
  }

  /**
   * The real submit handler.
   *
   * Makes form submit actions happen only once.
   * Since the original callback has to work both ajax and non-js submittions,
   * and because of that ::submitForm fired as ajax callback and after that as
   * the form's default submit handler, this function ensures that email sending
   * (and any further actions added in the future) will be fired only once.
   *
   * @param array $form
   *   The form array
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function realSubmit(array $form, FormStateInterface $form_state) {
    if ($this->submitted) {
      return;
    }
    // Send an email message to the given address.
    $address = $form_state->getValue('email');
    /** @var \Drupal\Core\Mail\MailManager $mail_manager */
    $mail_manager = \Drupal::service('plugin.manager.mail');
    $message = $mail_manager->mail('dfs_obio', 'sign-up', $address, LanguageInterface::LANGCODE_NOT_APPLICABLE);

    // Check for success.
    if ($message['result']) {
      $modal_message = $this->t('Thanks for signing up! An email confirmation has been sent to @address', [
          '@address' => $address,
        ]);
      $moduleHandler = \Drupal::service('module_handler');

      if ($moduleHandler->moduleExists('dfs_obio_commerce_individual_product_search')) {
        $link = Link::fromTextAndUrl('View All Products >', Url::fromUri('internal:/shop/products'))->toString();
        $modal_message = $this->t('Thanks for signing up! An email confirmation has been sent to @address. <p> Meanwhile, browse our products </p> @link ', [
            '@address' => $address,
            '@link' => $link,
          ]);
      }

      if (empty($this->message)) {
        $this->message = $modal_message;
      }
    }

    $this->submitted = TRUE;
  }

}
