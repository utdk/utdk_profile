<?php

namespace Drupal\utexas_entity_usage\Plugin\EntityUsage\Track;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Tracks usage of entities related in utexas_hero fields.
 *
 * @EntityUsageTrack(
 *   id = "utexas_featured_highlight_field",
 *   label = @Translation("UTexas Featured Highlight Field"),
 *   description = @Translation("Tracks relationships created with 'Featured Highlight' fields."),
 *   field_types = {"utexas_featured_highlight"},
 *   source_entity_class = "Drupal\Core\Entity\FieldableEntityInterface",
 * )
 */
class FeaturedHighlight extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    if (isset($value['media'])) {
      $references[] = 'media|' . $value['media'];
    }
    // Process copy field.
    $references = array_merge($references, $this->parseMediaFromText($value['copy']['value']));
    return $references;
  }

}
