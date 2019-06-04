<?php

namespace Drupal\as_lift\Commands;

use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use Drush\Commands\DrushCommands;


/**
 * Class ASLiftCommands Drush 9 port.
 */
class ASLiftCommands extends DrushCommands {

    /**
     * Initialize Lift.
     *
     * @command as_lift:init
     * @option purge-content clear content from Acquia Content Hub
     * @validate-module-enabled as_lift
     * @aliases asl
     *
     * @usage drush as_lift:init <customer> <api_key> <secret_key> -l https://mysite.com
     *    Initialize Lift.
     * @usage drush as_lift:init <customer> <api_key> <secret_key> -l https://mysite.com --purge-content
     *    Initialize Lift and purge existing content.
     *
     * @param string $url
     *   The URL of your Drupal site.
     * @param string $customer
     *   The Account ID of your Lift subscription.
     * @param string $api_key
     *   The API Key of your administrative user.
     * @param string $secret_key
     *   The API Secret Key of your administrative user.
     * @param string|NULL $customer_site
     *   (Optional) The ID of your site. If omitted, a site ID will be generated.
     *
     * @return bool
     */
    public function asl($url, $customer, $api_key, $secret_key, $customer_site = NULL, $options = ['purge-content' => 1])
    {
        $this->output()->writeln([dt('%msg This may take a minute...', ['%msg' => $this->_as_lift_get_startup_message()]), '']);

        // Loads the service.
        $scenario = \Drupal::service('as_lift.scenario');

        $args = [
          'url' => $url,
          'customer' => $customer,
          'api_key' => $api_key,
          'secret_key' => $secret_key,
          'customer_site' => $customer_site,
        ];
        foreach ($args as $key => $arg) {
          $$key = \Drupal::service('as_platform_registry')->check($key, $arg);
        }

        // Check uri for HTTPS and strip trailing slash.
        $uri = isset($url) ? $url : $options['uri'];
        if (!$uri) {
            $this->logger()->error(dt('Pass a URI to bootstrap Drupal using -l or --uri'));
            return FALSE;
        }
        else if (parse_url($uri, PHP_URL_SCHEME) !== 'https') {
            $this->logger()->error(dt('The URI must use HTTPS: %uri', ['%uri' => $uri]));
            return FALSE;
        }
        $uri = rtrim($uri, '/');
        $scenario->setURI($uri);

        // Ensure scenario is installed.
        if (!$scenario->getSettings()) {
            $this->logger()->error(dt('No valid scenario installed'));
            return FALSE;
        }

        // Configure Content Hub and register client.
        if (!$origin = $this->_as_lift_contenthub_register($customer, $api_key, $secret_key)) { //, $uri
            $this->logger()->error(dt('Unable to register Acquia Content Hub client'));
            return FALSE;
        }
        $this->output()->writeln(dt('✓ registered Acquia Content Hub client: %origin', ['%origin' => $origin]), 2);

        // Configure Entity settings.
        if ($task = $scenario->configureEntities()) {
            $this->output()->writeln(dt('✓ configured entities to publish to Acquia Content Hub'), 2);
        }

        // Configure Lift and register site.
        $customer_site = $this->_as_lift_lift_configure($origin, $customer, $customer_site, $uri);
        $this->output()->writeln(dt('✓ registered Acquia Lift site: %customer_site', ['%customer_site' => $customer_site]), 2);

        // Check permissions before continuing.
        if ($task = $scenario->checkPermissions($customer, $customer_site, $api_key, $secret_key)) {
            $this->output()->writeln(dt('✓ confirmed Acquia Lift permissions'), 2);
        }
        else {
            $this->logger()->error(dt('Unable to confirm Acquia Lift permissions'));
            return FALSE;
        }

        // Check Lift segments exist (notification only).
        if ($task = $scenario->validateSegments($customer, $customer_site, $api_key, $secret_key)) {
            $this->output()->writeln(dt('✓ checked Acquia Lift segments'));
        }
        else {
            $this->output()->writeln(dt('x Acquia Lift segments need to be configured'));
        }

        // Clear Drupal cache.
        $this->output()->writeln(dt('✓ flushing Drupal caches'));
        //drupal_flush_all_caches();

        // Purge content if necessary.
        if ($options['purge-content'] == TRUE) {
            if ($task = $scenario->purgeContent()) {
                $this->output()->writeln(dt('✓ content purged from Acquia Content Hub'));
            }
        }

        // Push default content to content hub.
        if ($task = $scenario->pushContent()) {
            $this->output()->writeln(dt('✓ default content pushed to Acquia Content Hub'));
        }

        // Setup default Content Hub filters.
        if ($task = $scenario->createFilters()) {
            $this->output()->writeln(dt('✓ created filters for Acquia Content Hub'));
        }

        // Create default Lift filters.
        if ($task = $scenario->createLiftFilters()) {
            $this->output()->writeln(dt('✓ created filters for Acquia Lift'));
        }
        else {
            $this->output()->writeln(dt('x Acquia Lift filters need to be configured'), 2);
        }

        // Create lift slots/rules/blocks.
        if ($task = $scenario->createLiftRules()) {
            $this->output()->writeln(dt('✓ created rules for Acquia Lift'));
        }
        else {
            $this->output()->writeln(dt('x Acquia Lift rules need to be configured'), 2);
        }

        // Create kiosk site, if applicable.
        if (\Drupal::config('scenarios.settings')->get('scenarios.enabled') === 'dfs_obio') {
            $body = [
                [
                    'id' => 'kiosk',
                    'name' => 'Kiosk',
                    'url' => 'https://obiobostonkioskpxjnkg9d2i.devcloud.acquia-sites.com/',
                ]
            ];
            _as_lift_web_request('POST', 'customer_sites', $body, TRUE);
            $this->output()->writeln(dt('✓ registered kiosk site with Acquia Lift'));
        }

        $this->output()->writeln(['', dt('Acquia Lift setup complete.')]);
        return TRUE;
    }

