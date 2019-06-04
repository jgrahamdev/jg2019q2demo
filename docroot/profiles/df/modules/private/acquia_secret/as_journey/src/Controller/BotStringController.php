<?php

namespace Drupal\as_journey\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigFactoryInterface;

/**
 * Class BotStringController.
 */
class BotStringController extends ControllerBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new BotStringController object.
   */
  public function __construct(RequestStack $request_stack, ConfigFactoryInterface $config_factory) {
    $this->requestStack = $request_stack;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.factory')
    );
  }

  /**
   * Output.
   *
   * @return array
   *   Render array with bot activation string.
   */
  public function output() {
    // Use tracking to get email and first name based on user id.
    $tracking = $this->configFactory->get('as_tracking.settings');
    $email = $tracking->get('user_id');
    $first_name = $this->extractName($email);
    // Use the request object to get our url.
    $url = $this->requestStack->getCurrentRequest()->getSchemeAndHttpHost();
    $site_url = preg_replace("/^http:/i", "https:", $url);
    // Use Content Hub and Lift settings to return arguments.
    $content_hub = $this->configFactory->get('acquia_contenthub.admin_settings');
    $lift = $this->configFactory->get('acquia_lift.settings');
    $lift_account = $lift->get('credential.account_id');
    $lift_site = $lift->get('credential.site_id');
    $ch_api = $content_hub->get('api_key');
    $ch_secret = $content_hub->get('secret_key');
    // Check our variables and set a bot string accordingly.
    if (!empty($lift_account) && !empty($lift_site) && !empty($ch_api) && !empty($ch_secret)) {
      $markup = $this->t(
        'activate @first_name @email @site_url @lift_account_id @lift_site_id @content_hub_api_key @content_hub_secret_key', [
          '@first_name' => $first_name,
          '@email' => $email,
          '@site_url' => $site_url,
          '@lift_account_id' => $lift_account,
          '@lift_site_id' => $lift_site,
          '@content_hub_api_key' => $ch_api,
          '@content_hub_secret_key' => $ch_secret
        ]
      );
    }
    else {
      $markup = $this->t('You must first register with Acquia Lift and Content Hub to see a bot string.');
    }
    // Drupal's render array is returned.
    return ['#type' => 'markup', '#markup' => $markup];
  }

  /**
   * @param string $email
   *
   * @return string
   */
  private function extractName(string $email) {
    $name = stristr($email, "@", true);
    if (strpos($name, '.') !== FALSE) {
      $name = stristr($name, ".", TRUE);
    }
    return ucwords($name);
  }
}
