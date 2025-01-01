<?php

namespace Hirasso\WPThumbhash\Tests\Unit;

use Hirasso\WPThumbhash\Admin;

/**
 * @coversDefaultClass \Hirasso\WPThumbhash\Admin
 */
final class AdminTest extends WPTestCase
{
    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();
    }

    /**
     * Test whether all actions are set
     *
     * @covers ::init
     */
    public function test_init(): void
    {
        $this->assertHasAction(
            'attachment_fields_to_edit',
            [Admin::class, 'attachmentFieldsToEdit'],
        );

        $this->assertHasAction(
            'admin_enqueue_scripts',
            [Admin::class, 'enqueueAssets'],
        );

        $this->assertHasAction(
            'wp_ajax_generate_thumbhash',
            [Admin::class, 'wpAjaxGenerateThumbhash'],
        );
    }

    /**
     * @covers ::enqueueAssets
     */
    public function test_enqueueAssets(): void
    {
        Admin::enqueueAssets();

        $this->assertTrue(wp_style_is(Admin::$assetHandle, 'enqueued'));
        $this->assertTrue(wp_script_is(Admin::$assetHandle, 'enqueued'));

        /**
         * Test wp_localize_script
         */
        $data = wp_scripts()->get_data(Admin::$assetHandle, 'data');
        $this->assertNotFalse($data);

        /**
         * Converts something like "var foo = {"bar": "baz"};" to "{"bar": "baz"}"
         */
        $start = strpos($data, '{');
        $length = strrpos($data, '}') - $start + 1;
        $localized = substr($data, $start, $length);

        $json = json_decode($localized);

        $this->assertObjectHasProperty('ajax', $json);

        $this->assertObjectHasProperty('url', $json->ajax);
        $this->assertSame($json->ajax->url, admin_url('admin-ajax.php'));

        $this->assertObjectHasProperty('action', $json->ajax);
        $this->assertSame($json->ajax->action, Admin::$ajaxAction);

        $this->assertObjectHasProperty('nonce', $json->ajax);
        $this->assertSame(1, wp_verify_nonce($json->ajax->nonce, Admin::$ajaxAction));
    }
}
