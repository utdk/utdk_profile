import { test, expect } from '@playwright/test';

test.use({ storageState: 'playwright/.auth/site-manager.json' });

const unrestrictedPages = [
  '/admin/structure/block',
  '/admin/content/block',
];
for (const pagePath of unrestrictedPages) {
  test(`verify access to ${pagePath}`, async ({ page }) => {
    const response = await page.goto(pagePath);
    expect(response?.status()).toBe(200);
  });
}

const pageNotFound = [
  '/admin/structure/types/manage/utexas_flex_page/fields',
];
for (const pagePath of pageNotFound) {
  test(`verify page not found for ${pagePath}`, async ({ page }) => {
    const response = await page.goto(pagePath);
    expect(response?.status()).toBe(404);
  });
}

const forbidden = [
  '/admin/people/permissions',
  '/admin/config/content/layout_builder_style',
];
for (const pagePath of forbidden) {
  test(`verify forbidden access to ${pagePath}`, async ({ page }) => {
    const response = await page.goto(pagePath);
    expect(response?.status()).toBe(403);
  });
}
