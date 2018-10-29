<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Defines layout configuration that includes an option for a background accent.
 */
class BackgroundAccent extends DefaultConfig {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['blur'] = TRUE;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    // The 'target_bundle in this form field limits this to the
    // media entities defined in /admin/structure/media.
    // Theoretically, a video entity could be referenced here,
    // with additional preprocess logic for rendering that video.
    $form['background-accent'] = [
      '#title' => $this->t('Background Accent'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'media',
      '#description' => $this->t('Optionally, add a background image for the "related content" region. For best results, upload an image of minimum size 1500x500 pixels. Allows GIF, PNG, JPG, JPEG.'),
      '#selection_handler' => 'default',
      '#selection_settings' => [
        'target_bundles' => ['utexas_image'],
      ],
    ];
    if (!empty($this->configuration['background-accent'])) {
      $form['background-accent']['#default_value'] = \Drupal::entityTypeManager()->getStorage('media')->load($this->configuration['background-accent']);
    }
    $form['blur'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply blur to image?'),
      '#default_value' => $this->configuration['blur'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['blur'] = $form_state->getValue('blur');
    $this->configuration['background-accent'] = $form_state->getValue('background-accent');
  }

}
