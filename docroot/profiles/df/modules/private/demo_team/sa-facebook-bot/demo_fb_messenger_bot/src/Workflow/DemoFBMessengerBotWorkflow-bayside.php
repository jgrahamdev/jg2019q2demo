<?php

namespace Drupal\demo_fb_messenger_bot\Workflow;

use Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface;
use Drupal\fb_messenger_bot\FacebookService;
use Drupal\fb_messenger_bot\Message\ButtonMessage;
use Drupal\fb_messenger_bot\Message\PostbackButton;
use Drupal\fb_messenger_bot\Message\TextMessage;
use Drupal\fb_messenger_bot\Message\ImageMessage;
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
        new TextMessage('Hi! Thanks for contacting Bayside Financial. What can I help you with?'),
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
      new ButtonMessage('I would be happy to help you with your policy. Do you want your policy details, or agent contact information?',
        array(
          new PostbackButton('Policy Details!', 'builtABot_Yes'),
          new PostbackButton("Agent contact information!", 'builtABot_No'),
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
        new ImageMessage("http://facebookbotd8fxgxzbvvis.devcloud.acquia-sites.com/sites/default/files/styles/large/public/tacoma.jpg"),
        new TextMessage("You have comprehensive auto insurance coverage on your 2009 Toyota Takoma (VIN 1FDLF17G9LCB11V9)."),
        new TextMessage("Your monthly payment of $150 is due on November 15th."),
        new ButtonMessage("Click below for recent news that may affect your coverage...",
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
        new TextMessage("Your dedicated, personal agent is Bud Mortenson"),
        new ImageMessage("http://facebookbotd8fxgxzbvvis.devcloud.acquia-sites.com/sites/default/files/styles/thumbnail/public/agent-7.jpg"),
        new TextMessage("Email: bud@baysidefin-group.com"),
        new TextMessage("Phone: 415-255-1234"),
        new ButtonMessage("Click below for recent news that may affect your coverage...",
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
        new TextMessage("Will Toyota recalls affect your insurance?"),
        new TextMessage('Toyota has been recalling a lot of cars in 2016. Find out if these recalls affect your insurance.'),
        new TextMessage("http://bayside3.forrester.acsitefactory.com"),
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
