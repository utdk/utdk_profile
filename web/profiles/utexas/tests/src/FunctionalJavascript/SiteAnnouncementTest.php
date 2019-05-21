<?php

namespace Drupal\Tests\utexas\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\utexas\Traits\EntityTestTrait;
use Drupal\Tests\utexas\Traits\UserTestTrait;

/**
 * Test all aspects of site announcement functionality.
 *
 * @group utexas
 */
class SiteAnnouncementTest extends WebDriverTestBase {

  use TestFileCreationTrait;
  use EntityTestTrait;
  use UserTestTrait;

  /**
   * Use the 'utexas' installation profile.
   *
   * @var string
   */
  protected $profile = 'utexas';

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    $this->strictConfigSchema = NULL;
    parent::setUp();
  }

  /**
   * Privileged users can create, edit, & delete site announcements.
   */
  public function testIcons() {
    $this->getSession()->resizeWindow(1200, 2000);
    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();

    $account = $this->drupalCreateUser([
      'administer site configuration',
    ]);
    $this->drupalLogin($account);
    // A user without the 'administer utexas announcement icons' cannot access.
    $this->drupalGet('/admin/config/site-announcement/icons');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/icons/add');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/icons/beacon/edit');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/icons/beacon/delete');
    $web_assert->pageTextContains('You are not authorized to access this page.');

    $account = $this->drupalCreateUser([
      'administer utexas announcement icons',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/site-announcement/icons');

    // Verify 3 default icons are present upon installation.
    $web_assert->pageTextContains('Beacon');
    $web_assert->pageTextContains('Bullhorn');
    $web_assert->pageTextContains('Warning');

    // Add a new icon.
    $page->findLink('Add Announcement Icon')->click();
    $web_assert->pageTextContains('Add announcement icon');
    $page->findField('label')->setValue("Test Icon");
    $upload_field = $web_assert->waitForElementVisible('css', 'input[type="file"]');
    $dir = drupal_get_path('module', 'utexas_site_announcement') . '/assets/';
    $default_icons = file_scan_directory($dir, '/^.*\.(svg)$/i', ['key' => 'name'], 0);
    $test_file_path = $default_icons['beacon']->uri;
    $page->attachFileToField('files[icon]', $test_file_path);
    $page->pressButton('Save');
    $page->findField('id')->setValue("test_icon");
    $page->pressButton('Save');
    $web_assert->pageTextContains('Created configuration for Test Icon');

    // Edit the icon.
    $this->drupalGet('/admin/config/site-announcement/icons/test_icon/edit');
    $page->findField('label')->setValue("Change Icon");
    $page->pressButton('Save');
    $web_assert->pageTextContains('Change Icon');

    // Delete the icon.
    $this->drupalGet('/admin/config/site-announcement/icons/test_icon/delete');
    $web_assert->pageTextContains('Are you sure you want to delete');
    $page->pressButton('Delete');
    $this->drupalGet('/admin/config/site-announcement/icons');
    $web_assert->pageTextNotContains('Change Icon');
  }

  /**
   * Test the color scheme functionality.
   */
  public function testColorSchemes() {
    $this->getSession()->resizeWindow(1200, 2000);
    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();

    $account = $this->drupalCreateUser([
      'administer site configuration',
    ]);
    $this->drupalLogin($account);
    // A user without the proper permission cannot access.
    $this->drupalGet('/admin/config/site-announcement/color-scheme');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/color-scheme/add');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/color-scheme/yellow_black/edit');
    $web_assert->pageTextContains('You are not authorized to access this page.');
    $this->drupalGet('/admin/config/site-announcement/color-scheme/yellow_black/delete');
    $web_assert->pageTextContains('You are not authorized to access this page.');

    $account = $this->drupalCreateUser([
      'administer utexas announcement color schemes',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/site-announcement/color-scheme');

    // Verify 4 Default color schemes are present.
    $web_assert->pageTextContains('Green Background, White Text');
    $web_assert->pageTextContains('Grey Background, White Text');
    $web_assert->pageTextContains('Orange Background, Black Text');
    $web_assert->pageTextContains('Yellow Background, Black Text');

    // Add a new color scheme.
    $page->findLink('Add Announcement Color Scheme')->click();
    $web_assert->pageTextContains('Add announcement color scheme');
    $page->findField('label')->setValue("Test Color");
    $page->findField('background_color')->setValue("#bf5700");
    $page->findField('text_color')->setValue("#ffffff");
    $page->pressButton('Save');
    $page->findField('id')->setValue("test_color");
    $page->pressButton('Save');
    $web_assert->pageTextContains('Created configuration for Test Color');
    $web_assert->pageTextContains('Test Color');
    $web_assert->pageTextContains('#bf5700');
    $web_assert->pageTextContains('#ffffff');

    // Edit the color scheme.
    $this->drupalGet('/admin/config/site-announcement/color-scheme/test_color/edit');
    $page->findField('label')->setValue("Change Color Scheme");
    $page->findField('background_color')->setValue("#a6cd57");
    $page->findField('text_color')->setValue("#f8971f");

    $page->pressButton('Save');
    $web_assert->pageTextContains('Change Color Scheme');
    $web_assert->pageTextContains('#a6cd57');
    $web_assert->pageTextContains('#f8971f');

    // Delete the color scheme.
    $this->drupalGet('/admin/config/site-announcement/color-scheme/test_color/delete');
    $web_assert->pageTextContains('Are you sure you want to delete');
    $page->pressButton('Delete');
    $this->drupalGet('/admin/config/site-announcement/color-scheme/color-scheme');
    $web_assert->pageTextNotContains('Change Color Scheme');
  }

  /**
   * Test the announcement creation functionality.
   */
  public function testSiteAnnouncementCreation() {
    $this->getSession()->resizeWindow(1200, 2000);
    $session = $this->getSession();
    $web_assert = $this->assertSession();
    $page = $session->getPage();
    // Create a test node.
    $test_page = $this->createBasicPage();

    $account = $this->initializeAdminUser();
    $this->drupalLogin($account);
    // A user without the proper permission cannot access.
    $this->drupalGet('/admin/config/site-announcement');
    $web_assert->pageTextContains('You are not authorized to access this page.');

    $account = $this->initializeAdminUser([
      'manage utexas site announcement',
    ]);
    $this->drupalLogin($account);
    $this->drupalGet('/admin/config/site-announcement');
    $this->submitForm([
      'edit-state-all' => 'all',
      'edit-title' => 'Tornado warning',
      'edit-icon-beacon' => 'beacon',
      'edit-scheme-yellow-black' => 'yellow_black',
      'edit-message-value' => 'It looks like we got a tornado coming, folks! Take shelter!',
      'edit-cta-url' => 'https://tylerfahey.com',
      'edit-cta-title' => 'How to get help',
    ], 'Save configuration');

    $web_assert->pageTextContains('The configuration options have been saved.');

    // Verify the announcement is visible on homepage & the test node.
    $pages_to_test = [
      '/node/' . $test_page,
      '<front>',
    ];
    foreach ($pages_to_test as $path) {
      $this->drupalGet($path);
      $web_assert->elementTextContains('css', 'h2.announcement-title', "Tornado warning");
      $web_assert->elementTextContains('css', '.announcement-body', 'It looks like we got a tornado coming, folks! Take shelter!');
      $web_assert->elementTextContains('css', '#block-siteannouncement .ut-btn.cta a', 'How to get help');
      $web_assert->elementAttributeContains('css', '#block-siteannouncement .ut-btn.cta a', 'href', 'https://tylerfahey.com');
      $web_assert->responseContains('#ffeb63');
      $web_assert->responseContains('#000000');
      // Beginning of beacon.svg data.
      $web_assert->responseContains('M16 8c0-4.418-3.582-8-8-8s-8 3.582-8 8c0 3.438 2.169 6.37');
    }

    // Go back and change to "homepage only" and change some other config.
    $this->drupalGet('/admin/config/site-announcement');
    $this->submitForm([
      'edit-state-homepage' => 'homepage',
      'edit-icon-bullhorn' => 'bullhorn',
      'edit-scheme-green-white' => 'green_white',
      'edit-cta-url' => 'https://utexas.edu',
    ], 'Save configuration');
    $web_assert->pageTextContains('The configuration options have been saved.');

    // Verify the announcement is visible on the homepage.
    $this->drupalGet('<front>');
    $web_assert->elementTextContains('css', 'h2.announcement-title', "Tornado warning");
    $web_assert->elementTextContains('css', '.announcement-body', 'It looks like we got a tornado coming, folks! Take shelter!');
    $web_assert->elementTextContains('css', '#block-siteannouncement .ut-btn.cta a', 'How to get help');
    $web_assert->elementAttributeContains('css', '#block-siteannouncement .ut-btn.cta a', 'href', 'https://utexas.edu');
    $web_assert->responseContains('#43695b');
    $web_assert->responseContains('#ffffff');
    // Beginning of bullhorn.svg data.
    $web_assert->responseContains('M16 6.707c0-3.139-0.919-5.687-2.054-5.707 0.005-0');

    // Verify the announcement is not visible on test node.
    $this->drupalGet('/node/' . $test_page);
    $web_assert->elementNotExists('css', '#site-announcement');
    $web_assert->responseNotContains('#43695b');
    $web_assert->responseNotContains('#ffffff');

    // Go back and change announcement to "Inactive" display setting.
    $this->drupalGet('/admin/config/site-announcement');
    $this->submitForm([
      'edit-state-inactive' => 'inactive',
    ], 'Save configuration');

    // Verify the announcement is not visible on homepage & the test node.
    $pages_to_test = [
      '/node/' . $test_page,
      '<front>',
    ];
    foreach ($pages_to_test as $path) {
      $this->drupalGet($path);
      $web_assert->elementNotExists('css', '#site-announcement');
      $web_assert->responseNotContains('#43695b');
      $web_assert->responseNotContains('#ffffff');
    }
  }

}
