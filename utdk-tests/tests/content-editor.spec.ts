import { test, expect } from '@playwright/test';

test.use({ storageState: 'playwright/.auth/content-editor.json' });

const unrestrictedPages = [
  '/admin/content/block',
  '/block/add',
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
  '/admin/structure/block',
];
for (const pagePath of forbidden) {
  test(`verify forbidden access to ${pagePath}`, async ({ page }) => {
    const response = await page.goto(pagePath);
    expect(response?.status()).toBe(403);
  });
}
