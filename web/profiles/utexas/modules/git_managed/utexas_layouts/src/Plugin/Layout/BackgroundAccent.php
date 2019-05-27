<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Drupal\image\Entity\ImageStyle;

/**
 * Defines layout configuration that includes an option for a background accent.
 */
class BackgroundAccent extends DefaultConfigLayout {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config['blur'] = FALSE;
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);
    $validators = [
      'file_validate_extensions' => ['jpg jpeg png gif'],
    ];
    $form['background-accent'] = [
      '#type' => 'media_library_element',
      '#target_bundles' => ['utexas_image'],
      '#cardinality' => 1,
      '#default_value' => !empty($this->configuration['background-accent']) ? $this->configuration['background-accent'] : 0,
      '#name' => 'background_accent',
      '#title' => $this->t('Background image'),
      '#description' => $this->t('Optionally, display an image behind section content. Ideal size is 1500x500 pixels.'),
      '#weight' => 1,
    ];
    $form['blur'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply blur to image?'),
      '#default_value' => $this->configuration['blur'],
      '#weight' => 2,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['blur'] = $form_state->getValue('blur');
    if ($form_state->getValue('background-accent')) {
      $this->configuration['background-accent'] = $form_state->getValue('background-accent')['media_library_selection'];
    }
    else {
      // There is no image.
      $this->configuration['background-accent'] = 0;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    if (!empty($this->configuration['background-accent'])) {
      if ($media = $this->entityTypeManager->getStorage('media')->load($this->configuration['background-accent'])) {
        $media_attributes = $media->get('field_utexas_media_image')->getValue();
        if ($file = $this->entityTypeManager->getStorage('file')->load($media_attributes[0]['target_id'])) {
          $uri = $file->getFileUri();
          $build['#attributes']['class'][] = 'background-accent';
          $build['#background_image'] = new Attribute();
          // Exclude GIFs from image style to allow for animation.
          if ($file->getMimeType() != 'image/gif') {
            // Apply an image style in an attempt to optimize huge images.
            $src = ImageStyle::load('utexas_image_style_1600w_500h')->buildUrl($uri);
          }
          else {
            $src = $file->toUrl();
          }
          if (!empty($this->configuration['blur'])) {
            // Apply blur effect first to prevent mangled UTF8 encoding on $src.
            $build['#background_image']['style'] = "filter:blur(5px);-webkit-filter:blur(5px);-ms-filter:blur(5px);margin:-10px;";
          }
          $build['#background_image']['style'] .= "background-image: url('$src');
            background-position: center;
            background-repeat: no-repeat;
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            z-index: -1000;
            background-size: cover;
            bottom: 0;";
        }
      }
    }
    return $build;
  }

}
