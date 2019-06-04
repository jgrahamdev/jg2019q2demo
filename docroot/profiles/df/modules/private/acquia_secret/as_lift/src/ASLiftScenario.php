<?php

namespace Drupal\as_lift;

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

/**
 * Class ASLiftScenario.
 *
 * @package Drupal\as_lift
 */
class ASLiftScenario {
  /**
   * The ID of your Lift customer.
   */
  protected $account_id;

  /**
   * The site ID.
   */
  protected $site_id;

  /**
   * The API Key of your administrative user.
   */
  protected $api_key;

  /**
   * The API Secret Key of your administrative user.
   */
  protected $secret_key;

  /**
   * The registered origin UUID.
   */
  protected $origin;

  /**
   * The URI of the site.
   */
  protected $uri;

  public function __construct() {
    $lift = \Drupal::config('acquia_lift.settings');
    $this->account_id = $lift->get('credential.account_id');
    $this->site_id = $lift->get('credential.site_id');

    $contentHub = \Drupal::config('acquia_contenthub.admin_settings');
    $this->api_key = $contentHub->get('api_key');
    $this->secret_key = $contentHub->get('secret_key');
    $this->origin = $contentHub->get('origin');
  }

  /**
   * Sets the URI from the command parameter.
   */
  public function setURI($uri) {
    $this->uri = $uri;
  }

  /**
   * Gets the current scenario settings.
   *
   * @return \Drupal\Core\Config\ImmutableConfig|bool
   *   The scenario settings, or FALSE if none is installed.
   */
  public function getSettings() {
    $scenario = \Drupal::installProfile();
    if ($scenario === 'dfs_obio_acm') {
      $scenario = 'dfs_obio';
    }
    return \Drupal::config('as_lift.settings.' . $scenario);
  }

  /**
   * Verifies that the given credentials have appropriate permissions.
   *
   * @param string $account_id
   *   The ID of your Lift customer.
   * @param string $site_id
   *   The Lift Customer Site.
   * @param string $api_key
   *   The API Key of your administrative user.
   * @param string $secret_key
   *   The API Secret Key of your administrative user.
   *
   * @return bool
   *   Whether or not the given credentials have appropriate permissions.
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  public function checkPermissions($account_id, $site_id, $api_key, $secret_key) {
    // Test that Lift is reachable.
    $base_url = \Drupal::configFactory()->get('as_lift.settings.lift')->get('decision_api');
    $lift_client = new Lift($account_id, $site_id, $api_key, $secret_key, [
      'base_url' => $base_url,
    ]);
    $ping = $lift_client->ping();
    if (!is_array($ping) || !isset($ping['message']) || !$ping['message']) {
      \Drupal::logger('as_lift')->error('Unable to reach Lift with the given credentials');
      return FALSE;
    }

    // Test that the Lift user can access slots.
    $request = new Request('GET', '/slots');
    $slot_request = $lift_client->getSlotManager()->getClient()->send($request);
    if ($slot_request->getStatusCode() !== 200) {
      \Drupal::logger('as_lift')->error('Provided Lift user does not have permissions to access slots');
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Test that the required segments for the current scenario exist.
   *
   * @param string $account_id
   *   The ID of your Lift customer.
   * @param string $site_id
   *   The Lift Customer Site.
   * @param string $api_key
   *   The API Key of your administrative user.
   * @param string $secret_key
   *   The API Secret Key of your administrative user.
   *
   * @return bool
   *   Missing segments for reporting.
   */
  public function validateSegments($account_id, $site_id, $api_key, $secret_key) {
    $base_url = \Drupal::configFactory()->get('as_lift.settings.lift')->get('decision_api');
    $lift_client = new Lift($account_id, $site_id, $api_key, $secret_key, [
      'base_url' => $base_url,
    ]);

    $segment_manager = $lift_client->getSegmentManager();
    $segment_ids = [];
    foreach ($segment_manager->query() as $segment) {
      $segment_ids[] = $segment->getId();
    }
    $slots = $this->getSettings()->get('slots');
    $segmentsValid = TRUE;
    foreach ($slots as $slot) {
      foreach ($slot['rules'] as $rule) {
        if (!empty($rule['segment']) && !in_array($rule['segment'], $segment_ids)) {
          $segmentsValid = FALSE;
          \Drupal::logger('as_lift')->warning('Lift site @site_id is missing the segment @segment.', ['@site_id' => $this->site_id, '@segment' => $rule['segment']]);
        }
      }
    }

    return $segmentsValid;
  }

