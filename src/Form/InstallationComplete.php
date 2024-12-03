<?php

namespace Drupal\utexas\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Defines a form for selecting which UTexas extensions to install.
 */
class InstallationComplete extends FormBase {
  /**
   * The URL Generator helper.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * InstallationComplete form constructor.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The module URL Generator service.
   */
  public function __construct(UrlGeneratorInterface $urlGenerator) {
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('url_generator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'utexas_finish_installation';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?array &$install_state = NULL) {
    // Do a cache rebuild to ensure all components are registered.
    // We intermittently saw site installations initially result in PHP fatal
    // errors, so this is a preventative measure.
    drupal_flush_all_caches();

    $redirect = $this->getInstallerRedirect();

    $form['#title'] = $this->t('Hook em!');
    $form['message'] = [
      'info' => [
        '#markup' => $this->t('Congratulations, you installed UT Drupal Kit! If you are not redirected in 5 seconds, <a href="@url">click here</a> to proceed to your site.', [
          '@url' => $redirect,
        ]),
      ],
      '#attached' => [
        'http_header' => [
          ['Cache-Control', 'no-cache'],
        ],
      ],
    ];
    // The installer doesn't make it easy (possible?) to return a redirect
    // response, so set a redirection META tag in the output.
    $meta_redirect = [
      '#tag' => 'meta',
      '#attributes' => [
        'http-equiv' => 'refresh',
        'content' => '0;url=' . $redirect,
      ],
    ];
    $form['#attached']['html_head'][] = [$meta_redirect, 'meta_redirect'];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    return new RedirectResponse($this->urlGenerator->generateFromRoute('<front>'));
  }

  /**
   * Helper function to return a redirect object to the homepage.
   */
  public function getInstallerRedirect() {
    $path = '<front>';
    $redirect = Url::fromUri('internal:/' . $path);
    // Explicitly set the base URL, if not previously set, to prevent weird
    // redirection snafus.
    $base_url = $redirect->getOption('base_url');
    if (empty($base_url)) {
      $redirect->setOption('base_url', $GLOBALS['base_url']);
    }
    return $redirect->setOption('absolute', TRUE)->toString();
  }

}
