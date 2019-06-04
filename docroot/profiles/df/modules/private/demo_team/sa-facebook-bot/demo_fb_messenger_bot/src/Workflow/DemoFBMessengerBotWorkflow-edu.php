<?php

namespace Drupal\demo_fb_messenger_bot\Workflow;

use Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface;
use Drupal\fb_messenger_bot\FacebookService;
use Drupal\fb_messenger_bot\Message\ButtonMessage;
use Drupal\fb_messenger_bot\Message\PostbackButton;
use Drupal\fb_messenger_bot\Message\TextMessage;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\fb_messenger_bot\Step\BotWorkflowStep;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\fb_messenger_bot\Workflow\FBMessengerBotWorkflow;
use Psr\Log\LoggerInterface;

/**
 * Class DemoFBMessengerBotWorkflow.
 *
 * @package Drupal\fb_messenger_bot\Workflow
 */
class DemoFBMessengerBotWorkflow extends FBMessengerBotWorkflow {

  /**
   * Constructs the demo fb messenger bot workflow.
   *
   * @param ConfigFactoryInterface $configFactory
   *   The config factory.
   * @param \Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface $conversationFactory
   *   The conversation factory.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   * @param FacebookService $fbService
   *   The facebook service.
   * @param \Psr\Log\LoggerInterface $logger
   *   A logger instance.
   */
  public function __construct(ConfigFactoryInterface $configFactory, ConversationFactoryInterface $conversationFactory, TranslationInterface $stringTranslation, FacebookService $fbService, LoggerInterface $logger) {
    parent::__construct($configFactory, $conversationFactory, $stringTranslation, $fbService, $logger);
  }

  /**
   * Helper function to build out steps.
   *
   * @return array (BotWorkflowStepInterface)
   *   An array of BotWorkflowStepInterfaces.
   *
   */
  public function buildSteps($conversation, $receivedMessage) {
    $stepList = array();

    // Set step welcoming user to conversation.
    $welcomeStep = new BotWorkflowStep('Welcome', 'welcome',
      array(
        new TextMessage('Hi! Thanks for contacting Bayside University. What can I help you with?'),
      )
    );

    $welcomeStep->setResponseHandlers(
      array(
        '*' => array(
          'handlerMessage' => NULL,
          'goto' => 'builtABot',
        ),
      )
    );

    $stepList['welcome'] = $welcomeStep;

    $builtStep = new BotWorkflowStep('Built A Bot', 'builtABot',
      new ButtonMessage('I would be happy to help you. Do you want your class schedule details, or professor contact information?',
        array(
          new PostbackButton('Class schedule!', 'builtABot_Yes'),
          new PostbackButton("Professor contact information!", 'builtABot_No'),
        )
      )
    );

    $builtStep->setResponseHandlers(
      array(
        'builtABot_Yes' => array(
          'handlerMessage' => NULL,
          'goto' => 'veteranBuilder',
        ),
        'builtABot_No' => array(
          'handlerMessage' => NULL,
          'goto' => 'neverBuilt',
        ),
      )
    );

    $stepList['builtABot'] = $builtStep;

    $veteranStep = new BotWorkflowStep('Veteran Builder', 'veteranBuilder',
      array(
        new TextMessage("Glad to help!"),
        new TextMessage("Your next class, Cellular & Molecular Foundations (Bio 201), is at 10:30 a.m."),
        new TextMessage("The class will be held in Rogers Hall, Room 210."),
        new ButtonMessage("Click below for latest on-campus news... ",
          array(
            new PostbackButton('Latest news', 'veteranBuilder_final'),
          )
        ),
      )
    );

    $veteranStep->setResponseHandlers(
      array(
        'veteranBuilder_final' => array(
          'handlerMessage' => NULL,
          'goto' => 'closing',
        ),
      )
    );

    $stepList['veteranBuilder'] = $veteranStep;

    $neverBuiltStep = new BotWorkflowStep('Never Built', 'neverBuilt',
      array(
        new TextMessage("Your professor for your upcoming class, Cellular & Molecular Foundations is Dr. Sipprell"),
        new TextMessage("Email: sipprella@bayside.edu"),
        new TextMessage("Phone: 704-337-2746"),
        new ButtonMessage("Click below for latest on-campus news... ",
          array(
            new PostbackButton('Latest news', 'neverBuilt_final'),
          )
        ),
      )
    );

    $neverBuiltStep->setResponseHandlers(
      array(
        'neverBuilt_final' => array(
          'handlerMessage' => NULL,
          'goto' => 'closing',
        ),
      )
    );

    $stepList['neverBuilt'] = $neverBuiltStep;

    $closingStep = new BotWorkflowStep('Closing', 'closing',
      array(
        new TextMessage("Queens Unveils Lounge for Veteran and Military Students"),
        new TextMessage('On Monday, Queens unveiled the newest addition to campus...'),
        new TextMessage("http://js09302016lift4urnu3momc.devcloud.acquia-sites.com/node/186"),
      )
    );

    $stepList['closing'] = $closingStep;

    // Set validation callbacks.
    foreach ($stepList as $step) {
      $step_name = $step->getMachineName();
      switch ($step_name) {
        case 'welcome':
          $validationFunction = $this->getTextMessageValidatorFunction();
          $invalidResponse = $this->getGenericValidationFailMessage();
          break;

        case 'builtABot':
          $allowedPayloads = ['builtABot_Yes', 'builtABot_No'];
          $validationFunction = $this->getPostbackValidatorFunction($allowedPayloads);
          $invalidResponse = $this->getPostbackValidationFailMessage();
          break;

        case 'veteranBuilder':
          $allowedPayloads = ['veteranBuilder_final'];
          $validationFunction = $this->getPostbackValidatorFunction($allowedPayloads);
          $invalidResponse = $this->getPostbackValidationFailMessage();
          break;

        case 'neverBuilt':
          $allowedPayloads = ['neverBuilt_final'];
          $validationFunction = $this->getPostbackValidatorFunction($allowedPayloads);
          $invalidResponse = $this->getPostbackValidationFailMessage();
          break;

        default:
          $validationFunction = $this->getGenericValidatorFunction();
          $invalidResponse = $this->getGenericValidationFailMessage();
          break;
      }

      $step->setValidationCallback($validationFunction);
      $step->setInvalidResponseMessage($invalidResponse);
    }

    return $stepList;
  }

  /**
   * Overrides default implementation provided in BotWorkflowTrait.
   *
   * {@inheritdoc}
   */
  protected function getTrollingMessage() {
    $messages = array();
    $messages[] = new TextMessage("Hey! Trying to demo here!");
    $messages[] = new TextMessage("Read the last message we sent out to get an idea of what kind of response we're expecting.");
    $messages[] = new TextMessage("You can also start over by sending us the text 'Start Over'.");
    return $messages;
  }

}
