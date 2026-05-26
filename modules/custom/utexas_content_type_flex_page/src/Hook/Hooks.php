<?php

namespace Drupal\utexas_content_type_flex_page\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations.
 */
class Hooks {

  use StringTranslationTrait;

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_node_form_alter')]
  public function formNodeFormAlter(&$form, &$form_state, $form_id) {
    /** @var Drupal\Core\Entity\FieldableEntityInterface $node */
    $node = $form_state->getFormObject()->getEntity();
    if ($node->getType() !== 'utexas_flex_page') {
      return;
    }
    $message = '';
    switch ($form_id) {
      case 'node_utexas_flex_page_edit_form':
        $message = $this->t('<ul><li>To set page metadata and control the display of the page title & breadcrumbs, use the vertical tabs on this page.</li><li>To add content, click the "Layout" tab, above.</li></ul>');
        break;

      case 'node_utexas_flex_page_form':
        $message = $this->t('To add content to this page, first add a title and save this form. A "Layout" tab will then appear, from which you can add content.');
        break;
    }
    if ($message !== '') {
      $form['utexas_content_type_flex_page_help_text'] = [
        '#markup' => '<div class="messages messages--status">' . $message . '</div>',
        '#weight' => -999,
      ];
    }
  }

}
