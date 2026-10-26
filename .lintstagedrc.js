export default {
  "**/*.{js,jsx,mjs,cjs,ts,mts,css,scss}": ["prettier --write"],
  // Our own sources only: vendor-prefixed/ is third-party
  "{*.php,{src,tests,config}/**/*.php}": ["pnpm run format:php"],
  "*": () => "config/cli/cli.js validate:php",
};
