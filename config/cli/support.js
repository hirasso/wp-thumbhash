#!/usr/bin/env node

// @ts-check

import {
  copyFileSync,
  cpSync,
  existsSync,
  mkdirSync,
  readFileSync,
  rmSync,
  writeFileSync,
} from "node:fs";
import { fileURLToPath } from "node:url";
import path, { basename, resolve } from "node:path";
import { execSync } from "node:child_process";
import { chdir, cwd, env, exit } from "node:process";
import pc from "picocolors";
import fg from "fast-glob";

// extract colors from common.js module picocolors
const { blue, bgRed, bold, gray, green } = pc;

// Get the equivalent of __filename
const __filename = fileURLToPath(import.meta.url);

/**
 * Dump and die
 * @param {...any} args
 */
export function dd(...args) {
  console.log(...args);
  process.exit();
}

/**
 * Validate that the script is being run from the root dir
 * This is being achieved by comparing the package name to
 */
export function isAtRootDir() {
  return (
    existsSync(resolve(cwd(), "package.json")) &&
    existsSync(resolve(cwd(), "composer.json"))
  );
}

/**
 * Get the current version from the package.json
 * In this project, the version in package.json is the
 * source of truth, as releases are handled by @changesets/action
 * @return {{version: string}}
 */
export function getInfosFromPackageJSON() {
  // Read the version and name from package.json
  const packageJsonPath = path.join(process.cwd(), "./package.json");
  const { version } = JSON.parse(readFileSync(packageJsonPath, "utf8"));
  return { version };
}

/**
 * Get the path to the scoped folder
 */
export function getScopedFolder() {
  const { packageName } = getInfosFromComposerJSON();
  return `scoped/${packageName}`;
}

/**
 * Get infos from the composer.json
 * @return {{fullName: string, vendorName: string, packageName: string}}
 */
export function getInfosFromComposerJSON() {
  // Read the version and name from package.json
  const composerJsonPath = path.join(process.cwd(), "./composer.json");
  const { name: fullName } = JSON.parse(readFileSync(composerJsonPath, "utf8"));
  if (!fullName) {
    throw new Error(`No name found in composer.json`);
  }
  if (!fullName.includes("/")) {
    throw new Error(
      `Invalid name found in composer.json. It must be 'vendor-name/package-name'`,
    );
  }
  const [vendorName, packageName] = fullName.split("/");
  return { fullName, vendorName, packageName };
}

/**
 * Run a command, stop execution on errors ({ stdio: "inherit" })
 * @param {string} command
 */
export const run = (command) => execSync(command, { stdio: "inherit" });

/**
 * Log an info message
 * @param {string} message
 * @param {...any} rest
 */
export const info = (message, ...rest) => {
  console.log(`💡 ${gray(message)}`, ...rest);
};

/**
 * Log a success message
 * @param {string} message
 * @param {...any} rest
 */
export const success = (message, ...rest) => {
  console.log(`✅ ${green(message)}`, ...rest);
};

/**
 * Log a success message
 * @param {string} message
 */
export const headline = (message) => {
  message = ` ℹ️  ${message} `;
  line();
  console.log(blue("-".repeat(message.length)));
  console.log(`${blue(message)}`);
  console.log(blue("-".repeat(message.length)));
  line();
};

/**
 * Log an error message and exit
 * @param {string} message
 * @param {...any} rest
 */
export const throwError = (message, ...rest) => {
  line();
  console.log(` ❌ ${bgRed(bold(`${message}`))}`, ...rest);
  exit(1);
};

/**
 * Log a line
 */
export const line = () => console.log("");

/**
 * Debug something to the console
 * @param {...any} args
 */
export const debug = (...args) => {
  line();
  console.log("🐛 ", ...args);
  line();
};

/**
 * Check if currently running on GitHub actions
 */
export const isGitHubActions = () => env.GITHUB_ACTIONS === "true";

/**
 * Compare two directories
 * @param {string} dir1
 * @param {string} dir2
 * @param {string[]} ignore
 */
export const validateDirectories = async (dir1, dir2, ignore = [".git"]) => {
  try {
    const pattern = ["*", ...ignore.map((ig) => `!${ig}`)];

    const { files1, files2 } = {
      files1: await fg(pattern, { cwd: dir1, onlyFiles: false }),
      files2: await fg(pattern, { cwd: dir2, onlyFiles: false }),
    };

    return (
      !!files1.length &&
      !!files2.length &&
      files1.length === files2.length &&
      files1.every((file, index) => file === files2[index])
    );
  } catch (err) {
    throwError("Error comparing directories:", err);
  }
};