  /**
   * Configures Content Hub entity settings for the current scenario.
   *
   * @return bool
   *   If entity settings were configured.
   */
  public function configureEntities() {
    $entity_config_map = $this->getSettings()->get('entity_config');
    if (empty($entity_config_map)) {
      return;
    }

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

    \Drupal::logger('as_lift')->debug('configured Content Hub entities');
    return TRUE;
  }

  /**
   * Sets up default Content Hub filters based on the current scenario.
   */
  public function createFilters() {
    $module_handler = \Drupal::moduleHandler();
    if (!$module_handler->moduleExists('dfs_obio')) {
      return;
    }

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

    \Drupal::logger('as_lift')->debug(dt('created default Content Hub filters'));
    return TRUE;
  }

  /**
   * Creates default filters based on the current scenario.
   *
   * @return bool
   */
  public function createLiftFilters() {
    $filters = $this->getSettings()->get('filters');
    $filters = $filters ?: [];
    // @todo Use a Filter Entity and FilterManager in the future.
    $client = _as_lift_get_client()->getSlotManager()->getClient();
    foreach ($filters as $uuid => $filter_config) {
      $filter_config['uuid'] = $uuid;
      $filter_config['filter_params']['origins'] = [$this->origin];
      $request = new Request('POST', '/filters', [], json_encode($filter_config));
      $response = $client->send($request);
      $body = (string) $response->getBody();
      $body = json_decode($body, TRUE);
      if ($body) {
        \Drupal::logger('as_lift')->debug('created Lift Filter "@name".', ['@name' => $filter_config['name']]);
      }
      else {
        return FALSE;
      }
    }
    return TRUE;
  }

