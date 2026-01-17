import { Page, expect } from '@playwright/test';
import * as path from 'path';

// ── Storage state paths ────────────────────────────────────────────────────────
export const SUPER_ADMIN_STATE = path.join(process.cwd(), '.auth/super-admin.json');
export const SUPPORT_AGENT_STATE = path.join(process.cwd(), '.auth/support-agent.json');

// ── Credentials ────────────────────────────────────────────────────────────────
export const CREDENTIALS = {
    superAdmin: {
        email:    process.env.SUPER_ADMIN_EMAIL   || 'admin@just-eat-clone.ch',
        password: process.env.SUPER_ADMIN_PASSWORD || 'password',
        role:     'super_admin',
    },
    supportAgent: {
        email:    process.env.SUPPORT_EMAIL    || 'support@just-eat-clone.ch',
        password: process.env.SUPPORT_PASSWORD || 'password',
        role:     'support_agent',
    },
};

// ── Login helper (for tests that need fresh login without storage state) ──────
export async function loginAs(
    page: Page,
    role: 'superAdmin' | 'supportAgent',
): Promise<void> {
    const creds = CREDENTIALS[role];
    await page.goto('/admin/login');
    await page.waitForSelector('form');
    await page.locator('input[type="email"], input[name="email"]').first().fill(creds.email);
    await page.locator('input[type="password"]').first().fill(creds.password);
    await page.locator('button[type="submit"]').first().click();
    await page.waitForURL(/\/admin\/(dashboard|$)/);
}

// ── Logout helper ──────────────────────────────────────────────────────────────
export async function logout(page: Page): Promise<void> {
    await page.goto('/admin/login');
    // Alternatively click logout button if rendered in nav
    await page.request.post('/admin/logout', {
        headers: { 'X-CSRF-TOKEN': await getCsrfToken(page) },
    });
}

// ── CSRF helper ────────────────────────────────────────────────────────────────
export async function getCsrfToken(page: Page): Promise<string> {
    return (await page.evaluate(() =>
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '',
    )) ?? '';
}

// ── Expect redirect to login ───────────────────────────────────────────────────
export async function expectRedirectToLogin(page: Page, url: string): Promise<void> {
    await page.goto(url);
    await expect(page).toHaveURL(/\/admin\/login/);
}

// ── Wait for Inertia navigation ───────────────────────────────────────────────
export async function waitForInertia(page: Page): Promise<void> {
    await page.waitForFunction(() => {
        return !document.body.classList.contains('loading');
    }, { timeout: 10_000 }).catch(() => {});
    // Fallback: just wait for network to be idle
    await page.waitForLoadState('networkidle');
}

// ── Get flash success message ─────────────────────────────────────────────────
export async function expectSuccess(page: Page): Promise<void> {
    // PrimeVue toast or custom flash message
    const toast = page.locator(
        '[role="status"], .p-toast-message-success, [class*="success"], [data-type="success"]',
    ).first();
    await expect(toast).toBeVisible({ timeout: 8_000 });
}

// ── Get flash error message ───────────────────────────────────────────────────
export async function expectError(page: Page): Promise<void> {
    const toast = page.locator(
        '[role="alert"], .p-toast-message-error, [class*="error"], [data-type="error"]',
    ).first();
    await expect(toast).toBeVisible({ timeout: 8_000 });
}

// ── Navigation helper ─────────────────────────────────────────────────────────
export async function navigateToAdmin(page: Page, path: string): Promise<void> {
    await page.goto(`/admin/${path.replace(/^\//, '')}`);
    await page.waitForLoadState('domcontentloaded');
}
