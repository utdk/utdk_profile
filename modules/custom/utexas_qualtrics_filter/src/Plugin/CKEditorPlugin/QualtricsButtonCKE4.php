<?php

namespace Drupal\utexas_qualtrics_filter\Plugin\CKEditorPlugin;

use Drupal\ckeditor\CKEditorPluginBase;
use Drupal\editor\Entity\Editor;

/**
 * Defines the "Qualtricsbutton" plugin.
 *
 * @CKEditorPlugin(
 *   id = "qualtricsbutton",
 *   label = @Translation("Qualtrics button"),
 *   module = "utexas_qualtrics_filter"
 * )
 */
class QualtricsButtonCKE4 extends CKEditorPluginBase {

  /**
   * Implements \Drupal\ckeditor\Plugin\CKEditorPluginInterface::getFile().
   */
  public function getFile() {
    $path = \Drupal::service('extension.list.module')->getPath('utexas_qualtrics_filter');
    return $path . '/js/ckeditor4_plugins/qualtricsbutton/plugin.js';
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(Editor $editor) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function isInternal() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getButtons() {
    return [
      'qualtricsbutton' => [
        'label' => 'Qualtrics Button',
        'image' => \Drupal::service('extension.list.module')->getPath('utexas_qualtrics_filter') . '/js/ckeditor4_plugins/qualtricsbutton/icons/qualtricsbutton.png',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getConfig(Editor $editor) {
    return [];
  }

}
