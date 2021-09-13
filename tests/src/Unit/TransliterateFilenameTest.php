<?php

namespace Drupal\Tests\utexas\Unit;

use Drupal\Component\Transliteration\PhpTransliteration;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Drupal\utexas_media_types\TransliterateName;

/**
 * @coversDefaultClass \Drupal\utexas\TranliterateName
 * @group utexas
 */
class TransliterateFilenameTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $container = new ContainerBuilder();
    \Drupal::setContainer($container);

    $container->set('utexas_media_types.tranliterate_name', new TransliterateName(new PhpTransliteration()));
  }

  /**
   * Tests sanitize filename.
   *
   * @param string $filename
   *   The tested file name.
   * @param string $expected
   *   The expected name of sanitized file.
   *
   * @dataProvider providerTransliterateName
   */
  public function testTransliterateName($filename, $expected) {
    $sanitize_filename = \Drupal::service('utexas_media_types.tranliterate_name');
    $this->assertEquals($expected, $sanitize_filename->transliterateFilename($filename));
  }

  /**
   * Provides data for self::testTransliterateName().
   */
  public function providerTransliterateName() {
    return [
      // Transliterate Non-US-ASCII.
      ['ąęółżźćśń.pdf', 'aeolzzcsn.pdf'],
      // Remove unknown unicodes.
      [chr(0xF8) . chr(0x80) . chr(0x80) . 'test.txt', 'test.txt'],
      // Convert all characters to lowercase.
      ['LOWERCASE.txt', 'lowercase.txt'],
      // Replace whitespace.
      ['test whitespace.txt', 'test-whitespace.txt'],
      ['test   whitespace.txt', 'test-whitespace.txt'],
      // Remove multiple consecutive non-alphabetical characters.
      ['---___.txt', '-_.txt'],
      ['--  --.txt', '-.txt'],
    ];
  }

}
