<?php

namespace Drupal\dfs_obio\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\CloseModalDialogCommand;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Ajax\RedirectCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for Subscribe form.
 */
class SubscribeController extends ControllerBase {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The ModalFormExampleController constructor.
   *
   * @param \Drupal\Core\Form\FormBuilder $formBuilder
   *   The form builder.
   */
  public function __construct(FormBuilder $formBuilder) {
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('form_builder')
    );
  }

  /**
   * Delivers Subscribe Form.
   *
   * @param string $js
   *   Either 'ajax' or 'nojs'.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse|array
   *   The ajax response or a renderable array.
   */
  public function subscribeForm($js = 'nojs') {
    $subscribe_form = $this->formBuilder->getForm('Drupal\dfs_obio\Form\SubscribeForm', $js);
    // Determine whether the request is coming from AJAX or not.
    if ($js === 'ajax') {
      $response = new AjaxResponse();

      $response->addCommand(new OpenModalDialogCommand($this->title(), $subscribe_form, [
        'width' => '500',
        'resizable' => FALSE,
        'autoResize' => TRUE,
        'dialogClass' => 'subscribe-modal',
      ]));
      return $response;
    }

    return $subscribe_form;
  }

  /**
   * Returns the title of the form.
   */
  public static function title() {
    return t('Subscribe to Our Newsletter');
  }
}
