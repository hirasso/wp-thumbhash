<?php

namespace Hirasso\WPThumbhash\Tests\E2E;

use Hirasso\WPThumbhash\WPThumbhash;

use WP_Query;

use Exception;
use Hirasso\WPThumbhash\Enums\QueryArgsCompare;

class E2EPlugin
{
    public function __construct()
    {
        add_action('wp_head', [$this, 'render_styles']);
        add_filter('the_content', [$this, 'the_content']);
        add_action('init', [$this, 'init']);
    }

    /**
     * Automatically upload the fixtures image if it's missing
     */
    public function init()
    {
        if (!count($this->getImages())) {
            $this->uploadImage(WP_PLUGIN_DIR . '/wp-thumbhash/tests/__fixtures__/original.jpg');
        }
    }

    /**
     * Render the styles for thumb-hash elements in the frontend
     */
    public function render_styles()
    {
        ob_start(); ?>
        <style>
            figure,
            figure img {
                position: relative;
            }

            figure img {
                display: block;
                width: 100%;
                height: auto;
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

        if (!count($images)) {
            return "<p><strong>Please upload at least one image to test wp-thumbhash!</strong></p>";
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

        <div data-testid="using-canvas">
            <figure>
                <?php if (class_exists('Hirasso\\WPThumbhash\\WPThumbhash')): ?>
                    <?= \Hirasso\WPThumbhash\WPThumbhash::render($id) ?>
                <?php endif; ?>
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
        if (!file_exists($file)) {
            throw new Exception(sprintf('The file %s does not exist', $file));
        }

        // Include necessary WordPress files
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';

        // Copy the file to a temporary location
        $tempFile = wp_tempnam($file);
        if (!$tempFile) {
            throw new Exception('Failed to create a temporary file.');
        }

        if (!copy($file, $tempFile)) {
            throw new Exception('Failed to copy the file to a temporary location.');
        }

        // Prepare the file for upload
        $fileArray = [
            'name'     => basename($file),
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
