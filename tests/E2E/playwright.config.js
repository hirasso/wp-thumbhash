// @ts-check

import { defineConfig, devices } from "@playwright/test";

import { fileURLToPath } from "node:url";
import path from "node:path";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);

/** The URL of the wp-env development site */
export const baseURL = "http://localhost:9783";
export const authFile = path.join(__dirname, "playwright/.auth/user.json");

/**
 * See https://playwright.dev/website/test-configuration.
 */
export default defineConfig({
  /* Run this file before starting the tests */
  // globalSetup: path.resolve(__dirname, './playwright.setup.ts'),
  /* Run this file after all the tests have finished */
  // globalTeardown: path.resolve(__dirname, './playwright.teardown.ts'),
  /* Directory containing the test files */
  testDir: "./tests",
  /* Folder for test artifacts: screenshots, videos, ... */
  outputDir: "./results",
  /* Timeout individual tests after 5 seconds */
  timeout: 10_000,
  /* Run tests in files in parallel */
  fullyParallel: true,
  /* Fail the build on CI if you accidentally left test.only in the source code. */
  forbidOnly: !!process.env.CI,
  /* Retry on CI only */
  retries: process.env.CI ? 1 : 0,
  /* Limit parallel workers on CI, use default locally. */
  workers: process.env.CI ? 1 : undefined,
  // Limit the number of failures on CI to save resources
  maxFailures: process.env.CI ? 10 : undefined,
  /* Reporter to use. See https://playwright.dev/website/test-reporters */
  reporter: process.env.CI
    ? [
        ["dot"],
        ["github"],
        ["json", { outputFile: "../../playwright-results.json" }],
      ]
    : [
        ["list"],
        ["html", { outputFolder: "./reports/html", open: "on-failure" }],
      ],

  expect: {
    /* Timeout async expect matchers after 3 seconds */
    timeout: 3_000,
  },

  /* Shared settings for all the projects below. See https://playwright.dev/website/api/class-testoptions. */
  use: {
    /* Base URL to use in actions like `await page.goto('/')`. */
    baseURL,
    /* Collect trace when retrying the failed test. See https://playwright.dev/website/trace-viewer */
    trace: "on-first-retry",
    /* Capture screenshot after each test failure. */
    screenshot: "only-on-failure",
    /* Capture video if failed tests. */
    video: "retain-on-failure",
  },

  /* Configure projects for setup and major browsers */
  projects: [
    { name: "setup", testMatch: /.*\.setup\.ts/ },
    {
      name: "chromium",
      use: {
        ...devices["Desktop Chrome"],
        storageState: authFile,
      },
      dependencies: ["setup"],
    },
    {
      name: "firefox",
      use: {
        ...devices["Desktop Firefox"],
        storageState: authFile,
      },
      dependencies: ["setup"],
    },
    {
      name: "webkit",
      use: {
        ...devices["Desktop Safari"],
        storageState: authFile,
      },
      dependencies: ["setup"],
    },
  ],

  /* Run your local dev server before starting the tests */
  webServer: {
    url: baseURL,
    command: "pnpm run wp-env start",
    reuseExistingServer: true, //!process.env.CI,
  },
});
