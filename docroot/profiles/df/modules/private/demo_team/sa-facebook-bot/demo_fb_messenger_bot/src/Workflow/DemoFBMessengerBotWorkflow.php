<?php

namespace Drupal\demo_fb_messenger_bot\Workflow;

use Drupal\fb_messenger_bot\Conversation\ConversationFactoryInterface;
use Drupal\fb_messenger_bot\FacebookService;
use Drupal\fb_messenger_bot\Message\ButtonMessage;
use Drupal\fb_messenger_bot\Message\ListMessage;
use Drupal\fb_messenger_bot\Message\TextMessage;
use Drupal\fb_messenger_bot\Message\QuickMessage;
use Drupal\fb_messenger_bot\Message\ImageMessage;
use Drupal\fb_messenger_bot\Message\MediaMessage;
use Drupal\fb_messenger_bot\Message\PostbackButton;
use Drupal\fb_messenger_bot\Message\UrlButton;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\fb_messenger_bot\Step\BotWorkflowStep;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\fb_messenger_bot\Workflow\FBMessengerBotWorkflow;
use Drupal\fb_messenger_bot\Conversation\BotConversationInterface;
use Psr\Log\LoggerInterface;
use GuzzleHttp\Exception\RequestException;

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
   * Build the steps.
   *
   * @param $conversation
   * @param $receivedMessage
   *
   * @return array
   */
  public function buildSteps($conversation, $receivedMessage) {
    $stepList = [];
    $products = [];
    $client = \Drupal::httpClient();

    if (!empty($conversation)) {
      $config = \Drupal::service('config.factory')
        ->getEditable('fb_bot.' . $conversation->getUserId());
      if (!empty($config->get('url'))) {
        $final_url = $config->get('url');
        try {
          $product_list = $client->get($final_url . '/api/node/collection?_format=json&cache=' . rand());
          $product = (string) $product_list->getBody();
          $products = json_decode($product, TRUE);
        } catch (RequestException $e) {
          watchdog_exception('fb_messenger_bot', $e);
          $config->set('name', '');
          $config->set('url', '');
          $config->set('email', '');
          $config->save();
        }
        try {
          $location_list = $client->get($final_url . '/api/node/location?_format=json&cache=' . rand());
          $location = (string) $location_list->getBody();
          $locations = json_decode($location, TRUE);
        } catch (RequestException $e) {
          watchdog_exception('fb_messenger_bot', $e);
        }
      }

      $getStarted = new BotWorkflowStep('Get Started', 'welcome', [
        new QuickMessage("Welcome! What can I help you with today?", [
          [
            'content_type' => 'text',
            'title' => 'Book an appointment',
            'payload' => 'optionAAnswer'
          ],
          [
            'content_type' => 'text',
            'title' => 'Explore products',
            'payload' => 'optionBAnswer'
          ]
        ])
      ]);
      $getStarted->setResponseHandlers([
        'Book an appointment' => [
          'handlerMessage' => NULL,
          'goto' => 'bookApt'
        ],
        'Explore products' => [
          'handlerMessage' => NULL,
          'goto' => 'productOptions'
        ]
      ]);
      $stepList['welcome'] = $getStarted;

      $bookApt = new BotWorkflowStep('Book Appointment', 'bookApt',
        [
          new QuickMessage("Awesome! I need just a few things to help you with that. First, help me understand what showroom would you like to visit. Where are you located right now?",
            [
              ['content_type' => 'location']
            ]
          )
        ]
      );
      $bookApt->setResponseHandlers([
        '*' => [
          'handlerMessage' => NULL,
          'goto' => 'locationLookup'
        ]
      ]);
      $stepList['bookApt'] = $bookApt;

      $locationA = 'Boston';
      if (isset($locations[0])) {
        $locationA = $locations[0]['title'];
      }
      $locationB = 'Cambridge';
      if (isset($locations[1])) {
        $locationB = $locations[1]['title'];
      }
      $locationLookup = new BotWorkflowStep('Location Lookup', 'locationLookup',
        [
          new QuickMessage("Thanks! We have two showrooms nearby - click on the one you’d prefer to visit.", [
            [
              'content_type' => 'text',
              'title' => $locationA,
              'payload' => 'answerLocationA'
            ],
            [
              'content_type' => 'text',
              'title' => $locationB,
              'payload' => 'answerLocationB'
            ]
          ])
        ]
      );
      $locationLookup->setResponseHandlers(
        [
          $locationA => ['handlerMessage' => NULL, 'goto' => 'goLocationA'],
          $locationB => ['handlerMessage' => NULL, 'goto' => 'goLocationB']
        ]
      );
      $stepList['locationLookup'] = $locationLookup;

      $anyLocationResponse = [
        '*' => [
          'handlerMessage' => NULL,
          'goto' => 'productOptions'
        ]
      ];
      $anyProducts = 'Are you interested in any specific products you want to see?';
      $goLocationA = new BotWorkflowStep($locationA, 'goLocationA',
        [
          new TextMessage('Great, ' . $locationA . ' it is! ' . $anyProducts)
        ]
      );
      $goLocationA->setResponseHandlers($anyLocationResponse);
      $stepList['goLocationA'] = $goLocationA;
      $goLocationB = new BotWorkflowStep($locationB, 'goLocationB',
        [
          new TextMessage('Great, ' . $locationB . ' it is! ' . $anyProducts)
        ]
      );
      $goLocationB->setResponseHandlers($anyLocationResponse);
      $stepList['goLocationB'] = $goLocationB;

      // Only show startup tagged products if available.
      $startup_tagged = [];
      if (isset($products[0]['field_tags'])) {
        foreach ($products as $product) {
          $tags = explode(', ', $product['field_tags']);
          if (is_array($tags) && in_array('startup', $tags)) {
            $startup_tagged[] = $product;
          }
        }
      }
      if (isset($startup_tagged[0]) && isset($startup_tagged[1])) {
        $products = $startup_tagged;
      }
      // Reset array so keys start at 0.
      array_values($products);
      // Only use the first two values from the results.
      if (isset($products[0]) && isset($products[1])) {
        $company1Product = $products[0]['title'];
        $company2Product = $products[1]['title'];
        $company1Desc = $products[0]['field_collection_description'];
        $company2Desc = $products[1]['field_collection_description'];
        $company1Path = $products[0]['path'] . '?identityType=facebook&identity=' . $conversation->getUserId();
        $company2Path = $products[1]['path'] . '?identityType=facebook&identity=' . $conversation->getUserId();
        $company1pic = $products[0]['uri'];
        $company2pic = $products[1]['uri'];
        $listElements = [
          [
            'title' => $company1Product,
            'image_url' => $company1pic,
            'subtitle' => substr($company1Desc, 0, 100),
            'buttons' => [
              [
                'type' => 'web_url',
                'title' => 'View More',
                'url' => $company1Path
              ],
            ]
          ],
          [
            'title' => $company2Product,
            'image_url' => $company2pic,
            'subtitle' => substr($company2Desc, 0, 100),
            'buttons' => [
              [
                'type' => 'web_url',
                'title' => 'View More',
                'url' => $company2Path
              ],
            ]
          ]
        ];
        $productStep = new BotWorkflowStep('Product Options', 'productOptions',
          [
            new TextMessage("Okay, here are some options we think you'll like."),
            new ListMessage($listElements),
            new QuickMessage('Ready to book an appointment?', [
              [
                'content_type' => 'text',
                'title' => 'See ' . $company1Product,
                'payload' => 'company1Payload'
              ],
              [
                'content_type' => 'text',
                'title' => 'See ' . $company2Product,
                'payload' => 'company2Payload'
              ]
            ])
          ]
        );
        $productStep->setResponseHandlers([
          'See ' . $company1Product => [
            'handlerMessage' => NULL,
            'goto' => 'closing'
          ],
          'See ' . $company2Product => [
            'handlerMessage' => NULL,
            'goto' => 'closing'
          ]
        ]);
        $stepList['productOptions'] = $productStep;
      }

      $closingStep = new BotWorkflowStep('Closing step', 'closing', [$this->bookAptLink($config)]);
      $stepList['closing'] = $closingStep;
    }

    // Set validation callbacks.
    foreach ($stepList as $step) {
      $step_name = $step->getMachineName();
      switch ($step_name) {
        case 'locationLookup':
          $validationFunction = $this->getLocationValidationFunction($conversation);
          break;
        default:
          $validationFunction = $this->getActivationValidationFunction($conversation);
          break;
      }
      $invalidResponse = $this->getActivationFailMessage();
      $step->setValidationCallback($validationFunction);
      $step->setInvalidResponseMessage($invalidResponse);
    }

    return $stepList;
  }

  protected function getLocationValidationFunction($conversation) {
    $locationValidator = function($input) use($conversation) {
      if (empty($conversation)) {
        return FALSE;
      }
      $config = \Drupal::service('config.factory')->getEditable('fb_bot.' . $conversation->getUserId());
      $message_content = trim($input['message_content']);
      if (preg_match('/^boston/i', $message_content)) {
        $config->set('showroom', 'Boston')->save();
      }
      elseif (preg_match('/^washington/i', $message_content)) {
        $config->set('showroom', 'Washington D.C.')->save();
      }
      elseif (preg_match('/^san/i', $message_content)) {
        $config->set('showroom', 'San Francisco')->save();
      }
      return TRUE;
    };
    return $locationValidator;
  }

  /**
   *
   * @return \Drupal\fb_messenger_bot\Message\MessageInterface
   *   The message to send back to the user.
   *
   */
  public static function getActivationFailMessage() {
    $outgoingMessage = new TextMessage("Your account is not activated to be used with this Bot. To activate, type: activate first_name email https://site.url lift_account_id lift_site_id content_hub_api_key content_hub_secret_key
    (Example: activate Bud bud.mortenson@acquia.com https://bmclient85z.devcloud.acquia-sites.com BUDSITE bmclient85z f0O B4r)");
    return $outgoingMessage;
  }

  /**
   * Set up a zip code validation function.
   *
   * @param $conversation
   *
   * @return callable
   *   A zip code validation function.
   */
  protected function getActivationValidationFunction($conversation) {
    $activationValidator = function($input) use($conversation) {
      if (!empty($conversation)) {
        $config = \Drupal::service('config.factory')->getEditable('fb_bot.' . $conversation->getUserId());
        if (empty($config->get('url'))) {
          return FALSE;
        }
        return TRUE;

      } else{
        return TRUE;
      }
    };
    return $activationValidator;
  }

  /**
   * Overrides default implementation provided in BotWorkflowTrait.
   *
   * {@inheritdoc}
   */
  protected function getTrollingMessage() {
    $messages = [];
    $messages[] = new TextMessage("Hey! Trying to demo here!");
    $messages[] = new TextMessage("Read the last message we sent out to get an idea of what kind of response we're expecting.");
    $messages[] = new TextMessage("You can also start over by sending us the text 'Get Started'.");
    return $messages;
  }

  /**
   * Overrides default implementation provided in BotWorkflowTrait.
   *
   * {@inheritdoc}
   */
  protected function preprocessSpecialMessages(array $receivedMessage, BotConversationInterface &$conversation) {
    $config = \Drupal::service('config.factory')->getEditable('fb_bot.' . $conversation->getUserId());
    $moduleHandler = \Drupal::service('module_handler');
    $email_validator = \Drupal::service('email.validator');
    $specialMessages = [];
    $message_content = trim($receivedMessage['message_content']);

    if (preg_match('/^activate/i', $message_content)) {
      $arguments = explode(' ', $message_content);
      if (count($arguments) != 8) {
        $specialMessages[] = new TextMessage('An insufficient number of arguments has been provided.');
        return $specialMessages;
      }
      list($foo, $name, $email, $url, $lift_account, $lift_site_id, $api_key, $secret_key) = $arguments;

      // Remove quotes from url
      $url = str_replace("'", "", $url);
      // Validate url
      $url_validate = parse_url($url);
      try {
        $response = \Drupal::httpClient()->get($url);
      }
      catch (RequestException $e) {
        watchdog_exception('fb_messenger_bot', $e);
      }
      // Check for https then proceed forward.
      if ($url_validate['scheme'] != 'https') {
        $specialMessages[] = new TextMessage('The URL must start with https');
      } elseif (!$email_validator->isValid($email)) {
        $specialMessages[] = new TextMessage('The email "' . $email . '" could not be validated');
      } elseif (isset($response) && $response->getStatusCode() == 200) {
        $config->set('name', $name);
        $config->set('url', $url);
        $config->set('email', $email);
        $config->set('lift_account', $lift_account);
        $config->set('lift_site_id', $lift_site_id);
        $config->set('api_key', $api_key);
        $config->set('secret_key', $secret_key);
        $config->save();
        // When Lift is enabled, log the activation message event.
        if ($moduleHandler->moduleExists('as_lift')) {
          $lift_config = \Drupal::service('config.factory')->getEditable('acquia_lift.settings');
          $lift_config->set('credential.account_id', $config->get('lift_account'));
          $lift_config->set('credential.site_id', $config->get('lift_site_id'));
          $content_hub_config = \Drupal::service('config.factory')->getEditable('acquia_contenthub.admin_settings');
          $content_hub_config->set('api_key', $config->get('api_key'));
          $content_hub_config->set('secret_key', $config->get('secret_key'));
          _as_lift_create_event($conversation->getUserId(),
            'facebook',
            [$email => 'email'],
            'Facebook message',
            'Facebook Messenger',
            [['event', '20', 'activate']],
            $lift_config,
            $content_hub_config
          );
          _as_tracking_event('fb_bot_activate', $email);
        }
        $specialMessages[] = new TextMessage('Thanks for registering, ' . $name . '!');
        $specialMessages[] = new TextMessage('You can say "Get Started" to start over at any time.');
      }
      else {
        $specialMessages[] = new TextMessage('Sorry, that url does not resolve.');
      }
    }

    // On every request, set Lift event when the email is set for the profile.
    if ($moduleHandler->moduleExists('as_lift') && $email_validator->isValid($config->get('email'))) {
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
        [['event', '20', 'User input: "' . $message_content . '"']],
        $lift_config,
        $content_hub_config
      );
    }

    // Get Started/Closing functionality.
    if (preg_match('/^get( )*started$/i', $message_content)) {
      $specialMessages = $this->startOver($conversation);
    }

    // Listen for a product request at any time.
    // @todo: loop over product list and generate listeners.
    if (preg_match('/^see( )*eco/i', $message_content)) {
      $config->set('collection', 'Eco')->save($config);
      $specialMessages[] = $this->bookAptLink($config);
    }
    elseif (preg_match('/^see( )*tech/i', $message_content)) {
      $config->set('collection', 'Technology')->save();
      $specialMessages[] = $this->bookAptLink($config);
    }


    // Reset everything.
    if (preg_match('/^reset( )*bot/i', $message_content)) {
      $config->clear('name');
      $config->clear('url');
      $config->clear('email');
      $config->clear('lift_account');
      $config->clear('lift_site_id');
      $config->clear('api_key');
      $config->clear('secret_key');
      $config->save();
      $specialMessages[] = new TextMessage('Bot has been reset');
    }

    // Check settings.
    if (preg_match('/^check( )*bot/i', $message_content)) {
      $specialMessages[] = new TextMessage('Name: ' . $config->get('name'));
      $specialMessages[] = new TextMessage('Url: ' . $config->get('url'));
      $specialMessages[] = new TextMessage('Email: ' . $config->get('email'));
      $specialMessages[] = new TextMessage('Lift Account: ' . $config->get('lift_account'));
      $specialMessages[] = new TextMessage('Lift Site Id: ' . $config->get('lift_site_id'));
      $specialMessages[] = new TextMessage('Showroom: ' . $config->get('showroom'));
      $specialMessages[] = new TextMessage('Collection: ' . $config->get('collection'));
      $specialMessages[] = new TextMessage('Booking Url: ' . $config->get('booking_url'));
    }

    return $specialMessages;
  }

  protected function bookAptLink($config) {
    $booking_url = $config->get('url') . '/book-apt?email=' . $config->get('email');
    if ($showroom = $config->get('showroom')) {
      $booking_url = $booking_url . '&showroom=' . $showroom;
    }
    if ($collection = $config->get('collection')) {
      $booking_url = $booking_url . '&collection=' . $collection;
    }
    $config->set('booking_url', $booking_url)->save();
    return new ButtonMessage(
      "Cool, almost done! I just need your contact details so please click on the link below to finish your appointment booking.",
      [
        new UrlButton('Book Appointment', $config->get('booking_url') . '&cache=' . rand())
      ]
    );
  }

}
