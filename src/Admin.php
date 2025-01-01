<?php
/*
 * Copyright (c) Rasso Hilber
 * https://rassohilber.com
 */

namespace Hirasso\WPThumbhash;

use Hirasso\WPThumbhash\Enums\AdminContext;
use WP_Post;

class Admin
{
    public static $assetHandle = 'wp-thumbhash-admin';
    public static $ajaxAction = 'generate_thumbhash';

    public static function init()
    {
        add_filter('attachment_fields_to_edit', [static::class, 'attachmentFieldsToEdit'], 10, 2);
        add_action('admin_enqueue_scripts', [static::class, 'enqueueAssets']);
        add_action('wp_ajax_' . static::$ajaxAction, [static::class, 'wpAjaxGenerateThumbhash']);
    }

    /**
     * Enqueue assets
     */
    public static function enqueueAssets(): void
    {
        // phpcs:disable WordPress.WP.EnqueuedResourceParameters.MissingVersion -- the version is derived from the filemtime
        wp_enqueue_style(static::$assetHandle, WPThumbhash::getAssetURI('/assets/admin.css'), [], null);
        wp_enqueue_script(static::$assetHandle, WPThumbhash::getAssetURI('/assets/admin.js'), ['jquery'], null, true);
        // phpcs:enable WordPress.WP.EnqueuedResourceParameters.MissingVersion

        wp_localize_script(static::$assetHandle, 'wpThumbhash', [
            'ajax' => [
                'url' => admin_url('admin-ajax.php'),
                'action' => static::$ajaxAction,
                'nonce' => wp_create_nonce(static::$ajaxAction),
            ],
        ]);
    }

    /**
     * Render the placeholder field
     * Uses a custom element for simple self-initialization
     */
    public static function attachmentFieldsToEdit(
        array $fields,
        WP_Post $attachment
    ): array {
        if (!wp_attachment_is_image($attachment)) {
            return $fields;
        }

        $fields['thumbhash-attachment-field'] = [
            'label' => __('Thumbhash', 'wp-thumbhash'),
            'input'  => 'html',
            'html' => static::renderAttachmentField($attachment->ID, AdminContext::INITIAL),
        ];

        return $fields;
    }

    /**
     * Render the attachment field
     */
    private static function renderAttachmentField(int $id, AdminContext $context): string
    {
        $element = WPThumbhash::render($id);
        $buttonLabel = !!$element ? __('Regenerate', 'wp-thumbhash') : __('Generate', 'wp-thumbhash');

        ob_start() ?>

        <thumbhash-attachment-field data-id="<?= esc_attr($id) ?>">
            <?= $element ?>

            <button
                data-placeholders-generate
                type="button"
                class="button button-small">
                <?php echo esc_html($buttonLabel) ?>
            </button>

            <?php if ($context === AdminContext::REGENERATE): ?>
                <i aria-hidden="true" data-thumbhash-regenerated></i>
            <?php endif; ?>

        </thumbhash-attachment-field>

<?php return ob_get_clean();
    }

    /**
     * (Re-)generate the thumbhash via AJAX.
     * Return the updated attachment field on success
     */
    public static function wpAjaxGenerateThumbhash(): void
    {
        check_ajax_referer(static::$ajaxAction, 'security');

        $id = intval($_POST['id'] ?? null);

        if (empty($id) || !is_numeric($id)) {
            wp_send_json_error([
                'message' => 'Invalid id provided',
            ]);
        }

        WPThumbhash::generate($id);

        wp_send_json_success([
            'html' => static::renderAttachmentField($id, AdminContext::REGENERATE),
        ]);
    }
}
