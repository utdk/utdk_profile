<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_layouts\Traits\BackgroundAccentTrait;
use Drupal\utexas_layouts\Traits\MultiWidthLayoutTrait;

/**
 * Configurable two column layout plugin class.
 */
class TwoColumnLayout extends DefaultConfigLayout {

  use BackgroundAccentTrait, MultiWidthLayoutTrait;

  /**
   * {@inheritdoc}
   */
  protected function getWidthOptions() {
    return [
      '50-50' => '50%/50%',
      '33-67' => '33%/67%',
      '67-33' => '67%/33%',
      '25-75' => '25%/75%',
      '75-25' => '75%/25%',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = $this->backgroundConfiguration();
    $config += $this->multiWidthConfiguration();
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->multiWidthConfigurationForm($form, $form_state);
    $form += $this->backgroundConfigurationForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->validateMultiWidthConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->submitBackgroundConfigurationForm($form, $form_state);
    $this->submitMultiWidthConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $this->buildBackground($build);
    $this->buildMultiWidth($build);
    return $build;
  }

}
