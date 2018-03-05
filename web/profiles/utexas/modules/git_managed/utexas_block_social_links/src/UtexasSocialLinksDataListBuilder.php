<?php

namespace Drupal\utexas_block_social_links;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\file\Entity\File;
use Drupal\Core\Render\Markup;

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
    $fid = $entity->get('icon');
    $file = File::load($fid);
    $icon_markup = "Missing Image";
    if ($file) {
      $filename = $file->getFileUri();
      $icon = file_get_contents($filename);
      $icon_markup = Markup::create($icon);
    }
    $row['label'] = $entity->label();
    $row['id'] = $entity->id();
    $row['icon'] = $icon_markup;
    return $row + parent::buildRow($entity);
  }

}
