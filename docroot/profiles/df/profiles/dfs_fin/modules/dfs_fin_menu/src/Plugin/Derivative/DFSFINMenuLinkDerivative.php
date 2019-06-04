<?php

/**
 * @file
 * Contains \Drupal\dfs_fin_menu\Plugin\Derivative\DFSFINMenuLinkDerivative.
 */

namespace Drupal\dfs_fin_menu\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\node\Entity\Node;

class DFSFINMenuLinkDerivative extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = array();

    // Get all nodes of type insurance_product and page.
    $nodeQuery = \Drupal::entityQuery('node');
    $group = $nodeQuery->orConditionGroup()
      ->condition('type', 'insurance_product')
      ->condition('type', 'page');
    $nodeQuery->condition($group)
      ->condition('status', TRUE);
    $ids = $nodeQuery->execute();
    $ids = array_values($ids);

    // Map node bundles to related menu links.
    $parent_map = [
      'insurance_product' => 'dfs_fin.products',
      'page' => 'dfs_fin.our_company',
    ];

    // Create a list of known titles to ignore.
    $title_blacklist = [
      'Agent License'
    ];

    $nodes = Node::loadMultiple($ids);

    /** @var \Drupal\node\Entity\Node $node */
    foreach($nodes as $node) {
      $title = $node->get('title');
      if (!in_array($title->getString(), $title_blacklist)) {
        $links['dfs_fin_menu_menulink_' . $node->id()] = [
            'title' => str_replace(' Insurance', '', $title->getString()),
            'menu_name' => 'main',
            'parent' => $parent_map[$node->bundle()],
            'route_name' => 'entity.node.canonical',
            'route_parameters' => [
              'node' => $node->id(),
            ],
          ] + $base_plugin_definition;
      }
    }

    return $links;
  }
}
