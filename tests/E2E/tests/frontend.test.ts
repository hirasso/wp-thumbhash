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
    expect(canvas).toHaveAttribute("width");
    expect(canvas).toHaveAttribute("height");
    expect(canvas).toHaveAttribute("style", "width: 100%; height: 100%;");
  });

  test("supports explicit strategy 'canvas'", async ({ page }) => {
    const el = page.getByTestId("strategy--canvas");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "canvas");

    const canvas = el.locator("thumb-hash canvas");
    expect(canvas).toHaveCount(1);
    expect(canvas).toHaveAttribute("width");
    expect(canvas).toHaveAttribute("height");
    expect(canvas).toHaveAttribute("style", "width: 100%; height: 100%;");
  });

  test("supports strategy 'img'", async ({ page }) => {
    const el = page.getByTestId("strategy--img");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "img");

    const img = el.locator("thumb-hash img");
    expect(img).toHaveAttribute("style", "width: 100%; height: 100%;");
    expect(img).toHaveAttribute("alt", "");
  });

  test("supports strategy 'average'", async ({ page }) => {
    const el = page.getByTestId("strategy--average");
    await el.scrollIntoViewIfNeeded();

    expect(el.locator("thumb-hash")).toHaveAttribute("strategy", "average");

    const div = el.locator("thumb-hash div");
    expect(div).toHaveCount(1);
    expect(div).toHaveAttribute(
      "style",
      "width: 100%; height: 100%; background: rgb(163, 134, 104);",
    );
  });
});
