<?php

namespace Hirasso\WPThumbhash\Tests\Unit;

use Hirasso\WPThumbhash\WPThumbhash;

/**
 * @coversDefaultClass \Hirasso\WPThumbhash\WPThumbhash
 */
final class WPThumbhashTest extends WPTestCase
{
    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();
    }

    /**
     * Test whether a placeholder is being created on upload
     *
     * @covers ::init
     * @covers ::generate
     * @covers ::getPlaceholder
     */
    public function test_generate_thumbhash_on_upload(): void
    {
        $this->assertHasAction(
            'add_attachment',
            [WPThumbhash::class, 'generate']
        );

        $attachmentID = $this->factory()->attachment->create_upload_object(
            WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $hash = WPThumbhash::getHash($attachmentID);

        $this->assertEquals(FIXTURES_EXPECTED_HASH, $hash);
    }

    /**
     * Test whether a placeholder is being created from the attachment URL
     * if the attached file cannot be found
     *
     * @covers ::generate
     */
    public function test_generateWithRemoteImage()
    {
        $attachmentID = $this->factory()->attachment->create_upload_object(
            WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $expectedHash = WPThumbhash::getHash($attachmentID);
        delete_post_meta($attachmentID, '_thumbhash');

        /** Filter the attached file name so that it can't be found */
        add_filter(
            'get_attached_file',
            fn($file) => 'i-do-not-exist.jpg'
        );

        /** Required for internal remote_get calls in docker */
        add_filter(
            'wp_get_attachment_url',
            fn($url) => str_replace('//localhost', '//host.docker.internal', $url)
        );

        WPThumbhash::generate($attachmentID);
        $hash = WPThumbhash::getHash($attachmentID);

        $this->assertEquals($expectedHash, $hash);
    }

    /**
     * @covers ::render
     */
    public function test_render()
    {
        $attachmentID = $this->factory()->attachment->create_upload_object(
            WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($attachmentID);

        $element = WPThumbhash::render($attachmentID);

        $this->assertEquals(FIXTURES_EXPECTED_CUSTOM_ELEMENT, $element);
    }
}
