<?php

namespace Drupal\as_platform;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Serialization\Yaml;

/**
 * Class ASPlatformRegistry.
 */
class ASPlatformRegistry implements ASPlatformRegistryInterface {

  /**
   * Drupal\Core\Config\ConfigFactoryInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ASPlatformRegistry object.
   */
  public function __construct(ConfigFactoryInterface $config_factory) {
    $this->configFactory = $config_factory;
  }

  /**
   * @return bool|mixed
   */
  public function loadRegistry() {
    $filename = str_replace('docroot', '', DRUPAL_ROOT) . 'registry.df.yml';
    if (file_exists($filename) && $file = file_get_contents($filename)) {
      return Yaml::decode($file);
    }
    return FALSE;
  }

  /**
   * @param string $key
   * @param mixed $setting
   *
   * @return mixed
   */
  public function check(string $key, $setting = NULL) {
    if ($setting == NULL && $registry = $this->loadRegistry()) {
      foreach ($registry as $index => $value) {
        if ($index == $key) {
          return $value;
        }
      }
    }
    return $setting;
  }

  /**
   * @param string $key
   *
   * @return bool|mixed
   */
  public function get(string $key) {
    $value = $this->check($key);
    if(!is_null($value)) {
      return $value;
    }
    return FALSE;
  }

}
