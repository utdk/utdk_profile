<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_layouts\Traits\BackgroundAccentTrait;
use Drupal\utexas_layouts\Traits\BackgroundColorTrait;

/**
 * Configurable four column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class FourColumnLayout extends DefaultConfigLayout {

  use BackgroundAccentTrait, BackgroundColorTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = parent::defaultConfiguration();
    $config += $this->backgroundConfiguration();
    $config += $this->backgroundColorConfiguration();
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->backgroundConfigurationForm($form, $form_state);
    $form += $this->backgroundColorConfigurationForm($form, $form_state);
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
    $this->submitBackgroundConfigurationForm($form, $form_state);
    $this->submitBackgroundColorConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attributes']['class'][] = $this->getPluginDefinition()->getTemplate();
    $this->buildBackground($build);
    $this->buildBackgroundColor($build);
    return $build;
  }

}
