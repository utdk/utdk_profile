import { test as setup, expect } from '@playwright/test';

const siteManagerAuthFile = 'playwright/.auth/site-manager.json';

setup('authenticate as site-manager', async ({ page }) => {
  await page.goto('/user/login');
  await page.getByLabel('Username').fill('site-manager');
  await page.getByLabel('Password').fill('test');
  await page.locator('input[name="op"]').click();
  await expect(page.getByRole('heading', { name: 'site-manager' })).toBeVisible();
  await page.context().storageState({ path: siteManagerAuthFile });
});

const contentEditorAuthFile = 'playwright/.auth/content-editor.json';

setup('authenticate as content-editor', async ({ page }) => {
  await page.goto('/user/login');
  await page.getByLabel('Username').fill('content-editor');
  await page.getByLabel('Password').fill('test');
  await page.locator('input[name="op"]').click();
  await expect(page.getByRole('heading', { name: 'content-editor' })).toBeVisible();
  await page.context().storageState({ path: contentEditorAuthFile });
});
