<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class InstallationOptions extends FormBase {

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
    return 'utexas_installation_options';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, array &$install_state = NULL) {
    $form['#title'] = $this->t('Installation options');
    $form['default_content'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Populate example pages and menu items to model realistic site content.'),
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
    $values = $form_state->getValues();
    if ($values['default_content']) {
      $this->stateFactory->set('utexas_installation_options.default_content', TRUE);
    }
  }

}
