<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Extension\InfoParserInterface;
use Drupal\Core\ProxyClass\Extension\ModuleInstaller;
use Drupal\Core\Extension\ThemeInstaller;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class ExtensionSelectForm extends FormBase {

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
   * The theme install helper.
   *
   * @var \Drupal\Core\Extension\ThemeInstaller
   */
  protected $themeInstaller;
  /**
   * The config update helper.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;

  /**
   * The module install helper.
   *
   * @var \Drupal\Core\Extension\ModuleInstaller
   */
  protected $moduleInstaller;

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
  public function __construct($root, InfoParserInterface $info_parser, TranslationInterface $translator, ThemeInstaller $themeInstaller, ConfigFactory $configFactory, ModuleInstaller $moduleInstaller) {
    $this->root = $root;
    $this->infoParser = $info_parser;
    $this->stringTranslation = $translator;
    $this->themeInstaller = $themeInstaller;
    $this->configFactory = $configFactory;
    $this->moduleInstaller = $moduleInstaller;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('app.root'),
      $container->get('info_parser'),
      $container->get('string_translation'),
      $container->get('theme_installer'),
      $container->get('config.factory'),
      $container->get('module_installer')
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
    // Default to Bartik.
    $this->themeInstaller->install(['bartik'], TRUE);
    $this->configFactory
      ->getEditable('system.theme')
      ->set('default', 'bartik')
      ->save();
  }

}
