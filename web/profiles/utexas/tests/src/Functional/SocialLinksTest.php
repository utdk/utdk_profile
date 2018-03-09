<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Core\Render\Markup;
use Drupal\Component\Utility\Random;

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
    $this->drupalLogin($this->drupalCreateUser(['administer social links data config', 'administer blocks']));
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

  /**
   * Validate configuring and rendering.
   *
   * This test will configure the default Site Wide Social Links block
   * with a new 'test' network and icon. It then validates that this
   * displays.
   */
  public function testRender() {
    // Create test SVG.
    $location = 'public://';
    $random_util = new Random();
    $svg_filename = $random_util->word(5);
    $svg_tag = $random_util->word(5);
    $svg_data = "<svg><title>" . $svg_tag . "</title></svg>";
    file_put_contents($location . $svg_filename . '.svg', $svg_data);
    $saved_file = file_save_data($location . $svg_filename . '.svg', 'public://' . $svg_filename . '.svg', FILE_EXISTS_REPLACE);
    // Determine markup for evaluating presence of SVG in rendered page.
    $svgFile1FileContents = file_get_contents($saved_file->getFileUri());
    $svgFile1Markup = Markup::create($svgFile1FileContents);
    // Add a custom Social Network with 1st test SVG.
    $this->drupalGet("/admin/structure/utexas_block_social_links/add");
    $edit = [
      'label' => 'test',
      'id' => 'test',
      'files[icon]' => \Drupal::service('file_system')->realpath($saved_file->getFileUri()),
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Edit /block/1 (the default social links block) and add test network.
    $this->drupalGet("/block/1");
    $edit = [
      'field_utexas_sl_social_links[0][social_account_name]' => 'test',
      'field_utexas_sl_social_links[0][social_account_url]' => "https://testsocial.com",
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Go to homepage and confirm test network is rendering with test svg path.
    $this->drupalGet("<front>");
    $this->assertRaw("https://testsocial.com");
    $this->assertRaw($svgFile1Markup);

  }

  /**
   * Validate permission grants access to edit Social Links.
   */
  public function testPermission() {
    // Logout with the current user.
    $this->drupalLogout();
    // Try to access the social links edit page to get a 403.
    $this->assertForbidden('admin/structure/social-links');
    // Try editing the FB social block entry to get a 403.
    $this->assertForbidden('admin/structure/utexas_block_social_links/facebook/edit');
    // Create a new user with our permission to manage social links and login.
    $this->drupalLogin($this->drupalCreateUser(['administer social links data config']));
    // Try to access the social links edit page to get a 200.
    $this->assertAllowed('admin/structure/social-links');
    // Try editing the FB social block entry to get a 200.
    $this->assertAllowed('admin/structure/utexas_block_social_links/facebook/edit');
  }

}
