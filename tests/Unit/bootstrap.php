<?php

/**
 * PHPUnit WP integration test bootstrap file
 */

namespace Hirasso\WPThumbhash\Tests\Unit;

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
 * The Composer autoloader must be loaded before WordPress, to make the PHPUnit Polyfills available
 */
require_once \dirname(__DIR__, 2).'/vendor/autoload.php';

/**
 * wp-env provides the WordPress PHPUnit test files in WP_TESTS_DIR (`/wordpress-phpunit/`)
 */
$testsDir = \getenv('WP_TESTS_DIR');

/**
 * Get access (GLOBAL!!) to tests_add_filter() function.
 */
require_once $testsDir.'/includes/functions.php';

\tests_add_filter(
    'muplugins_loaded',
    static function () {
        require_once \dirname(__DIR__, 2).'/wp-thumbhash.php';
    }
);

/*
 * Start up the WP testing environment.
 */
require_once $testsDir.'/includes/bootstrap.php';

/**
 * wp-phpunit's expectDeprecated() calls PHPUnit\Util\Test::parseTestMethodAnnotations(),
 * removed in PHPUnit 10+. No @expectedDeprecated annotations are used here.
 */
class TestCase extends \WP_UnitTestCase
{
    public function expectDeprecated(): void {}
}
