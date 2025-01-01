#!/usr/bin/env node

// @ts-check

/**
 * Push a new release to the dist repo
 */

import { basename } from "path";
import { chdir, cwd } from "process";
import { fileURLToPath } from "url";
import {
  run,
  throwError,
  dd,
  getInfosFromPackageJSON,
  getInfosFromComposerJSON,
  info,
  line,
  debug,
  isAtRootDir,
  isGitHubActions,
  validateDirectories,
  success,
} from "./support.js";

const rootDir = cwd();
const __filename = fileURLToPath(import.meta.url);

const onGitHub = isGitHubActions();
debug({ onGitHub });

if (!isAtRootDir()) {
  throwError(`${basename(__filename)} must be executed from the package root directory`); // prettier-ignore
}

const hasValidDirectories = await validateDirectories("scoped", "dist");
debug({ hasValidDirectories });
if (hasValidDirectories !== true) {
  throwError(
    `The validation of the scoped and dist folder failed.`,
    `Did you run 'config/scripts/prepareRelease.js'?`,
  );
}

/** Ensure the script is running in a GitHub Action */
if (!onGitHub) {
  throwError(`${basename(__filename)} can only run on GitHub`);
}

/** Get the package version and name */

const packageInfos = getInfosFromPackageJSON();
const { packageName } = getInfosFromComposerJSON();
const packageVersion = `v${packageInfos.version}`;

if (!packageVersion) {
  throwError("Empty package version");
}

info(`Committing and pushing new release: 'v${packageVersion}'...`);
line();

/** Navigate to the dist folder and perform Git operations */
try {
  chdir("dist/");
  run(`git add .`);
  run(`git commit -m "Release: ${packageName}@${packageVersion}"`);
  run(`git tag "${packageVersion}"`);
  run(`git push origin "${packageVersion}"`);
  success(`Released '${packageVersion}' to the dist repo.`);
  chdir(rootDir);
} catch (err) {
  throwError("An error occurred while releasing the package.", err);
}

/** Change back to the root dir */
chdir(rootDir);
