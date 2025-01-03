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

/**
 * Load the scoped version preferably for testing purposes
 */
if (is_readable(__DIR__ . '/scoped/main.php')) {
    return require_once(__DIR__ . '/scoped/main.php');
}

require_once(__DIR__ . '/main.php');
