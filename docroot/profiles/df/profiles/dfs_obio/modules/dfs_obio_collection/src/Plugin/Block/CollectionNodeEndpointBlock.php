<?php

namespace Drupal\dfs_obio_collection\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;

/**
 * Provides a 'CollectionNodeEndpointBlock' block.
 *
 * @Block(
 *  id = "collection_node_endpoint_block",
 *  admin_label = @Translation("Collection node endpoint block"),
 *  category = "Lists",
 * )
 */
class CollectionNodeEndpointBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;

  /**
   * CollectionNodeEndpointBlock constructor.
   * @param array $configuration
   * @param string $plugin_id
   * @param mixed $plugin_definition
   * @param \GuzzleHttp\Client $http_client
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Client $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClient = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = array();
    global $base_url;
    header('Content-Type: application/json');
    $json_output = (string) $this->httpClient->get($base_url . '/api/node/collection')->getBody();
    $json_pretty = json_encode(json_decode($json_output), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    $json_indented_by_2 = preg_replace('/^(  +?)\\1(?=[^ ])/m', '$1', $json_pretty);
    $build['collection_node_endpoint_block']['#markup'] = '<a data-toggle="language-json" class="button small">Expand Code</a> <div id="language-json" class="api-demo" data-toggler=".expanded"><pre><code class="language-json">' . $json_indented_by_2 . '</code></pre></div>';
    $build['collection_node_endpoint_block']['#attached']['library'][] = 'dfs_obio_collection/main';
    return $build;
  }

}
