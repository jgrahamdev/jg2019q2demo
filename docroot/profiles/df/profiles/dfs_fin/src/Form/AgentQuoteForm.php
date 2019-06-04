<?php

/**
 * @file
 * Contains \Drupal\dfs_fin\Form\AgentQuoteForm.
 */

namespace Drupal\dfs_fin\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\Query\QueryFactory;

/**
 * Provides a form for returning a quote request.
 */
class AgentQuoteForm extends FormBase {

  /**
   * @var QueryFactory $query
   */
  protected $entityQuery;

  public function __construct(QueryFactory $query) {
    $this->entityQuery = $query;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    // Instantiates this form class.
    return new static(
      $container->get('entity.query')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_fin_agent_quote_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, NodeInterface $node = NULL) {
    $form = [];

    $nid = $node->id();
    $form['quote_nid'] = array('#type' => 'hidden', '#value' => $nid);
    // Ensure Quote node is currently 'draft'.
    $state = $node->moderation_state->target_id;
    if ($state == 'review') {
      // Get the default product to be recommended.
      $default_product = 0;
      $tid = $node->field_tags->target_id;
      if ($term = Term::load($tid)) {
        $default_title = $term->getName() . ' Insurance';
        $query = $this->entityQuery->get('node');
        $query->condition('title', $default_title);
        $default_product = $query->execute();
        $default_product = reset($default_product);
      }

      // Options for selecting insurance product.
      $query = $this->entityQuery->get('node');
      $query->condition('type', 'insurance_product');
      $products = $query->execute();
      $options = ['0' => 'None (Quote Request Rejected)'];
      foreach ($products as $nid) {
        $product = Node::load($nid);
        $title = $product->getTitle();
        $options[$nid] = $title;
      }

      $form['product_select'] = [
        '#type' => 'select',
        '#title' => t('Select product based on the quote request'),
        '#options' => $options,
        '#default_value' => $default_product,
      ];

      $form['quote_amount'] = [
        '#type' => 'textfield',
        '#title' => t('Amount Quoted'),
        '#size' => 10,
        '#maxlength' => 32,
        '#required' => TRUE,
        '#default_value' => '$150/mo',
        '#states' => array(
          'invisible' => array(
            ':input[name="product_select"]' => ['value' => 0],
          ),
        )
      ];

      $form['submit'] = [
        '#type' => 'submit',
        '#value' => t('Send Quote')
      ];
    }

    if ($state == 'published') {
        $customer = $node->getRevisionAuthor();
        $customer_uid = $customer->id();
        $form['quote_node_published'] = [
          '#type' => 'html_tag',
          '#tag' => 'div',
          '#value' => t('This quote is currently awaiting <a href="/user/@customer_uid">customer</a> response.', ['@customer_uid' => $customer_uid])
        ];
      }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $node = Node::load($form_state->getValue('quote_nid'));
    $langcode = $node->language()->getId();
    $uid = $node->uid->target_id;
    $user = User::load($uid);
    $to = $user->getEmail();
    if ($form_state->getValue('product_select') != 0) {
      // Set quote amount on the node via the Agent input.
      $amount = $form_state->getValue('quote_amount');
      $node->set('field_quote_amount', $amount);
      // Set quote to Published so that it can be approved by the prospect.
      $node->moderation_state->target_id = 'published';
      drupal_set_message('Quote Sent for customer confirmation.', 'status');
      $message = \Drupal::service('plugin.manager.mail')->mail('dfs_fin_quotes', 'quote_approved', $to, $langcode);
    }
    else {
      // Rejected quote requests get archived immediately.
      $node->moderation_state->target_id = 'archived';
      drupal_set_message('User will be notified the request for a quote is rejected.', 'status');
      $message = \Drupal::service('plugin.manager.mail')->mail('dfs_fin_quotes', 'quote_rejected', $to, $langcode);
    }
    $node->save();
  }
}