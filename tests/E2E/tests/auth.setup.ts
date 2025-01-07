import { test as setup, expect } from "@playwright/test";
import { authFile, baseURL } from "../playwright.config.js";

setup("authenticate", async ({ page }) => {
  // Perform authentication steps.
  await page.goto(`${baseURL}/wp-admin/`);
  await page.fill("#user_login", "admin");
  await page.fill("#user_pass", "password");
  await page.click("#wp-submit");

  // Alternatively, you can wait until the page reaches a state where all cookies are set.
  await expect(page.locator("#wp-admin-bar-my-account")).toBeVisible();

  // End of authentication steps.
  await page.context().storageState({ path: authFile });
});
