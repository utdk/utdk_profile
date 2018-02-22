<?php

namespace Drupal\utexas_block_social_links;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of UTexas Block Social Links entities.
 */
class UtexasSocialLinksDataListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Social Links');
    $header['id'] = $this->t('Machine name');
    $header['icon'] = $this->t('Social Account Icon');
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['icon'] = $entity->get('icon');
    return $row + parent::buildRow($entity);
  }

}
