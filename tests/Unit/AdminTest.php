<?php

use Hirasso\WPThumbhash\Admin;

beforeAll(function () {
    Admin::enqueueAssets();
});

test('registers actions', function () {
    expect(has_action('admin_enqueue_scripts', [Admin::class, 'enqueueAssets']))->toBeTruthy();
    expect(has_action('admin_enqueue_scripts', [Admin::class, 'enqueueAssets']))->toBeTruthy();
    expect(has_action('wp_ajax_generate_thumbhash', [Admin::class, 'wpAjaxGenerateThumbhash']))->toBeTruthy();
});

test('enqueues admin assets', function () {
    expect(wp_style_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();
    expect(wp_script_is(Admin::$assetHandle, 'enqueued'))->toBeTrue();
});

test('prints the global admin script tag', function () {
    $jsString = wp_scripts()->get_inline_script_data(Admin::$assetHandle, 'before');

    expect($jsString)->not()->toBeEmpty();

    preg_match('/var\s+wpThumbhash\s*=\s*(\{.*\});/s', $jsString, $matches);

    $object = json_decode($matches[1]);

    expect($object->ajax->url)->toEqual(admin_url('admin-ajax.php'));
    expect($object->ajax->action)->toEqual(Admin::$ajaxAction);
    expect(wp_verify_nonce($object->ajax->nonce, Admin::$ajaxAction))->toEqual(1);
});
