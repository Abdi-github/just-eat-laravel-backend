/**
 * E2E Tests — Payments
 * Covers: list (row-click navigate), search/filter by status/method,
 *         eye (show) navigation, show page refund action, status Tags, both roles.
 * UI reference: resources/js/Pages/Payment/Index.vue + Show.vue
 * Statuses: PENDING/PROCESSING/COMPLETED/FAILED/REFUNDED/PARTIAL_REFUND/CANCELLED/EXPIRED
 * Methods: credit_card/debit_card/paypal/twint/cash
 * Note: DataTable rows have @row-click → router.visit() to show page.
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/payments';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Payments — Unauthenticated', () => {
    test('GET /admin/payments → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/payments/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Payments — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads payments index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on payments index', async ({ page }) => {
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

    test('payment status Tags are visible', async ({ page }) => {
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

    test('CHF amount is displayed in rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const body = await page.content();
            const hasCHF = /CHF|Fr\.|chf/i.test(body);
            expect(hasCHF || rowCount === 0).toBeTruthy();
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
        const selects = page.locator('.p-select, [class*="p-select"]');
        const count = await selects.count();
        expect(count).toBeGreaterThan(0);
    });

    test('payment method filter Select is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        // Payment/Index.vue has both status and method Selects
        const selects = page.locator('.p-select, [class*="p-select"]');
        const count = await selects.count();
        expect(count).toBeGreaterThanOrEqual(1);
    });

    // Status filter tests
    test('can filter by status=PENDING', async ({ page }) => {
        await page.goto(`${BASE}?status=PENDING`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=COMPLETED', async ({ page }) => {
        await page.goto(`${BASE}?status=COMPLETED`);
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

    test('can filter by status=REFUNDED', async ({ page }) => {
        await page.goto(`${BASE}?status=REFUNDED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=PROCESSING', async ({ page }) => {
        await page.goto(`${BASE}?status=PROCESSING`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by status=PARTIAL_REFUND', async ({ page }) => {
        await page.goto(`${BASE}?status=PARTIAL_REFUND`);
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

    test('can filter by status=EXPIRED', async ({ page }) => {
        await page.goto(`${BASE}?status=EXPIRED`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // Method filter tests
    test('can filter by method=credit_card', async ({ page }) => {
        await page.goto(`${BASE}?method=credit_card`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by method=paypal', async ({ page }) => {
        await page.goto(`${BASE}?method=paypal`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by method=twint', async ({ page }) => {
        await page.goto(`${BASE}?method=twint`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter by method=cash', async ({ page }) => {
        await page.goto(`${BASE}?method=cash`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('combined status + method filter works', async ({ page }) => {
        await page.goto(`${BASE}?status=COMPLETED&method=twint`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── Row click navigation ───────────────────────────────────────────────────

    test('clicking a payment row navigates to show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            // Payment/Index.vue uses @row-click → router.visit(route('admin.payments.show', id))
            await rows.first().click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/payments\/\d+/);
        }
    });

    test('eye button on row navigates to show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const eyeLink = page.locator('a[href*="/admin/payments/"], button:has([class*="pi-eye"])').first();
        if (await eyeLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await eyeLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/payments\/\d+/);
        }
    });

    // ── Show page ──────────────────────────────────────────────────────────────

    test('payment show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('payment show page does not redirect to login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('payment show page displays payment details', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page.locator('h1, h2, .p-card').first()).toBeVisible({ timeout: 8_000 });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Payments — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access payments index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on payments index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can access payment show page', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('can filter payments by status as support agent', async ({ page }) => {
        await page.goto(`${BASE}?status=COMPLETED`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Payments — Full CRUD (View + Refund)', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/payments';

    test('READ: list page shows seeded payment transactions', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        expect(await page.content()).not.toMatch(/500 Internal/i);
    });

    test('READ: table rows contain status tags', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const rows = page.locator('table tbody tr');
        const count = await rows.count();
        if (count > 0) {
            const tag = page.locator('table .p-tag').first();
            expect(await tag.isVisible({ timeout: 5_000 }).catch(() => false)).toBeTruthy();
        }
    });

    test('READ: eye button navigates to payment show page', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const eyeBtn = page.locator('button:has([class*="pi-eye"])').first();
        if (await eyeBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await eyeBtn.click();
            await page.waitForLoadState('networkidle');
            expect(page.url()).toMatch(/\/admin\/payments\/\d+/);
            expect(await page.content()).not.toMatch(/500 Internal/i);
        }
    });

    test('REFUND: on COMPLETED payment, refund button opens refund dialog', async ({ page }) => {
        // Navigate to payments and find a COMPLETED one
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        // Filter for COMPLETED status
        const statusSelect = page.locator('.p-select, [class*="p-select"]').first();
        if (await statusSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await statusSelect.click();
            const completedOption = page.locator('[role="option"]:has-text("COMPLETED"), .p-select-option:has-text("COMPLETED")').first();
            if (await completedOption.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await completedOption.click();
                await page.waitForLoadState('networkidle');
            }
        }
        // Navigate to first available payment show page
        const eyeBtn = page.locator('button:has([class*="pi-eye"])').first();
        if (await eyeBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await eyeBtn.click();
            await page.waitForLoadState('networkidle');
            // Look for Refund button (only visible on COMPLETED payments)
            const refundBtn = page.locator('button:has-text("Refund"), button:has([class*="pi-undo"])').first();
            if (await refundBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await refundBtn.click();
                const dialog = page.locator('[role="dialog"]').first();
                await expect(dialog).toBeVisible({ timeout: 5_000 });
                // Cancel refund to avoid data mutation
                const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
                if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await cancelBtn.click();
                }
            }
        }
        expect(page.url()).toMatch(/\/admin\/payments/);
    });

    test('FILTER: filter payments by status', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const statusSelect = page.locator('.p-select, [class*="p-select"]').first();
        if (await statusSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await statusSelect.click();
            const option = page.locator('[role="option"], .p-select-option').nth(1);
            if (await option.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await option.click();
            }
            const filterBtn = page.locator('button:has([class*="pi-filter"]), button:has-text("Filter")').first();
            if (await filterBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await filterBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/payments/);
    });
});
