<?php
/**
 * @file
 * Drush (< 9) integration.
 */

use Drush\Log\LogLevel;
use Acquia\ContentHubClient\ContentHub;
use Acquia\ContentHubClient\Middleware\MiddlewareHmacV1;
use Acquia\LiftClient\Lift;
use Acquia\LiftClient\Entity\Content;
use Acquia\LiftClient\Entity\Rule;
use Acquia\LiftClient\Entity\Slot;
use Acquia\LiftClient\Entity\ViewMode;
use Acquia\LiftClient\Entity\Visibility;
use Drupal\node\Entity\Node;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;

/**
 * Implements hook_drush_command().
 */
function as_lift_drush_command() {
  $items = [];

  $items['as-lift'] = [
    'description' => 'Configures Acquia Lift and Content Hub for the given Lift Customer site.',
    'arguments' => [
      'url' => 'The URL of your Drupal site. Must use HTTPS.',
      'customer' => 'The ID of your Lift customer.',
      'api_key' => 'The API Key of your administrative user',
      'secret_key' => 'The API Secret Key of your administrative user',
      'customer_site' => 'The Lift Customer Site. If omitted a Custom Site is generated for you.',
    ],
    'options' => [
      'restrict-content' => [
        'description' => 'Whether or not content should be restricted to this origin. Defaults to 1.',
        'example_value' => '1 or 0',
      ],
      'purge-content' => [
        'description' => 'Whether or not existing Content Hub content should be purged. Defaults to 1.',
        'example_value' => '1 or 0',
      ],
    ],
    'examples' => [
      'asl https://d8df.demo.acsitefactory.com BAYSIDE 5aMWaZh3R3 w0AhTH1sizLoN6  -l https://d8df.demo.acsitefactory.com' => 'Configures Lift and Content Hub for the BAYSIDE customer, with a dynamic customer site.',
    ],
    'drupal dependencies' => ['as_lift'],
    'aliases' => ['asl'],
  ];

  $items['lift-purge'] = [
    'description' => 'Purges a single person from Acquia Lift.',
    'arguments' => [
      'identity_source' => 'The identity source of the person. "tracking" is the correct source when using a tracking ID.',
      'identity' => 'The identity of the person.',
    ],
    'examples' => [
      'lift-purge tracking 69mVnbaaSTKiy0xrSCGoMz' => 'Purges the person with the tracking ID "69mVnbaaSTKiy0xrSCGoMz" from Lift.',
    ],
    'drupal dependencies' => ['as_lift'],
  ];

  $items['lift-check'] = [
    'description' => 'Checks the given Lift Credentials to ensure they are correct.',
    'arguments' => [
      'customer' => 'The ID of your Lift customer.',
      'customer_site' => 'The Lift Customer Site.',
      'api_key' => 'The API Key of your administrative user',
      'secret_key' => 'The API Secret Key of your administrative user',
    ],
    'examples' => [
      'lift-check BAYSIDE 5aMWaZh3R3 w0AhTH1sizLoN6 baysidesiteUkBn3r',
    ],
    'drupal dependencies' => ['as_lift'],
  ];

  return $items;
}

/**
 * Configures Acquia Lift and Content Hub for the given Lift Customer site.
 *
 * @param string $identity_source
 *   The identity source of the person.
 * @param string $identity
 *   The identity of the person.
 */
function drush_as_lift_lift_check($customer, $customer_site, $api_key, $secret_key) {
  if (_as_lift_check_lift_permissions($customer, $customer_site, $api_key, $secret_key)) {
    drush_log('Everything looks good!', LogLevel::SUCCESS);
  }
  else {
    drush_log('Error encountered, credentials provided are possibly incorrect.', LogLevel::ERROR);
  }
}

/**
 * Configures Acquia Lift and Content Hub for the given Lift Customer site.
 *
 * @param string $identity_source
 *   The identity source of the person.
 * @param string $identity
 *   The identity of the person.
 */
function drush_as_lift_lift_purge($identity_source, $identity) {
  if ($body = _as_lift_create_event($identity, $identity_source, [], 'purgePerson', 'web')) {
    drush_log('Sent purge request to Lift.', LogLevel::SUCCESS);
  }
  else {
    drush_log('Error encountered when purging, is Lift set up for this site?', LogLevel::ERROR);
  }
}

