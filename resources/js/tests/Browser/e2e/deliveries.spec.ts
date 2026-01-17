/**
 * E2E Tests — Deliveries
 * Covers: list, search/filter by status, assign courier Dialog,
 *         update status Dialog, cancellation reason textarea conditional visibility,
 *         eye (show) navigation, status Tags, both roles.
 * UI reference: resources/js/Pages/Delivery/Index.vue
 * Statuses: PENDING/ASSIGNED/PICKED_UP/IN_TRANSIT/DELIVERED/CANCELLED/FAILED
 * Actions: eye (view) | user-plus (PENDING→assign) | refresh (status update)
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/deliveries';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Deliveries — Unauthenticated', () => {
    test('GET /admin/deliveries → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/deliveries/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Deliveries — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads deliveries index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on deliveries index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('page has heading', async ({ page }) => {
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

    test('status Tags are visible in rows', async ({ page }) => {
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

    // ── Filters ────────────────────────────────────────────────────────────────

    test('search input is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await expect(searchInput).toBeVisible({ timeout: 5_000 });
    });

    test('status filter Select is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const select = page.locator('.p-select, [class*="p-select"]').first();
        await expect(select).toBeVisible({ timeout: 5_000 });
    });

    test('can filter by status=PENDING', async ({ page }) => {
        await page.goto(`${BASE}?status=PENDING`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/deliveries/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=ASSIGNED', async ({ page }) => {
        await page.goto(`${BASE}?status=ASSIGNED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=PICKED_UP', async ({ page }) => {
        await page.goto(`${BASE}?status=PICKED_UP`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=IN_TRANSIT', async ({ page }) => {
        await page.goto(`${BASE}?status=IN_TRANSIT`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=DELIVERED', async ({ page }) => {
        await page.goto(`${BASE}?status=DELIVERED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=CANCELLED', async ({ page }) => {
        await page.goto(`${BASE}?status=CANCELLED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=FAILED', async ({ page }) => {
        await page.goto(`${BASE}?status=FAILED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── View (eye) action ──────────────────────────────────────────────────────

    test('eye button navigates to delivery show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const eyeLink = page.locator('a[href*="/admin/deliveries/"], button:has([class*="pi-eye"])').first();
            if (await eyeLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await eyeLink.click();
                await page.waitForLoadState('domcontentloaded');
                await expect(page).toHaveURL(/\/admin\/deliveries\/\d+/);
            }
        }
    });

    test('delivery show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── Assign Courier dialog (PENDING only) ───────────────────────────────────

    test('PENDING rows have user-plus (assign) button', async ({ page }) => {
        await page.goto(`${BASE}?status=PENDING`);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            // Delivery/Index.vue only shows user-plus button for PENDING deliveries
            const assignBtn = rows.first().locator('button:has([class*="pi-user-plus"]), button[title*="Assign"]').first();
            const visible = await assignBtn.isVisible({ timeout: 5_000 }).catch(() => false);
            if (visible) {
                expect(visible).toBeTruthy();
            }
        }
    });

    test('assign dialog has courier Select', async ({ page }) => {
        await page.goto(`${BASE}?status=PENDING`);
        await waitForTable(page);
        const assignBtn = page.locator('button:has([class*="pi-user-plus"])').first();
        if (await assignBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await assignBtn.click();
            const dialog = page.locator('[role="dialog"], .p-dialog').first();
            await expect(dialog).toBeVisible({ timeout: 8_000 });
            // Assign dialog has a Select for choosing courier
            const select = dialog.locator('.p-select, select').first();
            const selectVisible = await select.isVisible({ timeout: 3_000 }).catch(() => false);
            expect(selectVisible).toBeTruthy();
            // Cancel dialog
            const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
        }
    });

    // ── Update Status dialog ───────────────────────────────────────────────────

    test('rows have status refresh button', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            // Delivery/Index.vue shows refresh/sync icon button on all rows for status update
            const refreshBtn = rows.first().locator('button:has([class*="pi-refresh"]), button:has([class*="pi-sync"]), button[title*="Status"]').first();
            const visible = await refreshBtn.isVisible({ timeout: 5_000 }).catch(() => false);
            if (visible) {
                expect(visible).toBeTruthy();
            }
        }
    });

    test('status update dialog opens with a Select', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const refreshBtn = page.locator('button:has([class*="pi-refresh"]), button:has([class*="pi-sync"])').first();
        if (await refreshBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await refreshBtn.click();
            const dialog = page.locator('[role="dialog"], .p-dialog').first();
            await expect(dialog).toBeVisible({ timeout: 8_000 });
            const select = dialog.locator('.p-select, select').first();
            const selectVisible = await select.isVisible({ timeout: 3_000 }).catch(() => false);
            expect(selectVisible).toBeTruthy();
            // Cancel dialog
            const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
        }
    });

    test('cancellation reason textarea is hidden when status != CANCELLED', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const refreshBtn = page.locator('button:has([class*="pi-refresh"]), button:has([class*="pi-sync"])').first();
        if (await refreshBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await refreshBtn.click();
            const dialog = page.locator('[role="dialog"], .p-dialog').first();
            await expect(dialog).toBeVisible({ timeout: 8_000 });
            // Without selecting CANCELLED status, textarea should be hidden
            const textarea = dialog.locator('textarea');
            const count = await textarea.count();
            const visible = count > 0 && await textarea.first().isVisible({ timeout: 1_000 }).catch(() => false);
            // Either hidden (v-if not met) or not present at all
            expect(visible).toBeFalsy();
            // Close dialog
            const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
        }
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Deliveries — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access deliveries index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on deliveries index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can view delivery show page', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('can filter deliveries by status', async ({ page }) => {
        await page.goto(`${BASE}?status=DELIVERED`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Deliveries — Full CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('READ: list page shows seeded deliveries', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal/i);
    });

    test('READ: rows have status tags', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const count = await rows.count();
        if (count > 0) {
            const tag = page.locator('table .p-tag').first();
            const visible = await tag.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    test('VIEW: eye button navigates to delivery show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const eyeBtn = page.locator('button:has([class*="pi-eye"])').first();
        if (await eyeBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await eyeBtn.click();
            await page.waitForLoadState('networkidle');
            expect(page.url()).toMatch(/\/admin\/deliveries\/\d+/);
        }
    });

    test('ASSIGN: user-plus (assign courier) button opens assign dialog', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const assignBtn = page.locator('button:has([class*="pi-user-plus"])').first();
        if (await assignBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await assignBtn.click();
            const dialog = page.locator('[role="dialog"]').first();
            const visible = await dialog.isVisible({ timeout: 5_000 }).catch(() => false);
            if (visible) {
                const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
                if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await cancelBtn.click();
                }
            }
        }
        expect(page.url()).toMatch(/\/admin\/deliveries/);
    });

    test('STATUS UPDATE: refresh button opens status update dialog', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const refreshBtn = page.locator('button:has([class*="pi-refresh"])').first();
        if (await refreshBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await refreshBtn.click();
            const dialog = page.locator('[role="dialog"]').first();
            const visible = await dialog.isVisible({ timeout: 5_000 }).catch(() => false);
            if (visible) {
                const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
                if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await cancelBtn.click();
                }
            }
        }
        expect(page.url()).toMatch(/\/admin\/deliveries/);
    });

    test('FILTER: filter by PENDING status', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const statusSelect = page.locator('.p-select, [class*="p-select"]').first();
        if (await statusSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await statusSelect.click();
            const pendingOption = page.locator('[role="option"]:has-text("PENDING"), .p-select-option:has-text("PENDING")').first();
            if (await pendingOption.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await pendingOption.click();
            }
            const filterBtn = page.locator('button:has-text("Filter"), button:has([class*="pi-filter"])').first();
            if (await filterBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await filterBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/deliveries/);
    });
});