/**
 * Create release files for usage in the release asset and dist repo
 * - scopes dependency namespaces using php-scoper
 * - creates a folder scoped/ with all required plugin files
 * - creates a zip file from the scoped/ folder, named after the package
 */
export function createReleaseFiles() {
  headline(`Creating Release Files...`);

  if (!isAtRootDir()) {
    throwError(`${basename(__filename)} must be executed from the package root directory`); // prettier-ignore
  }

  const { packageName } = getInfosFromComposerJSON();
  const scopedFolder = getScopedFolder();

  line();
  info(`Creating a scoped release in ${blue(scopedFolder)}...`);
  line();

  // Install Composer dependencies in GitHub Actions
  if (env.GITHUB_ACTIONS === "true") {
    console.log("💡 Installing non-dev composer dependencies...");
    run("composer install --no-scripts --no-dev");
  }

  /** Ensure php-scoper is available */
  const phpScoperPath = "config/php-scoper";
  info("Ensuring php-scoper is available...");

  if (!existsSync(phpScoperPath)) {
    run(`curl -sL https://github.com/humbug/php-scoper/releases/download/0.18.15/php-scoper.phar -o ${phpScoperPath}`); // prettier-ignore
    run(`chmod +x ${phpScoperPath}`);
  }

  /** Scope namespaces using php-scoper */
  info("Scoping namespaces using php-scoper...");
  rmSync(scopedFolder, { recursive: true, force: true });
  run(`${phpScoperPath} add-prefix --quiet --output-dir=${scopedFolder} --config=config/scoper.config.php`); // prettier-ignore
  success("Successfully scoped all namespaces!");
  line();

  /**
   * This needs to be done manually, since PUC causes problems when scoped.
   * All changes to the vendor dir have to run BEFORE dumping the autolaoder!
   */
  info(`Copying plugin-update-checker/ to ${scopedFolder}/...`);
  cpSync(
    "vendor/yahnis-elsts/plugin-update-checker",
    `${scopedFolder}/vendor/yahnis-elsts/plugin-update-checker`,
    { force: true, recursive: true },
  );

  /** Dump the autoloader in the scoped directory */
  info(`Dumping the autoloader in ${scopedFolder}...`);
  run(
    `composer dump-autoload --working-dir=${scopedFolder} --classmap-authoritative`,
  );

  line();

  /** Clean up the scoped directory */
  info(`Cleaning up ${scopedFolder}...`);
  ["composer.json", "composer.lock"].forEach((file) => {
    rmSync(resolve(`${cwd()}/${scopedFolder}`, file), { force: true });
  });

  info(`Overwriting the composer.json in ${scopedFolder}/...`);
  cpSync("composer.dist.json", `${scopedFolder}/composer.json`);

  info(`Copying assets/ to ${scopedFolder}/assets...`);
  cpSync("assets", `${scopedFolder}/assets`, { force: true, recursive: true });

  line();

  /** Create a zip file from the scoped directory */
  info(`Creating a zip file from ${scopedFolder}...`);
  run(
    `cd ${scopedFolder} && zip -rq "../../${packageName}.zip" . && cd - >/dev/null`,
  );

  line();
  success(`Created a scoped release folder: ${blue(scopedFolder)}`);
  success(`Created a scoped release asset: ${blue(`${packageName}.zip`)}`);
  line();
}

/**
 * Run Unit and E2E tests from the scoped plugin folder
 * @TODO:
 *  - copy the scoped folder to a /tmp location
 *  - copy phpunit.xml and tests/ into the /tmp folder
 *  - create a .wp-env.override.json with the "." replaced by the /tmp folder
 *  - Run wp-env start --update
 *  - Run php unit in the tmp folder (maybe not even any adjustment needed here?!)
 */
export function testScopedRelease() {
  const { plugins } = JSON.parse(readFileSync(".wp-env.json", "utf8"));
  throwError("Not yet implemented");
}

/**
 * Prepare the dist folder
 * - clones the dist repo into dist/
 * - checks out the empty root commit in dist/
 * - copies all files from scoped/ into dist/
 */
