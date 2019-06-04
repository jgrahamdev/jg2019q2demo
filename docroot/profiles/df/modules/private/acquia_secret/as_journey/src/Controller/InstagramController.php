<?php

namespace Drupal\as_journey\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * Instagram demo controller.
 */
class InstagramController extends ControllerBase {

  /**
   * Presents a fake Instagram page with Lift enabled.
   */
  public function demo() {
    $as_lift_config = $this->config('as_lift.settings.lift');
    $lift_config = $this->config('acquia_lift.settings');
    $assets_url = strip_tags($as_lift_config->get('assets'));
    $account_id = $lift_config->get('credential.account_id');
    $site_id = $lift_config->get('credential.site_id');
    if ($account_id && $site_id) {
      $json = json_encode([
        'account_id' => $account_id,
        'site_id' => $site_id,
        'liftAssetsURL' => $assets_url,
        'liftDecisionAPIURL' => $as_lift_config->get('decision_api'),
        'authEndpoint' => $as_lift_config->get('oauth'),
        'contentReplacementMode' => 'trusted',
      ], JSON_PRETTY_PRINT);
      $replacement = <<<EOD
<script>
window.AcquiaLift = $json;
</script>
<script src="$assets_url/lift.js"></script>
EOD;
    }
    else {
      $replacement = '<script>alert("Please configure Lift then re-visit this page.")</script>';
    }
    $html = str_replace('<!--LIFT_REPLACEME-->', $replacement, file_get_contents(__DIR__ . '/../../pages/instagram.html'));
    $response = new HtmlResponse();
    $response->setContent($html);
    $response->addCacheableDependency((new CacheableMetadata())->setCacheMaxAge(0));
    return $response;
  }

}
