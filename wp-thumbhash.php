<?php

/**
 * WP Thumbhash
 *
 * @author            Rasso Hilber
 * @copyright         2024 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: WP Thumbhash
 * Description: Generate and render thumbhash placeholders for your lazy-loaded images 🦦
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: wp-thumbhash
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP: 8.2
 * Version: 0.1.5
 */

namespace Hirasso\WPThumbhash;

/** Exit if accessed directly */
if (! defined('ABSPATH')) {
    exit;
}

/** Load vendors */
if (is_readable(__DIR__.'/vendor/scoper-autoload.php')) {
    /**
     * Load scoper-autoload if available
     *
     * @see https://github.com/humbug/php-scoper/discussions/1101
     */
    require_once __DIR__.'/vendor/scoper-autoload.php';
} elseif (is_readable(__DIR__.'/vendor/autoload.php')) {
    /**
     * Otherwise, load the normal autoloader if available
     */
    require_once __DIR__.'/vendor/autoload.php';
}

/** Get the plugin's base URL */
function baseURL()
{
    return plugins_url('', __FILE__);
}

/** Get the plugin's base directory */
function baseDir()
{
    return __DIR__;
}

WPThumbhash::init();
UpdateChecker::init(__DIR__.'/wp-thumbhash.php');
