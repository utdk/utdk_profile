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
    $form['help'] = [
      '#weight' => -1,
      '#prefix' => '<p>',
      '#suffix' => '</p>',
    ];
    $form['install_forty_acres_theme_option'] = [
      '#type' => 'checkbox',
      '#title' => 'Install Forty Acres theme',
      '#description' => 'Accessible, University-branded look & feel.',
      '#default_value' => FALSE,
    ];
    $form['layout_per_node'] = [
      '#type' => 'checkbox',
      '#title' => 'Install Layout Editor',
      '#description' => 'Drag-and-drop content to create customized pages.',
      '#default_value' => TRUE,
    ];
    $form['install_forty_acres_content_type'] = [
      '#type' => 'fieldset',
      '#title' => 'Content Types',
    ];
    $form['install_forty_acres_content_type']['utexas_event'] = [
      '#type' => 'checkbox',
      '#title' => 'Events',
      '#description' => 'Timely content with single-page & listing views.',
      '#default_value' => FALSE,
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
    $values = $form_state->getValues();
    if ($values['install_forty_acres_theme_option'] == 1) {
      // Enable forty_acres if selected.
      $this->themeInstaller->install(['forty_acres'], TRUE);
      $this->configFactory
        ->getEditable('system.theme')
        ->set('default', 'forty_acres')
        ->save();
      $this->moduleInstaller->install(['twig_tweak']);
    }
    else {
      // Default to Bartik.
      $this->themeInstaller->install(['bartik'], TRUE);
      $this->configFactory
        ->getEditable('system.theme')
        ->set('default', 'bartik')
        ->save();
    }

    // Install module options that are defined in the form.
    $modules = [
      'layout_per_node',
      'utexas_event',
    ];
    $modules_to_enable = [];
    foreach ($modules as $name) {
      if ($values[$name] == 1) {
        $modules_to_enable[] = $name;
      }
    }
    $this->moduleInstaller->install($modules_to_enable, TRUE);

  }

}
