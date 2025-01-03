<?php

/**
 * This is the main entry file
 */

namespace Hirasso\WPThumbhash;

use WP_Post;

/** Exit if accessed directly */
if (!defined('ABSPATH')) {
    exit;
}

define('WP_THUMBHASH_PLUGIN_URI', untrailingslashit(plugin_dir_url(__FILE__)));
define('WP_THUMBHASH_PLUGIN_DIR', untrailingslashit(__DIR__));

/** load the prefixed vendors if scoped  */
if (is_readable(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
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