/**
 * Configures Acquia Lift and Content Hub for the given Lift Customer site.
 *
 * @param string $url
 *   The URL of your Drupal site.
 * @param string $customer
 *   The ID of your Lift customer.
 * @param string $api_key
 *   The API Key of your administrative user.
 * @param string $secret_key
 *   The API Secret Key of your administrative user.
 * @param string|NULL $customer_site
 *   (Optional) The Lift Customer Site.
 *
 * @return bool
 */
function drush_as_lift($url = NULL, $customer = NULL, $api_key = NULL, $secret_key = NULL, $customer_site = NULL) {
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

  if (parse_url($url, PHP_URL_SCHEME) !== 'https') {
    drush_log(dt('The provided URL must use HTTPS'), LogLevel::ERROR);
    return FALSE;
  }

  $uri = drush_get_option('uri');
  if (!$uri) {
    drush_log(dt('You must pass a URI to bootstrap Drupal using -l or --uri'), LogLevel::ERROR);
    return FALSE;
  }
  else if (parse_url($uri, PHP_URL_SCHEME) !== 'https') {
    drush_log(dt('The URI passed with -l or --uri must use HTTPS'), LogLevel::ERROR);
    return FALSE;
  }

  if ($uri != $url) {
    drush_log('The --uri parameter passed does not match the URL command setting. This may cause unexpected results.', LogLevel::NOTICE);
  }

  $url = rtrim($url, '/');

  // Ensure a scenario is installed.
  if (!_as_lift_get_scenario_settings()) {
    drush_log(dt('No valid scenario installed'), LogLevel::ERROR);
    return FALSE;
  }

  // Configure Content Hub.
  if (!$origin = _as_lift_contenthub_configure($url, $customer, $api_key, $secret_key)) {
    drush_log(dt('Unable to register Content Hub client'), LogLevel::ERROR);
    return FALSE;
  }

  // Configure Entity settings.
  _as_lift_contenthub_entity_configure();

  // Configure Lift.
  _as_lift_lift_configure($url, $customer, $customer_site, $origin);

  // Check permissions before continuing.
  if (!_as_lift_check_lift_permissions($customer, $customer_site, $api_key, $secret_key)) {
    return FALSE;
  }

  // Clear Drupal cache.
  drupal_flush_all_caches();

  // Purge content if necessary.
  if (drush_get_option('purge-content', TRUE)) {
    _as_lift_purge_content($customer, $customer_site, $api_key, $secret_key, $origin);
  }

  // Push default content to content hub.
  _as_lift_push_default_content($uri);

  // Setup default Content Hub filters.
  _as_lift_setup_contenthub_filters();

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
  }

  // Create lift filters.
  if (!_as_lift_create_filters($url, $origin)) {
    return FALSE;
  }

  // Create lift slots/rules/blocks.
  if (!_as_lift_create_slots($url, $origin)) {
    return FALSE;
  }

  \Drupal::logger('as_lift')->info('Successfully set up Lift.');

  return TRUE;
}

/**
 * Purges content from Content Hub the given customer.
 *
 * @param string $customer
 *   The ID of your Lift customer.
 * @param string $customer_site
 *   The Lift Customer Site.
 * @param string $api_key
 *   The API Key of your administrative user.
 * @param string $secret_key
 *   The API Secret Key of your administrative user.
 * @param string $origin
 *   The content hub origin UUID.
 */
