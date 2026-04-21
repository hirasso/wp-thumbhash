<?php

declare(strict_types=1);

namespace Hirasso\WPThumbhash\CLI;

use Hirasso\WPThumbhash\Enums\QueryArgsCompare;
use Hirasso\WPThumbhash\UploadsDir;
use Hirasso\WPThumbhash\WPThumbhash;
use WP_CLI;
use WP_CLI_Command;
use WP_Query;

class ThumbhashCommand extends WP_CLI_Command
{
    /**
     * Generate thumbhash placeholders.
     *
     * ## OPTIONS
     *
     * [<ids>...]
     * : Only generate placeholders for these images.
     *
     * [--force]
     * : Generate placeholders also for images that already have one.
     *
     * @when after_wp_load
     *
     * @param  array<int, string>  $args
     * @param  array<string, mixed>  $assoc_args
     */
    public function generate(array $args, array $assoc_args): void
    {
        $force = (bool) ($assoc_args['force'] ?? false);

        $this->validateNumericArgs($args);

        WP_CLI::log(match ($force) {
            true => 'Generating Thumbhash Placeholders (force: true)',
            default => 'Generating Thumbhash Placeholders',
        });

        $queryArgs = WPThumbhash::getQueryArgs(QueryArgsCompare::NOT_EXISTS);

        if ($force) {
            unset($queryArgs['meta_query']);
        }

        if (! empty($args)) {
            $queryArgs['post__in'] = array_map('absint', $args);
        }

        $query = new WP_Query($queryArgs);

        $images = array_filter(
            $query->posts,
            fn ($image) => (bool) WPThumbhash::isEncodableImage($image)
        );

        UploadsDir::cleanup();

        if (! count($images)) {
            WP_CLI::success('No images without placeholders found');

            return;
        }

        $count = 0;
        foreach ($images as $id) {
            $fileName = basename(wp_get_attachment_url($id));
            $thumbhash = WPThumbhash::generate($id);

            if (! is_wp_error($thumbhash)) {
                $status = WP_CLI::colorize('%Ggenerated%n');
                $icon = WP_CLI::colorize('%G✔︎%n');
                $count++;
            } else {
                $status = WP_CLI::colorize('%Rfailed%n');
                $icon = WP_CLI::colorize('%R❌%n');
            }

            WP_CLI::log(Utils::getStatusLine(
                start: "ID $id – $fileName",
                end: $status,
                icon: $icon,
            ));
        }

        WP_CLI::success(match ($count) {
            1 => "$count placeholder generated",
            0 => 'No placeholders generated',
            default => "$count placeholders generated",
        });
    }

    /**
     * Clear thumbhash placeholders.
     *
     * ## OPTIONS
     *
     * [<ids>...]
     * : Only clear placeholders for these images.
     *
     * @when after_wp_load
     *
     * @param  array<int, string>  $args
     */
    public function clear(array $args): void
    {
        $this->validateNumericArgs($args);

        WP_CLI::log('Clearing Thumbhash Placeholders');

        $queryArgs = WPThumbhash::getQueryArgs(QueryArgsCompare::EXISTS);

        if (! empty($args)) {
            $queryArgs['post__in'] = array_map('absint', $args);
        }

        $query = new WP_Query($queryArgs);

        if (! $query->have_posts()) {
            WP_CLI::success('No images with placeholders found');

            return;
        }

        $count = 0;
        foreach ($query->posts as $id) {
            delete_post_meta($id, WPThumbhash::META_KEY);

            WP_CLI::log(Utils::getStatusLine(
                start: "ID $id – ".basename(wp_get_attachment_url($id)),
                end: WP_CLI::colorize('%Gcleared ✔︎%n'),
            ));

            $count++;
        }

        WP_CLI::success(match ($count) {
            1 => "$count thumbhash cleared",
            0 => 'No thumbhashes cleared',
            default => "$count thumbhashes cleared",
        });
    }

    /**
     * @param  array<mixed>  $args
     */
    private function validateNumericArgs(array $args): void
    {
        $nonNumeric = array_diff($args, array_filter($args, 'is_numeric'));
        if (empty($nonNumeric)) {
            return;
        }
        $values = implode(', ', array_map(
            fn ($v) => "'".sanitize_text_field((string) $v)."'",
            $nonNumeric
        ));
        WP_CLI::error("Non-numeric ids provided: $values");
    }
}
