<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
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
    $redirect = $this->get_installer_redirect();
    // The installer doesn't make it easy (possible?) to return a redirect
    // response, so set a redirection META tag in the output.
    $meta_redirect = [
      '#tag' => 'meta',
      '#attributes' => [
        'http-equiv' => 'refresh',
        'content' => '0;url=' . $redirect,
      ],
    ];
    $form['#attached']['html_head'][] = [$meta_redirect, 'meta_redirect'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return new RedirectResponse(\Drupal::urlGenerator()->generateFromRoute('<front>'));
  }
  /**
   * Helper function to return a redirect object to the homepage.
   * Implements hook_install_tasks_alter().
   */
  public function get_installer_redirect() {
    $path = '<front>';
    $redirect = Url::fromUri('internal:/' . $path);
    // Explicitly set the base URL, if not previously set, to prevent weird
    // redirection snafus.
    $base_url = $redirect->getOption('base_url');
    if (empty($base_url)) {
      $redirect->setOption('base_url', $GLOBALS['base_url']);
    }
    return $redirect->setOption('absolute', TRUE)->toString();
  }

}