function _as_lift_purge_content($customer, $customer_site, $api_key, $secret_key, $origin) {
  $base_uri = \Drupal::configFactory()->get('as_lift.settings.lift')->get('content_hub');
  $middleware = new MiddlewareHmacV1($api_key, $secret_key, 'V1');
  $contenthub_client = new ContentHub($origin, [$middleware], [
    'base_uri' => $base_uri,
  ]);

  $base_url = \Drupal::configFactory()->get('as_lift.settings.lift')->get('decision_api');
  $lift_client = new Lift($customer, $customer_site, $api_key, $secret_key, [
    'base_url' => $base_url,
  ]);
  drush_print('Searching for content to delete...');
  $entities = $contenthub_client->listEntities();
  $uuids = [];
  if (empty($entities['data'])) {
    drush_log('There\'s nothing in Content Hub to be purged.', LogLevel::SUCCESS);
    return;
  }
  foreach ($entities['data'] as $entity) {
    $entity = $contenthub_client->readEntity($entity['uuid']);
    switch ($entity->getType()) {
      case 'node':
      case 'commerce_product':
        $label = $entity->getAttribute('title')['value']['en'];
        break;
      case 'block_content':
        $label = $entity->getAttribute('info')['value']['en'];
        break;
      case 'taxonomy_term':
      case 'media':
        $label = $entity->getAttribute('name')['value']['en'];
        break;
      default:
        $label = 'Untitled';
        break;
    }
    // Node titles in Content Hub are usually not arrays, for some reason.
    if (is_array($label)) {
      $label = $label[0];
    }
    $uuid = $entity->getUuid();
    $type = $entity->getType();
    $uuids[] = $uuid;
    drush_print("- $label ($type:$uuid)", 1);
  }

  // List all Lift slots/rules.
  $slot_manager = $lift_client->getSlotManager();
  $rule_manager = $lift_client->getRuleManager();
  $slots = $slot_manager->query();
  $rules = $rule_manager->query();
  foreach ($slots as $slot) {
    drush_print('- Lift Slot: '. $slot->getLabel(), 1);
  }
  foreach ($rules as $rule) {
    drush_print('- Lift Rule: '. $rule->getLabel(), 1);
  }

  drush_print();
  $action = drush_prompt('Are you sure you want to delete the content above from Content Hub and Lift? (y/n)', 'n');
  if ($action === 'y') {
    foreach ($uuids as $uuid) {
      if (!$contenthub_client->deleteEntity($uuid)) {
        drush_log("Failed to delete $uuid from Content Hub.", LogLevel::ERROR);
      }
    }
    foreach ($slots as $slot) {
      if (!$slot_manager->delete($slot->getId())) {
        drush_log('Failed to delete Lift Slot: ' . $slot->getId(), LogLevel::ERROR);
      }
    }
    foreach ($rules as $rule) {
      if (!$rule_manager->delete($rule->getId())) {
        drush_log('Failed to delete Lift Rule: ' . $rule->getId(), LogLevel::ERROR);
      }
    }
    drush_log('Finished purging content from Content Hub and Lift.', LogLevel::SUCCESS);
  }
  else {
    drush_user_abort();
  }
}

/**
 * Verifies that the given credentials have appropriate permissions.
 *
 * @param string $customer
 *   The ID of your Lift customer.
 * @param string $customer_site
 *   The Lift Customer Site.
 * @param string $api_key
 *   The API Key of your administrative user.
 * @param string $secret_key
 *   The API Secret Key of your administrative user.
 *
 * @return bool
 *   Whether or not the given credentials have appropriate permissions.
 */
function _as_lift_check_lift_permissions($customer, $customer_site, $api_key, $secret_key) {
  // Test that Lift is reachable.
  $base_url = \Drupal::configFactory()->get('as_lift.settings.lift')->get('decision_api');
  $lift_client = new Lift($customer, $customer_site, $api_key, $secret_key, [
    'base_url' => $base_url,
  ]);

  try {
    $ping = $lift_client->ping();
  } catch (Exception $e) {
    drush_log(dt('Caught exception: %error', ['%error' => $e->getMessage()]), LogLevel::ERROR);
    return FALSE;
  }

  if (!is_array($ping) || !isset($ping['message']) || !$ping['message']) {
    drush_log(dt('Unable to reach Lift with the given credentials'), LogLevel::ERROR);
    return FALSE;
  }
  // Test that the Lift user can access slots.
  $request = new Request('GET', '/slots');
  try {
    $slot_request = $lift_client->getSlotManager()->getClient()->send($request);
  }
  catch(Exception $e) {
    drush_log(dt('Caught exception: %error', ['%error' => $e->getMessage()]), LogLevel::ERROR);
    return FALSE;
  }
  if ($slot_request->getStatusCode() !== 200) {
    drush_log(dt('Provided Lift user does not have permissions to access slots'), LogLevel::ERROR);
    return FALSE;
  }
  // Test that the required segments for the current scenario exist.
  $segment_manager = $lift_client->getSegmentManager();
  $segment_ids = [];
  foreach ($segment_manager->query() as $segment) {
    $segment_ids[] = $segment->getId();
  }
  $slots = _as_lift_get_scenario_settings()->get('slots');
  foreach ($slots as $slot) {
    foreach ($slot['rules'] as $rule) {
      if (!empty($rule['segment']) && !in_array($rule['segment'], $segment_ids)) {
        drush_log(dt('Lift customer is missing the "' . $rule['segment'] . '" segment.'), LogLevel::ERROR);
        return FALSE;
      }
    }
  }

  return TRUE;
}

