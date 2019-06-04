<?php

namespace Acquia\DF\Composer;

use Acquia\Lightning\IniEncoder;
use Composer\Package\Locker;
use Composer\Package\RootPackageInterface;
use Composer\Script\Event;

/**
 * Generates Drush make files for drupal.org's ancient packaging system.
 */
class Package {

  /**
   * The root Composer package (i.e., this composer.json).
   *
   * @var \Composer\Package\RootPackageInterface
   */
  protected $rootPackage;

  /**
   * The locker.
   *
   * @var \Composer\Package\Locker
   */
  protected $locker;

  /**
   * Package constructor.
   *
   * @param \Composer\Package\RootPackageInterface $root_package
   *   The root package (i.e., this composer.json).
   * @param \Composer\Package\Locker $locker
   *   The locker.
   */
  public function __construct(RootPackageInterface $root_package, Locker $locker) {
    $this->rootPackage = $root_package;
    $this->locker = $locker;
  }

  /**
   * Script entry point.
   *
   * @param \Composer\Script\Event $event
   *   The script event.
   */
  public static function execute(Event $event) {
    $composer = $event->getComposer();

    $handler = new static(
      $composer->getPackage(),
      $composer->getLocker()
    );

    $encoder = new IniEncoder();

    $make = $handler->make();
    $handler->makeFix($make);
    $core = $handler->makeCore($make);
    file_put_contents('drupal-org-core.make', $encoder->encode($core));
    file_put_contents('drupal-org.make', $encoder->encode($make));
  }

  /**
   * Extracts a core-only make file from a complete make file.
   *
   * @param array $make
   *   The complete make file.
   *
   * @return array
   *   The core-only make file structure.
   */
  protected function makeCore(array &$make) {
    $project = $make['projects']['drupal'];
    unset($make['projects']['drupal']);

    return [
      'core' => $make['core'],
      'api' => $make['api'],
      'projects' => [
        'drupal' => $project,
      ],
    ];
  }

  /**
   * Generates a complete make file structure from the root package.
   *
   * @return array
   *   The complete make file structure.
   */
  protected function make() {
    $info = [
      'core' => '8.x',
      'api' => 2,
      'defaults' => [
        'projects' => [
          'subdir' => 'contrib',
        ],
      ],
      'projects' => [],
      'libraries' => [],
    ];
    $lock = $this->locker->getLockData();

    foreach ($lock['packages'] as $package) {
      list(, $name) = explode('/', $package['name'], 2);

      if ($this->isDrupalPackage($package)) {
        if ($package['type'] == 'drupal-core') {
          $name = 'drupal';
        }
        $info['projects'][$name] = $this->buildProject($package);
      }
      elseif ($this->isLightning($package)) {
        $info['projects'][$name] = $this->buildProject($package);

        // The Lightning project uses semantic versioning.
        $sem_ver = explode('.', $package['version']);
        list($major, $minor, $patch) = $sem_ver;

        // drupal.org doesn't currently support semantic versioning, so
        // Lightning uses a custom release naming convention where 'patch' is
        // padded with leading zeros to two digits.
        // For example, version '3.0.3' becomes '3.003'.
        $padded_patch = str_pad($patch, 2, "0", STR_PAD_LEFT);

        $info['projects'][$name]['version'] = "$major.$minor$padded_patch";

        // Override the default 'contrib' subdirectory set above in order to
        // prevent the profile from being placed in a 'contrib' folder.
        $info['projects'][$name]['subdir'] = '""';
      }
      // Include any non-drupal libraries that exist in both .lock and .json.
      elseif ($this->isLibrary($package)) {
        $info['libraries'][$name] = $this->buildLibrary($package);
      }
    }

    return $info;
  }

  /**
   * 'Fixes' a make file by manually adjusting known package inaccuracies.
   *
   * @param array $make
   *   The complete make file.
   */
  protected function makeFix(array &$make) {
    // Build a whitelist of libraries which are approved for distribution via
    // drupal.org.
    // Composer supports adding libraries from anywhere, while drupal.org only
    // allows distributions to include libraries that have been specifically
    // approved. See the Packaging Whitelist for a list of approved libraries
    // (https://www.drupal.org/packaging-whitelist).
    $whitelist = ['dropzone', 'masonry', 'imagesloaded', 'ckeditor-track-changes', 'slick-carousel', 'lightbox2', 'cropper'];

    foreach ($make['libraries'] as $name => $info) {
      // Libraries must be located in the root 'libraries' folder.
      $make['libraries'][$name]['destination'] = "../../libraries";
      // Remove any libraries that are not specifically whitelisted above.
      // Users will need to download these libraries manually.
      if (!in_array($name, $whitelist)) {
        unset($make['libraries'][$name]);
      }
    }

    // The ckeditor-track-changes library is designed to be renamed, after
    // download, to 'lite'.
    if (isset($make['libraries']['ckeditor-track-changes'])) {
      $make['libraries']['ckeditor-track-changes']['directory_name'] = 'lite';
    }
  }

