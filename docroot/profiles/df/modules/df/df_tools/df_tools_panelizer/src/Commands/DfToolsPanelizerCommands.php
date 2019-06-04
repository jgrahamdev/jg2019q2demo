<?php

namespace Drupal\df_tools_panelizer\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class DfToolsPanelizerCommands extends DrushCommands {

  /**
   * Exports a given Node's Panelizer Display to a pseudo-config file.
   *
   * @param $nid
   *   The ID of the override Node.
   * @param $scenario
   *   The target Scenario.
   * @usage df-pe 1 dfs_tec
   *   Export the Panels Display of Node 1 to the given scenario's data directory.
   * @validate-module-enabled panelizer,df_tools_panelizer
   *
   * @command df:panelizer-export
   * @aliases df-pe,df-panelizer-export
   *
   * @return bool
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function panelizerExport($nid, $scenario) {
    // Load the Node.
    $node = \Drupal::entityTypeManager()->getStorage('node')->load($nid);

    // Check if it is Panelized.
    if ($node->panelizer) {
      // Get the path to the current scenario.
      $base_path = drupal_get_path('module', $scenario);
      if (!$base_path) {
        drupal_set_message(t('The scenario @scenario is not installed.', ['@scenario' => $scenario]), 'error');
        return FALSE;
      }

      // Write YAML to a file.
      $panels_file = $base_path . '/data/panelizer.panels_display.node.'.$node->uuid() . '.yml';
      $configuration = \Drupal::service('panelizer')->getPanelsDisplay($node, 'full')->getConfiguration();
      $yaml = \Symfony\Component\Yaml\Yaml::dump($configuration, 99, 2);
      file_put_contents($panels_file, $yaml);

      drupal_set_message(t('Successfully exported Panelizer display for Node @nid to @panels_file', ['@nid' => $node->id(), '@panels_file' => $panels_file]));
      return TRUE;
    }
    else {
      drupal_set_message(t('The given Node is not Panelized.'), 'error');
      return FALSE;
    }
  }

}
