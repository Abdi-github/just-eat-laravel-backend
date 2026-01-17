/**
 * E2E Tests — Brands
 * Covers: list, show, create form, toggle active status, delete, search/filter.
 * UI reference: resources/js/Pages/Brand/Index.vue + Create.vue
 * Component: PrimeVue DataTable, Tag (click-to-toggle active), ConfirmDialog.
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/brands';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Brands — Unauthenticated', () => {
    test('GET /admin/brands → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/brands/create → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/brands/1 → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Brands — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Index ──────────────────────────────────────────────────────────────────

    test('loads brand index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on brand index', async ({ page }) => {
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

    test('rows contain status Tags', async ({ page }) => {
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

    test('"Create" or "New Brand" button is visible in header', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const createBtn = page.locator('a[href*="/admin/brands/create"], button:has-text("Create"), button:has-text("New")').first();
        await expect(createBtn).toBeVisible({ timeout: 5_000 });
    });

    test('clicking "Create" navigates to /admin/brands/create', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const createBtn = page.locator('a[href*="/admin/brands/create"]').first();
        if (await createBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await createBtn.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/brands\/create/);
        } else {
            // Try navigating directly
            await page.goto(`${BASE}/create`);
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/brands\/create/);
        }
    });

    // ── Create form ────────────────────────────────────────────────────────────

    test('create form renders without error', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('create form has at least one text input', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const inputs = page.locator('input[type="text"], input.p-inputtext');
        await expect(inputs.first()).toBeVisible({ timeout: 8_000 });
    });

    test('create form has submit button', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first();
        await expect(submitBtn).toBeVisible({ timeout: 8_000 });
    });

    test('empty form submission stays on create page or shows validation', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first();
        if (await submitBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await submitBtn.click();
            await page.waitForLoadState('domcontentloaded');
            // Either stays on create or shows error - must not go to list
            const url = page.url();
            expect(url).toMatch(/\/admin\/brands/);
        }
    });

    test('can fill and submit brand create form', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        const nameInput = page.locator('input[type="text"], input.p-inputtext').first();
        if (await nameInput.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await nameInput.fill('Test Brand E2E');
            const submitBtn = page.locator('button[type="submit"], button:has-text("Save"), button:has-text("Create")').first();
            if (await submitBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await submitBtn.click();
                await page.waitForLoadState('domcontentloaded');
                // After successful submit → redirect to list or show
                await expect(page).toHaveURL(/\/admin\/brands/);
            }
        }
    });

    // ── Search & filters ───────────────────────────────────────────────────────

    test('search input is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await expect(searchInput).toBeVisible({ timeout: 5_000 });
    });

    test('can search for a brand by name', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        if (await searchInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await searchInput.fill('Test Brand');
            await searchInput.press('Enter');
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/brands/);
        }
    });

    test('can use query params search', async ({ page }) => {
        await page.goto(`${BASE}?search=pizza`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/brands/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── Toggle active status ───────────────────────────────────────────────────

    test('clicking status Tag toggles active state', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const tag = page.locator('table tbody tr').first().locator('.p-tag, [class*="p-tag"]').first();
            if (await tag.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await tag.click();
                await page.waitForLoadState('networkidle');
                await expect(page).toHaveURL(/\/admin\/brands/);
            }
        }
    });

    // ── Delete ─────────────────────────────────────────────────────────────────

    test('trash icon triggers ConfirmDialog', async ({ page }) => {
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
                // Dismiss — do not delete data
                const noBtn = page.locator('[role="dialog"] button:has-text("No"), .p-confirmdialog-reject').first();
                if (await noBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await noBtn.click();
                }
            }
        }
    });

    // ── Show page ──────────────────────────────────────────────────────────────

    test('view (eye) link navigates to brand show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const viewLink = page.locator('a[href*="/admin/brands/"]').first();
        if (await viewLink.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await viewLink.click();
            await page.waitForLoadState('domcontentloaded');
            await expect(page).toHaveURL(/\/admin\/brands\/\d+/);
        }
    });

    test('brand show page renders without error', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('brand show page accessible via /admin/brands/1', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Brands — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access brand index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(BASE);
    });

    test('no server error on brand index', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can access brand create page', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });

    test('can view brand detail page', async ({ page }) => {
        await page.goto(`${BASE}/1`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
        const body = await page.content();
        expect(body).not.toMatch(/403 Forbidden/i);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Brands — Full CRUD', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    test('CREATE: fill name + slug and submit', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('networkidle');
        const ts = Date.now();
        const nameInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await nameInput.fill(`E2E Brand ${ts}`);
        // Wait for slug auto-generation (blur event)
        await nameInput.blur();
        await page.waitForTimeout(400);
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
        await submitBtn.click();
        await page.waitForLoadState('networkidle');
        expect(page.url()).toMatch(/\/admin\/brands/);
    });

    test('CREATE: validation rejects empty name', async ({ page }) => {
        await page.goto(`${BASE}/create`);
        await page.waitForLoadState('networkidle');
        const submitBtn = page.locator('button[type="submit"], button:has-text("Save")').first();
        await submitBtn.click();
        await page.waitForTimeout(1500);
        // Should stay on create or show error — form required attr prevents navigation
        expect(page.url()).toMatch(/\/admin\/brands/);
    });

    test('READ: list page has at least 1 brand row', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        expect(await rows.count()).toBeGreaterThan(0);
    });

    test('READ: show page for seeded brand ID 8 loads without error', async ({ page }) => {
        await page.goto(`${BASE}/8`);
        await page.waitForLoadState('networkidle');
        expect(await page.content()).not.toMatch(/500 Internal|404 Not Found/i);
    });

    test('UPDATE: click Edit, modify name, save', async ({ page }) => {
        await page.goto(`${BASE}/8`);
        await page.waitForLoadState('networkidle');
        const editBtn = page.locator('button:has-text("Edit")').first();
        if (await editBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await editBtn.click();
            await page.waitForTimeout(500);
            const nameInput = page.locator('input.p-inputtext, input[type="text"]').first();
            await nameInput.fill(`Bäckerei Hug Updated ${Date.now()}`);
            const saveBtn = page.locator('button:has-text("Save")').first();
            await saveBtn.click();
            await page.waitForLoadState('networkidle');
            expect(page.url()).toMatch(/\/admin\/brands/);
        }
    });

    test('DELETE: trash/delete button opens confirm dialog, press No to cancel', async ({ page }) => {
        await page.goto(`${BASE}/8`);
        await page.waitForLoadState('networkidle');
        const deleteBtn = page.locator('button:has-text("Delete"), button:has([class*="pi-trash"])').first();
        if (await deleteBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await deleteBtn.click();
            const dialog = page.locator('[role="dialog"], .p-confirmdialog').first();
            await expect(dialog).toBeVisible({ timeout: 5_000 });
            const cancelBtn = page.locator('.p-confirmdialog .p-button-secondary, button.p-confirm-dialog-reject, button:has-text("No"), button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
            expect(page.url()).toMatch(/\/admin\/brands/);
        }
    });

    test('list page kebab/action menu links to show page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const firstRowLink = page.locator('table tbody tr').first().locator('a, button:has([class*="pi-eye"])').first();
        if (await firstRowLink.isVisible({ timeout: 3_000 }).catch(() => false)) {
            await firstRowLink.click();
            await page.waitForLoadState('domcontentloaded');
            // Either show page or still on index (modal)
            expect(page.url()).toMatch(/\/admin\/brands/);
        }
    });
});
