<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Hero Image field schema & validation.
 */
trait HeroImageTestTrait {

  /**
   * Test schema.
   */
  public function verifyHeroImage() {
    $assert = $this->assertSession();
    // Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Hero Image paragraph types.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();

    // Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-image-0-upload',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-caption-0-value',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-photo-credit-0-value',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-heading-0-value',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-subheading-0-value',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-link-0-uri',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-link-0-title',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-1',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-2',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-3',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-4',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-5',
      'edit-field-flex-page-hi-0-subform-field-utexas-hi-display-style-6',

    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }

    $this->assertAllowed("/node/add/utexas_flex_page");
    // Add the Hero Image paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();
    // Submit with headline & no image.
    $edit = [
      'title[0][value]' => 'Hero Image Test',
      'field_flex_page_hi[0][subform][field_utexas_hi_heading][0][value]' => 'Test no image',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // If any other data is present, image is required.
    $this->assertRaw('Image field is required.');
    // Submit an image with no alt text.
    $edit = [
      'title[0][value]' => 'Hero Image Test',
      'files[field_flex_page_hi_0_subform_field_utexas_hi_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // Images must have alt text.
    $this->assertRaw('Alternative text field is required.');

    $this->assertAllowed("/node/add/utexas_flex_page");

    // Add the Hero Image paragraph type.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-hi-add-more-add-more-button-utexas-hero-image')->click();
    $edit = [
      'title[0][value]' => 'Hero Image Test',
      'files[field_flex_page_hi_0_subform_field_utexas_hi_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_hi[0][subform][field_utexas_hi_caption][0][value]' => 'This is a caption',
      'field_flex_page_hi[0][subform][field_utexas_hi_photo_credit][0][value]' => 'This is a photo credit',
      'field_flex_page_hi[0][subform][field_utexas_hi_heading][0][value]' => 'Test Heading',
      'field_flex_page_hi[0][subform][field_utexas_hi_subheading][0][value]' => 'Test Subheading',
      'field_flex_page_hi[0][subform][field_utexas_hi_link][0][uri]' => 'https://google.com',
      'field_flex_page_hi[0][subform][field_utexas_hi_link][0][title]' => 'Test External Link',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_hi[0][subform][field_utexas_hi_image][0][alt]' => 'Alternative text',
    ],
    'edit-submit');
    $node = $this->drupalGetNodeByTitle('Hero Image Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('utexas_image_style_720w_389h/public/hero_images/image-test');
    $this->assertRaw('alt="Alternative text"');
    $this->assertRaw('This is a caption');
    $this->assertRaw('This is a photo credit');
    $this->assertRaw('Test Heading');
    $this->assertRaw('Test Subheading');
    $this->assertRaw('<a href="https://google.com">Test External Link</a>');

    // Go back to edit node and make link internal.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $basic_page = $this->drupalGetNodeByTitle('Test Basic Page');
    $edit = [
      'field_flex_page_hi[0][subform][field_utexas_hi_link][0][uri]' => '/node/' . $basic_page->id(),
      'field_flex_page_hi[0][subform][field_utexas_hi_link][0][title]' => 'Test Internal Link',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $node = $this->drupalGetNodeByTitle('Hero Image Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('<a href="/test-basic-page">Test Internal Link</a>');

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
