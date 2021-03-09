<?php

namespace Drupal\Tests\utexas\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\image\Kernel\ImageFieldCreationTrait;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;
use Drupal\Tests\utexas\Traits\InstallTestTrait;
use Drupal\Core\Render\Markup;

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
   * Specify the theme to be used in testing.
   *
   * @var string
   */
  protected $defaultTheme = 'forty_acres';

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
    $this->drupalLogin($this->drupalCreateUser([
      'administer social links data config',
      'administer blocks',
      'view the administration theme',
    ]));
  }

  /**
   * Test schema.
   */
  public function testSocialLinks() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the block type.
    $this->assertAllowed("/block/add/social_links");
    // 2. Verify the correct field schemae exist.
    $fields = [
      'edit-info-0-value',
      'edit-field-utexas-sl-social-links-0-headline',
      'edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-name',
      'edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-url',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }
    $page = $this->getSession()->getPage();
    $social_link_options = $page->findAll('css', '#edit-field-utexas-sl-social-links-0-social-account-links-0-social-account-name option');
    $options = [];
    foreach (array_values($social_link_options) as $option) {
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

    // CRUD: CREATE
    // Create a block & validate an external link is required for URL.
    $this->assertAllowed("/block/add/social_links");
    $edit = [
      'info[0][value]' => 'Social Links Test',
      'field_utexas_sl_social_links[0][social_account_links][0][social_account_name]' => 'instagram',
      'field_utexas_sl_social_links[0][social_account_links][0][social_account_url]' => 'https://instagram.com/our-site',
    ];
    $this->submitForm($edit, 'Save');

    // Verify the block has been created.
    $block = $this->drupalGetBlockByInfo('Social Links Test');
    $this->drupalGet("/block/" . $block->id());

    // CRUD: UPDATE.
    $page->pressButton('edit-field-utexas-sl-social-links-0-social-account-links-actions-add');
    $page->pressButton('edit-field-utexas-sl-social-links-0-social-account-links-actions-add');
    $edit = [
      'field_utexas_sl_social_links[0][social_account_links][1][social_account_name]' => 'twitter',
      'field_utexas_sl_social_links[0][social_account_links][1][social_account_url]' => 'https://twitter.com/our-site',
      'field_utexas_sl_social_links[0][social_account_links][2][social_account_name]' => 'facebook',
      'field_utexas_sl_social_links[0][social_account_links][2][social_account_url]' => 'https://facebook.com/our-site',
    ];
    $this->submitForm($edit, 'Save');

    // Return to the block interface.
    $this->drupalGet("/block/" . $block->id());
    // Remove the 2nd social link instance.
    $edit = [
      'field_utexas_sl_social_links[0][social_account_links][1][social_account_url]' => '',
    ];
    $this->submitForm($edit, 'Save');

    // Return to the block interface.
    $this->drupalGet("/block/" . $block->id());

    // Verify subsequent links persist after previous links are removed.
    $assert->fieldValueEquals('field_utexas_sl_social_links[0][social_account_links][1][social_account_name]', 'facebook');
    $assert->fieldValueEquals('field_utexas_sl_social_links[0][social_account_links][1][social_account_url]', 'https://facebook.com/our-site');

    // Verify that an external link is required for the URL.
    $edit = [
      'field_utexas_sl_social_links[0][social_account_links][1][social_account_name]' => 'facebook',
      'field_utexas_sl_social_links[0][social_account_links][1][social_account_url]' => 'blah',
    ];
    $this->submitForm($edit, 'Save');
    $this->assertRaw('1 error has been found');

    // Add a custom Social Network with 1st test SVG.
    $this->drupalGet("/admin/structure/social-links/add");
    $edit = [
      'label' => 'test',
      'id' => 'test',
      'files[icon]' => \Drupal::service('file_system')->realpath(DRUPAL_ROOT . '/profiles/contrib/utexas/tests/fixtures/check.svg'),
    ];
    $this->submitForm($edit, 'Save');

    // Edit the default social links block and add test network and headline.
    $sitewide_social_block_id = $this->drupalGetBlockByInfo('Sitewide Social Media Links')->id();
    $this->drupalGet("/block/" . $sitewide_social_block_id);
    $edit = [
      'field_utexas_sl_social_links[0][headline]' => 'Headline Test',
      'field_utexas_sl_social_links[0][social_account_links][0][social_account_name]' => 'test',
      'field_utexas_sl_social_links[0][social_account_links][0][social_account_url]' => "https://testsocial.com",
    ];
    $this->submitForm($edit, 'Save');

    // CRUD: READ.
    // Go to homepage and confirm test network is rendering with test svg path.
    $this->drupalGet("<front>");
    $this->assertRaw("https://testsocial.com");
    $this->assertRaw("Headline Test");
    $this->assertRaw('<path d="M6.464 13.676c-.194.194-.513.194-.707 0l-4.96-4.955c-.194-.193-.194-.513 0-.707l1.405-1.407c.194-.195.512-.195.707 0l2.849 2.848c.194.193.513.193.707 0l6.629-6.626c.195-.194.514-.194.707 0l1.404 1.404c.193.194.193.513 0 .707l-8.741 8.736z"></path>');

    // Go back and change icon.
    $svgFile2FileContents = file_get_contents(DRUPAL_ROOT . '/profiles/contrib/utexas/tests/fixtures/location.svg');
    Markup::create($svgFile2FileContents);

    // Edit the existing custom Social Network test network.
    $this->drupalGet('/admin/structure/social-links/test/edit');
    $edit = [
      'files[icon]' => \Drupal::service('file_system')->realpath(DRUPAL_ROOT . '/profiles/contrib/utexas/tests/fixtures/location.svg'),
    ];
    $this->submitForm($edit, 'Save');

    // Go to homepage & confirm test network is rendering with test 2 svg path.
    $this->drupalGet("<front>");
    $this->assertRaw('<path d="M5.4749999,0 C2.43935876,0 0,2.45021982 0,5.50153207 C0,8.5518841 5.4749999,16.0038459 5.4749999,16.0038459 C5.4749999,16.0038459 10.9499998,8.5518841 10.9499998,5.50153207 C10.9499998,2.45021982 8.51064105,0 5.4749999,0 Z M5.89615374,8.00192294 C4.48158136,8.00192294 3.36923071,6.89054251 3.36923071,5.4749999 C3.36923071,4.06042752 4.48061114,2.94807687 5.89615374,2.94807687 C7.31072613,2.94807687 8.42307678,4.0594573 8.42307678,5.4749999 C8.42307678,6.89051825 7.31075039,8.00192294 5.89615374,8.00192294 Z"></path>');

    // Logout with the current user.
    $this->drupalLogout();

    // Verify Permissions.
    // Try to access the social links edit page to get a 403.
    $this->assertForbidden('admin/structure/social-links');
    // Try editing the FB social block entry to get a 403.
    $this->assertForbidden('admin/structure/social-links/facebook/edit');
    // Create a new user with our permission to manage social links and login.
    $this->drupalLogin($this->drupalCreateUser(['administer social links data config']));
    // Try to access the social links edit page to get a 200.
    $this->assertAllowed('admin/structure/social-links');
    // Try editing the FB social block entry to get a 200.
    $this->assertAllowed('admin/structure/social-links/facebook/edit');
  }

}
