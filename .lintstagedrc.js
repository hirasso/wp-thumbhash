export default {
  "**/*.{js,jsx,mjs,cjs,ts,mts,css,scss}": ["prettier --write"],
  // Our own sources only: vendor-prefixed/ is third-party
  "{*.php,{src,tests,tools}/**/*.php}": ["pnpm run format:php"],
  "*": () => "tools/cli/cli.js validate:php",
};
