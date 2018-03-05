<?php

namespace Drupal\utexas_block_social_links\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\file\Entity\File;

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
    $icon_default = $utexas_block_social_links->get('icon') !== NULL ? [$utexas_block_social_links->get('icon')] : [];
    $form['icon'] = [
      '#type' => 'managed_file',
      '#title' => $this->t('SVG Icon'),
      '#description' => $this->t('SVG Icon to associate with this social account network.'),
      '#default_value' => $icon_default,
      '#upload_location' => 'public://social_icons/',
      '#upload_validators'    => [
        'file_validate_extensions'    => ['svg'],
        'file_validate_size'          => [25600000],
      ],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    // TODO: We need to clean up the old file, if the icon is being replaced.
    $image = $form_state->getValue('icon');
    $file = File::load($image[0]);
    $file->setPermanent();
    $file->save();
    $utexas_block_social_links = $this->entity;
    $utexas_block_social_links->set('icon', $file->id());
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
