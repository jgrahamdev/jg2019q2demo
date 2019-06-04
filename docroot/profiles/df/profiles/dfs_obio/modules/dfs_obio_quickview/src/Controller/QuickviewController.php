<?php

namespace Drupal\dfs_obio_quickview\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Ajax\RedirectCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for Quick View routes.
 *
 * @TODO Clean up.
 */
class QuickviewController extends ControllerBase {

  /**
   * Callback for link example.
   *
   * Takes different logic paths based on whether Javascript was enabled.
   * If $type == 'ajax', it tells this function that ajax.js has rewritten
   * the URL and thus we are doing an AJAX and can return an array of commands.
   *
   * @param string $entityType
   *   Type of the entity.
   * @param string $entityId
   *   Id of the entity.
   * @param string $nojs
   *   Either 'ajax' or 'nojs'.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The ajax response. Throws not found exception if page couldn't be loaded.
   */
  public function quickviewCallback($entityType, $entityId, $nojs = 'nojs') {
    // Determine whether the request is coming from AJAX or not.
    if ($nojs === 'ajax') {
      $entity = NULL;
      $content = [];

      try {
        $entityStorage = $this->entityTypeManager()->getStorage($entityType);

        try {
          $entity = $entityStorage->load($entityId);
          $viewBuilder = $this->entityTypeManager()->getViewBuilder($entityType);

          if ($entity && !empty($viewBuilder)) {
            $bundleEntityTypeLabel = $entity->getEntityType()->getBundleEntityType();
            $bundleEntityType = $this->entityTypeManager()->getStorage($bundleEntityTypeLabel)->load($entity->bundle());
            $view_mode = $bundleEntityType->getThirdPartySetting('dfs_obio_quickview', 'view_mode', 'default');
            $language = $this->languageManager()->getCurrentLanguage();
            $content = $viewBuilder->view($entity, $view_mode, $language->getId());
          }
        }
        // Entity does not exist.
        catch (\Exception $e) {
        }
      }
      // No storage for entityType.
      catch (\Exception $e) {
      }

      $response = new AjaxResponse();

      if ($entity && $content) {
        $modalConfig = $this->config('dfs_obio_quickview.settings')->get('modal') ?: [];
        $modalConfig['autoResize'] = !$modalConfig['resizable'];
        $modal_content = drupal_render_root($content);
        $content['#attached']['library'][] = 'dfs_obio_quickview/main';
        $dialog_classes = [
          'quickview-modal',
          'quickview-modal--' . ($modalConfig['resizable'] ? 'resizable' : 'fixed'),
        ];

        $response->addCommand(new OpenModalDialogCommand($entity->label(), $modal_content, $modalConfig + [
          'dialogClass' => implode(' ', $dialog_classes),
        ]));
        $response->setAttachments($content['#attached']);
      }
      else {
        // @TODO Redirect to a better destination.
        $url = Url::fromRoute('<front>', ['absolute' => TRUE]);
        $response->addCommand(new RedirectCommand($url->toString()));
      }

      return $response;
    }

    try {
      $entityStorage = $this->entityTypeManager()->getStorage($entityType);

      try {
        $entity = $entityStorage->load($entityId);

        if ($entity) {
          $canonical = $entity->toUrl();
          $routeName = $canonical->getRouteName();
          $routeParams = $canonical->getRouteParameters();
          $routeOptions = $canonical->getOptions();
          return $this->redirect($routeName, $routeParams, $routeOptions, 307);
        }
      }
      // Entity doesn't exist.
      catch (\Exception $e) {
      }
    }
    // No storage for entityType.
    catch (\Exception $e) {
    }

    throw new NotFoundHttpException();
  }

}
