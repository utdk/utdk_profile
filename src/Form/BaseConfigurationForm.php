<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;

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

    // Remove the default submit button provided by ConfigFormBase
    // until we have actual settings on this page by NOT returning the parent:
    // parent::buildForm($form, $form_state);.
    return $form;
  }

}
