<?php

namespace Drupal\fb_messenger_bot\Bot;

use Drupal\fb_messenger_bot\FacebookService;
use Drupal\fb_messenger_bot\Workflow\BotWorkflowInterface;
use Drupal\fb_messenger_bot\Conversation\ConversationFactory;

/**
 * Trait BotTrait.
 *
 * @package Drupal\fb_messenger_bot\Bot
 */
trait BotTrait {

  /**
   * The conversation factory.
   *
   * @var \Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface
   */
  protected $conversationFactory;

  /**
   * The Workflow the bot will use.
   *
   * @var \Drupal\fb_messenger_bot\Workflow\BotWorkflowInterface
   */
  protected $workflow;

  /**
   * The Facebook Service.
   *
   * @var \Drupal\fb_messenger_bot\FacebookService
   */
  protected $fbService;

  /**
   * {@inheritdoc}
   */
  public function process($data) {
    $incomingData = $this->fbService->translateRequest($data);

    if (empty($incomingData)) {
      return;
    }

    // Iterate through received messages.
    foreach ($incomingData as $uid => $incomingMessages) {
      foreach ($incomingMessages as $incomingMessage) {
        $conversation = $this->conversationFactory->getConversation($uid);
        $this->workflow->setSteps($this->workflow->buildSteps($conversation, $incomingMessage));
        $response = $this->workflow->processConversation($conversation, $incomingMessage);

        // Added in for Lift integration
        $answers = array_keys($conversation->getValidAnswers());
        $config = \Drupal::service('config.factory')
          ->getEditable('fb_bot.' . $conversation->getUserId());
        $moduleHandler = \Drupal::service('module_handler');
        if ($moduleHandler->moduleExists('as_lift')){
          foreach ($answers as $answer) {
            $email_validator = \Drupal::service('email.validator');
            if ($moduleHandler->moduleExists('as_lift') && $email_validator->isValid($config->get('email')) && $answer != 'welcome') {
              $lift_config = \Drupal::service('config.factory')->getEditable('acquia_lift.settings');
              $lift_config->set('credential.account_id', $config->get('lift_account'));
              $lift_config->set('credential.site_id', $config->get('lift_site_id'));
              $content_hub_config = \Drupal::service('config.factory')->getEditable('acquia_contenthub.admin_settings');
              $content_hub_config->set('api_key', $config->get('api_key'));
              $content_hub_config->set('secret_key', $config->get('secret_key'));
              _as_lift_create_event($conversation->getUserId(),
                'facebook',
                [$config->get('email') => 'email'],
                'Facebook message',
                'Facebook Messenger',
                [['event', '20', 'Bot responded with step: ' . $answer]],
                $lift_config,
                $content_hub_config
              );
            }
          }
        }

        $this->fbService->sendMessages($response, $conversation->getUserId());
      }
    }

  }

  /**
   * Sets the bot's $workflow property.
   *
   * @param BotWorkflowInterface $workflow
   *   The Workflow to set.
   *
   * @todo: Set workflow in the conversation iterator of the process() method.
   */
  public function setWorkflow(BotWorkflowInterface $workflow) {
    $this->workflow = $workflow;
  }

}
