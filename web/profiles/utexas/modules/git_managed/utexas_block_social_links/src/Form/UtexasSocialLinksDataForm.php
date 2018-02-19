<?php

namespace Drupal\utexas_block_social_links\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class UtexasSocialLinksDataForm.
 */
class UtexasSocialLinksDataForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $utexas_block_social_links = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $utexas_block_social_links->label(),
      '#description' => $this->t("Label for the social account network."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $utexas_block_social_links->id(),
      '#machine_name' => [
        'exists' => '\Drupal\utexas_block_social_links\Entity\UtexasSocialLinksData::load',
      ],
      '#disabled' => !$utexas_block_social_links->isNew(),
    ];

    $form['icon'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Icon'),
      '#description' => $this->t('Icon to associate with this social account network.'),
      '#default_value' => $utexas_block_social_links->get('icon'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $utexas_block_social_links = $this->entity;
    $status = $utexas_block_social_links->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created configuration for %label.', [
          '%label' => $utexas_block_social_links->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved configuration for %label.', [
          '%label' => $utexas_block_social_links->label(),
        ]));
    }
    $form_state->setRedirectUrl($utexas_block_social_links->toUrl('collection'));
  }

}
