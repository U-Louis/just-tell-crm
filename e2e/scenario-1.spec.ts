import { expect, test } from '@playwright/test';

test('create, qualify, and send an estimate for Northwind', async ({ page }) => {
    await page.goto('/');
    await page.getByLabel('Acting user').selectOption({ label: 'Ada' });
    await page.getByRole('button', { name: 'Select' }).click();
    await expect(page.locator('[data-acting-user="Ada"]')).toBeVisible();

    await page.getByLabel('Name').fill('Northwind');
    await page.getByRole('button', { name: 'Create contact' }).click();
    await expect(page.locator('[data-column="new"]')).toContainText('Northwind');

    await page.locator('[data-column="new"]').getByRole('link', { name: 'Northwind' }).click();
    await page.getByLabel('Email').fill('northwind@example.com');
    await page.getByLabel('Company').fill('Northwind');
    await page.getByRole('button', { name: 'Validate', exact: true }).click();
    await expect(page.locator('[data-column="qualified"]')).toBeVisible();

    await page.getByLabel('Cable quantity').fill('2');
    await page.getByLabel('Lamp quantity').fill('1');
    await expect(page.locator('[data-grand-total="25"]')).toBeVisible();

    await page.getByRole('button', { name: 'Validate estimate' }).click();
    await expect(page.locator('[data-column="estimate sent"]')).toBeVisible();
    await expect(page.locator('[data-saved-grand-total="25"]')).toBeVisible();
    await expect(page.getByText('This is a test.')).toBeVisible();
});
