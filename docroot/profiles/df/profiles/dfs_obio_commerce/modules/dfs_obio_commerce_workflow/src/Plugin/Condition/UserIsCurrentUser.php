<?php

namespace Drupal\dfs_obio_commerce_workflow\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'User Is Current User' condition.
 *
 * @Condition(
 *   id = "user_is_current_user",
 *   label = @Translation("User in route context is the current user"),
 *   context = {
 *     "currentUser" = @ContextDefinition("entity:user", label = @Translation("User"))
 *   }
 * )
 */
class UserIsCurrentUser extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * Creates a new 'User Is Current User' condition instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RouteMatchInterface $route_match) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->routeMatch = $route_match;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['active'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('User in url context is the current user'),
      '#default_value' => $this->configuration['active'],
      '#description' => $this->t('Check if comparison is needed. Evaluates to TRUE if unchecked, and always to FALSE if current user is anonymous (in that case, negate checkbox will be ignored).'),
    ];
    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'active' => FALSE,
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['active'] = empty($form_state->getValue('active')) ? FALSE : TRUE;
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {
    if (empty($this->configuration['negate'])) {
      return $this->t('The user in the context is the current user');
    }
    else {
      return $this->t('The user in the context is not the current user');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // Early opt-out.
    if (empty($this->configuration['active'])) {
      // The plugin isn't used; return TRUE.
      return TRUE;
    }
    /* @var \Drupal\user\UserInterface $currentUser */
    $currentUser = $this->getContextValue('currentUser');

    if ($currentUser->id() === '0') {
      if ($this->isNegated()) {
        // This will be negated to FALSE by the condition manager.
        return TRUE;
      }
      return FALSE;
    }

    $userIdFromUrlContext = $this->routeMatch->getRawParameter('user');

    return $userIdFromUrlContext && $currentUser->id() == $userIdFromUrlContext;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    $contexts = parent::getCacheContexts();
    $contexts[] = 'user';
    $contexts[] = 'url.path';
    return $contexts;
  }

}