/**
 * Configures Content Hub for the given URL and Customer.
 *
 * @param string $url
 *   The URL of your Drupal site.
 * @param string $customer
 *   The ID of your Lift customer.
 * @param string $api_key
 *   The API Key of your administrative user.
 * @param string $secret_key
 *   The API Secret Key of your administrative user.
 *
 * @return string|bool $origin
 *   The registered origin UUID, or FALSE if there was an error.
 */
function _as_lift_contenthub_configure($url, $customer, $api_key, $secret_key) {
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
  drush_log(dt('Registered Content Hub client'), LogLevel::OK);

  $config = $config_factory->getEditable('acquia_contenthub.admin_settings');
  $config->set('origin', $origin);
  $config->set('client_name', $client_name);
  $config->set('hostname', $hostname);
  $config->set('api_key', $api_key);
  $config->set('secret_key', $secret_key);
  $config->set('rewrite_domain', '');
  $config->save(TRUE);

  drupal_flush_all_caches();

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
    drush_log(dt('Added Content Hub Webhook'), LogLevel::OK);
    $config->set('webhook_uuid', $response['uuid']);
    $config->set('webhook_url', $response['url']);
    $config->save(TRUE);
  }
  catch (RequestException $e) {
    $response = $e->getResponse();
    drush_log($response->getStatusCode() . ' error when registering the Content Hub webhook: ' . $response->getBody(), LogLevel::ERROR);
    drush_log('Please visit ' . $url . '/admin/config/services/acquia-contenthub/webhooks' . ' and manually register the webhook, then re-run this script.', LogLevel::ERROR);
    return FALSE;
  }

  return $origin;
}

/**
 * Configures Content Hub entity settings for the current scenario.
 */
function _as_lift_contenthub_entity_configure() {
  $entity_config_map = _as_lift_get_scenario_settings()->get('entity_config');
  if (!empty($entity_config_map)) {
    $content_hub_entity_config = \Drupal::entityTypeManager()
      ->getStorage('acquia_contenthub_entity_config');
    foreach ($entity_config_map as $entity_type_id => $bundles_config) {
      /** @var \Drupal\acquia_contenthub\Entity\ContentHubEntityTypeConfig $entity_config */
      if (!$entity_config = $content_hub_entity_config->load($entity_type_id)) {
        $entity_config = $content_hub_entity_config->create([
          'id' => $entity_type_id,
        ]);
      }
      $bundles = $entity_config->getBundles();
      foreach ($bundles_config as $bundle => $bundle_config) {
        $bundles[$bundle] = $bundle_config;
      }
      $entity_config->setBundles($bundles);
      $entity_config->save();
    }
  }

  drush_log(dt('Configured Content Hub entities.'), LogLevel::OK);
}

/**
 * Configures the Acquia Lift module.
 *
 * @param string $url
 *   The URL of your Drupal site.
 * @param string $customer
 *   The ID of your Lift customer.
 * @param string|NULL &$customer_site
 *   (Optional) The Lift Customer Site.
 * @param string $origin
 *   The content hub origin UUID.
 */
function _as_lift_lift_configure($url, $customer, &$customer_site, $origin) {
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
  drush_log(dt('Configured Lift'), LogLevel::OK);

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
      drush_log(dt('Errors when creating Customer Site.'), LogLevel::ERROR);
      foreach ($result['errors'] as $error) {
        drush_log($error['message'], LogLevel::ERROR);
      }
    }
    else {
      drush_log(dt('Registered new Customer Site "@site".', ['@site' => $customer_site]), LogLevel::OK);
    }
  }
}

