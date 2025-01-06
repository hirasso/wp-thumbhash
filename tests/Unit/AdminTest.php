<?php

namespace Hirasso\WPThumbhash\Tests\Unit\AdminTest;

uses(\Hirasso\WPThumbhash\Tests\Unit\WPTestCase::class);

use Hirasso\WPThumbhash\Admin;

test('registers actions', function () {
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
});

test('enqueues all assets', function () {
    Admin::enqueueAssets();

    expect(wp_style_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();
    expect(wp_script_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();

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
    expect(admin_url('admin-ajax.php'))->toBe($json->ajax->url);

    $this->assertObjectHasProperty('action', $json->ajax);
    expect(Admin::$ajaxAction)->toBe($json->ajax->action);

    $this->assertObjectHasProperty('nonce', $json->ajax);
    expect(wp_verify_nonce($json->ajax->nonce, Admin::$ajaxAction))->toBe(1);
});
