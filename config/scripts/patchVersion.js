#!/usr/bin/env node

// @ts-check

import fs from "fs";
import path from "path";
import {
  dd,
  throwError,
  getInfosFromComposerJSON,
  getInfosFromPackageJSON,
  info,
  line,
  success,
} from "./support.js";

/** Read the version and name from package.json */
const { version: packageVersion } = getInfosFromPackageJSON();
const { packageName } = getInfosFromComposerJSON();

/** Allow to overwrite the plugin main file via an argument: `config/scripts/patchVersion.js foo.php` */
const [, , pluginFileName = `${packageName}.php`] = process.argv;

const pluginFilePath = path.join(process.cwd(), pluginFileName);

/** Bail early if the file doesn't exist */
if (!fs.existsSync(pluginFilePath)) {
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

pluginFile = pluginFile.replace(versionRegexp, `* Version: ${packageVersion}`);
fs.writeFileSync(pluginFilePath, pluginFile, "utf8");

success(`Patched version to ${packageVersion} in ${pluginFileName}`);
line();
