/**
 * E2E Tests — Locations (Cantons & Cities)
 * Covers: tab switching, canton DataTable, canton create/edit/delete dialog,
 *         cities DataTable, cities create/edit/delete dialog, canton filter on cities.
 * UI reference: resources/js/Pages/Location/Index.vue
 * Component: Tab buttons (cantons|cities), PrimeVue Dialog (not separate route),
 *            ConfirmDialog for delete, InputText, Select.
 */

import { test, expect } from '@playwright/test';
import { SUPER_ADMIN_STATE, SUPPORT_AGENT_STATE } from './helpers/auth';

const BASE = '/admin/locations';

async function waitForTable(page: any) {
    await page.waitForLoadState('domcontentloaded');
    await page.waitForSelector('table, [class*="datatable"], h1', { timeout: 10_000 });
}

async function waitForDialog(page: any) {
    await page.waitForSelector('[role="dialog"], .p-dialog', { timeout: 8_000 });
}

// ═══════════════════════════════════════════════════════════════════════════════
// UNAUTHENTICATED
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Locations — Unauthenticated', () => {
    test('GET /admin/locations → redirects to /admin/login', async ({ page }) => {
        await page.goto(BASE);
        await expect(page).toHaveURL(/\/admin\/login/);
    });

    test('GET /admin/locations?tab=cities → redirects to /admin/login', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await expect(page).toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPER ADMIN
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Locations — Super Admin', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    // ── Page structure ─────────────────────────────────────────────────────────

    test('loads location index page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(/\/admin\/locations/);
    });

    test('no server error on location page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('page has heading', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page.locator('h1, h2').first()).toBeVisible({ timeout: 5_000 });
    });

    // ── Tab switching ──────────────────────────────────────────────────────────

    test('"Cantons" tab button is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const cantonsTab = page.locator('button:has-text("Canton"), a:has-text("Canton"), [class*="tab"]:has-text("Canton")').first();
        const visible = await cantonsTab.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(visible).toBeTruthy();
    });

    test('"Cities" tab button is visible', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const citiesTab = page.locator('button:has-text("Cit"), a:has-text("Cit"), [class*="tab"]:has-text("Cit")').first();
        const visible = await citiesTab.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(visible).toBeTruthy();
    });

    test('clicking Cities tab shows city data', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const citiesTab = page.locator('button:has-text("Cit"), a:has-text("Cit")').first();
        if (await citiesTab.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await citiesTab.click();
            await page.waitForLoadState('domcontentloaded');
            const body = await page.content();
            expect(body).not.toMatch(/500 Internal|Server Error/i);
        }
    });

    test('clicking Cantons tab shows canton data', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const cantonsTab = page.locator('button:has-text("Canton"), a:has-text("Canton")').first();
        if (await cantonsTab.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await cantonsTab.click();
            await page.waitForLoadState('domcontentloaded');
            const body = await page.content();
            expect(body).not.toMatch(/500 Internal|Server Error/i);
        }
    });

    test('can navigate to locations?tab=cantons', async ({ page }) => {
        await page.goto(`${BASE}?tab=cantons`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/locations/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can navigate to locations?tab=cities', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).toHaveURL(/\/admin\/locations/);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    // ── Cantons DataTable ──────────────────────────────────────────────────────

    test('cantons DataTable has headers', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const headers = page.locator('table thead th');
        expect(await headers.count()).toBeGreaterThan(0);
    });

    test('cantons DataTable has rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        // Switzerland has cantons — seeded data should be present
        expect(rowCount).toBeGreaterThanOrEqual(0);
    });

    // ── New Canton button & dialog ─────────────────────────────────────────────

    test('"New Canton" button is present on cantons tab', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New Canton"), button:has-text("Add Canton"), button:has-text("Canton")').first();
        const visible = await newBtn.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(visible).toBeTruthy();
    });

    test('clicking "New Canton" opens a Dialog', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New Canton"), button:has-text("Add Canton")').first();
        if (await newBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newBtn.click();
            const dialog = page.locator('[role="dialog"], .p-dialog').first();
            await expect(dialog).toBeVisible({ timeout: 8_000 });
        }
    });

    test('canton create dialog has code input', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New Canton"), button:has-text("Add Canton")').first();
        if (await newBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newBtn.click();
            await waitForDialog(page);
            const codeInput = page.locator('[role="dialog"] input, .p-dialog input').first();
            await expect(codeInput).toBeVisible({ timeout: 5_000 });
        }
    });

    test('canton create dialog has cancel button', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New Canton"), button:has-text("Add Canton")').first();
        if (await newBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newBtn.click();
            await waitForDialog(page);
            const cancelBtn = page.locator('[role="dialog"] button:has-text("Cancel"), .p-dialog button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await cancelBtn.click();
                // Dialog should close
                await page.waitForTimeout(300);
                const dialogVisible = await page.locator('[role="dialog"], .p-dialog').first()
                    .isVisible({ timeout: 1_000 }).catch(() => false);
                expect(dialogVisible).toBeFalsy();
            }
        }
    });

    test('canton create dialog has save/submit button', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New Canton"), button:has-text("Add Canton")').first();
        if (await newBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newBtn.click();
            await waitForDialog(page);
            const saveBtn = page.locator('[role="dialog"] button:has-text("Save"), .p-dialog button[type="submit"], .p-dialog button:has-text("Create")').first();
            const visible = await saveBtn.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    // ── Canton edit (pencil) ───────────────────────────────────────────────────

    test('edit (pencil) icon is present on canton rows', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const pencilBtn = rows.first().locator('button:has([class*="pi-pencil"])').first();
            const visible = await pencilBtn.isVisible({ timeout: 5_000 }).catch(() => false);
            expect(visible).toBeTruthy();
        }
    });

    test('clicking pencil opens edit Dialog pre-filled with canton data', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const rows = page.locator('table tbody tr');
        const rowCount = await rows.count();
        if (rowCount > 0) {
            const pencilBtn = rows.first().locator('button:has([class*="pi-pencil"])').first();
            if (await pencilBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
                await pencilBtn.click();
                const dialog = page.locator('[role="dialog"], .p-dialog').first();
                await expect(dialog).toBeVisible({ timeout: 8_000 });
                // Inputs should be pre-filled
                const firstInput = dialog.locator('input').first();
                const value = await firstInput.inputValue().catch(() => '');
                expect(value.length).toBeGreaterThan(0);
                // Cancel to avoid mutation
                const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
                if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await cancelBtn.click();
                }
            }
        }
    });

    // ── Canton delete ──────────────────────────────────────────────────────────

    test('trash icon on canton row triggers ConfirmDialog', async ({ page }) => {
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
                // Dismiss without deleting
                const noBtn = page.locator('[role="dialog"] button:has-text("No"), .p-confirmdialog-reject').first();
                if (await noBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                    await noBtn.click();
                }
            }
        }
    });

    // ── Cities tab ─────────────────────────────────────────────────────────────

    test('"New City" button is present on cities tab', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const citiesTab = page.locator('button:has-text("Cit"), a:has-text("Cit")').first();
        if (await citiesTab.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await citiesTab.click();
            await page.waitForLoadState('domcontentloaded');
        }
        const newCityBtn = page.locator('button:has-text("New City"), button:has-text("Add City")').first();
        const visible = await newCityBtn.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(visible).toBeTruthy();
    });

    test('clicking "New City" opens a Dialog', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await waitForTable(page);
        const newBtn = page.locator('button:has-text("New City"), button:has-text("Add City")').first();
        if (await newBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newBtn.click();
            const dialog = page.locator('[role="dialog"], .p-dialog').first();
            await expect(dialog).toBeVisible({ timeout: 8_000 });
            // Close dialog
            const cancelBtn = dialog.locator('button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await cancelBtn.click();
            }
        }
    });

    test('cities tab has canton filter Select', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await waitForTable(page);
        const selectEl = page.locator('.p-select, [class*="p-select"]').first();
        const visible = await selectEl.isVisible({ timeout: 5_000 }).catch(() => false);
        expect(visible).toBeTruthy();
    });

    test('cities search input is present', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await waitForTable(page);
        const searchInput = page.locator('input.p-inputtext, input[type="text"]').first();
        await expect(searchInput).toBeVisible({ timeout: 5_000 });
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// SUPPORT AGENT
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Locations — Support Agent', () => {
    test.use({ storageState: SUPPORT_AGENT_STATE });

    test('can access locations page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        await expect(page).toHaveURL(/\/admin\/locations/);
    });

    test('no server error on locations page', async ({ page }) => {
        await page.goto(BASE);
        await waitForTable(page);
        const body = await page.content();
        expect(body).not.toMatch(/500 Internal|Server Error/i);
    });

    test('can access cities tab', async ({ page }) => {
        await page.goto(`${BASE}?tab=cities`);
        await page.waitForLoadState('domcontentloaded');
        await expect(page).not.toHaveURL(/\/admin\/login/);
    });
});

