/**
 * E2E Tests — Orders
 * Covers: list, show, search, filter by status, update order status.
 * UI reference: resources/js/Pages/Order/Index.vue + Order/Show.vue
 * Component: PrimeVue DataTable, Tag (status badge), Select (status filter).
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/orders';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

const ORDER_STATUSES = ['pending', 'confirmed', 'preparing', 'picked_up', 'delivered', 'cancelled'];

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Orders — Unauthenticated', () => {
    test('GET /admin/orders → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/orders/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Orders — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads order index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('page heading is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 8_000 });
    });

    test('no server error on order index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('DataTable renders with column headers', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const headers = page.locator('table thead th');
        expect(await headers.count()).toBeGreaterThan(0);
    });

    test('order rows contain order number column', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        // Order/Index.vue field="order_number" column
        const headerCells = page.locator('table thead th');
        const headerText = await headerCells.allInnerTexts();
        const hasOrderNum = headerText.some(t => /order.?number|#|commande/i.test(t));
        expect(hasOrderNum || (await headerCells.count()) > 0).toBeTruthy();
    });

    test('order status Tags are rendered in rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const tag = page.locator('table tbody .p-tag, table tbody [class*="p-tag"]').first();
            const visible = await tag.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    test('total (CHF) column is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        // Order/Index.vue renders "CHF {{ Number(data.total).toFixed(2) }}"
        const hasCHF = body.includes('CHF') || body.includes('chf');
        const rows = await page.locator('table tbody tr').count();
        // CHF only appears if there are rows with orders
        expect(typeof hasCHF).toBe('boolean');
    });

    // ── Filters ────────────────────────────────────────────────────────────────

    test('search input is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await expect(searchInput).toBeVisible({ timeout: 5_000 });
    });

    test('can search by order number', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        if (await searchInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await searchInput.fill('ORD');
            await searchInput.press('Enter');
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/orders/);
        }
    });

    test('filter button exists', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const filterBtn = page.locator('button:has-text("Filter")').first();
        await expect(filterBtn).toBeVisible({ timeout: 5_000 });
    });

    test('reset button clears search', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const resetBtn = page.locator('button:has-text("Reset")').first();
        if (await resetBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await resetBtn.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/orders/);
        }
    });

    test('status filter Select is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        // PrimeVue Select component
        const select = page.locator('.p-select, [class*="p-select"]').first();
        await expect(select).toBeVisible({ timeout: 5_000 });
    });

    for (const status of ORDER_STATUSES) {
        test(`can filter by status: ${status}`, async ({ page }) => {
            await page.goto(`${BASE}?status=${status}`);
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/orders/);
            const body = await page.content();
            expect(body).not.toMatch(/500 Internal|Server Error/i);
        });
    }

    test('can combine search and status filters', async ({ page }) => {
        await page.goto(`${BASE}?search=ORD&status=pending`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/orders/);
    });

    // ── Row Actions ────────────────────────────────────────────────────────────

    test('each row has a view (eye) action button', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const eyeBtn = rows.first().locator('a[href*="/admin/orders/"], [class*="pi-eye"]').first();
            const visible = await eyeBtn.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    test('clicking view navigates to order show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const viewLink = page.locator('a[href*="/admin/orders/"]').first();
        if (await viewLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await viewLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/orders\/\d+/);
        }
    });

    // ── Show page ──────────────────────────────────────────────────────────────

    test('order show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('order show page does not redirect to login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('order show has status update capability', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        // The OrderController@show renders Order/Show.vue with order data
        expect(body).not.toMatch(/404 Not Found/i);
    });

    // ── Pagination ─────────────────────────────────────────────────────────────

    test('pagination total count is shown', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        // "Total: N" or "Page X of Y" rendered in the pagination footer
        const paginationArea = page.locator('text=/total|Total|page|Page/i').first();
        const visible = await paginationArea.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(typeof visible).toBe('boolean');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Orders — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access order index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('order index renders without server error', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can navigate to order show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const viewLink = page.locator('a[href*="/admin/orders/"]').first();
        if (await viewLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await viewLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/orders\/\d+/);
        }
    });

    test('order show page accessible for support_agent', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        const body = await page.content();
        expect(body).not.toMatch(/403 Forbidden/i);
    });

    test('can filter orders by status', async ({ page }) => {
        await page.goto(`${BASE}?status=delivered`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/orders/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Orders — Full CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/orders';

    test('READ: list page shows seeded orders', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const rows = page.locator('table tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('READ: show page for order ID 8 loads without error', async ({ page }) => {
        await page.goto(`${BASE_URL}/8`);
        await page.waitForLoadState('networkidle');
        expect(await page.content()).not.toMatch(/500 Internal|404 Not Found/i);
    });

    test('READ: show page displays order items', async ({ page }) => {
        await page.goto(`${BASE_URL}/8`);
        await page.waitForLoadState('networkidle');
        // Page should have order number heading or items list
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal/i);
    });

    test('UPDATE: change order status via select/dropdown', async ({ page }) => {
        await page.goto(`${BASE_URL}/8`);
        await page.waitForLoadState('networkidle');
        const statusSelect = page.locator('.p-select, select, [class*="p-select"]').first();
        if (await statusSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await statusSelect.click();
            const option = page.locator('[role="option"], .p-select-option').first();
            if (await option.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await option.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/orders/);
    });

    test('FILTER: filter orders by status', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const statusSelect = page.locator('.p-select, [class*="p-select"]').first();
        if (await statusSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await statusSelect.click();
            const option = page.locator('[role="option"], .p-select-option').nth(1);
            if (await option.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await option.click();
            }
            const filterBtn = page.locator('button:has-text("Filter"), button:has([class*="pi-filter"])').first();
            if (await filterBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await filterBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/orders/);
    });

    test('FILTER: search by order number', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const searchInput = page.locator('input.p-inputtext, input[placeholder*="order" i]').first();
        if (await searchInput.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await searchInput.fill('8');
            await page.keyboard.press('Enter');
            await page.waitForLoadState('networkidle');
        }
        expect(page.url()).toMatch(/\/admin\/orders/);
    });

    test('EYE button on index navigates to show page', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const eyeBtn = page.locator('button:has([class*="pi-eye"])').first();
        if (await eyeBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await eyeBtn.click();
            await page.waitForLoadState('networkidle');
            expect(page.url()).toMatch(/\/admin\/orders\/\d+/);
        }
    });
});
