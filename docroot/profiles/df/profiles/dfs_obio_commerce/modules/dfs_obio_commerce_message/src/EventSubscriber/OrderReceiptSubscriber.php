<?php

namespace Drupal\dfs_obio_commerce_message\EventSubscriber;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\message\Entity\Message;
use Drupal\message\Entity\MessageTemplate;
use Drupal\message_notify\MessageNotifier;
use Drupal\state_machine\Event\WorkflowTransitionEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sends a receipt email when an order is placed.
 */
class OrderReceiptSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The order type entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $orderTypeStorage;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The message notification service.
   *
   * @var \Drupal\message_notify\MessageNotifier
   */
  protected $messageNotifier;

  /**
   * Constructs a new OrderReceiptSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager.
   * @param \Drupal\message_notify\MessageNotifier $message_notifier
   *   The message notifier.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, LanguageManagerInterface $language_manager, MessageNotifier $message_notifier) {
    $this->orderTypeStorage = $entity_type_manager->getStorage('commerce_order_type');
    $this->languageManager = $language_manager;
    $this->messageNotifier = $message_notifier;
  }

  /**
   * Sends an order receipt email.
   *
   * @param \Drupal\state_machine\Event\WorkflowTransitionEvent $event
   *   The event we subscribed to.
   */
  public function sendOrderReceipt(WorkflowTransitionEvent $event) {
    /** @var \Drupal\commerce_order\Entity\OrderInterface $order */
    $order = $event->getEntity();
    /** @var \Drupal\commerce_order\Entity\OrderTypeInterface $order_type */
    $order_type = $this->orderTypeStorage->load($order->bundle());
    if (!$order_type->shouldSendReceipt()) {
      return;
    }
    $to = $order->getEmail();
    if (!$to) {
      // The email should not be empty, unless the order is malformed.
      return;
    }

    $from = $order->getStore()->getEmail();

    if ($receipt_bcc = $order_type->getReceiptBcc()) {
      $bcc = $receipt_bcc;
    }

    // Retrieve the customer from the order.
    $customer = $order->getCustomer();

    // Replicated logic from EmailAction and contact's MailHandler.
    if ($customer) {
      $langcode = $customer->getPreferredLangcode();
    }
    else {
      $langcode = $this->languageManager->getDefaultLanguage()->getId();
    }

    // Send a message to the customer regarding their new order.
    if ($message_template = MessageTemplate::load('new_order')) {
      // Create the message.
      $message = Message::create(['template' => $message_template->id(), 'uid' => $customer->id()]);

      // Use the preferred language of the customer associated with the order.
      $message->setLanguage($langcode);

      // Add a reference to the order to enable token and tracking support.
      $message->field_message_order->entity = $order;
      $message->save();

      // Send the new order message.
      $this->messageNotifier->send($message, ['mail' => $to, 'language override' => TRUE], 'email');

      // Stop propagation in order to ignore default order creation messages.
      $event->stopPropagation();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = ['commerce_order.place.post_transition' => ['sendOrderReceipt', 0]];
    return $events;
  }

}