    /**
     * Register Content Hub for the given URL and Customer.
     *
     * @param string $customer
     *   The Account ID of your Lift customer.
     * @param string $api_key
     *   The API Key of your administrative user.
     * @param string $secret_key
     *   The API Secret Key of your administrative user.
     * @param string $url
     *   The URL of your Drupal site, required for webhook callback.
     *
     * @return string|bool $origin
     *   The registered origin UUID, or FALSE if there was an error.
     */
    private function _as_lift_contenthub_register($customer, $api_key, $secret_key, $url = NULL) {
        $config_factory = \Drupal::configFactory();
        $uuid_service = \Drupal::service('uuid');

        $hostname = \Drupal::configFactory()->get('as_lift.settings.lift')->get('content_hub');
        $origin = '';
        $middleware = new MiddlewareHmacV1($api_key, $secret_key, 'V1');
        $client_name = strtolower($customer) . '_' . $uuid_service->generate();

        $contenthub_client = new ContentHub($origin, [$middleware], [
            'base_uri' => $hostname,
        ]);
        $response = $contenthub_client->register($client_name);
        if (!isset($response['uuid'])) {
            return FALSE;
        }
        $origin = $response['uuid'];
        $this->logger()->info(dt('Registered Content Hub client'));

        $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
        $config->set('origin', $origin);
        $config->set('client_name', $client_name);
        $config->set('hostname', $hostname);
        $config->set('api_key', $api_key);
        $config->set('secret_key', $secret_key);
        $config->set('rewrite_domain', '');
        $config->save(TRUE);

        drupal_flush_all_caches();

        // Register Webhook if passed valid $url
        if (!empty($url)) {
            $contenthub_client = new ContentHub($origin, [$middleware], [
                'base_uri' => $hostname,
            ]);
            $webhook_url = $url . '/acquia-contenthub/webhook';
            $contenthub_settings = $contenthub_client->getSettings();

            // Remove old instances of the webhook, if they exist, and add a new webhook.
            if (!empty($contenthub_settings['webhooks'])) {
                foreach ($contenthub_settings['webhooks'] as $webhook) {
                    if ($webhook['url'] === $webhook_url) {
                        $contenthub_client->deleteWebhook($webhook['uuid']);
                    }
                }
            }
            try {
                $response = $contenthub_client->addWebhook($webhook_url);
                $this->logger()->info(dt('Added Content Hub Webhook'));
                $config->set('webhook_uuid', $response['uuid']);
                $config->set('webhook_url', $response['url']);
                $config->save(TRUE);
            }
            catch (RequestException $e) {
                $response = $e->getResponse();
                $this->logger()->error($response->getStatusCode() . ' error when registering the Content Hub webhook: ' . $response->getBody());
                $this->logger()->error('Please visit ' . $url . '/admin/config/services/acquia-contenthub/webhooks' . ' and manually register the webhook, then re-run this script.');
                return FALSE;
            }
        }

        return $origin;
    }

