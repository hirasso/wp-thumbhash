<?php

namespace Hirasso\WPThumbhash;

use WP_Filesystem_Base;
use WP_Filesystem_Direct;

class Utils
{
    public static function getFilesystem(): WP_Filesystem_Direct|WP_Filesystem_Base
    {
        /** @var WP_Filesystem_Base $wp_filesystem */
        global $wp_filesystem;

        // Initialize the WP_Filesystem if it hasn't been initialized yet.
        if (empty($wp_filesystem)) {
            require_once ABSPATH . 'wp-admin/includes/file.php';
            WP_Filesystem();
        }

        return $wp_filesystem;
    }
}
