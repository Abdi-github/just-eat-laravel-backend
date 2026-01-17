import { chromium } from '@playwright/test';
import * as fs from 'fs';
import * as path from 'path';

const AUTH_DIR = path.join(process.cwd(), '.auth');

async function loginAndSave(
    baseURL: string,
    email: string,
    password: string,
    filePath: string,
): Promise<void> {
    const browser = await chromium.launch();
    const context = await browser.newContext();
    const page = await context.newPage();

    await page.goto(`${baseURL}/admin/login`);

    // Wait for the login form to be ready
    await page.waitForSelector('form', { timeout: 10_000 });

    // Fill credentials
    const emailField = page.locator('input[type="email"], input[name="email"]').first();
    const passwordField = page.locator('input[type="password"]').first();
    await emailField.fill(email);
    await passwordField.fill(password);

    // Submit the form
    const submitBtn = page.locator('button[type="submit"]').first();
    await submitBtn.click();

    // Wait for redirect to dashboard
    await page.waitForURL(/\/admin\/(dashboard|$)/, { timeout: 15_000 });

    // Save storage state (cookies + localStorage)
    await context.storageState({ path: filePath });

    await browser.close();
    console.log(`✔ Auth state saved → ${filePath}`);
}

export default async function globalSetup(): Promise<void> {
    const baseURL = process.env.APP_URL || 'http://localhost:4005';

    if (!fs.existsSync(AUTH_DIR)) {
        fs.mkdirSync(AUTH_DIR, { recursive: true });
    }

    await loginAndSave(
        baseURL,
        process.env.SUPER_ADMIN_EMAIL   || 'admin@just-eat-clone.ch',
        process.env.SUPER_ADMIN_PASSWORD || 'password',
        path.join(AUTH_DIR, 'super-admin.json'),
    );

    await loginAndSave(
        baseURL,
        process.env.SUPPORT_EMAIL    || 'support@just-eat-clone.ch',
        process.env.SUPPORT_PASSWORD || 'password',
        path.join(AUTH_DIR, 'support-agent.json'),
    );
}
