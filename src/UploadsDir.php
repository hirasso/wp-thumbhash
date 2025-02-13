<?php

/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

class UploadsDir
{
    /**
     * Get the custom dir in /wp-content/uploads/
     */
    public static function getDir(): string
    {
        $uploadDir = wp_upload_dir()['basedir'];

        $dir = "$uploadDir/wp-thumbhash";

        if (! file_exists($dir)) {
            wp_mkdir_p($dir);
        }

        return $dir;
    }

    /**
     * Cleans up (deletes) files in the custom directory that are older than one hour.
     */
    public static function cleanup(int $age = MINUTE_IN_SECONDS): void
    {
        $files = list_files(static::getDir());
        $before = time() - $age;

        foreach ($files as $file) {
            if (filemtime($file) < $before) {
                wp_delete_file($file);
            }
        }
    }
}
