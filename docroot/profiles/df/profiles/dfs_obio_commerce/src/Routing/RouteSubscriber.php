<?php

namespace Drupal\dfs_obio_commerce\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Modifies some route options.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Routes which shouldn't be themed with the admin theme.
   *
   * @var array
   */
  protected $nonAdminThemedRoutes = [
    'entity.user.edit_form',
    'shortcut.set_switch',
  ];

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->nonAdminThemedRoutes as $nonAdminThemedRoute) {
      $route = $collection->get($nonAdminThemedRoute);
      if ($route) {
        $route->setOption('_admin_route', FALSE);
      }
    }
  }

}
