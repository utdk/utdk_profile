import { test, expect } from '@playwright/test';

test.use({ storageState: 'playwright/.auth/content-editor.json' });

// test('verify no target attribute', async ({ page }) => {
//   await page.goto('');
//   await expect(page.getByText('Undergraduate Program', { exact: true })).not.toHaveAttribute('target');
// });

test('verify main menu link target option', async ({ page }) => {
  await page.goto('/admin/structure/menu/item/9/edit');
  await expect(page.getByLabel("Open in new window/tab")).toBeVisible();
});

test('verify main menu link options can be set', async ({ page }) => {
  await page.goto('/admin/structure/menu/item/9/edit');
  await page.getByLabel('Link', { exact: true }).fill('https://utexas.edu');
  await page.getByLabel('Open in new window/tab').setChecked(true);
  await page.getByLabel('Authentication required icon').setChecked(true);
  await page.locator('input[name="op"]').click();
  await page.goto('');
  await expect(page.getByText('Undergraduate Program', { exact: true })).toHaveAttribute('target', '_blank');
});

test('verify main menu link options can be unset', async ({ page }) => {
  await page.goto('/admin/structure/menu/item/9/edit');
  await page.getByLabel('Open in new window/tab').setChecked(false);
  await page.getByLabel('No icon').setChecked(true);
  await page.locator('input[name="op"]').click();
  await page.goto('');
  await expect(page.getByText('Undergraduate Program', { exact: true })).not.toHaveAttribute('target');
});
