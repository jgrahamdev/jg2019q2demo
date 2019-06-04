<?php

namespace Drupal\as_platform;

/**
 * Interface ASPlatformRegistryInterface.
 */
interface ASPlatformRegistryInterface {

  /**
   * Loads the registry file from the Drupal docroot.
   *
   * @return bool|mixed
   */
  public function loadRegistry();

  /**
   * Accepts a key and value replacing NULL value with the first indexed value
   * found with a matching key in the loaded registry file.
   *
   * @param string $key
   * @param mixed $value
   *
   * @return mixed
   */
  public function check(string $key, $value);

  /**
   * Returns a single value from the registry.
   *
   * @param string $key
   *
   * @return bool|mixed
   */
  public function get(string $key);

}