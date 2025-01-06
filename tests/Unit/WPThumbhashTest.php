<?php

uses(\Hirasso\WPThumbhash\Tests\Unit\WPTestCase::class);

use Hirasso\WPThumbhash\WPThumbhash;

test('generates a thumbhash on upload', function () {
    $this->assertHasAction(
        'add_attachment',
        [WPThumbhash::class, 'generate']
    );

    $attachmentID = $this->factory()->attachment->create_upload_object(
        WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
    );

    expect($attachmentID)->toBeInt();

    $hash = WPThumbhash::getHash($attachmentID);

    expect($hash)->toEqual(FIXTURES_EXPECTED_HASH);
});

test('generates a thumbhash with a remote image', function () {
    $attachmentID = $this->factory()->attachment->create_upload_object(
        WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
    );

    expect($attachmentID)->toBeInt();

    $expectedHash = WPThumbhash::getHash($attachmentID);
    delete_post_meta($attachmentID, '_thumbhash');

    /** Filter the attached file name so that it can't be found */
    add_filter(
        'get_attached_file',
        fn ($file) => 'i-do-not-exist.jpg'
    );

    /** Required for internal remote_get calls in docker */
    add_filter(
        'wp_get_attachment_url',
        fn ($url) => str_replace('//localhost', '//host.docker.internal', $url)
    );

    WPThumbhash::generate($attachmentID);
    $hash = WPThumbhash::getHash($attachmentID);

    expect($hash)->toEqual($expectedHash);
});

test('renders the <thumb-hash> custom element', function () {
    $attachmentID = $this->factory()->attachment->create_upload_object(
        WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
    );

    expect($attachmentID)->toBeInt();

    $element = WPThumbhash::render($attachmentID);

    expect($element)->toEqual(FIXTURES_EXPECTED_CUSTOM_ELEMENT);
});
