<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

declare(strict_types=1);

namespace Hirasso\WPThumbhash;

use Hirasso\WPThumbhash\CLI\Commands\ClearCommand;
use Hirasso\WPThumbhash\CLI\Commands\GenerateCommand;
use Hirasso\WPThumbhash\Enums\QueryArgsCompare;
use Snicco\Component\BetterWPCLI\CommandLoader\ArrayCommandLoader;
use Snicco\Component\BetterWPCLI\WPCLIApplication;
use WP_Post;
use WP_Error;
use WP_CLI;

class WPThumbhash
{
    public const META_KEY = '_thumbhash';

    /**
     * Initialize the plugin
     */
    public static function init()
    {
        // Hook for generating Thumbhash on upload
        add_action('add_attachment', [static::class, 'generate']);
        add_action('plugins_loaded', [static::class, 'loadTextDomain']);

        // Load thumbhash-custom-element as early as possible on every page
        add_action('wp_enqueue_scripts', [static::class, 'enqueueAssets']);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAssets']);

        // Initialize WP CLI application
        if (defined('WP_CLI') && class_exists(WP_CLI::class)) {

            $cli = new WPCLIApplication('thumbhash', new ArrayCommandLoader([
                GenerateCommand::class,
                ClearCommand::class,
            ]));
            $cli->registerCommands();
        }

        Admin::init();
    }

    /**
     * Load the plugin text domain manually, so that it prevails over the folder name (it's "scoped" during tests)
     */
    public static function loadTextDomain(): void
    {
        // phpcs:ignore WordPress.WP.DeprecatedParameters.Load_plugin_textdomainParam2Found -- plugin-check fails here
        load_plugin_textdomain('wp-thumbhash', '', static::getAssetPath('/languages'));
    }

    /**
     * Enqueue the thumbhash-custom-element
     * @see https://github.com/hirasso/thumbhash-custom-element
     */
    public static function enqueueAssets(): void
    {
        wp_enqueue_script(
            handle: 'thumbhash-custom-element',
            src: static::getAssetURI('/assets/thumbhash-custom-element.iife.js'),
            deps: [],
            ver: null,
            args: false
        );
    }

    /**
     * Generate and attach a thumbhash for an image
     */
    public static function generate(
        int $attachmentID
    ): bool|WP_Error {
        if (!wp_attachment_is_image($attachmentID)) {
            return new WP_Error('not_an_image', sprintf(
                /* translators: %s is a path to a file */
                __('File is not an image: %s', 'wp-thumbhash'),
                esc_html($attachmentID)
            ));
        }

        $mimeType = get_post_mime_type($attachmentID);
        $file = get_attached_file($attachmentID);

        /** @var ImageDownloader|null $downloader */
        $downloader = null;
        if (!file_exists($file)) {
            $downloader = new ImageDownloader($mimeType);
            $file = $downloader->download(wp_get_attachment_url($attachmentID));
        }

        if (is_wp_error($file)) {
            return $file;
        }

        $hash = ThumbhashBridge::encode($file, $mimeType);

        $downloader?->destroy();

        if (is_wp_error($hash)) {
            return $hash;
        }

        update_post_meta($attachmentID, static::META_KEY, $hash);
        return true;
    }

    /**
     * Get the hash from an image
     */
    public static function getHash(int|WP_Post $imageID): ?string
    {
        $imageID = $imageID->ID ?? $imageID;

        if (!wp_attachment_is_image($imageID)) {
            return null;
        }

        return get_post_meta($imageID, static::META_KEY, true) ?: null;
    }

    /**
     * Get a <thumb-hash> element value for an image
     */
    public static function render(int|WP_Post $imageID): ?string
    {
        if (!$value = static::getHash($imageID)) {
            return null;
        }

        return sprintf(
            '<thumb-hash value="%s"></thumb-hash>',
            esc_attr($value)
        );
    }

    /**
     * Get the path to a plugin file
     */
    public static function getAssetPath(string $path): string
    {
        return WP_THUMBHASH_PLUGIN_DIR . '/' . ltrim($path, '/');
    }

    /**
     * Helper function to get versioned asset urls
     */
    public static function getAssetURI(string $path): string
    {
        $uri = WP_THUMBHASH_PLUGIN_URI . '/' . ltrim($path, '/');
        $file = static::getAssetPath($path);

        if (file_exists($file)) {
            $version = filemtime($file);
            $uri .= "?v=$version";
        }

        return $uri;
    }

    /**
     * Get args for querying images
     */
    public static function getQueryArgs(QueryArgsCompare $compare): array
    {
        $args = [
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'fields' => 'ids',
            // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- this only ever runs in WP CLI
            'meta_query' => [
                [
                    'key' => WPThumbhash::META_KEY,
                    'compare' => $compare->name,
                ],
            ],
        ];
        return $args;
    }
}
