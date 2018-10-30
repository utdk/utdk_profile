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
      '#type' => 'managed_file',
      '#name' => 'background_accent',
      '#title' => $this->t('Background image'),
      '#size' => 20,
      '#description' => $this->t('Optionally, display an image behind section content. Ideal size is 1500x500 pixels.'),
      '#upload_validators' => $validators,
      '#upload_location' => 'public://background_accent/',
      '#weight' => 2,
    ];
    if (!empty($this->configuration['background-accent'])) {
      $form['background-accent']['#default_value'] = [$this->configuration['background-accent']];
    }
    $form['blur'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Apply blur to image?'),
      '#default_value' => $this->configuration['blur'],
      '#weight' => 3,
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
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($form_state->getValue('background-accent')[0]);
      if ($file) {
        $this->configuration['background-accent'] = $file->id();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    if (!empty($this->configuration['background-accent'])) {
      $file = \Drupal::entityTypeManager()->getStorage('file')->load($this->configuration['background-accent']);
      if ($file && $uri = $file->getFileUri()) {
        $build['#attributes']['class'][] = 'background-accent';
        $build['#background_image'] = new Attribute();
        $src = ImageStyle::load('utexas_image_style_1600w_500h')->buildUrl($uri);
        if (!empty($this->configuration['blur'])) {
          $build['#background_image']['style'] = "filter:blur(5px);-webkit-filter:blur(5px);-ms-filter:blur(5px);";
        }
        $build['#background_image']['style'] .= "background-image: url('$src');
        background-position: center;
        background-repeat: no-repeat;
        position:absolute;
        height:100%;
        width:100%;
        z-index:-1000;
        background-size: cover;";
      }
    }
    return $build;
  }

}
