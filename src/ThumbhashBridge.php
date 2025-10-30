<?php

namespace Hirasso\WPThumbhash;

use Exception;
use Hirasso\WPThumbhash\Enums\ImageDriver;
use RuntimeException;
use Thumbhash\Thumbhash;
use WP_Error;
use WP_Image_Editor;

use function Thumbhash\extract_size_and_pixels_with_gd;
use function Thumbhash\extract_size_and_pixels_with_imagick;

class ThumbhashBridge
{
    /**
     * Generate a thumbhash from an image file
     */
    public static function encode(
        string $file,
        string $mimeType
    ): string|WP_Error {
        if (! file_exists($file)) {
            return new WP_Error(sprintf(
                'File not found: %s',
                esc_html($file)
            ));
        }

        /** @var WP_Image_Editor|WP_Error */
        $editor = wp_get_image_editor($file, [
            'mime_type' => $mimeType,
        ]);

        if (is_wp_error($editor)) {
            return $editor;
        }

        [$width, $height, $pixels] = self::extractSizeAndPixels(
            driver: self::getImageDriver($editor),
            image: self::getDownsizedImage($editor, $mimeType)
        );

        $hash = Thumbhash::RGBAToHash($width, $height, $pixels);

        return Thumbhash::convertHashToString($hash);
    }

    /**
     * Decode a stored hash
     */
    public static function getDataURI(
        string $hashString
    ): string|null|WP_Error {
        if (empty($hashString)) {
            return null;
        }

        try {

            $hashArray = Thumbhash::convertStringToHash($hashString);

            return Thumbhash::toDataURL($hashArray);

        } catch (Exception $e) {

            return new WP_Error(sprintf(
                'Error decoding thumbhash dataURI: %s',
                esc_html($e->getMessage())
            ));

        }
    }

    /**
     * Get a downsized version of an image
     */
    private static function getDownsizedImage(
        WP_Image_Editor $editor,
        string $mimeType
    ): string|WP_Error {
        $editor->resize(32, 32, false);

        UploadsDir::cleanup();

        $tmpFile = UploadsDir::getTmpFile('downsized');

        $saved = $editor->save($tmpFile, $mimeType);

        // clean up the temporary file
        wp_delete_file($tmpFile);

        if (is_wp_error($saved)) {
            return $saved;
        }

        $image = $saved['path'];

        $fs = Utils::getFilesystem();

        // Check if the image exists and is readable
        if (! $fs->exists($image) || ! $fs->is_readable($image)) {
            return new WP_Error('Temporary image file is not accessible.');
        }

        // Get the raw image data
        // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents --- this file is always local
        $imageData = $fs->get_contents($image);

        // Clean up the image file
        wp_delete_file($image);

        return $imageData ?: new WP_Error('Invalid $imageData');
    }

    /**
     * Extract the size and pixels from an image
     */
    private static function extractSizeAndPixels(
        ImageDriver $driver,
        string $image
    ): array {
        return match ($driver) {
            ImageDriver::IMAGICK => extract_size_and_pixels_with_imagick($image),
            ImageDriver::GD => extract_size_and_pixels_with_gd($image),
        };
    }

    /**
     * Get the current image driver
     */
    private static function getImageDriver(
        WP_Image_Editor $editor
    ): ImageDriver {
        return match ($editor::class) {
            'WP_Image_Editor_Imagick' => ImageDriver::IMAGICK,
            'WP_Image_Editor_GD' => ImageDriver::GD,
            default => throw new RuntimeException('Unsupported image driver')
        };
    }
}
