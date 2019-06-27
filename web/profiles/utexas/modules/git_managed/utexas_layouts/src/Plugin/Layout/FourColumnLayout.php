<?php

namespace Drupal\utexas_layouts\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_layouts\Traits\BackgroundAccentTrait;

/**
 * Configurable four column layout plugin class.
 *
 * @internal
 *   Plugin classes are internal.
 */
class FourColumnLayout extends DefaultConfigLayout {

  use BackgroundAccentTrait;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $config = $this->backgroundConfiguration();
    return $config;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = $this->backgroundConfigurationForm($form, $form_state);
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
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);
    $build['#attributes']['class'][] = $this->getPluginDefinition()->getTemplate();
    $this->buildBackground($build);
    return $build;
  }

}
