<?php

namespace Drupal\utexas_entity_usage\Plugin\EntityUsage\Track;

use Drupal\Core\Field\FieldItemInterface;

/**
 * Tracks usage of entities related in utexas_hero fields.
 *
 * @EntityUsageTrack(
 *   id = "utexas_hero_field",
 *   label = @Translation("UTexas Hero Field"),
 *   description = @Translation("Tracks relationships created with 'Hero' fields."),
 *   field_types = {"utexas_hero"},
 *   source_entity_class = "Drupal\Core\Entity\FieldableEntityInterface",
 * )
 */
class Hero extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    if (isset($value['media'])) {
      $references[] = 'media|' . $value['media'];
    }
    return $references;
  }

}
