import { test, expect } from '@playwright/test';
import { Base } from './base';

test.use({ storageState: 'playwright/.auth/content-editor.json' });

test('has social sharing block', async ({ page }) => {

  // Create basic page
  const base = new Base(page);
  await base.createBasicPage();

  await expect(page.getByText('Share this content')).toBeVisible();

});
