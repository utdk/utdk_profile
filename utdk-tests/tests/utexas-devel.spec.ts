import { test, expect } from '@playwright/test';

// Featured highlight

test('Featured Highlight', async ({ page }) => {
  await page.goto('/featured-highlight');
  await expect(page.locator('#featured-highlight')
    .filter({
      has: page.getByText('June 12, 2019')
    })
    .filter({
      has:page.getByRole('heading', { level: 2 })
        .filter({
          has: page.getByRole('link', { name: 'Featured Highlight' })
        })
    })
    .filter({
      has: page.getByText('Add descriptive text to provide a short summary of this featured content.')
    })
  ).toBeVisible();

  await expect(page.locator('#featured-highlight')
    .filter({
      has: page.getByRole('link')
    })
  ).toContainText('Visit UTexas');

  await expect(
    page.locator('#featured-highlight .image-wrapper img')
  ).toHaveAttribute('src', '/sites/default/files/styles/utexas_image_style_600w/public/generated_sample/tower-lighting_3.gif');
});

// Flex content area

test('flex content area', async ({ page }) => {
  await page.goto('/flex-content-area');
  await expect(page.locator('.ut-flex-content-area').first()
    .filter({
      has: page.getByRole('heading', { name: 'Flex Content Area 1'})
    })
    .filter({
      has: page.getByText('The Flex Content Area has a number of display options.')
    })
  ).toBeVisible();

  await expect(
    page.locator('.ut-flex-content-area .image-wrapper img').first()
  ).toHaveAttribute('src', '/sites/default/files/styles/utexas_image_style_340w_227h/public/generated_sample/tower-lighting_3.gif');
});

// Promo list

// test('promo list', async ({ page }) => {
//   await page.goto('/promo-list');
//   const promoListPath = 'styles/utexas_image_style_64w_64h/public/generated_sample/tower-lighting.gif';
//   await expect(page.locator('.promo-list .image-wrapper img')).toHaveAttribute('src', new RegExp(promoListPath));
//   await expect(page.locator('.utexas-promo-list-container h3.ut-headline--underline')).toContainText('Promo List Group 1');
//   await expect(page.locator('.promo-list .content')).toContainText('Short descriptive text can be formatted.');
// });

// test('promo unit', async ({ page }) => {
//   await page.goto('/promo-unit');
//   const promoUnitPath = 'styles/utexas_image_style_800w_500h/public/generated_sample/tower-lighting.gif';
//   await expect(page.locator('.utexas-promo-unit .image-wrapper img')).toHaveAttribute('src', new RegExp(promoUnitPath));
//   await expect(page.locator('.utexas-promo-unit-container h3.ut-headline--underline')).toContainText('Promo Unit Group 1');
//   await expect(page.locator('.utexas-promo-unit .data-wrapper p')).toContainText('Short descriptive text can be formatted.');
// });

// test('photo content area', async ({ page }) => {
//   await page.goto('/photo-content-area');
//   const photoContentAreaPath = 'styles/utexas_image_style_450w_600h/public/generated_sample/tower-lighting.gif';
//   await expect(page.locator('.ut-photo-content-area .photo-wrapper img')).toHaveAttribute('src', new RegExp(photoContentAreaPath));
//   await expect(page.locator('.ut-photo-content-area h2.ut-headline')).toContainText('Photo Content Area');
// });

// test('hero default', async ({ page }) => {
//   await page.goto('/hero-default');
//   const heroPath = 'styles/utexas_image_style_720w_389h/public/generated_sample/tower-lighting.gif';
//   await expect(page.locator('.ut-hero img')).toHaveAttribute('src', new RegExp(heroPath));
//   await expect(page.locator('.hero--caption-credit-wrapper .credit')).toContainText('Copyright University of Texas at Austin');
//   await expect(page.locator('.hero--caption-credit-wrapper .hero-caption')).toContainText('A short caption may be added, describing the hero');
// });

// test('quick links', async ({ page }) => {
//   await page.goto('/quick-links');
//   await expect(page.locator('.utexas-quick-links h3.ut-headline')).toContainText('Quick Links');
//   await expect(page.locator('.utexas-quick-links .ut-copy')).toContainText('Quick links include a headline, copy text, and links.');
//   await expect(page.locator('.utexas-quick-links .link-list a')).toContainText('Our commitment to diversity');
// });

// test('resources', async ({ page }) => {
//   await page.goto('/resources');
//   const resourceImagePath = 'styles/utexas_image_style_400w_250h/public/generated_sample/tower-lighting.gif';
//   await expect(page.locator('.utexas-resource .image-wrapper img')).toHaveAttribute('src', new RegExp(resourceImagePath));
//   await expect(page.locator('.ut-resources-wrapper h3.ut-headline--underline')).toContainText('Resource Group 1');
//   await expect(page.locator('.utexas-resource-items .utexas-resource h3.ut-headline')).toContainText('Resource 1');
// });