/**
 * Pushes default content entities based on the current scenario.
 *
 * @param string $uri
 *   The URL to your Drupal site.
 */
function _as_lift_push_default_content($uri) {
  $entities = _as_lift_get_scenario_settings()->get('default_content');
  $showroom_installed = \Drupal::moduleHandler()->moduleExists('dfs_obio_showroom');
  $do_not_clone = ['media', 'file', 'commerce_product', 'taxonomy_term'];

  if (!empty($entities)) {
    $type_manager = \Drupal::entityTypeManager();
    foreach ($entities as $entity_type_id => $condition_groups) {
      $storage = $type_manager->getStorage($entity_type_id);
      foreach ($condition_groups as $conditions) {
        try {
          $query = \Drupal::entityQuery($entity_type_id);
          foreach ($conditions as $field => $value) {
            $query->condition($field, $value);
          }
          $ids = $query->execute();
          $id = reset($ids);
          if ($entity = $storage->load($id)) {
            if ($showroom_installed && $entity->bundle() === 'location') {
              $entity->field_location_showroom = $uri;
            }
            if (!in_array($entity_type_id, $do_not_clone, TRUE)) {
              $clone = $entity->createDuplicate();
              $clone->search_api_skip_tracking = TRUE;
              $clone->save();
              $id = $clone->id();
            }
            drush_log(dt('Pushed @type entity "@label" (@id) to Content Hub.', [
              '@type' => $entity_type_id,
              '@id' => $id,
              '@label' => $entity->label(),
            ]), LogLevel::SUCCESS);
            // We have to do this in a new process as Content Hub registers shutdown
            // functions that push content.
            drush_invoke_process('@self', 'ev', ['$entity = entity_load("' . $entity_type_id . '", "' . $id .'");$entity->search_api_skip_tracking = TRUE;$entity->save();'], ['uri' => $uri]);
            if (!in_array($entity_type_id, $do_not_clone, TRUE)) {
              // Replace the old UUID in existing Panelizer displays with the new
              // UUID, if relevant.
              $nids = \Drupal::entityQuery('node')
                ->condition('panelizer', $entity->uuid(), 'CONTAINS')
                ->execute();
              if (count($nids) > 0) {
                $nid = reset($nids);
                $node = Node::load($nid);
                $display = str_replace($entity->uuid(), $clone->uuid(), serialize($node->panelizer->panels_display));
                $node->panelizer->panels_display = unserialize($display);
                $node->search_api_skip_tracking = TRUE;
                $node->save();
              }
              // Delete the original entity to avoid duplication.
              $entity->search_api_skip_tracking = TRUE;
              $entity->delete();
            }
          }
          else {
            drush_log(dt('Unable to load @type entity with conditions "' . json_encode($conditions) . '" in Drupal.', [
              '@type' => $entity_type_id,
            ]), LogLevel::ERROR);
          }
        }
        catch (\Exception $e) {
          drush_log(dt('Exception encountered when loading @type entity with conditions "' . json_encode($conditions) . '" in Drupal: ' . $e->getMessage(), [
            '@type' => $entity_type_id,
          ]), LogLevel::ERROR);
        }
      }
    }

    drush_log(dt('Pushed default content to Content Hub.'), LogLevel::OK);
  }
}

/**
 * Sets up default Content Hub filters based on the current scenario.
 */
function _as_lift_setup_contenthub_filters() {
  $module_handler = \Drupal::moduleHandler();
  if ($module_handler->moduleExists('dfs_obio')) {
    \Drupal::configFactory()
      ->getEditable('acquia_contenthub_subscriber.contenthub_filter.location_filter')
      ->setData([
        'uuid' => 'e152efcf-ad24-44a8-91da-f4e1630178c2',
        'langcode' => 'en',
        'dependencies' => [],
        'author' => 1,
        'tags' => '',
        'source' => '',
        'to_date' => '',
        'from_date' => '',
        'search_term' => 'location',
        'publish_setting' => 'publish',
        'name' => 'Location Filter',
        'id' => 'location_filter',
        'status' => TRUE,
      ])
      ->save();
  }
  drush_log(dt('Created default Content Hub filters.'), LogLevel::SUCCESS);
}