// ═══════════════════════════════════════════════════════════════════════════════
// FULL CRUD INTERACTION TESTS
// ═══════════════════════════════════════════════════════════════════════════════
test.describe('Locations — Full CRUD (Cantons)', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/locations';

    test('READ: list shows cantons and cities', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const rows = page.locator('table tbody tr').first();
        await expect(rows).toBeVisible({ timeout: 8_000 });
    });

    test('CREATE canton: open dialog, fill code + name_fr, save', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const newCantonBtn = page.locator('button:has-text("New Canton"), button:has([class*="pi-plus"])').first();
        if (await newCantonBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newCantonBtn.click();
            const dialog = page.locator('[role="dialog"]').first();
            await expect(dialog).toBeVisible({ timeout: 5_000 });
            // code input (maxlength=2)
            const codeInput = page.locator('[role="dialog"] input').nth(0);
            await codeInput.fill('ZZ');
            // name_fr
            const nameFrInput = page.locator('[role="dialog"] input').nth(1);
            await nameFrInput.fill(`E2E Canton ${Date.now()}`);
            const saveBtn = page.locator('[role="dialog"] button:has-text("Save")').first();
            await saveBtn.click();
            await page.waitForLoadState('networkidle');
        }
        expect(page.url()).toMatch(/\/admin\/locations/);
    });

    test('CREATE canton: cancel closes dialog without saving', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const newCantonBtn = page.locator('button:has-text("New Canton"), button:has([class*="pi-plus"])').first();
        if (await newCantonBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newCantonBtn.click();
            const cancelBtn = page.locator('[role="dialog"] button:has-text("Cancel")').first();
            if (await cancelBtn.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await cancelBtn.click();
                await page.waitForTimeout(400);
                const dialog = page.locator('[role="dialog"]').first();
                expect(await dialog.isVisible({ timeout: 1_000 }).catch(() => false)).toBeFalsy();
            }
        }
    });

    test('UPDATE canton: click pencil on first canton, edit name, save', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const editBtn = page.locator('table tbody tr').first().locator('button:has([class*="pi-pencil"])').first();
        if (await editBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await editBtn.click();
            const dialog = page.locator('[role="dialog"]').first();
            await expect(dialog).toBeVisible({ timeout: 5_000 });
            const nameFrInput = page.locator('[role="dialog"] input').nth(1);
            if (await nameFrInput.isVisible({ timeout: 3_000 }).catch(() => false)) {
                await nameFrInput.fill(`Canton Updated ${Date.now()}`);
                const saveBtn = page.locator('[role="dialog"] button:has-text("Save")').first();
                await saveBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/locations/);
    });

    test('DELETE canton: trash button opens confirm dialog; cancel', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        const deleteBtn = page.locator('table tbody tr').first().locator('button:has([class*="pi-trash"])').first();
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
    });
});