export function prepareDistFolder() {
  headline(`Preparing Dist Folder...`);

  const { fullName } = getInfosFromComposerJSON();
  const scopedFolder = getScopedFolder();

  // Check if the scoped folder exists
  if (!existsSync(scopedFolder)) {
    throwError(`'${scopedFolder}' scoped folder does not exist`);
  }

  // Initialize the dist folder if not in GitHub Actions
  if (env.GITHUB_ACTIONS !== "true") {
    info(`Cloning the dist repo into dist/...`);
    rmSync("dist", { recursive: true, force: true });
    run(`git clone -b empty git@github.com:${fullName}-dist.git dist/`); // prettier-ignore
  }

  info(`Checking out the empty tagged root commit..`);
  run("git -C dist checkout --detach empty");

  line();

  info(`Copying files from ${scopedFolder} to dist/...`);
  cpSync(scopedFolder, "dist", { recursive: true, force: true });

  success(`Dist folder preparation complete!`);
}

/**
 * Copy files from one foldder to another
 *
 * @param {string} sourceDir
 * @param {string} destDir
 * @param {string} pattern
 */
export async function copyFiles(sourceDir, destDir, pattern = "**/*.{js,css}") {
  const files = await fg(pattern, {
    cwd: sourceDir,
    absolute: true,
  });

  /** Ensure the destination directory exists */
  mkdirSync(destDir, { recursive: true });

  /** Copy each file */
  files.forEach((file) => {
    const relativePath = path.relative(sourceDir, file);
    const destPath = path.join(destDir, relativePath);

    /** Ensure destination subdirectories exist */
    mkdirSync(path.dirname(destPath), { recursive: true });

    /** Copy the file */
    copyFileSync(file, destPath);

    success(`Copied: ${sourceDir}/${relativePath} → ${destPath}`);
  });
}

/**
 * Push scoped release files to the dist repo
 */
export async function pushReleaseToDist() {
  const rootDir = cwd();
  const packageInfos = getInfosFromPackageJSON();
  const { packageName } = getInfosFromComposerJSON();
  const packageVersion = `v${packageInfos.version}`;
  const scopedFolder = getScopedFolder();

  const onGitHub = isGitHubActions();
  debug({ onGitHub });

  if (!isAtRootDir()) {
    throwError(`${basename(__filename)} must be executed from the package root directory`); // prettier-ignore
  }

  const hasValidDirectories = await validateDirectories(scopedFolder, "dist");

  debug({ hasValidDirectories });

  if (hasValidDirectories !== true) {
    throwError(
      `The validation of the scoped and dist folder failed.`,
      `Did you run 'release:prepare'?`,
    );
  }

  /** Ensure the script is running in a GitHub Action */
  if (!onGitHub) {
    throwError(`${basename(__filename)} can only run on GitHub`);
  }

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
}

/**
 * A simplistic build script for "compiling" the frontend assets
 */
export async function buildAssets() {
  rmSync("assets", { force: true, recursive: true });

  await copyFiles("assets-src", "assets", "**/*.{js,css}");

  cpSync(
    "node_modules/@hirasso/thumbhash-custom-element/dist/index.umd.js",
    "assets/thumbhash-custom-element.iife.js",
  );
}

/**
 * Patch the version in the main plugin php file, based on the
 * current version in the package.json
 */
export async function patchVersion() {
  /** Read the version and name from package.json */
  const { version: packageVersion } = getInfosFromPackageJSON();
  const { packageName } = getInfosFromComposerJSON();

  /** Allow to overwrite the plugin main file via an argument: `config/cli/cli.js version:patch foo.php` */
  const [, , pluginFileName = `${packageName}.php`] = process.argv;

  const pluginFilePath = path.join(process.cwd(), pluginFileName);

  /** Bail early if the file doesn't exist */
  if (!existsSync(pluginFilePath)) {
    throwError(`❌ plugin file not found: ${pluginFileName}`);
  }

  /** Update the version in the main plugin PHP file */
  let pluginFile = fs.readFileSync(pluginFilePath, "utf8");

  const versionRegexp = /\*\s*Version:\s*(\d+\.\d+\.\d+)/;
  const currentVersion = pluginFile.match(versionRegexp)?.[1];

  line();
  info(`Patching version in ${pluginFileName}...`);

  if (!currentVersion) {
    throwError(`No version found in file: ${pluginFileName}`);
    process.exit(1);
  }

  if (currentVersion === packageVersion) {
    success(`Version already patched in ${pluginFileName}: ${currentVersion}`);
    process.exit(0);
  }

  pluginFile = pluginFile.replace(
    versionRegexp,
    `* Version: ${packageVersion}`,
  );
  writeFileSync(pluginFilePath, pluginFile, "utf8");

  success(`Patched version to ${packageVersion} in ${pluginFileName}`);
  line();
}
