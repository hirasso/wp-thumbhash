import { test, expect } from "@playwright/test";
import { scrollTo, scrollToEnd, expectScrollPosition, sleep } from "./support";
import { baseURL } from "../playwright.config.js";

test.describe("Strategies", () => {
  test.beforeEach(async ({ page }) => {
    await page.goto("/");
  });

  test("Renders a canvas (strategy: 'canvas')", async ({ page }) => {
    await page.goto("/");
    page.setViewportSize({ width: 1000, height: 1000 });

    await page.waitForTimeout(2000);

    // Locate the thumb-hash element
    const canvas = page
      .getByTestId("using-canvas")
      .locator("thumb-hash canvas");
    expect(canvas).toHaveCount(1);
    expect(canvas).toHaveAttribute("width");
    expect(canvas).toHaveAttribute("height");
    expect(canvas).toHaveAttribute("style", "width: 100%; height: 100%;");
  });

  // test("Renders the average color in a div (strategy: 'average')", async ({ page }) => {
  //   await page.goto('/');
  //   page.setViewportSize({ width: 1000, height: 1000 });

  //   await page.waitForTimeout(2000);

  //   const div = page.getByTestId('demos-1').locator('thumb-hash[strategy="average"] div');
  //   expect(div).toHaveCount(1);
  //   expect(div).toHaveAttribute('style', 'width: 100%; height: 100%; background: rgb(163, 134, 104);');
  // });

  // test("Renders an image with a data URI (strategy: 'img')", async ({ page }) => {
  //   await page.goto('/');
  //   page.setViewportSize({ width: 1000, height: 1000 });

  //   await page.waitForTimeout(2000);

  //   const div = page.getByTestId('demos-2').locator('thumb-hash[strategy="img"] img');
  //   expect(div).toHaveCount(1);
  //   expect(div).toHaveAttribute('style', 'width: 100%; height: 100%;');
  //   expect(div).toHaveAttribute('alt', '');
  // });
});
