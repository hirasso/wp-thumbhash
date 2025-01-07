<?php

/**
 * PHPUnit WP integration test bootstrap file
 */

namespace Hirasso\WPThumbhash\Tests\Unit;

use Yoast\WPTestUtils\WPIntegration;

define('WP_ENV_DEV_URL', 'http://localhost:9783');
define('WP_ENV_TEST_URL', 'http://localhost:9784');
define('FIXTURES_ORIGINAL_IMAGE', '/tests/__fixtures__/original.jpg');
define('FIXTURES_EXPECTED_HASH', 'YTkGJwaRhWUIt4lbgnhZl3ath2BUBGYA');
define('FIXTURES_EXPECTED_CUSTOM_ELEMENT', '<thumb-hash value="YTkGJwaRhWUIt4lbgnhZl3ath2BUBGYA" strategy="canvas"></thumb-hash>');

// Disable xdebug backtrace.
if (\function_exists('xdebug_disable')) {
    \xdebug_disable();
}

/*
 * Load the plugin(s).
 */
require_once \dirname(__DIR__, 2) . '/vendor/yoast/wp-test-utils/src/WPIntegration/bootstrap-functions.php';

/**
 * Get access (GLOBAL!!) to tests_add_filter() function.
 * wp-phpunit is located in `/wordpress-phpunit/` in the wp-env container
 */
require_once WPIntegration\get_path_to_wp_test_dir() . 'includes/functions.php';

\tests_add_filter(
    'muplugins_loaded',
    static function () {
        require_once \dirname(__DIR__, 2) . '/wp-thumbhash.php';
    }
);

/*
 * Load WordPress, which will load the Composer autoload file,
 * and load the MockObject autoloader after that.
 */
WPIntegration\bootstrap_it();
