import fs from "fs";
import { parseArgs } from "node:util";
import { dd, c, fileExists, error } from "./utils.js";

const distignoreFile = ".distignore";

/**
 * Parse the command-line arguments
 */
const args = parseArgs({
  options: {
    exclude: { type: "string" }, // Define `--exclude` for excluded files and folders
  },
  allowPositionals: false,
});
const excludes = args.values.exclude
  ? args.values.exclude.split(",").map((exclude) => exclude.trim())
  : [];

/**
 * Get a headline. The result looks like this:
 *
 * #-----------------------------------#
 * # I am a headline of various length #
 * # I can be multiline                #
 * #-----------------------------------#
 *
 * @param {string[]} lines - The headline text
 * @return {string} - The resulting headline string
 */
const getHeadline = (lines) => {
  const longestLine = lines.reduce((longest, current) => {
    return current.length >= longest.length ? current : longest;
  }, "");

  const dashes = `#${"-".repeat(longestLine.length + 2)}#`;
  const formattedLines = lines.map((line) => {
    const spaces = " ".repeat(longestLine.length - line.length);
    return `# ${line} ${spaces}#`;
  });

  return (
    "\n" +
    [dashes, ...formattedLines, dashes].map((line) => line.trim()).join("\n") +
    "\n\n"
  );
};

generateDistignore();

/**
 * Main function to generate .distignore from .gitignore and .gitattributes
 */
function generateDistignore() {
  /** Check if .gitattributes exists */
  if (!fileExists(".gitattributes")) {
    error(".gitattributes file not found!");
  }

  /** Check if .gitignore exists */
  if (!fileExists(".gitignore")) {
    error(".gitignore file not found!");
  }

  /** Create or overwrite the .distignore file with a header */
  fs.writeFileSync(
    distignoreFile,
    getHeadline([
      "⛔️ DO NOT EDIT THIS FILE DIRECTLY ⛔️",
      "Use `pnpm distignore:generate` instead.",
      "",
      "All files and folders listed here will be",
      "ignored in the distributed WordPress plugin",
    ]),
  );

  /** Create or overwrite the .distignore file with a header */
  fs.appendFileSync(distignoreFile, getHeadline(["From .gitignore:"]));

  /** Read and append .gitignore content to .distignore */
  const gitignoreContent = fs.readFileSync(".gitignore", "utf8");
  fs.appendFileSync(distignoreFile, `${gitignoreContent}\n`);

  /** Append header for .gitattributes content */
  fs.appendFileSync(distignoreFile, getHeadline(["From .gitattributes:"]));

  /**
   * Read .gitattributes and process each line
   * If a line ends with " export-ignore", it removes that part and appends the rest to .distignore
   */
  const gitattributesContent = fs.readFileSync(".gitattributes", "utf8");
  const gitattributesLines = [
    ...new Set(gitattributesContent.split("\n").map((line) => line.trim())),
  ];
  const distignoreLines = gitattributesLines
    .filter((line) => line.endsWith(" export-ignore"))
    .map((line) => line.replace(" export-ignore", ""));

  /** Append processed lines from .gitattributes to .distignore */
  fs.appendFileSync(distignoreFile, `${distignoreLines.join("\n")}\n`);

  console.log(
    `${c.green}✔ ${distignoreFile} generated from .gitignore and .gitattributes.${c.reset}`,
  );

  /** Read .distignore and comment out lines starting with excluded files/folders */
  const distignoreContent = fs.readFileSync(".distignore", "utf8");
  const filteredContent = distignoreContent
    .split("\n")
    .map((line) => {
      return excludes.some((exclude) => line.startsWith(exclude))
        ? `# ${line} (excluded)`
        : line;
    })
    .join("\n");

  /** Write the filtered content back to .distignore */
  fs.writeFileSync(distignoreFile, filteredContent);

  if (excludes.length) {
    console.log(
      `${c.green}✔ excluded:${c.reset}\n`,
      excludes.map((exclude) => `${c.blue}  - ${exclude}${c.reset}`).join("\n"),
    );
  }
}
