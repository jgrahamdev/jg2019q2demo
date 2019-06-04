<?php

namespace Drupal\dfs_obio_commerce_product\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Plugin\Field\FieldFormatter\EntityReferenceEntityFormatter;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the assembly_instructions formatter.
 *
 * @FieldFormatter(
 *   id = "assembly_instructions",
 *   label = @Translation("Assembly instructions"),
 *   field_types = {
 *     "entity_reference"
 *   }
 * )
 */
class AssemblyInstructionsFormatter extends EntityReferenceEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = parent::viewElements($items, $langcode);
    $entity = $items->getEntity();

    if ($entity->hasField('field_assembly_instructions') && !$entity->field_assembly_instructions->isEmpty()) {
      $fieldRenderable = $entity->field_assembly_instructions->view([
        'label' => 'hidden',
        'type' => 'text_default',
      ]);
      array_unshift($elements, $fieldRenderable);
    }

    return $elements;
  }

}
