<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

use RuntimeException;

class ImageDownloader
{
    private ?string $file = null;

    /**
     * Get the custom dir in /wp-content/uploads/
     */
    private static function getDir(): string
    {
        $uploadDir = wp_upload_dir();
        $dir = $uploadDir['basedir'] . '/' . 'wp-thumbhash';
        if (!file_exists($dir)) {
            wp_mkdir_p($dir);
        }
        return $dir;
    }

    /**
     * Download a remote image and save it to the custom directory.
     */
    public function download(string $url): string
    {
        $response = wp_remote_get($url, ['timeout' => 300]);

        if (is_wp_error($response)) {
            throw new RuntimeException(esc_html($response->get_error_message()));
        }

        $responseCode = wp_remote_retrieve_response_code($response);
        if ($responseCode !== 200) {
            throw new RuntimeException(sprintf(
                'Failed to download image. Response Code: %s',
                esc_html($responseCode)
            ));
        }

        $fs = Utils::getFilesystem();

        $filename = uniqid() . '-' . basename($url);
        $file = static::getDir() . "/$filename";
        $fileContents = wp_remote_retrieve_body($response);

        if ($fs->put_contents($file, $fileContents, FS_CHMOD_FILE) === false) {
            throw new RuntimeException('Failed to write file to uploads directory');
        }

        $this->file = $file;

        return $file;
    }

    public function destroy()
    {
        if ($this->file) {
            wp_delete_file($this->file);
        }
    }

    /**
     * Cleans up (deletes) images in the custom directory that are older than one hour.
     */
    public static function cleanupTemporaryFiles(int $age = MINUTE_IN_SECONDS): void
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