  /**
   * Pushes default content entities based on the current scenario.
   */
  public function pushContent() {
    $entities = $this->getSettings()->get('default_content');
    $showroom_installed = \Drupal::moduleHandler()->moduleExists('dfs_obio_showroom');
    $do_not_clone = ['media', 'file', 'commerce_product', 'taxonomy_term'];

    if (empty($entities)) {
      return;
    }

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
              $entity->field_location_showroom = $this->uri;
            }
            if (!in_array($entity_type_id, $do_not_clone, TRUE)) {
              $clone = $entity->createDuplicate();
              $clone->search_api_skip_tracking = TRUE;
              $clone->save();
              $id = $clone->id();
            }
            \Drupal::logger('as_lift')->debug(dt('pushed @type entity "@label" (@id) to Content Hub.', [
              '@type' => $entity_type_id,
              '@id' => $id,
              '@label' => $entity->label(),
            ]));
            // We have to do this in a new process as Content Hub registers shutdown
            // functions that push content.
            drush_invoke_process('@self', 'ev', ['$entity = entity_load("' . $entity_type_id . '", "' . $id .'");$entity->search_api_skip_tracking = TRUE;$entity->save();'], ['uri' => $this->uri]);
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
            \Drupal::logger('as_lift')->error(dt('unable to load @type entity with conditions "' . json_encode($conditions) . '" in Drupal.', [
              '@type' => $entity_type_id,
            ]));
          }
        }
        catch (\Exception $e) {
          \Drupal::logger('as_lift')->error(dt('exception encountered when loading @type entity with conditions "' . json_encode($conditions) . '" in Drupal: ' . $e->getMessage(), [
            '@type' => $entity_type_id,
          ]));
        }
      }
    }

    \Drupal::logger('as_lift')->debug('pushed default content to Acquia Content Hub.');
    return TRUE;
  }

  /**
   * Purge content from Content Hub
   *
   * @TODO implement Symphony io->confirm(), remove drush_print, drush_log implementation
   */
  public function purgeContent() {
    $base_uri = \Drupal::configFactory()->get('as_lift.settings.lift')->get('content_hub');
    $middleware = new MiddlewareHmacV1($this->api_key, $this->secret_key, 'V1');
    $contenthub_client = new ContentHub($this->origin, [$middleware], [
      'base_uri' => $base_uri,
    ]);

    $base_url = \Drupal::configFactory()->get('as_lift.settings.lift')->get('decision_api');
    $lift_client = new Lift($this->account_id, $this->site_id, $this->api_key, $this->secret_key, [
      'base_url' => $base_url,
    ]);

    // Get entities from Content Hub.
    $entities = $contenthub_client->listEntities();
    if (empty($entities['data'])) {
      drush_log(dt('no content to purge in Acquia Content Hub'));
      return;
    }

    // @TODO remove drush_print, drush_log implementation
    drush_print(dt('✓ content found in Acquia Content Hub'));
    drush_print('');

    $uuids = [];
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

      drush_print("- $label ($type:$uuid)");
    }

    // List all Lift slots/rules. Assumes there can be no slots/rules without content above.
    $slot_manager = $lift_client->getSlotManager();
    $rule_manager = $lift_client->getRuleManager();
    $slots = $slot_manager->query();
    $rules = $rule_manager->query();
    foreach ($slots as $slot) {
      drush_print('- Lift Slot: '. $slot->getLabel());
    }
    foreach ($rules as $rule) {
      drush_print('- Lift Rule: '. $rule->getLabel());
    }
    drush_print(''); //blank line

    // @TODO implement Symphony io->confirm()
    $action = TRUE; //$this->io->confirm('Are you sure you want to delete the content above from Content Hub and Lift? (y/n)');
    if ($action) {
      foreach ($uuids as $uuid) {
        if (!$contenthub_client->deleteEntity($uuid)) {
          \Drupal::logger('as_lift')->error("failed to delete $uuid from Content Hub.");
        }
      }
      foreach ($slots as $slot) {
        if (!$slot_manager->delete($slot->getId())) {
          \Drupal::logger('as_lift')->error('failed to delete Lift Slot: ' . $slot->getId());
        }
      }
      foreach ($rules as $rule) {
        if (!$rule_manager->delete($rule->getId())) {
          \Drupal::logger('as_lift')->error('failed to delete Lift Rule: ' . $rule->getId());
        }
      }

      \Drupal::logger('as_lift')->debug('content purged from Acquia Content Hub');
      return TRUE;
    }
    else {
      //drush_user_abort();
    }
  }

  /**
   * Creates default slots based on the current scenario.
   *
   * @return bool
   * @throws \Acquia\LiftClient\Exception\LiftSdkException
   */
  public function createLiftRules() {
    $base_uri = \Drupal::configFactory()->get('as_lift.settings.lift')->get('content_hub');
    $middleware = new MiddlewareHmacV1($this->api_key, $this->secret_key, 'V1');
    /** @var Acquia\ContentHubClient\ContentHub $contenthub_client */
    $contenthub_client = new ContentHub($this->origin, [$middleware], [
      'base_uri' => $base_uri,
    ]);

    $slots = $this->getSettings()->get('slots');

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
        \Drupal::logger('as_lift')->error($response->getStatusCode() . ' error when creating Lift slot: ' . $response->getBody());
        return FALSE;
      }

      \Drupal::logger('as_lift')->debug(dt('Created Lift Slot "@label".', [
        '@label' => $slot_config['label'],
      ]));

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
            $options['origin'] = $this->origin;
          }

          $list = $contenthub_client->listEntities($options);
          if ($list['total'] <= 0 || !isset($list['data'][0]['uuid'])) {
            \Drupal::logger('as_lift')->error(dt('Unable to find Rule content with filter "' . json_encode($content_filters) .'.'));
            \Drupal::logger('as_lift')->error(dt('Content is missing from Content Hub - please check you configuration and try installing again.'));
            return FALSE;
          }
          $content_id = $list['data'][0]['uuid'];
          $content = new Content();
          $content->setId($content_id)
            ->setBaseUrl($this->uri)
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
          \Drupal::logger('as_lift')->error($response->getStatusCode() . ' error when creating Lift rule: ' . $response->getBody());
          return FALSE;
        }

        \Drupal::logger('as_lift')->debug(dt('Created Lift Rule "@label" with content "' . json_encode($rule_config['content']) .'".', [
          '@label' => $rule_config['label'],
        ]));
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

        \Drupal::logger('as_lift')->debug(dt('Placed Lift Slot block on "@label" landing page.', [
          '@label' => $node->label(),
        ]));
      }
      elseif (isset($slot_config['page_variant_id'])) {
        $variant = \Drupal::configFactory()->getEditable('page_manager.page_variant.' . $slot_config['page_variant_id']);
        $data = $variant->getRawData();
        if (empty($data)) {
          \Drupal::logger('as_lift')->error(dt('Unable to load page variant content with ID "@id"', [
            '@id' => $slot_config['page_variant_id'],
          ]));
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

        \Drupal::logger('as_lift')->debug(dt('Placed Lift Slot block on "@id" page variant.', [
          '@id' => $slot_config['page_variant_id'],
        ]));
      }
    }
    return TRUE;
  }

}