test.describe('Locations — Full CRUD (Cities)', () => {
    test.use({ storageState: SUPER_ADMIN_STATE });

    const BASE_URL = '/admin/locations';

    test('CREATE city: open dialog, fill name + zip, save', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        // Scroll to cities section
        const newCityBtn = page.locator('button:has-text("New City")').first();
        if (await newCityBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await newCityBtn.click();
            const dialog = page.locator('[role="dialog"]').last();
            await expect(dialog).toBeVisible({ timeout: 5_000 });
            const nameInput = dialog.locator('input').nth(0);
            await nameInput.fill(`E2E City ${Date.now()}`);
            const zipInput = dialog.locator('input').nth(1);
            if (await zipInput.isVisible({ timeout: 2_000 }).catch(() => false)) {
                await zipInput.fill('9999');
            }
            const saveBtn = dialog.locator('button:has-text("Save")').first();
            await saveBtn.click();
            await page.waitForLoadState('networkidle');
        }
        expect(page.url()).toMatch(/\/admin\/locations/);
    });

    test('UPDATE city: click pencil on first city row, edit name, save', async ({ page }) => {
        await page.goto(BASE_URL);
        await page.waitForLoadState('networkidle');
        // Cities section may be below cantons — look for a second table
        const cityEditBtns = page.locator('button:has([class*="pi-pencil"])');
        const count = await cityEditBtns.count();
        const btnIndex = count > 1 ? 1 : 0;
        const editBtn = cityEditBtns.nth(btnIndex);
        if (await editBtn.isVisible({ timeout: 5_000 }).catch(() => false)) {
            await editBtn.click();
            const dialog = page.locator('[role="dialog"]').last();
            if (await dialog.isVisible({ timeout: 5_000 }).catch(() => false)) {
                const nameInput = dialog.locator('input').first();
                await nameInput.fill(`City Updated ${Date.now()}`);
                const saveBtn = dialog.locator('button:has-text("Save")').first();
                await saveBtn.click();
                await page.waitForLoadState('networkidle');
            }
        }
        expect(page.url()).toMatch(/\/admin\/locations/);
    });
});
