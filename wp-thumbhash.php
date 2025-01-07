<?php

/**
 * Plugin Name
 *
 * @package           wp-thumbhash
 * @author            Rasso Hilber
 * @copyright         2024 Rasso Hilber
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name: wp-thumbhash
 * Description: Generate and render thumbhash placeholders for your lazy-loaded images 🦦
 * Author: Rasso Hilber
 * Author URI: https://rassohilber.com/
 * Text Domain: wp-thumbhash
 * License: GPL-3.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP: 8.2
 * Version: 0.0.2
 */

namespace Hirasso\WPThumbhash;

use WP_Post;

/** Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

/** load the prefixed vendors if scoped  */
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
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
UpdateChecker::init(__DIR__ . '/wp-thumbhash.php');

/**
 * Render a <thumb-hash> custom element for an image
 */
function render(int|WP_Post $imageID)
{
    return WPThumbhash::render($imageID);
}

/**
 * Render a <thumb-hash> custom element for an image
 */
function getHash(int|WP_Post $imageID)
{
    return WPThumbhash::getHash($imageID);
}
