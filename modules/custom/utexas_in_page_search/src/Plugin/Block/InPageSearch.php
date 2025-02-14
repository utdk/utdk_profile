<?php

namespace Drupal\utexas_in_page_search\Plugin\Block;

use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Block\BlockBase;

#[Block(
  id: "in_page_search",
  admin_label: new TranslatableMarkup("In-page search form"),
  category: new TranslatableMarkup("Search")
)]
/**
 * Provides an in-page search form.
 */
class InPageSearch extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'target' => '',
      'delimiter' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['target'] = [
      '#title' => $this->t('HTML ID of section to search'),
      '#type' => 'textfield',
      '#required' => TRUE,
      '#description' => $this->t('An HTML ID attribute must be present on a wrapper around the content to search. Omit # in the field (e.g, "block-2"'),
      '#default_value' => $this->configuration['target'],
    ];
    $form['delimiter'] = [
      '#title' => $this->t('HTML Content separator'),
      '#type' => 'radios',
      '#options' => [
        'p' => 'p',
        'li' => 'li',
        'div' => 'div',
        'dt' => 'dt',
        'summary' => $this->t('summary'),
        'span' => $this->t('span'),
        'tr' => 'tr',
      ],
      '#required' => TRUE,
      '#description' => $this->t('The HTML element that contains the data to search for. This will be wrapped in the ID entered above.'),
      '#default_value' => $this->configuration['delimiter'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $values = $form_state->getValues();
    $this->configuration['target'] = $values['target'];
    $this->configuration['delimiter'] = $values['delimiter'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build['#theme'] = 'in_page_search';
    $build['#attached']['library'][] = 'utexas_in_page_search/searchform';
    $build['#attached']['drupalSettings']['in_page_search']['target'] = $this->configuration['target'];
    $build['#attached']['drupalSettings']['in_page_search']['delimiter'] = $this->configuration['delimiter'];
    return $build;
  }

}
