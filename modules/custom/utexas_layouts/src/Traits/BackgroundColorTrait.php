<?php

namespace Drupal\utexas_layouts\Traits;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines layout configuration that includes an option for a background color.
 */
trait BackgroundColorTrait {

  /**
   * {@inheritdoc}
   */
  public function backgroundColorConfiguration() {
    $config['background-color'] = "none";
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function backgroundColorConfigurationForm(array $form, FormStateInterface $form_state) {

    $color_palette = [
      'none' => 'Transparent',
      '074d6a' => 'Bluebonnet ',
      '138791' => 'Turquoise ',
      'f9fafb' => 'Light shading 20% ',
      'e6ebed' => 'Light shading 40% ',
      'c4cdd4' => 'Light shading 60% ',
      '7d8a92' => 'Dark shading 20% ',
      '5e686e' => 'Dark shading 40% ',
      '3e4549' => 'Dark shading 60% ',
      '487d39' => 'Turtlepond ',
      '9d4700' => 'Burnt Orange ',
      'ebeced' => 'Charcoal 20% ',
      'c2c5c8' => 'Charcoal 40% ',
      '858c91' => 'Charcoal 60% ',
      '1f262b' => 'Charcoal 80% ',
      'fbfbf9' => 'Limestone Light 20% ',
      'f2f1ed' => 'Limestone Light 40% ',
      'e6e4dc' => 'Limestone Light 60% ',
      'aba89e' => 'Limestone Dark 20% ',
      '807e76' => 'Limestone Dark 40% ',
      '56544e' => 'Limestone Dark 60% ',
    ];
    $form['background-color-wrapper'] = [
      '#type' => 'details',
      '#title' => 'Background color',
    ];
    $form['background-color-wrapper']['background-color'] = [
      '#type' => 'radios',
      '#options' => $color_palette,
      '#default_value' => !empty($this->configuration['background-color']) ? $this->configuration['background-color'] : "none",
      '#name' => 'background_color',
      '#title' => $this->t('Background color'),
      '#weight' => -1,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitBackgroundColorConfigurationForm(array &$form, FormStateInterface $form_state) {
    $wrapper = $form_state->getValue('background-color-wrapper');
    $this->configuration['background-color'] = $wrapper['background-color'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildBackgroundColor(&$build) {
    if (!empty($this->configuration['background-color'])) {
      if ($this->configuration['background-color'] !== "none") {
        $build['#attributes']['class'][] = 'background-accent';
        $build['#attributes']['class'][] = 'utexas-bg-' . $this->configuration['background-color'];
        $build['#attached']['library'][] = 'utexas_layouts/background-colors';
      }
    }
    return $build;
  }

}
