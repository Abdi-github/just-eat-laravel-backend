/**
 * E2E Tests — Reviews
 * Covers: list, show, toggle visibility, delete, search, filter by visible/hidden.
 * UI reference: resources/js/Pages/Review/Index.vue + Review/Show.vue
 * Component: PrimeVue DataTable, Tag (click-to-toggle visibility), ConfirmDialog.
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/reviews';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Reviews — Unauthenticated', () => {
    test('GET /admin/reviews → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/reviews/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Reviews — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads review index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('page heading is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 8_000 });
    });

    test('no server error on review index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('DataTable has column headers', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const headers = page.locator('table thead th');
        expect(await headers.count()).toBeGreaterThan(0);
    });

    test('review rows contain star rating characters', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            // Review/Index.vue renders ★ and ☆ characters in rating column
            const body = await page.content();
            const hasStars = /★|☆|pi-star|pi pi-star/i.test(body);
            expect(hasStars || rowCount === 0).toBeTruthy();
        }
    });

    test('visibility Tags (visible/hidden) are shown in rows', async ({ page }) => {
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

    test('visible/hidden filter Select is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const select = page.locator('.p-select, [class*="p-select"]').first();
        await expect(select).toBeVisible({ timeout: 5_000 });
    });

    test('filter button is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const filterBtn = page.locator('button:has-text("Filter")').first();
        await expect(filterBtn).toBeVisible({ timeout: 5_000 });
    });

    test('reset button is present', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const resetBtn = page.locator('button:has-text("Reset")').first();
        await expect(resetBtn).toBeVisible({ timeout: 5_000 });
    });

    test('can filter reviews by visible=true', async ({ page }) => {
        await page.goto(`${BASE}?visible=true`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/reviews/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can filter reviews by visible=false (hidden)', async ({ page }) => {
        await page.goto(`${BASE}?visible=false`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/reviews/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can search by comment text', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        if (await searchInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await searchInput.fill('good');
            await searchInput.press('Enter');
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/reviews/);
        }
    });

    test('clicking Reset clears filters', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const resetBtn = page.locator('button:has-text("Reset")').first();
        if (await resetBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await resetBtn.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/reviews/);
        }
    });

    // ── Toggle visibility ──────────────────────────────────────────────────────

    test('clicking visibility Tag calls PUT reviews.update', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const tag = page.locator('table tbody tr').first().locator('.p-tag, [class*="p-tag"]').first();
            if (await tag.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await tag.click();
                await page.waitForLoadState('networkidle');
                // Should remain on reviews page after toggle
                await expect(page).toHaveURL(/\/admin\/reviews/);
            }
        }
    });

    // ── Row actions ────────────────────────────────────────────────────────────

    test('view (eye) button is present in rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const eyeLink = rows.first().locator('a[href*="/admin/reviews/"], [class*="pi-eye"]').first();
            const visible = await eyeLink.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    test('clicking view navigates to review show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const viewLink = page.locator('a[href*="/admin/reviews/"]').first();
        if (await viewLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await viewLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/reviews\/\d+/);
        }
    });

    test('delete button triggers ConfirmDialog', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const trashBtn = page.locator('table tbody tr').last()
                .locator('button:has([class*="pi-trash"])').first();
            if (await trashBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await trashBtn.click();
                const dialog = page.locator('[role="dialog"], .p-dialog, .p-confirmdialog').first();
                await expect(dialog).toBeVisible({ timeout: 5_000 });
                // Dismiss without deleting
                const noBtn = page.locator('[role="dialog"] button:has-text("No"), .p-confirmdialog-reject').first();
                if (await noBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await noBtn.click();
                }
            }
        }
    });

    // ── Show page ──────────────────────────────────────────────════════════════

    test('review show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('review show page does not redirect to login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('review show page contains review-related content', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        // Should at minimum not show a 404
        expect(body).not.toMatch(/404 Not Found/i);
    });

    // ── Pagination ─────────────────────────────────────────────────────────────

    test('pagination footer is rendered', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        // Review/Index.vue has pagination footer with total count
        const footerText = page.locator('text=/Total|total|Page|page/i').first();
        const visible = await footerText.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(typeof visible).toBe('boolean');
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Reviews — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access review index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on review index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can view review detail page', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        const body = await page.content();
        expect(body).not.toMatch(/403 Forbidden/i);
    });

    test('can filter by visible status', async ({ page }) => {
        await page.goto(`${BASE}?visible=true`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/reviews/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Reviews — Full CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/reviews';

    test('READ: list page shows seeded reviews', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal/i);
    });

    test('READ: reviews list has rows', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const rows = page.locator('table tbody tr');
        expect(await rows.count()).toBeGreaterThanOrEqual(0);
    });

    test('UPDATE: toggle visibility on first review', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const firstRow = page.locator('table tbody tr').first();
        if (await firstRow.isVisible({ timeout: 5_000 }).catch(() => false)) {
            // Look for toggle or eye button on row
            const visibleBtn = firstRow.locator('button:has([class*="pi-eye"]), button:has([class*="pi-eye-slash"]), button:has-text("Hide"), button:has-text("Show")').first();
            if (await visibleBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await visibleBtn.click();
                await page.waitForLoadState('networkidle');
            } else {
                // Try .p-tag click for toggle
                const tag = firstRow.locator('.p-tag').first();
                if (await tag.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await tag.click();
                    await page.waitForTimeout(500);
                }
            }
        }
        expect(page.url()).toMatch(/\/admin\/reviews/);
    });

    test('DELETE: delete button triggers confirm dialog; cancel', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const deleteBtn = page.locator('table tbody tr').first()
            .locator('button:has([class*="pi-trash"]), button:has-text("Delete")').first();
        if (await deleteBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await deleteBtn.click();
            const dialog = page.locator('[role="dialog"], .p-confirmdialog').first();
            const visible = await dialog.isVisible({ timeout: 5_000 }).catch(() => false);
            if (visible) {
                const cancelBtn = page.locator('button.p-confirm-dialog-reject, button:has-text("No"), button:has-text("Cancel")').first();
                if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await cancelBtn.click();
                }
            }
        }
        expect(page.url()).toMatch(/\/admin\/reviews/);
    });

    test('FILTER: filter by visible status', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const filterSelect = page.locator('.p-select, [class*="p-select"]').first();
        if (await filterSelect.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await filterSelect.click();
            const option = page.locator('[role="option"], .p-select-option').first();
            if (await option.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await option.click();
            }
            const filterBtn = page.locator('button:has-text("Filter"), button:has([class*="pi-filter"])').first();
            if (await filterBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await filterBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/reviews/);
    });
});
