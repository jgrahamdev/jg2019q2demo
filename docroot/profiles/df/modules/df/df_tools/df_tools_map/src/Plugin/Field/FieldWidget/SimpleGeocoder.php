<?php

namespace Drupal\df_tools_map\Plugin\Field\FieldWidget;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\geocoder\GeocoderInterface;
use Drupal\geofield\WktGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'df_tools_map_simple_geocoder' widget.
 *
 * @FieldWidget(
 *   id = "df_tools_map_simple_geocoder",
 *   label = @Translation("Simple Geocoder"),
 *   field_types = {
 *     "geofield"
 *   },
 * )
 */
class SimpleGeocoder extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */
  protected $entityFieldManager;

  /**
   * The geocoder.
   *
   * @var \Drupal\geocoder\GeocoderInterface
   */
  protected $geocoder;

  /**
   * The WKT generator.
   *
   * @var \Drupal\geofield\WktGeneratorInterface
   */
  protected $wktGenerator;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, EntityFieldManagerInterface $entity_field_manager, GeocoderInterface $geocoder, WktGeneratorInterface $wkt_generator) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->entityFieldManager = $entity_field_manager;
    $this->geocoder = $geocoder;
    $this->wktGenerator = $wkt_generator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('entity_field.manager'),
      $container->get('geocoder'),
      $container->get('geofield.wkt_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_field' => '',
      'show_coordinates' => TRUE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $entity_field_definitions = $this->entityFieldManager->getFieldDefinitions($this->fieldDefinition->getTargetEntityTypeId(), $this->fieldDefinition->getTargetBundle());

    $options = [];
    foreach ($entity_field_definitions as $id => $definition) {
      if ($definition->getType() == 'string' || $definition->getType() == 'address') {
        $options[$id] = $definition->getLabel();
      }
    }

    $elements['source_field'] = [
      '#type' => 'select',
      '#title' => $this->t('Source Field'),
      '#default_value' => $this->getSetting('source_field'),
      '#required' => TRUE,
      '#options' => $options,
      '#description' => t('The source Text or Address field to Geocode from.')
    ];

    $elements['show_coordinates'] = [
      '#type' => 'checkbox',
      '#title' => t('Show Coordinates'),
      '#default_value' => $this->getSetting('show_coordinates'),
      '#description' => t('Whether or not the current coordinates should be shown in the form.')
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Source Field: @source', ['@source' => $this->getSetting('source_field')]);

    $coordinates = $this->getSetting('show_coordinates');
    if ($coordinates) {
      $summary[] = $this->t('Coordinates are shown');
    }
    else {
      $summary[] = $this->t('Coordinates are hidden');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element += [
      '#type' => 'item',
      '#title' => $this->t('Coordinates'),
      '#markup' => t('Latitude: @lat, Longitude: @lon', ['@lat' => $items[$delta]->lat, '@lon' => $items[$delta]->lon]),
      '#description' => t('These values are set dynamically on submit from the @field field.', ['@field' => $this->getSetting('source_field')]),
    ];

    // Hide the coordinates if none are available or the associated setting is
    // disabled.
    if (empty($items[$delta]->lat) && empty($items[$delta]->lon) || !$this->getSetting('show_coordinates')) {
      $element['#access'] = FALSE;
    }

    return ['value' => $element];
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    // Retrieve the name of the source 'text' or 'address' field.
    $source_field = $this->getSetting('source_field');

    // Only proceed if a source field is available.
    if (empty($source_field)) {
      return $values;
    }

    // Get the values of the source field. Otherwise, return an empty array so
    // that we can loop through it.
    $source_field_values = $form_state->getValue($source_field, []);

    // For each source field value, geocode the address and set our coordinates.
    foreach ($source_field_values as $delta => $value) {
      // The address information is located in an 'address' key.
      $address = $value['address'];

      // Create an empty string to store address information.
      $string = '';

      // Check if this field is an Address field.
      if (isset($address['address_line1'])) {
        // Format the address as a single string.
        $string .= $address['address_line1'] . "\n";
        $string .= !empty($address['address_line2']) ? $address['address_line2'] . "\n" : '';
        $string .= $address['locality'] . ', ';
        $string .= str_replace('US-', '', $address['administrative_area']) . ' ';
        $string .= $address['postal_code'] . "\n";
        $string .= $address['country_code'];
      }

      // Geocode the source field's value using Google Maps.
      if (!empty($string) && $collection = $this->geocoder->geocode($string, ['googlemaps'])) {
        // Set our value in a similar way to Geofield's LatLon Widget.
        // @see \Drupal\geofield\Plugin\Field\FieldWidget\GeofieldLatLonWidget::massageFormValues()
        $coordinates = $collection->first()->getCoordinates();
        $point = [$coordinates->getLongitude(), $coordinates->getLatitude()];
        $values[]['value'] = $this->wktGenerator->WktBuildPoint($point);
      }
    }

    return $values;
  }

}
