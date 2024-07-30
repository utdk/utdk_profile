<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\file\Entity\File;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Configure settings for the utexas module.
 */
class BaseConfigurationForm extends ConfigFormBase {

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Class constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration object factory.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, EntityTypeManager $entity_type_manager, MessengerInterface $messenger) {
    parent::__construct($config_factory);
    $this->entityTypeManager = $entity_type_manager;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_general_config';
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

    $form['intro']['#markup'] = $this->t('<h3>Introduction</h3><p>The UT Drupal Kit is a website solution tailored to the Texas brand, based on the Drupal content management system. This configuration section includes settings that are specifically provided by the UT Drupal Kit, distinct from general Drupal settings.</p><p>Permissions associated with the Drupal Kit can be assigned to site roles via the <a href="/admin/config/content/utexas/permissions">Permissions configuration</a> tab.');
    // We allow static calls to services.
    // phpcs:ignore
    $fid = \Drupal::state()->get('default_og_image');
    if (!$fid) {
      $fid = 0;
    }
    $form['default_og_image'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://',
      '#multiple' => FALSE,
      '#description' => $this->t('Allowed extensions: jpg, jpeg, png. You must press "Save configuration" for changes to take effect.'),
      '#upload_validators' => [
        'file_validate_extensions' => ['jpg png jpeg'],
      ],
      '#default_value' => [$fid],
      '#title' => $this->t('Default image for social media sharing'),
    ];
    $form = parent::buildForm($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We allow static calls to services.
    // phpcs:ignore
    $config = \Drupal::configFactory();
    // phpcs:ignore
    $state_api = \Drupal::state();
    $metatag_default = $config->getEditable('metatag.metatag_defaults.global');
    $field = $form_state->getValue('default_og_image');
    if (!isset($field[0])) {
      // The OG image has been cleared. Reflect this in the settings.
      $state_api->delete('default_og_image');
      $tags = $metatag_default->get('tags');
      unset($tags['og_image']);
      $metatag_default->set('tags', $tags);
      $metatag_default->save();
    }
    else {
      // Upload the new default OG image.
      // We allow static calls to services.
      // phpcs:ignore
      $file = File::load($field[0]);
      // This will set the file status to 'permanent' automatically.
      // We allow static calls to services.
      // phpcs:ignore
      \Drupal::service('file.usage')->add($file, 'utexas', 'file', $file->id());
      $state_api->set('default_og_image', $file->id());
      $uri = $file->getFileUri();
      // We allow static calls to services.
      // phpcs:ignore
      $filepath = \Drupal::service('file_url_generator')->generateString($uri);
      $tags = $metatag_default->get('tags');
      $tags['og_image'] = $filepath;
      $metatag_default->set('tags', $tags);
      $metatag_default->save();
    }
    drupal_flush_all_caches();
  }

}
