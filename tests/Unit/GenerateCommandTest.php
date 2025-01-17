<?php

uses(\Yoast\WPTestUtils\WPIntegration\TestCase::class);

use Hirasso\WPThumbhash\CLI\Commands\GenerateCommand;
use Hirasso\WPThumbhash\WPThumbhash;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

beforeEach(function () {
    $this->attachmentID = $this->factory()->attachment->create_upload_object(
        WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
    );

    expect($this->attachmentID)->toBeInt();
});

test('synopsis', function () {
    $synopsis = GenerateCommand::synopsis();
    expect($synopsis->hasRepeatingPositionalArgument())->toBeTrue();

    [$ids, $force] = $synopsis->toArray();

    expect($ids['name'])->toEqual('ids');
    expect($ids['repeating'])->toBeTrue();
    expect($ids['optional'])->toBeTrue();

    expect($force['name'])->toEqual('force');
    expect($force['optional'])->toBeTrue();
});

test('execute', function () {
    $tester = new CommandTester(new GenerateCommand);

    $tester->run([], ['force' => true]);

    $tester->assertCommandIsSuccessful();
    $tester->assertStatusCode(0);

    $tester->seeInStderr('Generating Thumbhash Placeholders (force: true)');
    $tester->seeInStderr('[OK]');
});
