<?php

namespace Drupal\demo_fb_messenger_bot\Workflow;

use Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface;
use Drupal\fb_messenger_bot\FacebookService;
use Drupal\fb_messenger_bot\Message\ButtonMessage;
use Drupal\fb_messenger_bot\Message\PostbackButton;
use Drupal\fb_messenger_bot\Message\TextMessage;
use Drupal\fb_messenger_bot\Message\ImageMessage;
use Drupal\fb_messenger_bot\Message\FacebookGenericMessage;
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

    $product_list = \Drupal::httpClient()->get("https://bcp.demo.acsitefactory.com/api/node/product?cache=".rand());
    $product = (string) $product_list->getBody();
    $products = json_decode($product, TRUE);
    /*foreach($products as $product) {
            foreach($product['field_tags'] as $tags) {
//                    if ($tags['target_id'] == 3 || $tags['target_id'] == 6) {
                      if ($tags[0]['value'] == "downtown" || $tags[0]["value"] == "eco") {
                            // Eco collection, target it
                            $target_products[] = $product;
                    }
            }
    }*/ 
$company1 = "Catapult Dynamics is using the " . $products[1]["title"]; //$target_products[0]['title']."and Product Field Tags = ".$product['field_tags']."... done";
$company2 = "Short Stack Integration is using the " . $products[2]["title"];
$company1Desc = $products[1]['field_product_description']; 
$company2Desc = $products[2]['field_product_description']; 
$company1pic = $products[1]['uri']; 
$company2pic = $products[2]['uri']; 
//$pic = rand(1, 3);

    // Set step welcoming user to conversation.
    $welcomeStep = new BotWorkflowStep('Welcome', 'welcome',
      array(
      new TextMessage('Hi! Thanks for contacting Tiffany & Co. What can I help you with?'),
      )
    );

//////////////////////////
//START WIT.AI STORY HERE/
//////////////////////////


    $welcomeStep->setResponseHandlers(
      array(
        '*' => array(
          'handlerMessage' => NULL,
          'goto' => 'whatsYourLook',
        ),
      )
    );

    $stepList['welcome'] = $welcomeStep;

    $yourLookStep = new BotWorkflowStep('What is your look', 'whatsYourLook',
      array(
        new TextMessage("I would be glad to help. Do you have a particular ring or collection in mind?"), 
//        new PostbackButton('Getting engaged', 'copmanyXAmbiance'),
//        new PostbackButton('Upcoming anniversary', 'copmanyYAmbiance'),
//        new PostbackButton('Push present', 'copmanyXAmbiance'),
//        new PostbackButton('Upcoming anniversary', 'copmanyYAmbiance'),
      )
    );

    $yourLookStep->setResponseHandlers(
            array(
        '*' => array(
          'handlerMessage' => NULL,
          'goto' => 'ambianceOptions',
        ),
      )
    );

    $stepList['whatsYourLook'] = $yourLookStep;

    $ambianceStep = new BotWorkflowStep('Ambiance Options', 'ambianceOptions',
      array (
      new TextMessage('Excellent!'), 
      //Catapult Dynamics Title and Image
//      new TextMessage($company1),
//      new ImageMessage($company1pic),
//      new TextMessage($company2),
//      new ImageMessage($company2pic), 
/*      new FacebookGenericMessage('Test', 'Testing',  
          array(
         new TextMessage($company1),
         new ImageMessage($company1pic),
         new PostbackButton('Hyperloop', 'copmanyXAmbiance'),
         )
        ), 
*/ 
      new ButtonMessage("Let me show you a few items from that collection.",
          array(
          new PostbackButton('Hyperloop', 'copmanyXAmbiance'),
          new PostbackButton('Solar Winds', 'copmanyYAmbiance'),          
          ) 
        ),
      )
    );

    $ambianceStep->setResponseHandlers(
      array(
        'copmanyXAmbiance' => array(
          'handlerMessage' => NULL,
          'goto' => 'packageA',
        ),
        'copmanyYAmbiance' => array(
          'handlerMessage' => NULL,
          'goto' => 'packageB',
        ),
      )
    );
    $stepList['ambianceOptions'] = $ambianceStep;

    $packageStep = new BotWorkflowStep('Package A', 'packageA',
      array(
        new TextMessage("Hyperloop"),
        new ImageMessage("https://obio2.drupal8love.com/sites/g/files/rdojhy621/files/styles/hero_image/public/hyperloop_0.jpg"),
        new TextMessage("Inspired by visionary Elon Musk, the Hyperloop collection sparkles with an effortless affinity for perfection."),
        new TextMessage("Click below for more details and to purchase on Tiffany.com"),
        new TextMessage("https://obio2.drupal8love.com/product/tiffany-hyperloop"),
      )
    );

    $stepList['packageA'] = $packageStep;

    $packageStep->setResponseHandlers(
      array(
        '*' => array(
          'handlerMessage' => NULL,
          'goto' => 'closing',
        ),
      )
    );

//    $stepList['ambianceOptions'] = $packageStep;
      $stepList['packageA'] = $packageStep;    

    $packageBStep = new BotWorkflowStep('Package B', 'packageB',
     array(
        new TextMessage("Solar Winds"),
        new ImageMessage("https://obio2.drupal8love.com/sites/g/files/rdojhy621/files/styles/hero_image/public/solar-winds.jpg"),
        new TextMessage("Inspired by visionary Elon Musk, the Solar Winds collection elevates a parade of diamonds to another level."),
        new TextMessage("Click below for more details and to purchase on Tiffany.com"),
        new TextMessage("https://obio2.drupal8love.com/product/tiffany-hyperloop"),
      )
    );

    $packageBStep->setResponseHandlers(
      array(
        '*' => array(
          'handlerMessage' => NULL,
          'goto' => 'closing',
        ),
      )
    );

    $stepList['packageB'] = $packageBStep;

    $closingStep = new BotWorkflowStep('Closing', 'closing',
      array(
        new TextMessage("Thank you for your interest. If you would like a Tiffany's sales consultant to contact you, !"),
        new ImageMessage("https://facebookbotd8fxgxzbvvis.devcloud.acquia-sites.com/sites/default/files/styles/article/public/wendy_1.png"),
        new TextMessage('Your professional design consultant, Wendy, will be contacting you shortly at your phone number 202-555-2362'),
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
