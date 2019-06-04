<?php
/**
 * @file
 * Contains Drupal\fb_messenger_bot\Message\MediaMessage.
 */

namespace Drupal\fb_messenger_bot\Message;

/**
 * Class ListMessage.
 *
 * @package Drupal\fb_messenger_bot
 */
class MediaMessage implements MessageInterface {

  /**
   * A nested array of list elements.
   */
  protected $mediaElements;

  /**
   * ListMessage constructor.
   *
   * @param array $mediaElements
   *   The elements array.
   *
   * @throws \InvalidArgumentException
   *   Thrown if the $listElements argument is not an array.
   *
   */
  public function __construct($mediaElements) {
    if (is_array($mediaElements)) {
      $this->mediaElements = $mediaElements;
    }
    else {
      throw new \InvalidArgumentException("Invalid elements array.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedMessage() {
    return [
      'attachment' => [
        'type' => 'template',
        'payload' => [
          'template_type' => 'media',
          'elements' => $this->mediaElements,
        ],
      ],
    ];
  }

}
