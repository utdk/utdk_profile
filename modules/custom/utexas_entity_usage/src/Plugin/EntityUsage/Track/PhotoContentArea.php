<?php

namespace Drupal\utexas_entity_usage\Plugin\EntityUsage\Track;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Tracks usage of entities referenced in a custom field type.
 *
 * @EntityUsageTrack(
 *   id = "utexas_photo_content_area",
 *   label = @Translation("UTexas Photo Content Area"),
 *   description = @Translation("Tracks relationships created with 'Photo Content Area' fields."),
 *   field_types = {"utexas_photo_content_area"},
 *   source_entity_class = "Drupal\Core\Entity\FieldableEntityInterface",
 * )
 */
#[
  EntityUsageTrack(
    id: 'utexas_photo_content_area',
    label: new TranslatableMarkup('UTexas Photo Content Area'),
    description: new TranslatableMarkup("Tracks relationships created with 'Photo Content Area' fields."),
    field_types: ['utexas_photo_content_area'],
    source_entity_class: 'Drupal\Core\Entity\FieldableEntityInterface'
  )
]
class PhotoContentArea extends UtexasEntityUsageTrackBase {

  /**
   * {@inheritdoc}
   */
  public function getTargetEntities(FieldItemInterface $item): array {
    $references = [];
    $value = $item->getValue();
    // The entity_usage module is designed to execute implementations of
    // getTargetEntities() on each delta of a field instance; since our custom
    // field types that have media upload fields only allow a single media item
    // at a time, we can safely assume that the media value below is always a
    // a single integer, not a string.
    if (isset($value['media'])) {
      $references[] = 'media|' . $value['media'];
    }
    // Process media entities references in copy field.
    // UtexasEntityUsageTrackBase::parseMediaFromText() largely replicates logic
    // from the entity_usage module's MediaEmbed::parseEntitiesFromText().
    $references = array_merge($references, $this->parseMediaFromText($value['copy']['value']));
    return $references;
  }

}
