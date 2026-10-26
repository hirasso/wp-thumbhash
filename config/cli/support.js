import {
  existsSync,
  mkdirSync,
  readFileSync,
  rmSync,
  writeFileSync,
} from "node:fs";
import path, { basename, extname, resolve } from "node:path";
import { execSync } from "node:child_process";
import { cwd, env, exit } from "node:process";
import pc from "picocolors";
import fg from "fast-glob";

/** extract colors from common.js module picocolors */
const { blue, red, bold, gray, green } = pc;

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
  const packageJsonPath = path.join(process.cwd(), "./package.json");
  const { version } = JSON.parse(readFileSync(packageJsonPath, "utf8"));
  return { version };
}

/**
 * Get the path to the release build folder
 */
export function getBuildFolder() {
  const { packageName } = getInfosFromComposerJSON();
  return `build/${packageName}`;
}

/**
 * Get infos from the composer.json
 * @return {{
 *    fullName: string,
 *    vendorName: string,
 *    packageName: string,
 *    dependencies: string[],
 *    devDependencies: string[]
 * }}
 */
export function getInfosFromComposerJSON() {
  const composerJsonPath = path.join(process.cwd(), "./composer.json");
  const json = JSON.parse(readFileSync(composerJsonPath, "utf8"));
  const fullName = json.name;
  const dependencies = json["require"] || {};
  const devDependencies = json["require-dev"] || {};
  if (!fullName) {
    throw new Error(`No name found in composer.json`);
  }
  if (!fullName.includes("/")) {
    throw new Error(
      `Invalid name found in composer.json. It must be 'vendor-name/package-name'`,
    );
  }
  const [vendorName, packageName] = fullName.split("/");
  return { fullName, vendorName, packageName, dependencies, devDependencies };
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
export const error = (message, ...rest) => {
  line();
  console.log(` ❌ ${red(bold(`${message}`))}`, ...rest);
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
 * Extract the first version number from a composer constraint
 * e.g. ">=8.2" => "8.2"
 * @param {string|undefined} constraint
 * @return {string|undefined}
 */
const extractVersion = (constraint) => constraint?.match(/\d+(\.\d+)*/)?.[0];

/**
 * Extract a header from a plugin file or a readme.txt
 * e.g. "Requires PHP: 8.2" => "8.2"
 * @param {string|undefined} contents
 * @param {string} header
 * @return {string|undefined}
 */
const extractHeader = (contents, header) =>
  contents?.match(new RegExp(`^[ \\t/*#@]*${header}:(.*)$`, "mi"))?.[1].trim();

/**
 * Make sure the required PHP version is declared consistently everywhere.
 * Each of these is read by a different consumer:
 * - the main plugin file: WordPress, when activating or installing an upload
 * - readme.txt: update checkers, when reporting available updates
 * - composer.json: composer, for installs of the plugin
 */
export function validatePHPVersion() {
  const { packageName, dependencies } = getInfosFromComposerJSON();

  /** @type {Record<string, string|undefined>} */
  const versions = {
    [`${packageName}.php`]: extractHeader(
      readFile(`${packageName}.php`),
      "Requires PHP",
    ),
    "readme.txt": extractHeader(readFile("readme.txt"), "Requires PHP"),
    "composer.json": extractVersion(dependencies.php),
  };

  const declarations = Object.entries(versions)
    .map(([file, version]) => `  - ${blue(file)}: ${version ?? red("missing")}`)
    .join("\n");

  if (Object.values(versions).some((version) => !version)) {
    error(`The required PHP version is not declared everywhere:`, `\n${declarations}`); // prettier-ignore
  }

  if (new Set(Object.values(versions)).size > 1) {
    error(`The required PHP version is declared inconsistently:`, `\n${declarations}`); // prettier-ignore
  }

  success(`Required PHP version is consistently declared as ${Object.values(versions)[0]}`); // prettier-ignore
}

/**
 * Create the release asset.
 * Composer serves the tag's git archive; this zip is that same archive.
 */
export async function createRelease() {
  headline(`Creating Release Files...`);

  /** Bail early if the committed prefixed dependencies are stale */
  await verifyPrefixedDependencies();

  const { packageName } = getInfosFromComposerJSON();
  const buildFolder = getBuildFolder();

  line();
  info(`Creating a release in ${blue(buildFolder)}...`);
  line();

  /** --worktree-attributes so an uncommitted .gitattributes still applies */
  info(`Exporting tracked files to ${buildFolder}...`);
  rmSync(path.dirname(buildFolder), { recursive: true, force: true });
  mkdirSync(buildFolder, { recursive: true });
  run(`git archive --worktree-attributes --format=tar HEAD | tar -x -C ${buildFolder}`); // prettier-ignore

  line();

  /** `zip` appends, so a leftover archive would keep stale files */
  info(`Creating a zip file from ${buildFolder}...`);
  rmSync(`${packageName}.zip`, { force: true });
  run(`cd ${buildFolder} && zip -rq "${resolve(cwd(), `${packageName}.zip`)}" . && cd - >/dev/null`); // prettier-ignore

  line();
  success(`Created a release folder: ${blue(buildFolder)}`);
  success(`Created a release asset: ${blue(`${packageName}.zip`)}`);
  line();
}

/** Regenerated with a different value on every commit, and read by nothing */
const VOLATILE_PREFIXED_FILE = "vendor-prefixed/composer/installed.php";

/**
 * Verify vendor-prefixed/ matches composer.lock and that prefixing covered every symbol.
 * Committed build output drifts silently, hence the check in CI.
 */
export async function verifyPrefixedDependencies() {
  const { prefixedNamespaces, namespacePrefix } = getStraussConfig();

  info("Regenerating the prefixed dependencies...");
  run("composer prefix --quiet");

  /**
   * installed.php embeds the root package's commit SHA, so it changes on every commit
   * and could never match what is committed. Nothing reads it, so restore it.
   */
  run(`git checkout -- ${VOLATILE_PREFIXED_FILE} 2>/dev/null || true`);

  /** Against the index, not `git status`: staged-but-uncommitted still counts as in sync */
  const capture = (command) => execSync(command, { encoding: "utf-8" }).trim();

  const changed = [
    capture("git diff --name-only -- vendor-prefixed"),
    capture("git ls-files --others --exclude-standard -- vendor-prefixed"),
  ]
    .filter(Boolean)
    .join("\n");

  if (changed.length) {
    error(
      `vendor-prefixed/ is out of sync with composer.lock. Run ${blue("composer prefix")} and commit the result:`,
      `\n${changed}`,
    );
  }

  success("vendor-prefixed/ is in sync with composer.lock");

  /** Catches what a static rewriter misses: dynamic class names, names in strings */
  info("Checking that no unprefixed namespaces survived...");

  const files = await fg("**/*.php", {
    cwd: "vendor-prefixed",
    absolute: true,
  });
  const leaked = [];

  for (const file of files) {
    /** Normalise escaped backslashes to match names inside strings too */
    const contents = readFileSync(file, "utf-8").replaceAll("\\\\", "\\");

    for (const prefixedNamespace of prefixedNamespaces) {
      const pattern = new RegExp(
        `(?<!${escapeRegExp(`${namespacePrefix}\\`)})\\b${escapeRegExp(prefixedNamespace)}\\\\`,
      );
      if (pattern.test(contents)) {
        leaked.push(
          `  - ${prefixedNamespace} in ${path.relative(cwd(), file)}`,
        );
      }
    }
  }

  if (leaked.length) {
    error(`Unprefixed namespaces survived in vendor-prefixed/:`, `\n${leaked.join("\n")}`); // prettier-ignore
  }

  success("No unprefixed namespaces found in vendor-prefixed/");
}

/**
 * Read the prefixed namespaces from the generated autoloader rather than hardcoding them.
 *
 * @return {{ prefixedNamespaces: string[], namespacePrefix: string }}
 */
function getStraussConfig() {
  const composerJson = JSON.parse(readFile("composer.json") || "{}");
  const strauss = composerJson.extra?.strauss ?? {};
  const namespacePrefix = (strauss.namespace_prefix ?? "").replace(/\\+$/, "");

  if (!namespacePrefix) {
    error(`No extra.strauss.namespace_prefix found in composer.json`);
  }

  const psr4 = readFile("vendor-prefixed/composer/autoload_psr4.php") || "";

  /** e.g. "Hirasso\\WPThumbhash\\Vendor\\Thumbhash\\" => "Thumbhash" */
  const prefixedNamespaces = [
    ...new Set(
      [...psr4.matchAll(/'((?:[A-Za-z0-9_]+\\\\)+)'\s*=>/g)]
        .map(([, ns]) => ns.replaceAll("\\\\", "\\").replace(/\\+$/, ""))
        .filter((ns) => ns.startsWith(`${namespacePrefix}\\`))
        .map((ns) => ns.slice(namespacePrefix.length + 1)),
    ),
  ];

  if (!prefixedNamespaces.length) {
    error(`No prefixed namespaces found in vendor-prefixed/composer/autoload_psr4.php`); // prettier-ignore
  }

  return { prefixedNamespaces, namespacePrefix };
}

/**
 * Escape a string for literal use inside a RegExp
 * @param {string} value
 */
const escapeRegExp = (value) => value.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");

/**
 * Read a file, fall back to undefined if it doesn't exist
 * @param {string} path
 */
function readFile(path) {
  return existsSync(path)
    ? readFileSync(path, { encoding: "utf-8" })
    : undefined;
}

/**
 * Run Unit and E2E tests from the development version
 */
export function testDev() {
  if (existsSync(".wp-env.test.override.json")) {
    info(`Deleting plugins in .wp-env.test.override.json...`);
    const overrides = JSON.parse(
      readFile(".wp-env.test.override.json") || "{}",
    );
    rmSync(".wp-env.test.override.json", { force: true });
    delete overrides.plugins;
    if (Object.values(overrides).length) {
      writeJsonFile(".wp-env.test.override.json", overrides);
    }

    info(`Re-Starting wp-env with root folder...`);
    run(`wp-env --config .wp-env.test.json start --update`);
  }

  info(`Running tests against the development version...`);
  run("pnpm run test");
}

/**
 * Write JSON to a file
 * @param {string} name
 * @param {any} data
 */
function writeJsonFile(name, data) {
  writeFileSync(name, JSON.stringify(data, undefined, 2), "utf-8");
}

/**
 * Run E2E tests from the release folder.
 * This command is only required for local tests.
 */
export async function testRelease() {
  await createRelease();

  const buildFolder = getBuildFolder();

  // info(`Installing dev dependencies in ${buildFolder}...`);
  // const { devDependencies } = getInfosFromComposerJSON();

  // const requireDev = Object.entries(devDependencies).reduce(
  //   /**
  //    * @param {string[]} acc - The accumulator array.
  //    * @param {[string, string]} entry - An array containing the dependency name and version.
  //    * @returns {string[]} The updated accumulator array.
  //    */
  //   (acc, [name, version]) => {
  //     acc.push(`"${name}:${version}"`);
  //     return acc;
  //   },
  //   [],
  // );

  // run(`composer require --dev ${requireDev.join(" ")} --quiet --working-dir=${buildFolder} --with-all-dependencies`); // prettier-ignore

  if (!isGitHubActions()) {
    /** @type {{ plugins: string[] }} */
    const { plugins } = JSON.parse(readFile(".wp-env.test.json") || "{}");
    const overrides = JSON.parse(
      readFile(".wp-env.test.override.json") || "{}",
    );

    overrides.plugins = plugins.map((path) => {
      // return path.replace(/^\.\/?/, `./${buildFolder}/`);
      return path === "." ? `./${buildFolder}/` : path;
    });

    writeJsonFile(".wp-env.test.override.json", overrides);
    debug("Contents of .wp-env.test.override.json:", overrides);

    info(`Re-Starting wp-env with ${buildFolder}...`);

    run(`wp-env --config .wp-env.test.json start --update`);
  }

  info(`Running e2e tests against ${buildFolder}...`);
  run("pnpm run test:e2e");
}

/**
 * Patch the version in the main plugin php file, based on the
 * current version in the package.json
 */
export async function patchVersion() {
  const { version: packageVersion } = getInfosFromPackageJSON();
  const { packageName } = getInfosFromComposerJSON();

  const phpFiles = await fg("*.php");

  const nameRegexp = /\*\s*Plugin Name:\s*/;
  const versionRegexp = /\*\s*Version:\s*(\d+\.\d+\.\d+)/;

  const fileName = phpFiles.find((file) => {
    const contents = readFileSync(file, "utf-8");
    const fileSlug = basename(file, extname(file));
    const hasName = nameRegexp.test(contents);
    const hasVersion = versionRegexp.test(contents);
    return fileSlug === packageName && hasName && hasVersion;
  });

  if (!fileName) {
    return error(`Main plugin file not found: ${fileName}`);
  }

  const contents = readFileSync(fileName, "utf8");
  const currentVersion = contents.match(versionRegexp)?.[1];

  line();
  info(`Patching version in ${fileName}...`);

  if (!currentVersion) {
    error(`No version found in file: ${fileName}`);
    process.exit(1);
  }

  if (currentVersion === packageVersion) {
    success(`Version already patched in ${fileName}: ${currentVersion}`);
    process.exit(0);
  }

  writeFileSync(
    fileName,
    contents.replace(versionRegexp, `* Version: ${packageVersion}`),
    "utf8",
  );

  success(`Patched version to ${packageVersion} in ${fileName}`);
  line();
}