/**
 * Creates default filters based on the current scenario.
 *
 * @param string $url
 *   The URL of your Drupal site.
 * @param string $origin
 *   The content hub origin UUID.
 *
 * @return bool
 */
function _as_lift_create_filters($url, $origin) {
  $filters = _as_lift_get_scenario_settings()->get('filters');
  $filters = $filters ?: [];
  // @todo Use a Filter Entity and FilterManager in the future.
  $client = _as_lift_get_client()->getSlotManager()->getClient();
  foreach ($filters as $uuid => $filter_config) {
    $filter_config['uuid'] = $uuid;
    $filter_config['filter_params']['origins'] = [$origin];
    $request = new Request('POST', '/filters', [], json_encode($filter_config));
    $response = $client->send($request);
    $body = (string) $response->getBody();
    $body = json_decode($body, TRUE);
    if ($body) {
      drush_log(dt('Created Lift Filter "@name".', [
        '@name' => $filter_config['name'],
      ]), LogLevel::OK);
    }
    else {
      return FALSE;
    }
  }
  return TRUE;
}

/**
 * Creates default slots based on the current scenario.
 * 
 * @param $url
 *  The URL of your Drupal site.
 * @param $origin
 *  The content hub origin UUID.
 *
 * @return bool
 * @throws \Acquia\LiftClient\Exception\LiftSdkException
 */
