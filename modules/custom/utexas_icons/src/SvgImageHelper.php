<?php

namespace Drupal\utexas_icons;

use Drupal\Core\Render\Markup;
use Drupal\file\Entity\File;
use enshrined\svgSanitize\Sanitizer;

/**
 * SVG helper functions.
 */
class SvgImageHelper {

  /**
   * Provides content of the file.
   *
   * @param \Drupal\file\Entity\File $file
   *   File to handle.
   *
   * @return string|bool
   *   File content or FALSE if the file does not exist or is invalid.
   */
  public static function fileGetContents(File $file) {
    $fileUri = $file->getFileUri();

    if (file_exists($fileUri)) {
      // Make sure that SVG is safe.
      $rawSvg = file_get_contents($fileUri);
      return (new Sanitizer())->sanitize($rawSvg);
    }

    return FALSE;
  }

  /**
   * Renders a file as raw SVG.
   *
   * @param \Drupal\file\Entity\File $file
   *   The file.
   */
  public static function renderAsSvg(File $file) {
    $svgRaw = self::fileGetContents($file);
    if (!$svgRaw) {
      return;
    }
    $svgRaw = preg_replace(['/<\?xml.*\?>/i', '/<!DOCTYPE((.|\n|\r)*?)">/i'], '', $svgRaw);
    $markup = Markup::create(trim($svgRaw));
    $element = [
      '#markup' => $markup,
    ];
    return $element;
  }

}
