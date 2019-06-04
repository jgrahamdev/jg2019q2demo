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
$company1 = "Beach-bound retreats"; //$target_products[0]['title']."and Product Field Tags = ".$product['field_tags']."... done";
$company2 = "Family fun-pack";
$company1Desc = $products[1]['field_product_description']; 
$company2Desc = $products[2]['field_product_description']; 
$company1pic = "http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/styles/hero_image/public/Antigua_960x420.jpg"; 
$company2pic = "http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/styles/hero_image/public/UOR960x420.jpg"; 
//$pic = rand(1, 3);

    // Set step welcoming user to conversation.
    $welcomeStep = new BotWorkflowStep('Welcome', 'welcome',
      array(
      new TextMessage('Hi! Thanks for contacting veryBlue. What can I help you with?'),
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
        new TextMessage('OK! Do you have a particular destination in mind?'), 
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
      new TextMessage('Here are our top vacation packages for the week...'), 
      //Catapult Dynamics Title and Image
      new TextMessage($company1),
      new ImageMessage($company1pic),
      new TextMessage($company2),
      new ImageMessage($company2pic), 
      new ButtonMessage("Which of these do you like?",
          array(
          new PostbackButton('Beach bound retreats', 'copmanyXAmbiance'),
          new PostbackButton('Family fun-pack', 'copmanyYAmbiance'),
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
        new TextMessage("The Westin Cape Coral Resort"),
        new ImageMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/image_url_19126.jpg"),
        new TextMessage("4-day/3-night air + hotel from New York (JFK) to Cape Coral"),
        new TextMessage("$345 per person"),
        new TextMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/vacation/westin-cape-coral-resort"),
        new TextMessage("Blue Haven, Turks and Caicos"),
        new ImageMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/image_url_16728.jpg"),
        new TextMessage("4-day/3-night air + hotel nonstop from New York (JFK) to Turks and Caicos"),
        new TextMessage("$650 per person"),
        new TextMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/vacation/blue-haven-turks-and-caicos"),
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
        new TextMessage("Universal Orlando Resort™"),
        new ImageMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/UOR960x420.jpg"),
        new TextMessage("The ultimate Universal Orlando™ vacation begins at one of the spectacular on-site hotels. There's one for every vacation style and budget, with thoughtfully designed rooms and suites that welcome you to enjoy every moment of your stay. "),
        new TextMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/vacation/universal-orlando-resorttm"),
        new TextMessage("Walt Disney World ® Resort"),
        new ImageMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/hotel_714.jpg"),
        new TextMessage("The ultimate Disney vacation begins at one of the spectacular on-site hotels. There's one for every vacation style and budget, with thoughtfully designed rooms and suites that welcome you to enjoy every moment of your stay."),
        new TextMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/vacation/walt-disney-world-r-resort"),
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
        new TextMessage("I'd be happy to put you in touch with an agent!"),
        new ImageMessage("http://tb201703jetbluebn5v4rzp3s.devcloud.acquia-sites.com/sites/default/files/agent.jpg"),
        new TextMessage('A veryBlue agent will be contacting you shortly at your phone number 202-555-2362'),
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
