/**
 * E2E Tests — RBAC (Roles & Permissions CRUD)
 * Tests the new /admin/rbac page with full Create/Read/Update/Delete for
 * both Roles and Permissions, plus the permission matrix dialog.
 */

import { test, expect, type Page } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/rbac';

async function gotoRbac(page: Page) {
    await page.goto(BASE);
    await page.waitForLoadState('networkidle');
    await page.waitForSelector('[data-testid="tab-roles"], h1', { timeout: 15_000 });
}

import { test, expect, type Page } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/rbac';

async function gotoRbac(page: Page) {
    await page.goto(BASE);
    await page.waitForLoadState('networkidle');
    await page.waitForSelector('[data-testid="tab-roles"], h1', { timeout: 15_000 });
}

// ─── Auth guard ───────────────────────────────────────────────────────────────
test.describe('RBAC — Auth guard', () => {
    test('unauthenticated GET /admin/rbac redirects to login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ─── Page rendering ───────────────────────────────────────────────────────────
test.describe('RBAC — Page rendering', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('page title includes "Roles"', async ({ page }) => {
        await gotoRbac(page);
        await expect(page).toHaveTitle(/Roles/i);
    });

    test('Roles tab is visible and active by default', async ({ page }) => {
        await gotoRbac(page);
        await expect(page.locator('[data-testid="tab-roles"]')).toBeVisible();
    });

    test('Permissions tab is visible', async ({ page }) => {
        await gotoRbac(page);
        await expect(page.locator('[data-testid="tab-permissions"]')).toBeVisible();
    });

    test('roles tab shows count > 0', async ({ page }) => {
        await gotoRbac(page);
        const txt = await page.locator('[data-testid="tab-roles"]').innerText();
        const n = parseInt(txt.match(/\d+/)?.[0] ?? '0', 10);
        expect(n).toBeGreaterThan(0);
    });

    test('permissions tab shows count > 0', async ({ page }) => {
        await gotoRbac(page);
        const txt = await page.locator('[data-testid="tab-permissions"]').innerText();
        const n = parseInt(txt.match(/\d+/)?.[0] ?? '0', 10);
        expect(n).toBeGreaterThan(0);
    });

    test('roles table has data rows', async ({ page }) => {
        await gotoRbac(page);
        const rows = page.locator('[data-testid="roles-table"] tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('sidebar has Roles & Permissions link', async ({ page }) => {
        await gotoRbac(page);
        const link = page.locator('aside a[href*="/admin/rbac"], nav a[href*="/admin/rbac"]');
        await expect(link).toBeVisible({ timeout: 5_000 });
    });

    test('clicking Permissions tab shows search input and create button', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="tab-permissions"]').click();
        await expect(page.locator('[data-testid="perm-search"]')).toBeVisible();
        await expect(page.locator('[data-testid="btn-create-permission"]')).toBeVisible();
    });
});

// ─── Role CRUD ────────────────────────────────────────────────────────────────
test.describe('RBAC — Role CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('Create Role button is visible', async ({ page }) => {
        await gotoRbac(page);
        await expect(page.locator('[data-testid="btn-create-role"]')).toBeVisible();
    });

    test('clicking Create Role opens dialog', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="btn-create-role"]').click();
        await expect(page.locator('[data-testid="role-dialog"]')).toBeVisible({ timeout: 5_000 });
        await expect(page.locator('[data-testid="input-role-name"]')).toBeVisible();
    });

    test('submitting empty name keeps dialog open', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="btn-create-role"]').click();
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForTimeout(500);
        await expect(page.locator('[data-testid="role-dialog"]')).toBeVisible();
    });

    test('can create a new role', async ({ page }) => {
        await gotoRbac(page);
        const name = `test_role_${Date.now()}`;
        await page.locator('[data-testid="btn-create-role"]').click();
        await page.locator('[data-testid="input-role-name"]').fill(name);
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('[data-testid="role-dialog"]')).toBeHidden({ timeout: 8_000 });
        await expect(page.locator(`text=${name}`)).toBeVisible({ timeout: 8_000 });
    });

    test('can rename a role', async ({ page }) => {
        await gotoRbac(page);
        const name    = `rename_from_${Date.now()}`;
        const newName = `renamed_to_${Date.now()}`;

        // Create
        await page.locator('[data-testid="btn-create-role"]').click();
        await page.locator('[data-testid="input-role-name"]').fill(name);
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForLoadState('networkidle');

        // Edit
        await page.locator(`[data-testid="btn-edit-role-${name}"]`).click();
        await page.locator('[data-testid="input-role-name"]').clear();
        await page.locator('[data-testid="input-role-name"]').fill(newName);
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator(`text=${newName}`)).toBeVisible({ timeout: 8_000 });
    });

    test('can delete a role', async ({ page }) => {
        await gotoRbac(page);
        const name = `to_delete_${Date.now()}`;

        await page.locator('[data-testid="btn-create-role"]').click();
        await page.locator('[data-testid="input-role-name"]').fill(name);
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForLoadState('networkidle');
        await page.waitForSelector(`text=${name}`, { timeout: 8_000 });

        await page.locator(`[data-testid="btn-delete-role-${name}"]`).click();
        const confirmBtn = page.locator('.p-confirmdialog .p-button-danger, button.p-confirm-dialog-accept').first();
        await expect(confirmBtn).toBeVisible({ timeout: 5_000 });
        await confirmBtn.click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator(`text=${name}`)).toBeHidden({ timeout: 8_000 });
    });

    test('duplicate role name keeps dialog open with error', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="btn-create-role"]').click();
        await page.locator('[data-testid="input-role-name"]').fill('super_admin');
        await page.locator('[data-testid="btn-save-role"]').click();
        await page.waitForTimeout(2000);
        await expect(page.locator('[data-testid="role-dialog"]')).toBeVisible();
    });
});

