<?php

declare(strict_types=1);

namespace Drupal\utexas_form_elements\Plugin\CKEditor5Plugin;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefault;
use Drupal\editor\EditorInterface;

/**
 * CKEditor 5 Link Styles plugin.
 */
class LinkOptions extends CKEditor5PluginDefault {

  /**
   * {@inheritDoc}
   *
   * Adds options to the link ckeditor plugin.
   *
   * See https://ckeditor.com/docs/ckeditor5/latest/features/link.html#custom-link-attributes-decorators
   */
  public function getDynamicPluginConfig(array $static_plugin_config, EditorInterface $editor): array {
    $config = [];
    $config['link']['decorators'][] = [
      'mode' => 'manual',
      'label' => 'Primary button',
      'attributes' => [
        'class' => 'ut-btn',
      ],
    ];
    $config['link']['decorators'][] = [
      'mode' => 'manual',
      'label' => 'Secondary button',
      'attributes' => [
        'class' => 'ut-btn--secondary',
      ],
    ];
    return $config;
  }
}
