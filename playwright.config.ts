import { defineConfig, devices } from '@playwright/test';

export default defineConfig({
    testDir: './resources/js/tests/Browser/e2e',
    fullyParallel: false,
    forbidOnly: !!process.env.CI,
    retries: process.env.CI ? 1 : 0,
    workers: 1,
    reporter: [['html', { outputFolder: 'playwright-report' }], ['list']],
    use: {
        baseURL: process.env.APP_URL || 'http://localhost:4005',
        trace: 'on-first-retry',
        screenshot: 'only-on-failure',
        video: 'on-first-retry',
        actionTimeout: 15_000,
        navigationTimeout: 30_000,
    },
    globalSetup: './resources/js/tests/Browser/e2e/global-setup.ts',
    projects: [
        {
            name: 'chromium',
            use: { ...devices['Desktop Chrome'] },
        },
    ],
    outputDir: 'test-results',
});
