<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class ExtensionSelectForm extends FormBase {

  /**
   * The state helper.
   *
   * @var \Drupal\Core\Extension\ModuleInstaller
   */
  protected $stateFactory;

  /**
   * ExtensionSelectForm constructor.
   *
   * @param \Drupal\Core\State\State $stateFactory
   *   The module state service.
   */

  /**
   * Drupal\Core\Config\ConfigFactory definition.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * Constructor.
   */
  public function __construct(State $stateFactory, ConfigFactory $config_factory) {
    $this->stateFactory = $stateFactory;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_select_extensions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Enable additional features');
    $form['utexas_enable_flex_page_content_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Flex Page content type'),
      '#description' => $this->t('Fully-featured page type with configurable layout.'),
      '#default_value' => 1,
      '#weight' => -10,
      '#states' => [
        'checked' => [
          ':input[name="utexas_enable_fp_editor_role"]' => [
            'checked' => TRUE,
          ],
        ],
        'unchecked' => [
          ':input[name="utexas_enable_flex_page_content_type"]' => [
            'checked' => FALSE,
          ],
        ],
        'disabled' => [
          ':input[name="utexas_enable_fp_editor_role"]' => [
            'checked' => TRUE,
          ],
        ],
      ],
    ];
    $form['utexas_enable_fp_editor_role'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Flex Page Editor role'),
      '#description' => $this->t('Requires the Flex Page content type.'),
      '#default_value' => 0,
      '#weight' => -9,
    ];
    $form['utexas_enable_social_links'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Social Media Links'),
      '#description' => $this->t('Display icon-style links to social media assets as a block.'),
      '#default_value' => 1,
      '#weight' => -8,
    ];
    $form['utexas_create_default_content'] = [
      '#prefix' => '<hr />',
      '#type' => 'checkbox',
      '#title' => $this->t('Install realistic default content'),
      '#description' => $this->t('Example pages and blocks to model content creation.'),
      '#default_value' => 1,
      '#weight' => 0,
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Complete installation'),
      ],
      '#type' => 'actions',
      '#weight' => 0,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // We build an array of module machine names to be installed.
    // This array is passed to state, where it can be processed in
    // the next installation step.
    $values = $form_state->getValues();
    $modules_to_install = [];
    if ($values['utexas_enable_flex_page_content_type'] == 1) {
      $modules_to_install[] = 'utexas_content_type_flex_page';
    }
    if ($values['utexas_enable_fp_editor_role'] == 1) {
      $modules_to_install[] = 'utexas_role_flex_page_editor';
    }
    if ($values['utexas_enable_social_links'] == 1) {
      $modules_to_install[] = 'utexas_block_social_links';
    }
    // Set the form state for the batch process to know what's enabled.
    $this->stateFactory->set('utexas-install.modules_to_enable', $modules_to_install);

    if ($values['utexas_create_default_content'] == 1) {
      $this->stateFactory->set('utexas-install.default_content', TRUE);
    }

    // Setting default country and timezone.
    $system_date = $this->configFactory->getEditable('system.date');
    $system_date->set('timezone.default', 'America/Chicago')
      ->set('country.default', 'US')->save();
  }

}
