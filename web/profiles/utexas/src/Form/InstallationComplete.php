<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class InstallationComplete extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_finish_installation';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Hook em!');
    $form['message'] = [
      '#markup' => 'Congrats, you are all set to start using your Drupal Kit!',
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Visit your new site'),
      ],
      '#type' => 'actions',
      '#weight' => 0,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return new RedirectResponse(\Drupal::urlGenerator()->generateFromRoute('<front>'));
  }

}
