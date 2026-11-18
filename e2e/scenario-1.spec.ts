import { expect, test, type Page } from '@playwright/test';

test('create, qualify, and send an estimate for Northwind', async ({ page }) => {
    await page.goto('/');
    await chooseActingUser(page, 'Ada');
    await expect(page.locator('[data-acting-user="Ada"]')).toBeVisible();

    await page.getByLabel('Name').fill('Northwind');
    await page.getByRole('button', { name: 'Create contact' }).click();
    await expect(page.locator('[data-column="new"]')).toContainText('Northwind');

    await page.locator('[data-column="new"]').getByRole('link', { name: 'Northwind' }).last().click();
    await page.locator('#qualify').getByLabel('Email').fill('northwind@example.com');
    await page.locator('#qualify').getByLabel('Company').fill('Northwind');
    await page.getByRole('button', { name: 'Validate', exact: true }).click();
    await expect(page.locator('[data-column="qualified"]')).toBeVisible();

    await page.getByLabel('Cable quantity').fill('2');
    await page.getByLabel('Lamp quantity').fill('1');
    await expect(page.locator('[data-grand-total="25"]')).toBeVisible();

    const quantities = page.locator('#estimate').getByRole('spinbutton');
    const quantityCount = await quantities.count();
    for (let index = 0; index < quantityCount; index += 1) {
        const field = quantities.nth(index);
        if ((await field.inputValue()) === '') {
            await field.fill('0');
        }
    }

    await page.getByRole('button', { name: 'Validate estimate' }).click();
    await expect(page.locator('[data-column="estimate sent"]')).toBeVisible();
    await expect(page.locator('[data-saved-grand-total="25"]')).toBeVisible();
    await expect(page.getByText('This is a test.')).toBeVisible();

    await chooseActingUser(page, 'Ben');
    await expect(page.locator('[data-acting-user="Ben"]')).toBeVisible();
    await expect(page.locator('[data-column="new"]')).not.toContainText('Northwind');
    await expect(page.locator('[data-column="qualified"]')).not.toContainText('Northwind');
    await expect(page.locator('[data-column="estimate sent"]')).not.toContainText('Northwind');
});

test('dragging a card opens the next column edit and skips no column', async ({ page }) => {
    await page.goto('/');
    await chooseActingUser(page, 'Ada');
    await expect(page.locator('[data-acting-user="Ada"]')).toBeVisible();

    const name = `Harbor ${Date.now()}`;
    await page.getByLabel('Name').fill(name);
    await page.getByRole('button', { name: 'Create contact' }).click();
    await expect(page.locator('[data-column="new"]').getByRole('link', { name, exact: true })).toBeVisible();

    await dragCard(page, 'new', name, 'estimate sent');
    await expect(page).toHaveURL(/\/$/);
    await expect(page.locator('[data-column="new"]').getByRole('link', { name, exact: true })).toBeVisible();

    await dragCard(page, 'new', name, 'qualified');
    await expect(page).toHaveURL(/\/contacts\/\d+#qualify$/);
    await expect(page.locator('#qualify')).toBeInViewport();
    await expect(page.locator('[data-column="new"]')).toBeVisible();

    await page.getByRole('link', { name: 'Board' }).click();
    await expect(page.locator('[data-column="new"]').getByRole('link', { name, exact: true })).toBeVisible();

    await page.locator('[data-column="new"]').getByRole('link', { name, exact: true }).click();
    await page.locator('#qualify').getByLabel('Email').fill('harbor@example.com');
    await page.locator('#qualify').getByLabel('Company').fill('Harbor');
    await page.getByRole('button', { name: 'Validate', exact: true }).click();
    await page.getByRole('link', { name: 'Board' }).click();
    await expect(page.locator('[data-column="qualified"]').getByRole('link', { name, exact: true })).toBeVisible();

    await dragCard(page, 'qualified', name, 'estimate sent');
    await expect(page).toHaveURL(/\/contacts\/\d+#estimate$/);
    await expect(page.locator('#estimate')).toBeInViewport();
});

async function chooseActingUser(page: Page, name: string) {
    await page.getByRole('button', { name: 'Acting user' }).click();
    await page.getByRole('menuitem', { name, exact: true }).click();
}

async function dragCard(page: Page, fromColumn: string, name: string, toColumn: string) {
    const card = page.locator(`[data-column="${fromColumn}"]`).getByRole('link', { name, exact: true });
    const target = page.locator(`[data-column="${toColumn}"]`);
    const cardBox = await card.boundingBox();
    const targetBox = await target.boundingBox();
    if (cardBox === null || targetBox === null) {
        throw new Error('Missing card or column for drag');
    }
    await page.mouse.move(cardBox.x + cardBox.width / 2, cardBox.y + cardBox.height / 2);
    await page.mouse.down();
    await page.mouse.move(targetBox.x + targetBox.width / 2, targetBox.y + Math.min(120, targetBox.height / 2), { steps: 25 });
    await page.mouse.up();
}
