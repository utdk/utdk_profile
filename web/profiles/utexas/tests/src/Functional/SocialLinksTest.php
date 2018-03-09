<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;

/**
 * Verifies Social Links field schema & validation.
 *
 * @group utexas
 */
class SocialLinksTest extends BrowserTestBase {
  use EntityTestTrait;
  use UserTestTrait;
  use ImageFieldCreationTrait;
  use TestFileCreationTrait;
  use InstallTestTrait;
  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';
  /**
   * An user with permissions to administer content types and image styles.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $testUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->utexasSharedSetup();
    parent::setUp();
    $this->initializeFlexPageEditor();
    $this->drupalLogin($this->testUser);
  }

  /**
   * Test schema.
   */
  public function testSchema() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the block type.
    $this->assertAllowed("/block/add/social_links");
    // 3. Verify the correct field schemae exist.
    $fields = [
      'edit-info-0-value',
      'edit-field-utexas-sl-social-links-0-social-account-url',
      'edit-field-utexas-sl-social-links-0-social-account-name',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
    $page = $this->getSession()->getPage();
    $social_link_options = $page->findAll('css', '#edit-field-utexas-sl-social-links-0-social-account-name option');
    $options = [];
    foreach ($social_link_options as $key => $option) {
      $options[] = $option->getValue();
    }
    $available_icons = [
      'facebook',
      'flickr',
      'googleplus',
      'instagram',
      'linkedin',
      'pinterest',
      'reddit',
      'snapchat',
      'tumblr',
      'twitter',
      'vimeo',
      'youtube',
    ];
    $this->assertTrue($available_icons == $options);
  }

  /**
   * Create a block & validate an external link is required for URL.
   */
  public function testValidation() {
    $this->assertAllowed("/block/add/social_links");
    $edit = [
      'info[0][value]' => 'Social Links Test',
      'field_utexas_sl_social_links[0][social_account_name]' => 'instagram',
      'field_utexas_sl_social_links[0][social_account_url]' => 'https://instagram.com/our-site',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Verify the block has been created.
    $block = $this->drupalGetBlockByInfo('Social Links Test');
    $this->drupalGet("/block/" . $block->id());

    $edit = [
      'field_utexas_sl_social_links[1][social_account_name]' => 'twitter',
      'field_utexas_sl_social_links[1][social_account_url]' => 'https://twitter.com/our-site',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Return to the block interface.
    $this->drupalGet("/block/" . $block->id());

    // Verify that an external link is required for the URL.
    $edit = [
      'field_utexas_sl_social_links[2][social_account_name]' => 'facebook',
      'field_utexas_sl_social_links[2][social_account_url]' => 'blah',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    $this->drupalGet("/block/" . $block->id());
    $social_link_0 = $this->getSession()->getPage()->find('css', '#edit-field-utexas-sl-social-links-0-social-account-url');
    $value = $social_link_0->getValue();
    $this->assertTrue($value == 'https://instagram.com/our-site');
    $social_link_2 = $this->getSession()->getPage()->find('css', '#edit-field-utexas-sl-social-links-2-social-account-url');
    $value = $social_link_2->getValue();
    $this->assertTrue($value == '');
    $this->drupalGet('<front>');
    $this->assertRaw('<svg');
    $this->assertRaw('<title id="facebook-title">Facebook</title>');
    $this->assertRaw('<path');
    $this->assertRaw('</svg>');
  }

}
