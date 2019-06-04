<?php

namespace Drupal\df_tools_message\Plugin\RulesAction;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Mail\MailManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rules\Core\RulesActionBase;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Provides "Send styled email" rules action.
 *
 * @RulesAction(
 *   id = "df_tools_message_send_styled_email",
 *   label = @Translation("Send styled email"),
 *   category = @Translation("System"),
 *   context = {
 *     "to" = @ContextDefinition("email",
 *       label = @Translation("Send to"),
 *       description = @Translation("Email address drupal will send an email to."),
 *     ),
 *     "subject" = @ContextDefinition("string",
 *       label = @Translation("Subject"),
 *       description = @Translation("The email's subject."),
 *     ),
 *     "message" = @ContextDefinition("string",
 *       label = @Translation("Message"),
 *       description = @Translation("The email's message body. If you want any non-default styling, use inline styles."),
 *     ),
 *     "format" = @ContextDefinition("string",
 *       label = @Translation("Format"),
 *       description = @Translation("The email's message text format."),
 *       default_value = "rich_text",
 *     )
 *   }
 * )
 */
class SendStyledEmail extends RulesActionBase implements ContainerFactoryPluginInterface {

  /**
   * The logger channel the action will write log messages to.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Mail\MailManagerInterface
   */
  protected $mailManager;

  /**
   * Constructs a SendEmail object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Psr\Log\LoggerInterface $logger
   *   The alias storage service.
   * @param \Drupal\Core\Mail\MailManagerInterface $mail_manager
   *   The mail manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, LoggerInterface $logger, MailManagerInterface $mail_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->logger = $logger;
    $this->mailManager = $mail_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('logger.factory')->get('rules'),
      $container->get('plugin.manager.mail')
    );
  }

  /**
   * Send a styled email.
   *
   * @param string $to
   *   Email address of the recipient.
   * @param string $subject
   *   Subject of the email.
   * @param string $message
   *   Email message text.
   * @param string $format
   *   Email message text format.
   */
  protected function doExecute($to, $subject, $message, $format) {
    $params = [
      'subject' => $subject,
      'message' => $message,
      'format' => $format,
    ];
    // Set a unique key for this mail.
    $key = 'rules_action_mail_' . $this->getPluginId();
    $message = $this->mailManager->mail('df_tools_message', $key, $to, LanguageInterface::LANGCODE_NOT_SPECIFIED, $params);
    if ($message['result']) {
      $this->logger->notice('Successfully sent styled email to %recipient', ['%recipient' => $to]);
    }
  }

}