function _as_lift_create_slots($url, $origin) {
  /** @var Acquia\ContentHubClient\ContentHub $contenthub_client */
  $contentHub = \Drupal::config('acquia_contenthub.admin_settings');
  $api_key = $contentHub->get('api_key');
  $secret_key = $contentHub->get('secret_key');
  $base_uri = \Drupal::configFactory()->get('as_lift.settings.lift')->get('content_hub');
  $middleware = new MiddlewareHmacV1($api_key, $secret_key, 'V1');
  /** @var Acquia\ContentHubClient\ContentHub $contenthub_client */
  $contenthub_client = new ContentHub($origin, [$middleware], [
    'base_uri' => $base_uri,
  ]);

  $slots = _as_lift_get_scenario_settings()->get('slots');

  foreach ($slots as $slot_id => $slot_config) {
    $lift_site_id = isset($slot_config['lift_site_id']) ? $slot_config['lift_site_id'] : FALSE;
    $lift_client = _as_lift_get_client($lift_site_id);
    $rule_manager = $lift_client->getRuleManager();
    $slot_manager = $lift_client->getSlotManager();

    // Create the slot.
    $slot = new Slot();
    $slot->setLabel($slot_config['label']);
    $slot->setDescription($slot_config['description']);
    $slot->setId($slot_id);
    $slot->setStatus(TRUE);
    if (isset($slot_config['css_selector'])) {
      $slot['css_selector'] = $slot_config['css_selector'];
    }

    $visibility = new Visibility();
    $visibility->setCondition('show');
    $visibility->setPages(['*']);
    $slot->setVisibility($visibility);

    try {
      $slot = $slot_manager->add($slot);
    }
    catch (ClientException $e) {
      $response = $e->getResponse();
      drush_log($response->getStatusCode() . ' error when creating Lift slot: ' . $response->getBody(), LogLevel::ERROR);
      return FALSE;
    }

    drush_log(dt('Created Lift Slot "@label".', [
      '@label' => $slot_config['label'],
    ]), LogLevel::OK);

    $view_mode = new ViewMode();
    $view_mode->setId('full');

    // Create each rule and assign content hub content.
    foreach ($slot_config['rules'] as $rule_id => $rule_config) {
      $content_list = [];
      // Assemble the content list.
      foreach ($rule_config['content'] as $content_filters) {
        $options = [
          'limit' => 1,
          'filters' => $content_filters,
        ];
        if (drush_get_option('restrict-content', TRUE)) {
          $options['origin'] = $origin;
        }
        $list = $contenthub_client->listEntities($options);
        if ($list['total'] <= 0 || !isset($list['data'][0]['uuid'])) {
          drush_log(dt('Unable to find Rule content with filter "' . json_encode($content_filters) .'.'), LogLevel::ERROR);
          drush_log(dt('Content is missing from Content Hub - please check you configuration and try installing again.'), LogLevel::ERROR);
          return FALSE;
        }
        $content_id = $list['data'][0]['uuid'];
        $content = new Content();
        $content->setId($content_id)
          ->setBaseUrl($url)
          ->setViewMode($view_mode);
        $content_list[] = $content;
      }

      // Create the rule.
      $rule = new Rule();
      $rule->setId($rule_id)
        ->setLabel($rule_config['label'])
        ->setDescription($rule_config['description'])
        ->setSlotId($slot->getId())
        ->setStatus('published')
        ->setSegmentId($rule_config['segment'])
        ->setPriority((int) $rule_config['priority'])
        ->setContentList($content_list);
      try {
        $rule_manager->add($rule);
      }
      catch (ClientException $e) {
        $response = $e->getResponse();
        drush_log($response->getStatusCode() . ' error when creating Lift rule: ' . $response->getBody(), LogLevel::ERROR);
        return FALSE;
      }

      drush_log(dt('Created Lift Rule "@label" with content "' . json_encode($rule_config['content']) .'".', [
        '@label' => $rule_config['label'],
      ]), LogLevel::OK);
    }

    // Place a new block in our panels display.
    if (isset($slot_config['landing_page_title'])) {
      $ids = \Drupal::entityQuery('node')
        ->condition('title', $slot_config['landing_page_title'])
        ->condition('type', 'landing_page')
        ->execute();
      $id = reset($ids);
      if (!$node = Node::load($id)) {
        drush_log(dt('Unable to find Landing Page content with title "@title"', [
          '@title' => $slot_config['landing_page_title'],
        ]), LogLevel::ERROR);
        return FALSE;
      }
      $display = $node->panelizer->panels_display;
      $display['blocks'][$slot_config['block_plugin_uuid']] = [
        'id' => 'lift_slot',
        'label' => $slot_config['label'],
        'provider' => 'as_lift',
        'label_display' => 0,
        'uuid' => $slot_config['block_plugin_uuid'],
        'lift_slot_id' => $slot->getId(),
        'lift_full_width' => $slot_config['block_plugin_full_width'],
        'context_mapping' => [],
        'region' => $slot_config['block_plugin_region'],
        'weight' => $slot_config['block_plugin_weight'],
      ];
      $node->panelizer->panels_display = $display;
      $node->search_api_skip_tracking = TRUE;
      $node->save();

      drush_log(dt('Placed Lift Slot block on "@label" landing page.', [
        '@label' => $node->label(),
      ]), LogLevel::OK);
    }
    elseif (isset($slot_config['page_variant_id'])) {
      $variant = \Drupal::configFactory()->getEditable('page_manager.page_variant.' . $slot_config['page_variant_id']);
      $data = $variant->getRawData();
      if (empty($data)) {
        drush_log(dt('Unable to load page variant content with ID "@id"', [
          '@id' => $slot_config['page_variant_id'],
        ]), LogLevel::ERROR);
        return FALSE;
      }
      $data['variant_settings']['blocks'][$slot_config['block_plugin_uuid']] = [
        'id' => 'lift_slot',
        'label' => $slot_config['label'],
        'provider' => 'as_lift',
        'label_display' => 0,
        'uuid' => $slot_config['block_plugin_uuid'],
        'lift_slot_id' => $slot->getId(),
        'lift_full_width' => $slot_config['block_plugin_full_width'],
        'context_mapping' => [],
        'region' => $slot_config['block_plugin_region'],
        'weight' => $slot_config['block_plugin_weight'],
      ];
      $variant->setData($data);
      $variant->save(TRUE);

      drush_log(dt('Placed Lift Slot block on "@id" page variant.', [
        '@id' => $slot_config['page_variant_id'],
      ]), LogLevel::OK);
    }
  }
}

/**
 * Gets the current scenario settings.
 *
 * @return \Drupal\Core\Config\ImmutableConfig|bool
 *   The scenario settings, or FALSE if none is installed.
 */
function _as_lift_get_scenario_settings() {
  $scenario = \Drupal::installProfile();
  if ($scenario === 'dfs_obio_acm') {
    $scenario = 'dfs_obio';
  }
  return \Drupal::config('as_lift.settings.' . $scenario);
}
