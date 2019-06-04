<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Form\AgentQuoteForm.
 */

namespace Drupal\dfs_fin\Form;

use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\Entity\Node;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for returning a quote request.
 */
class MemberQuoteForm extends FormBase {

  /**
   * @var AccountInterface $account
   */
  protected $account;

  /**
   * @var QueryFactory $query
   */
  protected $entityQuery;

  public function __construct(AccountInterface $account, QueryFactory $query) {
    $this->account = $account;
    $this->entityQuery = $query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('current_user'),
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_fin_member_quote_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, AccountInterface $user = NULL) {
    $form = [];

    $query = $this->entityQuery->get('node')
      ->condition('type', 'quote')
      ->condition('uid', $user->id());
    $user_quotes = $query->execute();

    foreach ($user_quotes as $nid) {
      $node = Node::load($nid);
      $state = $node->moderation_state->target_id;
      if ($state == 'published') {
        $form['quote_awaits_customer'] = [
          '#type' => 'html_tag',
          '#tag' => 'h3',
          '#value' => t('A quote is currently awaiting your response.')
        ];
        $agent_uid = $node->field_agent_assigned->target_id;
        $agent = User::load($agent_uid);
        $product = Node::load($node->field_associated_product->target_id);
        $offer = 'Agent ' . $agent->field_first_name->value . ' ' . $agent->field_last_name->value . ' returned an offer for ' . $product->getTitle() . ' quoted at ' . $node->field_quote_amount->value;
        $form['quote_nid'] = [
          '#type' => 'hidden',
          '#value' => $nid
        ];
        $form['quote_amount'] = [
          '#type' => 'html_tag',
          '#tag' => 'h4',
          '#value' => $offer
        ];
        $form['quote_accept'] = [
          '#type' => 'select',
          '#title' => 'Do you accept the offer?',
          '#options' => [
            1 => 'Yes, I accept this offer. Please bill me at the quoted rate.',
            0 => 'No thanks, I am declining this offer.'
          ]
        ];
        $form['submit'] = [
          '#type' => 'submit',
          '#value' => t('Send My Response')
        ];
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $quote = Node::load($form_state->getValue('quote_nid'));
    if ($form_state->getValue('quote_accept')) {
      // Load the customer user object.
      $customer = $quote->getRevisionAuthor();
      // Load the agent user object.
      $agent_uid = $quote->field_agent_assigned->target_id;
      $agent = User::load($agent_uid);
      // Load the Agent's location node.
      $agent_location = Node::load($agent->field_user_agent_location->target_id);
      // Load the associated Insurance Product node.
      $product = Node::load($quote->field_associated_product->target_id);
      // Assign the agent to the customer user.
      $customer->set('field_user_agent_location', $agent_location);
      // Customer is now a Member user.
      $customer->addRole('member');
      // Add new product subscription.
      $products = [$product];
      // Check for existing products.
      $subscriptions = $customer->field_subscriptions->getValue();
      foreach ($subscriptions as $subscription) {
        $existing = Node::load($subscription['target_id']);
        $products[] = $existing;
      }
      // Assign product to customer.
      $customer->set('field_subscriptions', $products);
      $customer->save();
      // Show confirmation.
      $_SESSION['fin_modal'] = t('Quote Accepted. Thank you for your business.');
      // Redirect the customer to their account info.
      $form_state->setRedirect('user.page', [], ['fragment' => 'member-info']);
    }
    else {
      // Customer rejected the quote.
      $_SESSION['fin_modal'] = t('Thanks for your consideration. We hope to hear from you again.');
    }
    // In any case, archive the quote request.
    $quote->moderation_state->target_id = 'archived';
    $quote->save();
  }
}