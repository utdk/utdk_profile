import { test, expect } from '@playwright/test';

test.use({ storageState: 'playwright/.auth/site-manager.json' });

// Verify 200 access

const unrestrictedPaths = [
  '/admin/structure/types/manage/page/fields',
  '/admin/structure/types/manage/page/form-display',
  '/admin/structure/types/manage/page/display',
  '/admin/structure/types/manage/page/fields/add-field',
  '/admin/structure/block-content/manage/basic/fields',
  '/admin/structure/block-content/manage/basic/fields/add-field',
  '/admin/structure/block-content/manage/basic/form-display',
  '/admin/structure/block-content/manage/basic/display',
  '/admin/structure/views/view/content',
  '/admin/structure/views/view/content/delete',
  '/admin/config/content/formats',
  '/admin/config/content/formats/manage/basic_html',
  '/admin/config/content/formats/manage/full_html',
  '/admin/config/content/formats/manage/restricted_html',
  '/admin/config/media/image-styles/manage/media_library',
  '/admin/config/media/image-styles/manage/medium',
  '/admin/config/media/image-styles/manage/medium/delete',
  '/admin/config/media/image-styles/manage/thumbnail',
  '/admin/config/media/image-styles/manage/thumbnail/delete',
  '/admin/config/media/image-styles/manage/large',
  '/admin/config/media/image-styles/manage/large/delete'
];
for (const path of unrestrictedPaths) {
  test(`verify ${path} 200 access`, async ({ page }) => {
    await page.goto(path);
    await expect(page.getByText('This component is read-only and should not be modified.')).not.toBeVisible();
  });
}

// Content types

// utexas_flex_page
test('verify utexas_flex_page read only access', async ({ page }) => {
  await page.goto('/admin/structure/types/manage/utexas_flex_page');
  await page.screenshot({ path: './screenshots/utexas_flex_page.png' })
  await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
});

// utexas_flex_page/fields/add-field - access denied
test('verify utexas_flex_page/fields/add-field access denied', async ({ page }) => {
  const response = await page.goto('/admin/structure/types/manage/utexas_flex_page/fields/add-field');
  expect(response?.status()).toBe(403);
});


// Image styles

const imageStyles = [
  'utexas_image_style_1000w',
  'utexas_image_style_1000w_600h',
  'utexas_image_style_1000w_666h',
  'utexas_image_style_112w_112h',
  'utexas_image_style_1140w_616h',
  'utexas_image_style_1200w',
  'utexas_image_style_1200w_750h',
  'utexas_image_style_120w_150h',
  'utexas_image_style_128w_128h',
  'utexas_image_style_1350w',
  'utexas_image_style_140w_140h',
  'utexas_image_style_1440w_778h',
  'utexas_image_style_150w_188h',
  'utexas_image_style_1600w',
  'utexas_image_style_1600w_500h',
  'utexas_image_style_170w_170h',
  'utexas_image_style_176w_112h',
  'utexas_image_style_1800w',
  'utexas_image_style_1800w_2400h',
  'utexas_image_style_1920w_1038h',
  'utexas_image_style_2000w',
  'utexas_image_style_220w_140h',
  'utexas_image_style_2250w_900h',
  'utexas_image_style_2280w_1232h',
  'utexas_image_style_250w_150h',
  'utexas_image_style_280w_152h',
  'utexas_image_style_280w_280h',
  'utexas_image_style_300w_376h',
  'utexas_image_style_3200w',
  'utexas_image_style_3200w_1000h',
  'utexas_image_style_330w_200h',
  'utexas_image_style_340w_227h',
  'utexas_image_style_400w_250h',
  'utexas_image_style_440w_280h',
  'utexas_image_style_450w_300h',
  'utexas_image_style_450w_600h',
  'utexas_image_style_500w',
  'utexas_image_style_500w_300h',
  'utexas_image_style_500w_333h',
  'utexas_image_style_500w_500h',
  'utexas_image_style_600w',
  'utexas_image_style_600w_375h',
  'utexas_image_style_64w_64h',
  'utexas_image_style_660w_400h',
  'utexas_image_style_675w',
  'utexas_image_style_680w_454h',
  'utexas_image_style_720w_389h',
  'utexas_image_style_800w_500h',
  'utexas_image_style_85w_85h',
  'utexas_image_style_900w',
  'utexas_image_style_900w_1200h',
  'utexas_image_style_900w_600h',
  'utexas_image_style_960w_519h',
];
for (const imageStyle of imageStyles) {
  test(`verify ${imageStyle} image style is read only`, async ({ page }) => {
    await page.goto('/admin/config/media/image-styles/manage/' + imageStyle);
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
  test(`verify ${imageStyle} image style /delete is read only`, async ({ page }) => {
    await page.goto('/admin/config/media/image-styles/manage/' + imageStyle + '/delete');
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
  test(`verify ${imageStyle} image style /flush is not read only`, async ({ page }) => {
    await page.goto('/admin/config/media/image-styles/manage/' + imageStyle + '/flush');
    await expect(page.getByText('This component is read-only and should not be modified.')).not.toBeVisible();
  });
}

// Block types

const blockTypes = [
  'call_to_action',
  'utexas_featured_highlight',
  'utexas_flex_content_area',
  'utexas_flex_list',
  'utexas_hero',
  'utexas_image_link',
  'utexas_instagram',
  'utexas_photo_content_area',
  'utexas_promo_list',
  'utexas_promo_unit',
  'utexas_quick_links',
  'utexas_resources',
  'social_links',
];
for (const blockType of blockTypes) {
  test(`verify ${blockType} block type read only access`, async ({ page }) => {
    await page.goto('/admin/structure/block-content/manage/' + blockType);
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
  test(`verify ${blockType} block type/fields/add-field access denied`, async ({ page }) => {
    const response = await page.goto('/admin/structure/block-content/manage/' + blockType + '/fields/add-field');
    expect(response?.status()).toBe(403);
  });
}

// Media types

const mediaTypes = [
  'utexas_image',
  'utexas_video_external',
  'utexas_document',
];
for (const mediaType of mediaTypes) {
  test(`verify ${mediaType} media type /fields read only access`, async ({ page }) => {
    await page.goto('/admin/structure/media/manage/' + mediaType + '/fields');
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
  test(`verify ${mediaType} media type /form-display read  only access`, async ({ page }) => {
    await page.goto('/admin/structure/media/manage/' + mediaType + '/form-display');
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
  test(`verify ${mediaType} media type /display read only access`, async ({ page }) => {
    await page.goto('/admin/structure/media/manage/' + mediaType + '/display');
    await expect(page.getByText('This component is read-only and should not be modified.')).toBeVisible();
  });
}