    /**
     * Configures the Acquia Lift module.
     *
     * @param string $origin
     *   The content hub origin UUID.
     * @param string $customer
     *   The Account ID of your Lift customer.
     * @param string|NULL &$customer_site
     *   (Optional) The ID of your site.
     * @param string $url
     *   The URL of your Drupal site.
     */
    private function _as_lift_lift_configure($origin, $customer, &$customer_site, $url) {
        $urls = \Drupal::configFactory()->get('as_lift.settings.lift');
        $config = \Drupal::configFactory()->getEditable('acquia_lift.settings');

        $data = $config->getRawData();
        $data['credential'] = [
            'account_id' => $customer,
            'site_id' => $customer_site,
            'assets_url' => $urls->get('assets'),
            'decision_api_url' => $urls->get('decision_api'),
            'oauth_url' => $urls->get('oauth'),
            'user_access' => 'isBetaTester',
        ];
        if (drush_get_option('restrict-content', TRUE)) {
            $data['credential']['content_origin'] = $origin;
        }
        $data['field_mappings']['persona'] = 'field_tags';
        $data['field_mappings']['content_keywords'] = 'field_tags';
        $data['advanced']['content_replacement_mode'] = 'trusted';
        $data['visibility']['path_patterns'] .= "\n/entity-browser/*";
        $data['identity'] = [
            'capture_identity' => FALSE, // This is the default value for this setting.
            'identity_parameter' => 'identity',
            'identity_type_parameter' => 'identityType',
            'default_identity_type' => 'tracking',
        ];
        $config->setData($data);
        $config->save(TRUE);
        $this->logger()->info(dt('Configured Lift'));

        // Create an existing Customer Site if one is not provided.
        if (!$customer_site) {
            $url_host = parse_url($url, PHP_URL_HOST);
            $machine_name = preg_replace('@[^a-z0-9_]+@', 'x', $url_host);
            $customer_site = substr($machine_name, 0, 20);
            $name = substr($machine_name, 0, 50);
            $data['credential']['site_id'] = $customer_site;
            $config->setData($data);
            $config->save(TRUE);

            // Create a new customer site.
            $body = [
                [
                    'id' => $customer_site,
                    'name' => $name,
                    'url' => $url,
                ]
            ];
            $result = _as_lift_web_request('POST', 'customer_sites', $body, TRUE);
            $result = reset($result);
            if ($result['status'] === 'FAILURE') {
                $this->logger()->error(dt('Errors when creating customer site.'));
                foreach ($result['errors'] as $error) {
                    $this->logger()->error($error['message']);
                }
            }
            else {
                $this->logger()->info(dt('registered new customer site "@site".', ['@site' => $customer_site]));
            }
        }

        return $customer_site;
    }

    /**
     * Returns a random startup message. For fun.
     */
    private function _as_lift_get_startup_message() {
        $str = [
            'Charging the dilithium crystals.',
            'Defluxing the flux capacitor.',
            'Deicing the Northern Westeros Wall.',
            'Nuking from orbit to be sure.',
            'Excavating the Mines of Moria.',
        ];
        return  $str[array_rand($str)];
    }

}
