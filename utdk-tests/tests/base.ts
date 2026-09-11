import { Page, expect } from '@playwright/test';
export class Base {

  constructor(private page: Page) {}

  async createBasicPage() {
    await this.page.goto('/node/add/page');
    await this.page.getByLabel('Title',{exact: true}).fill('Test Page');
    await this.page.click('#edit-submit');
    await expect(this.page.getByText('Basic page Test Page has been created.')).toBeVisible();
  }
}
