<?php

namespace Drupal\dfs_obio_commerce_cart\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Base class for configurable regions.
 */
class ObioCommerceLayoutBase extends LayoutDefault implements PluginFormInterface {

  /**
   * Checks that the class should provide the additional configurations.
   *
   * @return bool
   *   True if additional configs are needed, false if not.
   */
  public function isLayoutConfigurable() {
    $additionals = $this->pluginDefinition->get('additional');
    return !empty($additionals['dfs_obio_base']);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $defaultConfig = parent::defaultConfiguration();

    if ($this->isLayoutConfigurable()) {
      $defaultConfig += [
        'dfs_defaults' => [
          'classes_wrapper' => '',
          'classes_main' => '',
        ],
        'dfs_regions' => [],
      ];

      foreach ($this->getPluginDefinition()->getRegionNames() as $regionName) {
        $defaultConfig['dfs_regions']["classes_$regionName"] = "layout__$regionName";
      }
    }

    return $defaultConfig;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = [];

    if ($this->isLayoutConfigurable()) {
      $configuration = $this->getConfiguration();
      $regionLabels = $this->getPluginDefinition()->getRegionLabels();

      $form['attributes'] = [
        '#type' => 'vertical_tabs',
        '#parents' => ['attributes'],
      ];

      $form['dfs_defaults'] = [
        '#type' => 'details',
        '#title' => $this->t('Layout defaults'),
        '#group' => 'attributes',
      ];
      $form['dfs_defaults']['preamble'] = [
        '#type' => 'item',
        '#markup' => $this->t('We need a lot of wrappers in certain scenarios to provide the best integration.'),
      ];
      $form['dfs_defaults']['classes_wrapper'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Layout wrapper CSS classes'),
        '#description' => $this->t('Not printed if this configuration is empty'),
        '#default_value' => $configuration['dfs_defaults']['classes_wrapper'],
      ];
      $form['dfs_defaults']['classes_main'] = [
        '#type' => 'textfield',
        '#title' => $this->t('Layout main CSS classes'),
        '#description' => $this->t('Not printed if this configuration is empty'),
        '#default_value' => $configuration['dfs_defaults']['classes_main'],
      ];

      if (!empty($configuration['dfs_regions']) && !empty($regionLabels)) {
        $form['dfs_regions'] = [
          '#type' => 'details',
          '#title' => $this->t('Region settings'),
          '#group' => 'attributes',
        ];

        foreach ($regionLabels as $region => $regionLabel) {
          $configKey = "classes_$region";
          $form['dfs_regions'][$configKey] = [
            '#type' => 'textfield',
            '#title' => $this->t('@regionName region CSS classes', [
              '@regionName' => $regionLabel,
            ]),
            '#description' => $this->t('Not printed if this configuration or the region itself is empty'),
            '#default_value' => $configuration['dfs_regions'][$configKey],
          ];
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->isLayoutConfigurable()) {
      // Twig sanitizes the user-provided values nicely, we don't have to do
      // anything special here.
      // @see dfs_obio_commerce_preprocess_layout()
      $values = $form_state->getValues();
      $this->configuration['dfs_defaults']['classes_wrapper'] = $values['dfs_defaults']['classes_wrapper'];
      $this->configuration['dfs_defaults']['classes_main'] = $values['dfs_defaults']['classes_main'];

      if (!empty($values['dfs_regions']) && is_array($values['dfs_regions'])) {
        foreach ($values['dfs_regions'] as $configKey => $configValue) {
          $this->configuration['dfs_regions'][$configKey] = $configValue;
        }
      }
    }
  }

}
