#!/usr/bin/env node

// @ts-check

import { parseArgs } from "node:util";
import { basename } from "node:path";
import { fileURLToPath } from "node:url";

import pc from "picocolors";

import {
  dd,
  createReleaseFiles,
  testScopedRelease,
  pushReleaseToDist,
  buildAssets,
  patchVersion,
  prepareDistFolder,
  isAtRootDir,
  throwError,
} from "./support.js";
import { execSync } from "node:child_process";

// Get the equivalent of __filename
const __filename = fileURLToPath(import.meta.url);

// List of available commands
const commands = {
  "assets:build": buildAssets,
  "release:create": createReleaseFiles,
  "release:test": testScopedRelease,
  "version:patch": patchVersion,
  "dist:prepare": prepareDistFolder,
  "dist:push": pushReleaseToDist,
};

const {
  positionals: [command],
} = parseArgs({ allowPositionals: true });

// Function to print usage
function printUsage() {
  console.log(`
Usage: cli.js <command>

Available commands:
  ${pc.blue(Object.keys(commands).join("\n  "))}`);
}

// Validate correct invocation
if (!command || typeof commands[command] !== "function") {
  printUsage();
  process.exit(1);
}

// Ensure the script is run from the project root
if (!isAtRootDir()) {
  throwError(
    `${basename(__filename)} must be executed from the package root directory`,
  );
}

// Execute the command
commands[command]();
