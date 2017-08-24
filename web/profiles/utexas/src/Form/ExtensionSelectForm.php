<?php
namespace Drupal\utexas\Form;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\Extension\ThemeInstaller;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\utexas\Extender;
use Drupal\utexas\FormHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class ExtensionSelectForm extends FormBase {
  /**
   * The UTexas extender configuration object.
   *
   * @var \Drupal\utexas\Extender
   */
  protected $extender;
  /**
   * The Drupal application root.
   *
   * @var string
   */
  protected $root;
  /**
   * The info parser service.
   *
   * @var \Drupal\Core\Extension\InfoParserInterface
   */
  protected $infoParser;
  /**
   * The form helper.
   *
   * @var \Drupal\utexas\FormHelper
   */
  protected $formHelper;
  /**
   * The form helper.
   *
   * @var \Drupal\utexas\FormHelper
   */
  protected $themeInstaller;
  /**
   * The form helper.
   *
   * @var \Drupal\utexas\FormHelper
   */
  protected $configFactory;
  /**
   * ExtensionSelectForm constructor.
   *
   * @param \Drupal\utexas\Extender $extender
   *   The UTexas extender configuration object.
   * @param string $root
   *   The Drupal application root.
   * @param \Drupal\Core\Extension\InfoParserInterface $info_parser
   *   The info parser service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $translator
   *   The string translation service.
   * @param \Drupal\utexas\FormHelper $form_helper
   *   The form helper.
   */
  public function __construct(Extender $extender, $root, InfoParserInterface $info_parser, TranslationInterface $translator, FormHelper $form_helper, ThemeInstaller $themeInstaller, ConfigFactory $configFactory) {
    $this->extender = $extender;
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
    $this->formHelper = $form_helper;
    $this->themeInstaller = $themeInstaller;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('utexas.extender'),
      $container->get('app.root'),
      $container->get('info_parser'),
      $container->get('string_translation'),
      $container->get('utexas.form_helper'),
      $container->get('theme_installer'),
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
    $form['#title'] = $this->t('Custom Functionality');
    $form['help'] = [
      '#weight' => -1,
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    $form['install_forty_acres_theme_option'] = [
      '#type' => 'checkbox',
      '#title' => 'Install Forty Acres default theme?',
      '#description' => 'Check this option to have the Forty Acres theme installed.'
    ];
    $form['actions'] = [
      'continue' => [
        '#type' => 'submit',
        '#value' => $this->t('Continue'),
      ],
      '#type' => 'actions',
      '#weight' => 5,
    ];
    $form['sub_components'] = [
      '#type' => 'value',
      '#value' => [],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Enable forty_acres if selected
    $enable_forty_acres_theme = $form_state->getValue('install_forty_acres_theme_option');
    if ($enable_forty_acres_theme == '1') {
      // Install default theme.
      $this->themeInstaller->install(['forty_acres']);
      $this->configFactory
        ->getEditable('system.theme')
        ->set('default', 'forty_acres')
        ->save();
    }
  }
}