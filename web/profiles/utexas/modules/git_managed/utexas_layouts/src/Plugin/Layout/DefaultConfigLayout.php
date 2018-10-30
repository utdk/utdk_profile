<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Defines base layout configuration that includes edge-to-edge display.
 */
class DefaultConfigLayout extends LayoutDefault implements PluginFormInterface {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'class' => '',
      'full_width' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    if (!empty($this->configuration['class'])) {
      $build['#attributes']['class'][] = $this->configuration['class'];
    }
    if (!empty($this->configuration['full_width'])) {
      $build['#attributes']['class'][] = 'edge-to-edge';
    }
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['full_width'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Span entire screen width'),
      '#default_value' => $this->configuration['full_width'],
      '#description' => 'When checked, this section will fit the screen, edge-to-edge.',
    ];
    $form['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS class'),
      '#default_value' => $this->configuration['class'],
      '#description' => $this->t("Added to the section's wrapper div. Separate multiple classes with spaces."),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['class'] = $form_state->getValue('class');
    $this->configuration['full_width'] = $form_state->getValue('full_width');
  }

}
