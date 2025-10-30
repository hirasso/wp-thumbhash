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

    public function __construct() {}

    /**
     * Download a remote image and save it to the custom directory.
     */
    public function download(string $url): string
    {
        UploadsDir::cleanup();

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

        $filename = 'remote'.'-'.uniqid().'-'.basename($url);
        $file = UploadsDir::getDir()."/$filename";
        $fileContents = wp_remote_retrieve_body($response);

        if ($fs->put_contents($file, $fileContents, FS_CHMOD_FILE) === false) {
            throw new RuntimeException('Failed to write file to uploads directory');
        }

        $this->file = $file;

        return $file;
    }

    public function destroy(): void
    {
        if ($this->file) {
            wp_delete_file($this->file);
        }
    }
}
