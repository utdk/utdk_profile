<?php

namespace Drupal\utexas_media_types;

use Drupal\Component\Transliteration\TransliterationInterface;

/**
 * Helper class to transliterate filenames.
 */
class TransliterateName {

  /**
   * The transliteration service.
   *
   * @var \Drupal\Component\Transliteration\TransliterationInterface
   */
  protected $transliteration;

  /**
   * TransliterateName constructor.
   *
   * @param \Drupal\Component\Transliteration\TransliterationInterface $transliteration
   *   The transliteration service.
   */
  public function __construct(TransliterationInterface $transliteration) {
    $this->transliteration = $transliteration;
  }

  /**
   * Sanitize the file name.
   *
   * @param string $filename
   *   The file name that will be sanitized.
   *
   * @return string
   *   Transliterated file name.
   */
  public function transliterateFilename($filename) {
    $original = $filename;
    $filename = $this->transliteration->transliterate($filename);
    // Replace whitespace.
    $filename = str_replace(' ', '-', $filename);
    // Remove remaining unsafe characters.
    $filename = preg_replace('![^0-9A-Za-z_.-]!', '', $filename);
    // Remove multiple consecutive non-alphabetical characters.
    $filename = preg_replace('/(_)_+|(\.)\.+|(-)-+/', '\\1\\2\\3', $filename);
    // Force lowercase to prevent issues on case-insensitive file systems.
    $filename = mb_strtolower($filename);

    // If transliteration results in empty filename, just return the original.
    $name = explode('.', $filename);
    $name = reset($name);
    $extension = explode(".", $filename);
    $extension = end($extension);
    if (!$name) {
      return $original;
    }
    return $filename;
  }

}
