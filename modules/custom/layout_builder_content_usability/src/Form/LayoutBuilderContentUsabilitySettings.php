<?php

namespace Drupal\layout_builder_content_usability\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure settings for the utprof module.
 */
class LayoutBuilderContentUsabilitySettings extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utprof_general_config';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $secret = \Drupal::state()->get('layout_builder_content_usability_secret') ?? '';
    $form['layout_builder_content_usability_secret'] = [
      '#title' => $this->t('Client Secret'),
      '#type' => 'textfield',
      '#default_value' => $secret,
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::state()->set('layout_builder_content_usability_secret', $form_state->getValue('layout_builder_content_usability_secret'));
    parent::submitForm($form, $form_state);
  }
}
