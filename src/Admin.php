<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\WPThumbhash;

use RuntimeException;
use WP_Post;

class Admin
{
    public static $assetHandle = 'wp-thumbhash-admin';

    public static $ajaxAction = 'generate_thumbhash';

    public static function init()
    {
        add_filter('attachment_fields_to_edit', [self::class, 'attachmentFieldsToEdit'], 10, 2);
        add_action('admin_enqueue_scripts', [self::class, 'enqueueAssets']);
        add_action('wp_ajax_'.self::$ajaxAction, [self::class, 'wpAjaxGenerateThumbhash']);
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets(): void
    {
        static $enqueued = false;
        if ($enqueued) {
            return;
        }
        $enqueued = true;

        // phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the version is derived from the filemtime
        wp_enqueue_style(self::$assetHandle, WPThumbhash::getAssetURI('/assets/admin.css'), [], null);
        wp_enqueue_script(self::$assetHandle, WPThumbhash::getAssetURI('/assets/admin.js'), ['jquery'], null, true);
        // phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion

        $globals = [
            'ajax' => [
                'url' => admin_url('admin-ajax.php'),
                'action' => self::$ajaxAction,
                'nonce' => wp_create_nonce(self::$ajaxAction),
            ],
        ];
        wp_add_inline_script(
            self::$assetHandle,
            sprintf(
                'var wpThumbhash = %s;',
                wp_json_encode($globals, JSON_PRETTY_PRINT)
            ),
            'before'
        );
    }

    /**
     * Render the placeholder field
     * Uses a custom element for simple self-initialization
     */
    public static function attachmentFieldsToEdit(
        array $fields,
        WP_Post $attachment
    ): array {
        if (! WPThumbhash::isEncodableImage($attachment)) {
            return $fields;
        }

        $fields['thumbhash-attachment-field'] = [
            'label' => __('Thumbhash', 'wp-thumbhash'),
            'input' => 'html',
            'html' => self::renderAttachmentField($attachment->ID),
        ];

        return $fields;
    }

    /**
     * Render the attachment field
     */
    private static function renderAttachmentField(int $id): string
    {
        $hash = WPThumbhash::getHash($id);

        [, $width, $height] = wp_get_attachment_image_src($id, 'full');

        $ratio = $width && $height ? $width / $height : 1;

        $isGenerated = (bool) $hash;
        $buttonLabel = $isGenerated ? __('Show', 'wp-thumbhash') : __('Generate', 'wp-thumbhash');
        $action = $isGenerated ? 'show' : 'generate';

        ob_start() ?>

        <thumbhash-attachment-field data-id="<?= esc_attr((string) $id) ?>">

            <?php if ($hash) { ?>
                <thumb-hash
                    value="<?= $hash ?>"
                    style="aspect-ratio: <?= $ratio ?>;">
                    <span data-thumb-hash-value><?= $hash ?></span>
                </thumb-hash>
            <?php } ?>

            <button
                data-thumbhash-action="<?= $action ?>"
                type="button"
                class="button button-small">
                <?php echo esc_html($buttonLabel) ?>
            </button>

        </thumbhash-attachment-field>

<?php return ob_get_clean();
    }

    /**
     * (Re-)generate the thumbhash via AJAX.
     * Return the updated attachment field on success
     */
    public static function wpAjaxGenerateThumbhash(): void
    {
        check_ajax_referer(self::$ajaxAction, 'security');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (! $id) {
            wp_send_json_error([
                'message' => 'Invalid id provided',
            ]);
        }

        try {
            WPThumbhash::generate($id);
            wp_send_json_success([
                'html' => self::renderAttachmentField($id),
            ]);
        } catch (RuntimeException $exception) {
            wp_send_json_error([
                'html' => self::renderAttachmentField($id),
                'error' => $exception->getMessage(),
            ]);
        }

    }
}
