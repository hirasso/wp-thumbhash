#!/usr/bin/env node

import { createReleaseFiles, prepareDistFolder } from "./support.js";

/**
 * Prepares a full release
 */
createReleaseFiles();
prepareDistFolder();
