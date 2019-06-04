<?php
/**
 * @file
 * Contains Drupal\fb_messenger_bot\Message\QuickMessage.
 */

namespace Drupal\fb_messenger_bot\Message;

use Drupal\fb_messenger_bot\Message\MessageInterface;

class QuickMessage implements MessageInterface {

  /**
   * The message text.
   */
  protected $messageText;

  /**
   * The quick replies array.
   */
  protected $quickReplies;

  /**
   * TextMessage constructor.
   *
   * @param string $text
   *   The text to use for this message.
   */
  public function __construct($text, $quickReplies) {
    $this->messageText = $text;
    if (is_array($quickReplies)) {
      $this->quickReplies = $quickReplies;
    }
    else {
      throw new \InvalidArgumentException("Invalid quick_replies array.");
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedMessage() {
    return [
      'text' => $this->messageText,
      'quick_replies' => $this->quickReplies
    ];
  }

}
