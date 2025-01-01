#!/usr/bin/env node

// @ts-check

import { cpSync, rmSync } from "node:fs";
import { copyFiles } from "./support.js";

rmSync("assets", { force: true, recursive: true });

await copyFiles('assets-src', 'assets', '**/*.{js,css}');

cpSync(
  "node_modules/@hirasso/thumbhash-custom-element/dist/index.umd.js",
  "assets/thumbhash-custom-element.iife.js",
);
