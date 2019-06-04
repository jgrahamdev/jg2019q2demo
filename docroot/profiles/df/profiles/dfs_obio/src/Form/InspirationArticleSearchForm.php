<?php

namespace Drupal\dfs_obio\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\Entity\Node;

/**
 * Provides a form to search 'inspiration' articles.
 */
class InspirationArticleSearchForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dfs_obio_inspiration_article_search_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = [];

    $form['nid'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Search'),
      '#title_display' => 'invisible',
      '#required' => TRUE,
      '#target_type' => 'node',
      '#selection_settings' => [
        'target_bundles' => ['article'],
      ],
    ];

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Verify that the node exists.
    if ($form_state->getValue('nid') && !$node = Node::load($form_state->getValue('nid'))) {
      $form_state->setErrorByName('nid', $this->t('The requested article could not be found.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Redirect the user to the selected node.
    if ($node = Node::load($form_state->getValue('nid'))) {
      $form_state->setRedirect('entity.node.canonical', ['node' => $node->id()]);
    }
  }

}
