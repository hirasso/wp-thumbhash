import { test, expect } from "@playwright/test";

test.describe("Render", () => {
  test.beforeEach(async ({ page }) => {
    page.setViewportSize({ width: 1000, height: 1000 });
    await page.goto("/");
  });

  test("supports default strategy 'canvas'", async ({ page }) => {
    const el = page.getByTestId("strategy--default");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "canvas");

    const canvas = el.locator("thumb-hash canvas");
    expect(canvas).toHaveCount(1);
  });

  test("supports explicit strategy 'canvas'", async ({ page }) => {
    const el = page.getByTestId("strategy--canvas");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "canvas");

    const canvas = el.locator("thumb-hash canvas");
    expect(canvas).toHaveCount(1);
  });

  test("supports strategy 'img'", async ({ page }) => {
    const el = page.getByTestId("strategy--img");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "img");

    const img = el.locator("thumb-hash img");
    expect(img).toHaveCount(1);
  });

  test("supports strategy 'average'", async ({ page, browserName }) => {
    const el = page.getByTestId("strategy--average");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "average");

    /** This test is flaky in FireFox on CI for some reason */
    if (!!process.env.CI && browserName === "firefox") return;

    const div = el.locator("thumb-hash div");
    expect(div).toHaveCount(1);
  });
});
