<?php
/*
Plugin Name: wp-thumbhash e2e bootstrap
Description: Runs as a mu-plugin to support e2e tests using playwright
*/

namespace Hirasso\WPThumbhash\Tests\E2E;

/**
 * This Plugin will be mounted automatically in the wp-env container
 * It renders all images with a thumbhash automatically on 'the_content'.
 * This URL can be used in e2e tests: http://localhost:9783/
 */

add_action('plugins_loaded', fn() => new E2EPlugin());
