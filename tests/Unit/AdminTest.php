<?php

uses(\Hirasso\WPThumbhash\Tests\Unit\WPTestCase::class);

use Hirasso\WPThumbhash\Admin;

beforeAll(function () {
    Admin::enqueueAssets();
});

test('registers actions', function () {
    /** @var \Hirasso\WPThumbhash\Tests\Unit\WPTestCase $this */
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

test('enqueues admin assets', function () {
    expect(wp_style_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();
    expect(wp_script_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();
});

test('prints the global admin script tag', function () {
    $jsString = wp_scripts()->get_inline_script_data(Admin::$assetHandle, 'before');
    $this->assertNotEmpty($jsString);

    preg_match('/var\s+wpThumbhash\s*=\s*(\{.*\});/s', $jsString, $matches);

    $object = json_decode($matches[1]);

    expect($object)->toHaveProperty('ajax');

    $this->assertObjectHasProperty('url', $object->ajax);
    expect(admin_url('admin-ajax.php'))->toBe($object->ajax->url);

    $this->assertObjectHasProperty('action', $object->ajax);
    expect(Admin::$ajaxAction)->toBe($object->ajax->action);

    $this->assertObjectHasProperty('nonce', $object->ajax);
    expect(wp_verify_nonce($object->ajax->nonce, Admin::$ajaxAction))->toBe(1);
});
