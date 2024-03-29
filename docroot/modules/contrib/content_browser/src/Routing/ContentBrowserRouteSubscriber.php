<?php

/**
 * @file
 * Contains \Drupal\content_browser\Routing\ContentBrowserRouteSubscriber.
 */

namespace Drupal\content_browser\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Use the frontend theme conditionally for our entity browser.
 */
class ContentBrowserRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    $config = \Drupal::config('content_browser.settings');
    if ($config->get('use_frontend_theme') !== FALSE) {
      foreach (['entity_browser.browse_content', 'entity_browser.browse_content_iframe'] as $name) {
        if ($route = $collection->get($name)) {
          $route->setOption('_admin_route', FALSE);
        }
      }
    }
  }

}
