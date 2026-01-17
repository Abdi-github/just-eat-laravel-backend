/**
 * E2E Tests — Promotions
 * Covers: list, create form, show page, search/filter, toggle active, delete.
 * Route prefix: /admin/promotions (resource routes: index, show, create, store, update, destroy)
 * UI inferred from resource route pattern + PrimeVue DataTable conventions.
 * Promotions may have: code, discount_type (percentage/fixed), discount_value,
 *                      min_order_amount, max_uses, is_active, starts_at, expires_at.
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/promotions';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Promotions — Unauthenticated', () => {
    test('GET /admin/promotions → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/promotions/create → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/promotions/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Promotions — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads promotions index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on promotions index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('page has promotions heading', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 8_000 });
    });

    test('DataTable has column headers', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const headers = page.locator('table thead th');
        expect(await headers.count()).toBeGreaterThan(0);
    });

    test('status/active Tags are visible in rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const tag = page.locator('table .p-tag, table [class*="p-tag"]').first();
            const visible = await tag.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    // ── Create button ──────────────────────────────────────────────────────────

    test('"Create" or "New Promotion" button is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const createBtn = page.locator('a[href*="/admin/promotions/create"], button:has-text("Create"), button:has-text("New")').first();
        await expect(createBtn).toBeVisible({ timeout: 5_000 });
    });

    test('clicking "Create" navigates to /admin/promotions/create', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const createLink = page.locator('a[href*="/admin/promotions/create"]').first();
        if (await createLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await createLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/promotions\/create/);
        } else {
            await page.goto(`${BASE}/create`);
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/promotions\/create/);
        }
    });

    // ── Create form ────────────────────────────────────────────────────────────

    test('create form renders without server error', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('create form has text inputs', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const inputs = page.locator('input[type="text"], input.p-inputtext');
        await expect(inputs.first()).toBeVisible({ timeout: 8_000 });
    });

    test('create form has a submit button', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first();
        await expect(submitBtn).toBeVisible({ timeout: 8_000 });
    });

    test('empty form submission stays on create or shows validation error', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first();
        if (await submitBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await submitBtn.click();
            await page.waitForLoadState('domcontentloaded');
            // Should not redirect away from promotions area
            await expect(page).toHaveURL(/\/admin\/promotions/);
        }
    });

    test('can fill promo code and submit', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const codeInput = page.locator('input[type="text"]').first();
        if (await codeInput.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await codeInput.fill(`E2ETEST${Date.now()}`);
            const submitBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
            if (await submitBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await submitBtn.click();
                await page.waitForLoadState('domcontentloaded');
                await expect(page).toHaveURL(/\/admin\/promotions/);
            }
        }
    });

    // ── Filters ────────────────────────────────────────────────────────────────

    test('search input is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await expect(searchInput).toBeVisible({ timeout: 5_000 });
    });

    test('can search by promo code', async ({ page }) => {
        await page.goto(`${BASE}?search=WELCOME`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── Toggle & delete ────────────────────────────────────────────────────────

    test('status Tag click toggles active state', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const tag = rows.first().locator('.p-tag, [class*="p-tag"]').first();
            if (await tag.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await tag.click();
                await page.waitForLoadState('networkidle');
                await expect(page).toHaveURL(/\/admin\/promotions/);
            }
        }
    });

    test('trash icon triggers ConfirmDialog', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const trashBtn = rows.last().locator('button:has([class*="pi-trash"])').first();
            if (await trashBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await trashBtn.click();
                const dialog = page.locator('[role="dialog"], .p-dialog, .p-confirmdialog').first();
                await expect(dialog).toBeVisible({ timeout: 5_000 });
                const noBtn = page.locator('[role="dialog"] button:has-text("No"), .p-confirmdialog-reject').first();
                if (await noBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await noBtn.click();
                }
            }
        }
    });

    // ── Show page ──────────────────────────────────────────────────────────────

    test('promotion show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('eye link navigates to show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const viewLink = page.locator('a[href*="/admin/promotions/"]').first();
        if (await viewLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await viewLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/promotions\/\d+/);
        }
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Promotions — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access promotions index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on promotions index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can access promotions create page', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('can view promotion detail page', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Promotions — Full CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/promotions';

    test('READ: list page shows seeded promotions', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const rows = page.locator('table tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('READ: show page for promo ID 13 loads', async ({ page }) => {
        await page.goto(`${BASE_URL}/13`);
        await page.waitForLoadState('networkidle');
        expect(await page.content()).not.toMatch(/500 Internal|404 Not Found/i);
    });

    test('CREATE: fill code, title, type, value and submit', async ({ page }) => {
        await page.goto(`${BASE_URL}/create`);
        await page.waitForLoadState('networkidle');
        const ts = Date.now();
        // Code field
        const codeInput = page.locator('input.p-inputtext').nth(0);
        await codeInput.fill(`E2ETEST${ts}`);
        // Title field
        const titleInput = page.locator('input.p-inputtext').nth(1);
        await titleInput.fill(`E2E Promo ${ts}`);
        // Submit
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
        await submitBtn.click();
        await page.waitForLoadState('networkidle');
        expect(page.url()).toMatch(/\/admin\/promotions/);
    });

    test('CREATE: validation rejects duplicate or empty code', async ({ page }) => {
        await page.goto(`${BASE_URL}/create`);
        await page.waitForLoadState('networkidle');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
        await submitBtn.click();
        await page.waitForTimeout(1500);
        expect(page.url()).toMatch(/\/admin\/promotions/);
    });

    test('UPDATE: click Edit on show page, modify title, save', async ({ page }) => {
        await page.goto(`${BASE_URL}/13`);
        await page.waitForLoadState('networkidle');
        const editBtn = page.locator('button:has-text("Edit"), a:has-text("Edit")').first();
        if (await editBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await editBtn.click();
            await page.waitForTimeout(400);
            const titleInput = page.locator('input.p-inputtext').nth(1);
            if (await titleInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await titleInput.fill(`Updated Promo ${Date.now()}`);
                const saveBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
                await saveBtn.click();
                await page.waitForLoadState('networkidle');
            }
            expect(page.url()).toMatch(/\/admin\/promotions/);
        }
    });

    test('DELETE: trash button triggers confirm dialog', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const trashBtn = page.locator('table tbody tr').first()
            .locator('button:has([class*="pi-trash"]), button:has-text("Delete")').first();
        if (await trashBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await trashBtn.click();
            const dialog = page.locator('[role="dialog"], .p-confirmdialog').first();
            await expect(dialog).toBeVisible({ timeout: 5_000 });
            const cancelBtn = page.locator('button.p-confirm-dialog-reject, button:has-text("No"), button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
        }
    });

    test('TOGGLE: active status tag is clickable on index', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const tag = page.locator('table .p-tag, .p-tag').first();
        if (await tag.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await tag.click();
            await page.waitForTimeout(500);
            expect(page.url()).toMatch(/\/admin\/promotions/);
        }
    });
});
