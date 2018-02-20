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
    $this->assertRaw('<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 32 32">');
    $this->assertRaw('<title>facebook</title>');
    $this->assertRaw('<path d="M32.016-0.647h-32.016v32.016h15.309l-0.038-11.982h-3.666v-5.292h3.402v-4.574c0-2.155 0.68-3.099 0.68-3.099 1.474-2.873 3.893-3.629 6.993-3.629 3.062 0 5.556 0.983 5.556 0.983l-0.907 5.254c-1.474-0.832-3.818-0.491-3.818-0.491-1.739 0.265-1.701 1.852-1.701 1.852v3.704h5.67l-0.378 5.292h-5.292v11.982h10.206v-32.016z"></path>');

  }

}
