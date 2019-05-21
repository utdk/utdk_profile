<?php

namespace Drupal\utexas_site_announcement\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\utexas_site_announcement\Services\UTexasAnnouncementIconOptions;
use Drupal\Core\Render\Markup;

/**
 * Class UTexasAnnouncementIconForm.
 */
class UTexasAnnouncementIconForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $utexas_site_announcement = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $utexas_site_announcement->label(),
      '#description' => $this->t("Label for the icon."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $utexas_site_announcement->id(),
      '#machine_name' => [
        'exists' => '\Drupal\utexas_site_announcement\Entity\UTexasAnnouncementIcon::load',
      ],
      '#disabled' => !$utexas_site_announcement->isNew(),
    ];
    $form['icon'] = [
      '#type' => 'file',
      '#title' => $this->t('SVG Icon'),
      '#description' => $this->t('Upload an SVG Icon to set as the default icon for this label. This upload will replace the active SVG icon if one is already set below.'),
      '#maxlength' => 40,
      '#upload_validators' => [
        'file_validate_extensions' => ['svg'],
        'file_validate_size' => [25600000],
      ],
    ];

    // Get current icon to render as markup on form if an existing account.
    if ($form['id']['#default_value'] !== NULL) {
      $icons = UTexasAnnouncementIconOptions::getIcons();
      $id = $form['id']['#default_value'];
      $icon = $icons[$id];
      if ($icon && $icon_contents = file_get_contents($icon)) {
        $icon_markup = Markup::create($icon_contents);
        $form['active_icon'] = [
          '#type' => 'texfield',
          '#markup' => $icon_markup,
          '#prefix' => $this->t('<label>Active icon</label>'),
        ];
      }
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Check for a newly uploaded logo.
    $file = _file_save_upload_from_form($form['icon'], $form_state, 0);
    if ($file) {
      // Put the temporary file in form_values so we can save it on submit.
      $form_state->setValue('icon', $file);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $utexas_site_announcement = $this->entity;
    if ($temp_image_file = $form_state->getValue('icon')) {
      // The user is uploading a new SVG.
      $temp_image_data = file_get_contents($temp_image_file->getFileUri());
      $destination = 'public://announcement_icons/';
      \Drupal::service('file_system')->prepareDirectory($destination, FILE_CREATE_DIRECTORY | FILE_MODIFY_PERMISSIONS);
      $unmanaged_file = \Drupal::service('file_system')->saveData($temp_image_data, $destination . $temp_image_file->getFilename());
      $utexas_site_announcement->set('icon', $unmanaged_file);
      $temp_image_file->delete();
    }
    else {
      // No change has been made to the SVG.
      // Ensure that the existing SVG is maintained.
      $icons = UTexasAnnouncementIconOptions::getIcons();
      $id = $form_state->getValue('id');
      $icon = $icons[$id];
      $utexas_site_announcement->set('icon', $icon);
    }
    $status = $utexas_site_announcement->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
    }
    $form_state->setRedirectUrl($utexas_site_announcement->toUrl('collection'));
    Cache::invalidateTags(['utexas_site_announcement']);
  }

}
