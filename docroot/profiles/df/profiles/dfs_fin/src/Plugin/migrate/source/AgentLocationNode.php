<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Plugin\migrate\source\AgentLocationNode.
 */

namespace Drupal\dfs_fin\Plugin\migrate\source;

use Drupal\geocoder\Geocoder;
use Drupal\migrate\Row;
use Drupal\migrate_source_csv\Plugin\migrate\source\CSV;
use Drupal\df_tools_migration\Plugin\migrate\source\AuthorMigrationTrait;
use Drupal\df_tools_migration\Plugin\migrate\source\ImageMigrationTrait;

/**
 * Source for the Agent Location node CSV.
 *
 * @MigrateSource(
 *   id = "agent_location_node"
 * )
 */
class AgentLocationNode extends CSV {

  use AuthorMigrationTrait;
  use ImageMigrationTrait;

  public function prepareRow(Row $row) {
    $this->setUidProperty($row, null);
    $this->setImageProperty($row, 'Image');

    if ($value = $row->getSourceProperty('Areas of Focus')) {
      $row->setSourceProperty('Areas of Focus', explode(', ', $value));
    }

    // Prefix the state per the normal Address schema.
    if ($state = $row->getSourceProperty('Address State')) {
      $row->setSourceProperty('Address State', 'US-' . $state);
    }

    // Use a cached geolocation if possible.
    if ($geolocation = $row->getSourceProperty('Geolocation')) {
      list($lat, $lon) = explode(',', $geolocation);
      $point = [$lon, $lat];
      $row->setSourceProperty('Geofield', \Drupal::service('geofield.wkt_generator')->WktBuildPoint($point));
    }
    else {
      // Manually geocode from source information.
      $address = $row->getSourceProperty('Address Line 1');
      $address2 = $row->getSourceProperty('Address Line 2');
      $city = $row->getSourceProperty('Address City');
      $zip = $row->getSourceProperty('Address Zip');

      if ($address && $city && $zip && $state) {
        $address2 = $address2 ? $address2 . "\n" : '';
        $address_string = "$address\n$address2$city, $state $zip\nUS";
        if ($collection =  \Drupal::service('geocoder')->geocode($address_string, ['googlemaps'])) {
          // Set our value in a similar way to Geofield's LatLon Widget.
          // @see \Drupal\geofield\Plugin\Field\FieldWidget\GeofieldLatLonWidget::massageFormValues()
          $coordinates = $collection->first()->getCoordinates();
          $point = array(
            $coordinates->getLongitude(),
            $coordinates->getLatitude()
          );
          $row->setSourceProperty('Geofield', \Drupal::service('geofield.wkt_generator')
            ->WktBuildPoint($point));
        }
      }
    }
  }

}
