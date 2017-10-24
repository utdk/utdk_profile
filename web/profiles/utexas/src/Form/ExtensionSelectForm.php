<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\State\State;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
   * @param string $root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param \Drupal\Core\Extension\ThemeInstaller $themeInstaller
   *   The theme installer service.
   * @param \Drupal\Core\Config\ConfigFactory $configFactory
   *   The configuration service.
   * @param \Drupal\Core\ProxyClass\Extension\ModuleInstaller $moduleInstaller
   *   The module installer service.
   */
  public function __construct(State $stateFactory) {
    $this->stateFactory = $stateFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('state')
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
    $form['#title'] = $this->t('Custom Functionality');
    $form['utexas_enable_flex_page_content_type'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Flex Page content type?'),
      '#default_value' => 1,
      '#weight' => -10,
    ];
    $form['utexas_enable_fp_editor_role'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable Flex Page Editor role?'),
      '#default_value' => 0,
      '#weight' => -9,
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
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
    // Set the form state for the batch process to know what's enabled.
    $this->stateFactory->set('utexas-install.modules_to_enable', $modules_to_install);
  }


}