  /**
   * Builds a make structure for a library (i.e., not a Drupal project).
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildLibrary(array $package) {
    $info = [
      'type' => 'library',
    ];
    return $info + $this->buildPackage($package);
  }

  /**
   * Builds a make structure for a Drupal module, theme, profile, or core.
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildProject(array $package) {
    $info = [];

    switch ($package['type']) {
      case 'drupal-core':
      case 'drupal-profile':
      case 'drupal-theme':
      case 'drupal-module':
        $info['type'] = substr($package['type'], 7);
        break;
    }
    $info += $this->buildPackage($package);

    // Dev versions should use git branch + revision, otherwise a tag is used.
    if (strstr($package['version'], 'dev')) {
      // 'dev-' prefix indicates a branch-alias. Stripping the dev prefix from
      // the branch name is sufficient.
      // @see https://getcomposer.org/doc/articles/aliases.md
      if (strpos($package['version'], 'dev-') === 0) {
        $info['download']['branch'] = substr($package['version'], 4);
      }
      // Otherwise, leave as is. Version may already use '-dev' suffix.
      else {
        $info['download']['branch'] = $package['version'];
      }
      $info['download']['revision'] = $package['source']['reference'];
    }
    // Core should always use git branch + revision, or patches won't apply
    // correctly.
    elseif ($package['type'] == 'drupal-core') {
      // Composer downloads core from its subtree split on GitHub, but the
      // packaging system will choke on that.
      $info['download']['url'] = 'https://git.drupal.org/project/drupal.git';
      // Derive the branch from the version string.
      $info['download']['branch'] = preg_replace(
        // 8.4.2 --> 8.4.x
        // 8.6.0-beta2 --> 8.6.x
        ['/\.\d(-\w+\d+)?$/', '/-dev$/'],
        // 8.5.x-dev --> 8.5.x
        ['.x', NULL],
        $package['version']
      );
      // We never want to specify a commit hash, regardless of whether this is
      // a dev branch or tagged release.
      unset($info['download']['revision']);
      // But, if it is a tagged release (i.e., there's no -dev suffix in the
      // version), we do want to specify that tag.
      if (strpos($package['version'], '-dev') === FALSE) {
        $info['download']['tag'] = $package['version'];
      }
    }
    // Any other type of package can use a standard Drupal version number.
    else {
      // Drupalize the tag versioning, e.g. 8.1.0-alpha1 => 8.x-1.0-alpha1.
      $version = sprintf(
        '%d.x-%s',
        $package['version']{0},
        substr($package['version'], 2)
      );
      // Make the version Drush make-compatible: 1.x-13.0-beta2 --> 1.13-beta2
      $info['version'] = preg_replace(
        '/^([0-9]+)\.x-([0-9]+)\.[0-9]+(-.+)?/',
        '$1.$2$3',
        $version
      );
      unset($info['download']);
    }
    return $info;
  }

  /**
   * Builds a make structure for any kind of package.
   *
   * @param array $package
   *   The Composer package definition.
   *
   * @return array
   *   The generated make structure.
   */
  protected function buildPackage(array $package) {
    if ($package['type'] == 'npm-asset') {
      $download = [
        'type' => 'get',
        'url' => $package['dist']['url'],
      ];
    }
    else {
      $download = [
        'type' => 'git',
        'url' => str_replace('git@github.com:', 'https://github.com/', $package['source']['url']),
        'branch' => $package['version'],
        'revision' => $package['source']['reference'],
      ];
    }
    $info = ['download' => $download];
    if (isset($package['extra']['patches_applied'])) {
      $info['patch'] = array_values($package['extra']['patches_applied']);
    }
    return $info;
  }

  /**
   * Determines if a package is a Drupal core, module, theme, or profile.
   *
   * @param array $package
   *   The package info.
   *
   * @return bool
   *   TRUE if the package is a Drupal core, module, theme, or profile;
   *   otherwise FALSE.
   */
  protected function isDrupalPackage(array $package) {
    $package_types = [
      'drupal-core',
      'drupal-module',
      'drupal-theme',
      'drupal-profile',
    ];

    return (
      strpos($package['name'], 'drupal/') === 0 &&
      in_array($package['type'], $package_types)
    );
  }

  /**
   * Determines if a package is the Lightning profile.
   *
   * @param array $package
   *   The package info.
   *
   * @return bool
   *   TRUE if the package is Lightning; otherwise FALSE.
   */
  protected function isLightning(array $package) {
    return (
      $package['name'] == 'acquia/lightning' &&
      $package['type'] == 'drupal-profile'
    );
  }

  /**
   * Determines if a package is an asset library.
   *
   * @param array $package
   *   The package info.
   *
   * @return bool
   *   TRUE if the package is an asset library, otherwise FALSE.
   */
  protected function isLibrary(array $package) {
    return in_array($package['type'], ['drupal-library', 'bower-asset', 'npm-asset'], TRUE);
  }

}
