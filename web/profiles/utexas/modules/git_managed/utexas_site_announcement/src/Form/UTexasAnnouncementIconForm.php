<?php

namespace Drupal\utexas_site_announcement\Form;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\utexas_site_announcement\Services\UTexasAnnouncementIconOptions;
use Drupal\Core\Render\Markup;

/**
 * Class UTexasAnnouncementIconForm.
 */
class UTexasAnnouncementIconForm extends EntityForm {

  use MessengerTrait;

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
    $file = $this->saveFromForm($form['icon'], $form_state, 0);
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
        $this->messenger()->addStatus($this->t('Created configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
        break;

      default:
        $this->messenger()->addStatus($this->t('Saved configuration for %label.', [
          '%label' => $utexas_site_announcement->label(),
        ]));
    }
    $form_state->setRedirectUrl($utexas_site_announcement->toUrl('collection'));
    Cache::invalidateTags(['utexas_site_announcement']);
  }

  /**
   * Saves form file uploads.
   *
   * The files will be added to the {file_managed} table as temporary files.
   * Temporary files are periodically cleaned. Use the 'file.usage' service to
   * register the usage of the file which will automatically mark
   * it as permanent.
   *
   * @param array $element
   *   The FAPI element whose values are being saved.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param null|int $delta
   *   (optional) The delta of the file to return the file entity.
   *   Defaults to NULL.
   * @param int $replace
   *   (optional) The replace behavior when the destination file already exists.
   *   Possible values include:
   *   - FILE_EXISTS_REPLACE: Replace the existing file.
   *   - FILE_EXISTS_RENAME: (default) Append _{incrementing number} until the
   *     filename is unique.
   *   - FILE_EXISTS_ERROR: Do nothing and return FALSE.
   *
   * @return array|\Drupal\file\FileInterface|null|false
   *   An array of file entities or a single file entity if $delta != NULL. Each
   *   array element contains the file entity if the upload succeeded or FALSE
   *   if there was an error. Function returns NULL if no file was uploaded.
   *
   * @internal
   *   This function wraps file_save_upload() to allow correct error handling in
   *   forms.
   */
  private function saveFromForm(array $element, FormStateInterface $form_state, $delta = NULL, $replace = FILE_EXISTS_RENAME) {
    // Get all errors set before calling this method. This will also clear them
    // from $_SESSION.
    $errors_before = $this->messenger()->deleteByType(MessengerInterface::TYPE_ERROR);

    $upload_location = isset($element['#upload_location']) ? $element['#upload_location'] : FALSE;
    $upload_name = implode('_', $element['#parents']);
    $upload_validators = isset($element['#upload_validators']) ? $element['#upload_validators'] : [];

    $result = file_save_upload($upload_name, $upload_validators, $upload_location, $delta, $replace);

    // Get new errors that are generated while trying to save the upload. This
    // will also clear them from $_SESSION.
    $errors_new = $this->messenger()->deleteByType(MessengerInterface::TYPE_ERROR);
    if (!empty($errors_new)) {

      if (count($errors_new) > 1) {
        // Render multiple errors into a single message.
        // This is needed because only one error per element is supported.
        $render_array = [
          'error' => [
            '#markup' => t('One or more files could not be uploaded.'),
          ],
          'item_list' => [
            '#theme' => 'item_list',
            '#items' => $errors_new,
          ],
        ];
        $error_message = \Drupal::service('renderer')->renderPlain($render_array);
      }
      else {
        $error_message = reset($errors_new);
      }

      $form_state->setError($element, $error_message);
    }

    // Ensure that errors set prior to calling this method are still shown to
    // the user.
    if (!empty($errors_before)) {
      foreach ($errors_before as $error) {
        $this->messenger()->addError($error);
      }
    }

    return $result;
  }

}