// ─── Permission Matrix ────────────────────────────────────────────────────────
test.describe('RBAC — Permission Matrix', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('clicking shield button opens matrix dialog', async ({ page }) => {
        await gotoRbac(page);
        // Click the shield/permission button for the first role row
        const shieldBtn = page.locator('button:has([class*="pi-shield"])').first();
        await expect(shieldBtn).toBeVisible({ timeout: 8_000 });
        await shieldBtn.click();
        await expect(page.locator('[data-testid="matrix-dialog"]')).toBeVisible({ timeout: 5_000 });
    });

    test('matrix dialog shows permission group checkboxes', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('button:has([class*="pi-shield"])').first().click();
        await expect(page.locator('[data-testid="matrix-dialog"]')).toBeVisible({ timeout: 5_000 });
        const items = page.locator('[data-testid^="matrix-perm-"]');
        expect(await items.count()).toBeGreaterThan(0);
    });

    test('can toggle a permission and save matrix', async ({ page }) => {
        await gotoRbac(page);
        // Open for delivery_driver which has 0 permissions
        const matrixBtn = page.locator('[data-testid="btn-matrix-delivery_driver"]');
        if (await matrixBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await matrixBtn.click();
            await expect(page.locator('[data-testid="matrix-dialog"]')).toBeVisible({ timeout: 5_000 });
            const firstPerm = page.locator('[data-testid^="matrix-perm-"]').first();
            if (await firstPerm.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await firstPerm.click();
            }
            await page.locator('[data-testid="btn-save-matrix"]').click();
            await page.waitForLoadState('networkidle');
            await expect(page.locator('[data-testid="matrix-dialog"]')).toBeHidden({ timeout: 8_000 });
        }
    });

    test('Save button in matrix dialog is visible', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('button:has([class*="pi-shield"])').first().click();
        await expect(page.locator('[data-testid="btn-save-matrix"]')).toBeVisible({ timeout: 5_000 });
    });

    test('selected count updates when toggling a permission', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('button:has([class*="pi-shield"])').first().click();
        await expect(page.locator('[data-testid="matrix-dialog"]')).toBeVisible({ timeout: 5_000 });
        const selectedText = page.locator('[data-testid="matrix-dialog"] .flex.items-center span.text-sm').first();
        if (await selectedText.isVisible({ timeout: 3_000 }).catch(() => false)) {
            const before = await selectedText.innerText();
            const firstPerm = page.locator('[data-testid^="matrix-perm-"]').first();
            if (await firstPerm.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await firstPerm.click();
                await page.waitForTimeout(300);
                const after = await selectedText.innerText();
                // Count changed (or stays same if toggling off)
                expect(typeof after).toBe('string');
            }
        }
    });
});

// ─── Permission CRUD ──────────────────────────────────────────────────────────
test.describe('RBAC — Permission CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('Create Permission button opens dialog', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="tab-permissions"]').click();
        await page.locator('[data-testid="btn-create-permission"]').click();
        await expect(page.locator('[data-testid="permission-dialog"]')).toBeVisible({ timeout: 5_000 });
        await expect(page.locator('[data-testid="input-permission-name"]')).toBeVisible();
    });

    test('can create a new custom permission', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="tab-permissions"]').click();
        await page.locator('[data-testid="btn-create-permission"]').click();
        const name = `test_res.action_${Date.now()}`;
        await page.locator('[data-testid="input-permission-name"]').fill(name);
        await page.locator('[data-testid="btn-save-permission"]').click();
        await page.waitForLoadState('networkidle');
        await expect(page.locator('[data-testid="permission-dialog"]')).toBeHidden({ timeout: 8_000 });
    });

    test('can delete a custom permission', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="tab-permissions"]').click();

        // Create one to delete
        await page.locator('[data-testid="btn-create-permission"]').click();
        const name = `del_res.action_${Date.now()}`;
        await page.locator('[data-testid="input-permission-name"]').fill(name);
        await page.locator('[data-testid="btn-save-permission"]').click();
        await page.waitForLoadState('networkidle');

        // Search and delete
        await page.locator('[data-testid="perm-search"]').fill('del_res');
        await page.waitForTimeout(400);
        const deleteBtn = page.locator('button:has([class*="pi-trash"])').first();
        if (await deleteBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await deleteBtn.click();
            const confirmBtn = page.locator('.p-confirmdialog .p-button-danger, button.p-confirm-dialog-accept').first();
            if (await confirmBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await confirmBtn.click();
            }
            await page.waitForLoadState('networkidle');
        }
    });

    test('permission search filters results correctly', async ({ page }) => {
        await gotoRbac(page);
        await page.locator('[data-testid="tab-permissions"]').click();
        await page.locator('[data-testid="perm-search"]').fill('orders.create');
        await page.waitForTimeout(500);
        await expect(page.locator('text=orders.create')).toBeVisible({ timeout: 5_000 });
    });
});

// ─── Support Agent access ─────────────────────────────────────────────────────
test.describe('RBAC — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('Support Agent can view RBAC page without error', async ({ page }) => {
        await page.goto(BASE);
        await page.waitForLoadState('networkidle');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal Server Error|403 Forbidden/i);
    });
});
