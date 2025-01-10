import { test, expect } from "@playwright/test";
import { wait } from "./support";

test.describe("Admin Interface", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/wp-admin/upload.php");
  });

  test("Renders the admin UI in the media modal", async ({ page }) => {
    page.setViewportSize({ width: 1000, height: 900 });

    await page.locator(".attachment-preview .thumbnail").click();

    await page
      .locator("tr.compat-field-thumbhash-attachment-field")
      .scrollIntoViewIfNeeded();

    await wait(100);

    const canvas = page.locator("thumb-hash canvas");
    expect(canvas).toHaveCount(1);

    const button = page.locator("[data-thumbhash-action]");
    expect(button).toHaveCount(1);
    expect(button).toHaveText("Show");
  });
});
