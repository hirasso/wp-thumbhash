# Changelog

## 0.1.6

### Patch Changes

- 25a1112: phpstan fails if there is an error ignored in `ignoreErrors` that is not being thrown 🤦‍😆

## 0.1.5

### Patch Changes

- cce79ff: Do not attempt to create thumbhash placeholders for svg images
- 17a98f6: Suppress errors during upload. Make errors non-blocking
- cce79ff: Beautify console output for the generate command
- 8257f3a: Fix the query in the `wp thumbhash generate` command

## 0.1.4

### Patch Changes

- ad4defc: Re-apply readme and canonical plugin name (where did it go?!)

## 0.1.3

### Patch Changes

- 32081bb: In the admin, switch back to the `canvas` render strategy for thumbhash previews
- 6af28d8: Remove tests that are already covered by `@hirasso/thumbhash-custom-element`

## 0.1.2

### Patch Changes

- 993331a: `cli.js version:patch`: Infer the main plugin file from it's filename instead of assuming "Plugin Name" matches the `packageName`
- 44f49e0: Do not create and attach an `assets.zip` to the release anymore
- c633bb9: Load the scoper-autoload.php if available, as discussed in https://github.com/humbug/php-scoper/discussions/1101
- dcef3ab: Optimize the admin intrface in the media library popup
- d058228: Update `@hirasso/thumbhash-custom-element` to 0.5.4

## 0.1.1

### Patch Changes

- 253e906: Render the ID when generating or claring via WP-CLI
- 253e906: Throw more exceptions if WP_DEBUG is true

## 0.1.0

### Minor Changes

- ffcc62c: Add render strategies for the <thumb-hash> element: "canvas" | "img" | "average". Default is "canvas". See the docs of the [underlying library](https://github.com/hirasso/thumbhash-custom-element?tab=readme-ov-file#strategies) for more information.

### Patch Changes

- 84d100b: Reorganize npm scripts
- 7dadbce: Switch php tests from phpunit to pest
- d9d7a1b: Run only the playwright e2e tests against the scoped release
- 1322292: Update usage guide to include wp-cli commands
- 17278cf: Add e2e tests for admin interface
- c005370: Create a minimal `cli` for all scripts
- f05f7b0: Throw an exception if WP_DEBUG is true and the strategy in`do_action('wp-thumbhash/render', $id, $strategy)` doesn't exist
- c60b235: Add phpstan

## 0.0.2

### Patch Changes

- 2989d2c: Manually copy plugin-update-checker (don't scope it at all)
- 2989d2c: Load the scoped version at runtime when available

## 0.0.1

### Patch Changes

- b625e81: Fix the php-scoper patcher for plugin-update-checker
- 9584703: Add support for manual updates via plugin-update-checker
- ea38149: Do not run husky/lintstaged in version PR
- a4bdaf3: Patch plugin-update-checker when scoping
- a4bdaf3: Do not exclude dev dependencies from scoped package
