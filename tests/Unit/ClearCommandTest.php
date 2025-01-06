<?php

uses(\Hirasso\WPThumbhash\Tests\Unit\WPTestCase::class);

use Hirasso\WPThumbhash\CLI\Commands\ClearCommand;
use Hirasso\WPThumbhash\WPThumbhash;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

beforeEach(function () {
    $this->attachmentID = $this->factory()->attachment->create_upload_object(
        WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
    );
    expect($this->attachmentID)->toBeInt();
});

test('synopsis', function () {
    $synopsis = ClearCommand::synopsis();
    expect($synopsis->hasRepeatingPositionalArgument())->toBeTrue();

    [$ids] = $synopsis->toArray();

    expect($ids['name'])->toEqual('ids');
    expect($ids['repeating'])->toBeTrue();
    expect($ids['optional'])->toBeTrue();
});

test('execute', function () {
    $tester = new CommandTester(new ClearCommand());

    $tester->run(["$this->attachmentID"]);

    $tester->assertCommandIsSuccessful();
    $tester->assertStatusCode(0);

    $tester->seeInStderr('[OK] 1 thumbhash cleared');

    $hash = WPThumbhash::getHash($this->attachmentID);

    expect($hash)->toBeNull();
});
