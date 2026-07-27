<?php

namespace Drupal\utexas_google_search\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for Googlesearch.
 */
class Hooks {

  /**
   * Implements hook_form_FORM_ID_alter() for the search_block_form form.
   *
   * Since the exposed form is a GET form, we don't want it to send the form
   * tokens. However, you cannot make this happen in the form builder function
   * itself, because the tokens are added to the form after the builder function
   * is called. So, we have to do it in a form_alter.
   */
  #[Hook('form_utexas_google_search_box_form_alter')]
  public function formGoogleCseSearchBoxFormAlter(&$form, FormStateInterface $form_state): void {
    $form['form_build_id']['#access'] = FALSE;
    $form['form_token']['#access'] = FALSE;
    $form['form_id']['#access'] = FALSE;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public static function theme($existing, $type, $theme, $path) {
    // Register the Google Search Results theme template.
    return [
      'utexas_google_search_results' => [
        'variables' => [
          'path' => $path,
          'noscript' => NULL,
        ],
        'template' => 'utexas_google_search_results',
      ],
    ];
  }

}
