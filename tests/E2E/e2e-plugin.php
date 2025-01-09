<?php

/**
 * Plugin Name: wp-thumbhash e2e test plugin
 * Description: Runs as a plugin to support e2e tests using playwright via wp-env
 */

/**
 * This Plugin will be mounted automatically in the wp-env container.
 * It renders all images with a thumbhash automatically on 'the_content'.
 * It will also automatically create one image if none exists, yet
 * This URL can be used in e2e tests: http://localhost:9783/
 */

namespace Hirasso\WPThumbhash\E2EPlugin;

use Exception;
use Hirasso\WPThumbhash\Enums\QueryArgsCompare;
use Hirasso\WPThumbhash\WPThumbhash;
use WP_Query;

add_action('plugins_loaded', fn () => new WPThumbhashE2EPlugin);

class WPThumbhashE2EPlugin
{
    public function __construct()
    {
        add_action('wp_head', [$this, 'render_styles']);
        add_filter('the_content', [$this, 'the_content']);
        add_action('init', [$this, 'init']);
    }

    /**
     * Make sure necessary data is available
     */
    public function init()
    {
        if (! count(get_posts())) {
            wp_insert_post([
                'post_title' => 'Hello World',
                'post_content' => 'Welcome to WordPress!',
                'post_status' => 'publish',
                'post_author' => 1,
                'post_type' => 'post',
            ]);
        }
        if (! count($this->getImages())) {
            assert(file_exists('__fixtures__/original.jpg'));
            $this->uploadImage('__fixtures__/original.jpg');
        }
    }

    /**
     * Render the styles for thumb-hash elements in the frontend
     */
    public function render_styles()
    {
        ob_start(); ?>
        <style>
            figure:has(thumb-hash) {
                aspect-ratio: 1;
            }

            figure,
            figure img {
                position: relative;
            }

            figure img {
                display: block;
                width: 100%;
                height: auto;
                transform: translate(10px, 10px);
            }

            figure thumb-hash {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
            }
        </style>
    <?php echo ob_get_clean();
    }

    /**
     * Filter the content to only render images with thumbhashes
     */
    public function the_content(): string
    {
        $images = $this->getImages();

        if (! count($images)) {
            return '<p><strong>Please upload at least one image to test wp-thumbhash!</strong></p>';
        }

        ob_start();
        foreach ($images as $id) {
            echo $this->renderImage($id);
        }

        return ob_get_clean();
    }

    /**
     * Render an image, according to docs
     */
    private function renderImage(int $id): string
    {
        ob_start() ?>

        <div data-testid="strategy--default">
            <h2>Default Strategy ('canvas'):</h2>
            <figure>
                <?php do_action('wp-thumbhash/render', $id) ?>
                <?php echo wp_get_attachment_image($id, 'large') ?>
            </figure>
        </div>

        <div data-testid="strategy--canvas">
            <h2>Explicit Strategy 'canvas':</h2>
            <figure>
                <?php do_action('wp-thumbhash/render', $id, 'canvas') ?>
                <?php echo wp_get_attachment_image($id, 'large') ?>
            </figure>
        </div>

        <div data-testid="strategy--img">
            <h2>Explicit Strategy 'img':</h2>
            <figure>
                <?php do_action('wp-thumbhash/render', $id, 'img') ?>
                <?php echo wp_get_attachment_image($id, 'large') ?>
            </figure>
        </div>

        <div data-testid="strategy--average">
            <h2>Explicit Strategy 'average'</h2>
            <figure>
                <?php do_action('wp-thumbhash/render', $id, 'average') ?>
                <?php echo wp_get_attachment_image($id, 'large') ?>
            </figure>
        </div>

<?php return ob_get_clean();
    }

    /**
     * Get all thumbhash images
     */
    private function getImages()
    {
        $query = new WP_Query(WPThumbhash::getQueryArgs(QueryArgsCompare::EXISTS));

        return $query->posts;
    }

    /**
     * Upload an image to the WP media library by local path
     */
    private function uploadImage(string $file)
    {
        // Check if the file exists
        if (! file_exists($file)) {
            throw new Exception(sprintf('The file %s does not exist', $file));
        }

        // Include necessary WordPress files
        require_once ABSPATH.'wp-admin/includes/file.php';
        require_once ABSPATH.'wp-admin/includes/media.php';
        require_once ABSPATH.'wp-admin/includes/image.php';

        // Copy the file to a temporary location
        $tempFile = wp_tempnam($file);
        if (! $tempFile) {
            throw new Exception('Failed to create a temporary file.');
        }

        if (! copy($file, $tempFile)) {
            throw new Exception('Failed to copy the file to a temporary location.');
        }

        // Prepare the file for upload
        $fileArray = [
            'name' => basename($file),
            'tmp_name' => $tempFile,
        ];

        // Upload the file and handle attachment
        $result = media_handle_sideload($fileArray, 0);

        if (is_wp_error($result)) {
            throw new Exception($result->get_error_message());
        }

        return $result; // Return the attachment ID
    }
}
