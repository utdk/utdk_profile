<?php

namespace Drupal\utexas_site_announcement\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Utility\Html;

use Drupal\utexas_form_elements\UtexasLinkOptionsHelper;

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The path to the configurable page.
 */
const UTEXAS_SITE_ANNOUNCEMENT_CONFIG_FORM_PATH = 'admin/config/site-announcement';
/**
 * Provides a 'Site Announcement' block.
 *
 * @Block(
 *   id = "utexas_announcement",
 *   admin_label = @Translation("Site Announcement"),
 *   category = @Translation("UTexas")
 * )
 */
class AnnouncementBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The EntityTypeManager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Block constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManager $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManager $entity_type_manager = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $config = $this->getConfiguration();
    $form['state'] = [
      '#type' => 'radios',
      '#title' => $this->t("Announcement status"),
      '#options' => [
        'inactive' => $this->t("Inactive"),
        'homepage' => $this->t("Active on homepage only"),
        'all' => $this->t("Active on all pages"),
      ],
      '#default_value' => $config['status'] ?? 'inactive',
    ];
    $form['title'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Announcement title'),
      '#description' => $this->t('Enter the text that should appear as the headline for the announcement'),
      '#default_value' => $config['title'] ?? '',
    ];
    $icons = $this->entityTypeManager->getStorage('utexas_announcement_icon')->loadMultiple();
    foreach ($icons as $icon) {
      $icon_content = file_get_contents($icon->get('icon'));
      $icon_options[$icon->id()] = $this->t('%icon :txt',
       [
         '%icon' => Markup::create($icon_content),
         ':txt' => $icon->get('label'),
       ]);
    }
    // Account for no available icons.
    if (empty($icon_options)) {
      $icon_options = ['none' => $this->t('None')];
    }
    $icon_option_keys = array_keys($icon_options);
    $default_icon_option = reset($icon_option_keys);
    $form['icon'] = [
      '#type' => 'radios',
      '#title' => $this->t("Icon to display"),
      '#options' => $icon_options,
      '#default_value' => $config['icon'] ?? $default_icon_option,
    ];
    $schemes = $this->entityTypeManager->getStorage('utexas_announcement_color_scheme')->loadMultiple();
    foreach ($schemes as $scheme) {
      $scheme_options[$scheme->id()] = $this->t('<span style="background-color: :bg">&nbsp;&nbsp;&nbsp;&nbsp;</span> (:bg) background with <span style="background-color: :txt; border:1px solid black;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span> (:txt) text.',
      [
        ':bg' => $scheme->get('background_color'),
        ':txt' => $scheme->get('text_color'),
      ]);
    }
    // Account for no available color schemes.
    if (empty($scheme_options)) {
      $scheme_options = ['none' => $this->t('None')];
    };
    $scheme_options_keys = array_keys($scheme_options);
    $default_color_scheme_option = reset($scheme_options_keys);
    $form['scheme'] = [
      '#type' => 'radios',
      '#title' => $this->t("Color scheme"),
      '#options' => $scheme_options,
      '#default_value' => $config['scheme'] ?? $default_color_scheme_option,
    ];
    $form['message'] = [
      '#title' => 'Message',
      '#type' => 'text_format',
      '#default_value' => $config['message']['value'] ?? '',
      '#format' => $config['message']['format'] ?? 'restricted_html',
    ];
    $form['cta_wrapper'] = [
      '#type' => 'fieldset',
      '#title' => $this->t("Call to Action"),
    ];
    $form['cta_wrapper']['cta'] = [
      '#type' => 'utexas_link_options_element',
      '#default_value' => [
        'uri' => $config['cta']['uri'] ?? '',
        'title' => $config['cta']['title'] ?? '',
        'options' => $config['cta']['options'] ?? [],
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    // @todo cta is not saving here.
    $form_elements = [
      'state',
      'message',
      'title',
      'icon',
      'scheme',
    ];
    foreach ($form_elements as $element) {
      $this->configuration[$element] = $form_state->getValue($element);
    }
    $cta_wrapper = $form_state->getValue('cta_wrapper');
    $this->configuration['cta'] = $cta_wrapper['cta'];
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $cta = "";
    $config = $this->getConfiguration();
    if (!empty($config['cta']['uri'])) {
      $link_item['link'] = $config['cta'];
      $cta = UtexasLinkOptionsHelper::buildLink($link_item, ['ut-btn']);
    }

    // @todo: sanitize svg?
    $icon = $this->entityTypeManager->getStorage('utexas_announcement_icon')->load($config['icon']);
    if ($icon !== NULL) {
      $icon_content = file_get_contents($icon->get('icon'));
      $icon_markup = Markup::create($icon_content);
      $config['icon'] = [
        '#type' => 'texfield',
        '#markup' => $icon_markup,
      ];
    }
    $scheme = $this->entityTypeManager->getStorage('utexas_announcement_color_scheme')->load($config['scheme']);
    $background_color = $scheme !== NULL ? Html::escape($scheme->get('background_color')) : '';
    $text_color = $scheme !== NULL ? Html::escape($scheme->get('text_color')) : '';
    $unique_id = Html::getUniqueId("site-announcement");
    return [
      '#theme' => 'utexas_site_announcement',
      '#title' => !empty($config['title']) ? Html::escape($config['title']) : '',
      '#icon' => $config['icon'] === 'none' ? NULL : $config['icon'],
      '#message' => !empty($config['message']['value']) ? check_markup($config['message']['value'], $config['message']['format']) : '',
      '#unique_id' => $unique_id,
      '#cta' => $cta,
      '#attached' => [
        'library' => [
          'utexas_site_announcement/announcements',
        ],
        'html_head' => [
          [
            [
              '#tag' => 'style',
              '#value' => $this->generateInlineCss($unique_id, $background_color, $text_color),
            ],
            'utexas-announcement-' . $unique_id,
          ],
        ],
      ],
    ];
  }

  /**
   * Generate the inline CSS for the announcement.
   */
  public function generateInlineCss($unique_id, $background_color_hex, $text_color_hex) {

    $css = "
    #$unique_id.announcement-content {
      background-color: $background_color_hex;
      color: $text_color_hex;
    }
    #$unique_id .announcement-body {
      color: $text_color_hex;
    }
    #$unique_id .announcement-title {
      color: $text_color_hex;
    }
    #$unique_id .announcement-icon svg path {
      fill: $text_color_hex;
    ";

    return $css;

  }

}
