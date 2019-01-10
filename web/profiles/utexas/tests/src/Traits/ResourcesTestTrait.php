<?php

namespace Drupal\Tests\utexas\Traits;

/**
 * Verifies Resource schema & validation.
 */
trait ResourcesTestTrait {

  /**
   * Test schema.
   */
  public function verifyResources() {
    $assert = $this->assertSession();
    // Verify a user has access to the content type.
    $this->assertAllowed("/node/add/utexas_flex_page");

    // Add a Resource paragraph type instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-add-more-add-more-button-utexas-resource-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-add-more-add-more-button-utexas-resource')->click();

    // Verify the correct field schema exist.
    $fields = [
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-title-0-value',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-image-0-upload',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-headline-0-value',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-links-0-uri',
      'edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-subform-field-utexas-resource-links-0-title',
    ];
    foreach ($fields as $field) {
      $assert->fieldExists($field);
    }

    $this->assertAllowed("/node/add/utexas_flex_page");
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-add-more-add-more-button-utexas-resource-container')->click();
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-add-more-add-more-button-utexas-resource')->click();
    $edit = [
      'title[0][value]' => 'Resource Test',
      'field_flex_page_resource[0][subform][field_utexas_rc_title][0][value]' => "Test Title for Resource Field",
      'files[field_flex_page_resource_0_subform_field_utexas_rc_items_0_subform_field_utexas_resource_image_0]' => \Drupal::service('file_system')->realpath($this->testImage),
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_headline][0][value]' => 'Resource Headline',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][0][uri]' => 'https://www.google.com',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][0][title]' => 'Resource Link 1',
    ];
    $this->drupalPostForm(NULL, $edit, 'edit-submit');

    // Alt text must be submitted *after* the image has been added.
    $this->drupalPostForm(NULL, [
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_image][0][alt]' => 'alternative text',
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Resource Test');
    $this->drupalGet('node/' . $node->id() . '/edit');
    // Verify we can add a second link item to Resource instance.
    $this->getSession()->getPage()->find('css', '#edit-field-flex-page-resource-0-subform-field-utexas-rc-items-0-top-links-edit-button')->click();
    $basic_page = $this->drupalGetNodeByTitle('Test Basic Page');
    $this->drupalPostForm(NULL, [
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][1][title]' => 'Resource Link 2',
      'field_flex_page_resource[0][subform][field_utexas_rc_items][0][subform][field_utexas_resource_links][1][uri]' => '/node/' . $basic_page->id(),
    ],
      'edit-submit');
    $node = $this->drupalGetNodeByTitle('Resource Test');
    $this->drupalGet('node/' . $node->id());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertRaw('Test Title for Resource Field');
    $this->assertRaw('Resource Headline');
    // Test image upload and alt text.
    $this->assertRaw('utexas_image_style_800w_500h/public/resources/image-test');
    $this->assertRaw('alt="alternative text"');
    $this->assertRaw('Resource Link 1');
    // Test external link.
    $this->assertRaw('<a href="https://www.google.com"');
    // Test internal link and addition of multiple links.
    $this->assertRaw('<a href="/test-basic-page" class="ut-link--darker">Resource Link 2</a>');

    $this->drupalGet('node/' . $node->id() . '/delete');
    $this->submitForm([], 'Delete');
  }

}
