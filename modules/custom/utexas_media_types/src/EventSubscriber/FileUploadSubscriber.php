<?php

namespace Drupal\utexas_media_types\EventSubscriber;

use Drupal\Core\File\Event\FileUploadSanitizeNameEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Drupal\utexas_media_types\TransliterateName;

/**
 * Event subscriber for file uploads.
 */
class FileUploadSubscriber implements EventSubscriberInterface {

  /**
   * A filename transliterate service instance.
   *
   * @var \Drupal\utexas_media_types\TransliterateName
   */
  protected $transliterateNameService;

  /**
   * FileUploadSubscriber constructor.
   *
   * @param \Drupal\utexas_media_types\TransliterateName $transliterate_name_service
   *   The TransliterateName service.
   */
  public function __construct(TransliterateName $transliterate_name_service) {
    $this->transliterateNameService = $transliterate_name_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    $events[FileUploadSanitizeNameEvent::class][] = ['transliterateName', -100];
    return $events;
  }

  /**
   * Transliterates the upload's filename.
   *
   * @param \Drupal\Core\File\Event\FileUploadSanitizeNameEvent $event
   *   File upload sanitize name event.
   */
  public function transliterateName(FileUploadSanitizeNameEvent $event): void {
    $filename = $event->getFilename();
    $filename = $this->transliterateNameService->transliterateFilename($filename);
    $event->setFilename($filename);
  }

}
