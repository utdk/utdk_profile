<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Featured Highlight field schema & output.
 */
trait FeaturedHighlightTestTrait {

  /**
   * Test schema.
   */
  public function verifyFeaturedHighlight() {
    $assert = $this->assertSession();
    // 1. Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");
    // 2. Add the Featured Highlight.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fh-add-more-add-more-button-utexas-featured-highlight')->click();
    // 3. Verify the correct field schemae exist.
    $fields = [
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-media-0-upload',
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-headline-0-value',
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-date-0-value-date',
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-copy-0-value',
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-cta-0-title',
      'edit-field-flex-page-fh-0-subform-field-utexas-fh-cta-0-uri',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }

    $this->assertAllowed("/node/add/utexas_flex_page");
    // 1. Add the Featured Highlight paragraph widget.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-fh-add-more-add-more-button-utexas-featured-highlight')->click();

    $edit = [
      'title[0][value]' => 'Featured Highlight Test',
      'files[field_flex_page_fh_0_subform_field_utexas_fh_media_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_fh[0][subform][field_utexas_fh_headline][0][value]' => 'Featured Highlight Headline!',
      'field_flex_page_fh[0][subform][field_utexas_fh_copy][0][value]' => 'Featured Highlight copy text.',
      'field_flex_page_fh[0][subform][field_utexas_fh_cta][0][uri]' => 'https://markfullmer.com',
      'field_flex_page_fh[0][subform][field_utexas_fh_date][0][value][date]' => '2018-01-01',
      'field_flex_page_fh[0][subform][field_utexas_fh_cta][0][title]' => 'Featured Highlight Link',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    // 2. Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_fh[0][subform][field_utexas_fh_media][0][alt]' => 'Alt text',
    ],
    'edit-submit');
    $node = $this->drupalGetNodeByTitle('Featured Highlight Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);

    // 3. Verify initial content is displayed.
    // Headline should be a link at this point:
    $this->assertRaw('<a href="https://markfullmer.com">Featured Highlight Headline!</a>');
    $this->assertRaw('Featured Highlight copy text.');
    $this->assertRaw('Jan. 1, 2018');
    // External links must be allowed in the CTA field.
    $this->assertRaw('<a href="https://markfullmer.com" class="ut-btn button">Featured Highlight Link</a>');

    $this->assertRaw('utexas_image_style_250w_150h/public/featured_highlight/image-test');

    // 4. Verify that an internal link can be used.
    $basic_page = $this->drupalGetNodeByTitle('Test Basic Page');
    $this->drupalGet('node/' . $node->id() . '/edit');
    $edit = [
      'field_flex_page_fh[0][subform][field_utexas_fh_cta][0][uri]' => '/node/' . $basic_page->id(),
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('<a href="/test-basic-page" class="ut-btn button">Featured Highlight Link</a>');

    // 5. Verify if a CTA link is not present, headline displays with no link.
    $this->drupalGet('node/' . $node->id() . '/edit');
    $edit = [
      'field_flex_page_fh[0][subform][field_utexas_fh_cta][0][uri]' => '',
      'field_flex_page_fh[0][subform][field_utexas_fh_cta][0][title]' => '',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');
    $this->drupalGet('node/' . $node->id());
    $this->assertRaw('Featured Highlight Headline!');

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
