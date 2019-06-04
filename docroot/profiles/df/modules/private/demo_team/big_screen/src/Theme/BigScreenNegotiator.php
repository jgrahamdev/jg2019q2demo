<?php

namespace Drupal\big_screen\Theme;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

class BigScreenNegotiator implements ThemeNegotiatorInterface, ContainerInjectionInterface {

  /**
   * @var ConfigFactory $config_factory
   */
  protected $configFactory;

  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $routes = [
      'big_screen.view_output',
      'big_screen.preview_output'
    ];
    // Conditionally add the canonical node route.
    if ($node = $route_match->getParameter('node')) {
      if ($node->always_big->value) {
        $routes[] = 'entity.node.canonical';
        $routes[] = 'entity.node.latest_version';
      }
    }
    return in_array($route_match->getRouteName(), $routes);
  }

  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    $config = $this->configFactory->get('big_screen.settings');
    return $config->get('theme');
  }

}
