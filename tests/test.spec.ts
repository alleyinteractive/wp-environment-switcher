import { Page } from '@playwright/test';
import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.beforeEach(async ({ page }) => {
  await page.goto('/wp-login.php');
  await page.waitForTimeout(1500);
  await page.locator('#user_login').fill('admin');
  await page.locator('#user_pass').fill('password');
  await page.locator('#wp-submit').click();
  await page.waitForTimeout(1500);
  await expect(
    page.getByRole('heading', { name: 'Dashboard', level: 1 }),
  ).toBeVisible();
});

const testPage = async (page: Page) => {
  const switcher = page.locator('#wp-admin-bar-wp-environment-switcher');
  await expect(switcher).toBeVisible();
  await expect(switcher.locator('> a')).toHaveText('Staging');

  // Hover over it to reveal the dropdown.
  await switcher.hover();

  const dropdown = switcher.locator('.ab-sub-wrapper');
  await expect(dropdown).toBeVisible();

  await expect(dropdown.getByText('Production')).toBeVisible();
  await expect(dropdown.getByText('Staging')).toBeVisible();
  await expect(dropdown.getByText('Local')).toBeVisible();
};

test('switcher is in the admin bar', async ({ page }) => {
  await page.goto('/wp-admin/');

  await testPage(page);
});

test('switcher is in the admin bar (key-value pairs)', async ({ page }) => {
  // Simulate a key-value pair environment configuration.
  await page.goto('/wp-admin/?keyvalue=true');

  await testPage(page);
});
