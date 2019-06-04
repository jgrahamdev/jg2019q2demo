<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\Block\MemberQuoteFormBlock.
 */

namespace Drupal\dfs_fin\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides the "Agent Quote Form" block.
 *
 * @Block(
 *   id = "member_quote_form",
 *   admin_label = @Translation("Member Quote Form"),
 *   context = {
 *     "user" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   },
 *   category = @Translation("Forms")
 * )
 *
 */
 class MemberQuoteFormBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilder
   */
  protected $formBuilder;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $user;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, FormBuilder $form_builder, AccountProxyInterface $user) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $form_builder;
    $this->user = $user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder'),
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Get the current user.
    $user = $this->getContextValue('user');
    // Return the form with the user.
    return [$this->formBuilder->getForm('\Drupal\dfs_fin\Form\MemberQuoteForm', $user)];
  }

}
