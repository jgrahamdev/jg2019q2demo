<?php

namespace Drupal\dfs_obio_commerce_cart\Plugin\Block;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Cart Submit Button' block.
 *
 * @Block(
 *   id = "obio_commerce_cart_submit_button",
 *   admin_label = @Translation("Cart Submit Button"),
 *   category = @Translation("Commerce")
 * )
 */
class ObioCommerceCartSubmitButtonBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * Constructs a new Cart Page Block.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin ID for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\commerce_cart\CartProviderInterface $cartProvider
   *   The cart provider.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, CartProviderInterface $cartProvider) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->cartProvider = $cartProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('commerce_cart.cart_provider')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $cacheableMetadata = new CacheableMetadata();
    $cacheableMetadata->addCacheContexts(['user', 'session']);

    $carts = $this->cartProvider->getCarts();
    $carts = array_filter($carts, function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      return $cart->hasItems();
    });
    if (!empty($carts)) {
      $build['checkout'] = [
        '#type' => 'html_tag',
        '#tag' => 'a',
        '#value' => $this->t('Checkout'),
        '#attributes' => [
          'href' => '#',
          'class' => [
            'button',
            'obio-checkout',
            'js-obio-checkout-button',
            'expanded',
          ],
        ],
      ];
      $build['#attached']['library'][] = 'dfs_obio_commerce_cart/cart-submit-button';
    }

    $build['#cache'] = [
      'contexts' => $cacheableMetadata->getCacheContexts(),
      'tags' => $cacheableMetadata->getCacheTags(),
      'max-age' => $cacheableMetadata->getCacheMaxAge(),
    ];

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['cart']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $cacheTags = parent::getCacheTags();
    $cartCacheTags = [];

    /** @var \Drupal\commerce_order\Entity\OrderInterface[] $carts */
    $carts = $this->cartProvider->getCarts();
    foreach ($carts as $cart) {
      // Add tags for all carts regardless items or cart flag.
      $cartCacheTags = Cache::mergeTags($cartCacheTags, $cart->getCacheTags());
    }
    return Cache::mergeTags($cacheTags, $cartCacheTags);
  }

}
