#!/usr/bin/env node

// @ts-check

import { getInfosFromPackageJSON } from "./support.js";

const { version } = getInfosFromPackageJSON();

/**
 * Log the version to the console,
 * so that it can be consumed by other scripts,
 * e.g. `PACKAGE_VERSION=$(config/scripts/logVersion)`
 */
console.log(version);
