<?php

namespace Drupal\df_tools_message\Event;

use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\Event;

/**
 * Event that is fired when a user registers for a subscription.
 *
 * Also contains a lot of site-wide information, to fill in for Rules problems.
 */
class SubscribeEvent extends Event {

  const EVENT_NAME = 'df_tools_message_subscribe';

  /**
   * An email address.
   *
   * @var string
   */
  public $email;

  /**
   * The site name.
   *
   * @var string
   */
  public $site;

  /**
   * The absolute URL to the site.
   *
   * @var string
   */
  public $url;

  /**
   * The current langcode.
   *
   * @var string
   */
  public $langcode;

  /**
   * Constructs the object.
   *
   * @param string $email
   *   An email address.
   */
  public function __construct($email) {
    $this->email = $email;
    $query_options = [
      'utm_source' => 'subscription',
      'utm_medium' => 'email',
    ];
    $url = new Url('<front>');
    $this->url = $url->setOption('query', $query_options)->setAbsolute(TRUE)->toString();
    $this->site = \Drupal::configFactory()->get('system.site')->get('name');
    $this->langcode = \Drupal::languageManager()->getCurrentLanguage()->getId();
  }

}
