<?php

namespace Drupal\dfs_obio_commerce_cart\Plugin\Block;

use Drupal\commerce_cart\CartProviderInterface;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Cart page' block.
 *
 * @Block(
 *   id = "obio_commerce_cart_page",
 *   admin_label = @Translation("Cart page as block"),
 *   category = @Translation("Commerce")
 * )
 */
class ObioCommerceCartPageBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

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
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, CartProviderInterface $cartProvider, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->cartProvider = $cartProvider;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('commerce_cart.cart_provider'),
      $container->get('entity_type.manager')
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
      $cartViews = $this->getCartViews($carts);
      foreach ($carts as $cartId => $cart) {
        $build[$cartId] = [
          '#prefix' => '<div class="cart cart-form">',
          '#suffix' => '</div>',
          '#type' => 'view',
          '#name' => $cartViews[$cartId],
          '#arguments' => [$cartId],
          '#embed' => TRUE,
        ];
        $cacheableMetadata->addCacheableDependency($cart);
      }
    }
    else {
      $build['empty'] = [
        '#theme' => 'commerce_cart_empty_page',
      ];
    }
    $build['#cache'] = [
      'contexts' => $cacheableMetadata->getCacheContexts(),
      'tags' => $cacheableMetadata->getCacheTags(),
      'max-age' => $cacheableMetadata->getCacheMaxAge(),
    ];

    return $build;
  }

  /**
   * Gets the cart views for each cart.
   *
   * @param \Drupal\commerce_order\Entity\OrderInterface[] $carts
   *   The cart orders.
   *
   * @return array
   *   An array of view ids keyed by cart order ID.
   */
  protected function getCartViews(array $carts) {
    $orderTypeIds = array_map(function ($cart) {
      /** @var \Drupal\commerce_order\Entity\OrderInterface $cart */
      return $cart->bundle();
    }, $carts);
    $orderTypeStorage = $this->entityTypeManager->getStorage('commerce_order_type');
    $orderTypes = $orderTypeStorage->loadMultiple(array_unique($orderTypeIds));
    $cartViews = [];
    foreach ($orderTypeIds as $cartId => $orderTypeId) {
      /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $orderType */
      $orderType = $orderTypes[$orderTypeId];
      $cartViews[$cartId] = $orderType->getThirdPartySetting('commerce_cart', 'cart_form_view', 'commerce_cart_form');
    }

    return $cartViews;
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
