<?php

namespace Hirasso\WPThumbhash\Tests\Unit;

use Hirasso\WPThumbhash\CLI\Commands\GenerateCommand;
use Hirasso\WPThumbhash\WPThumbhash;
use Snicco\Component\BetterWPCLI\Testing\CommandTester;

/**
 * @coversDefaultClass \Hirasso\WPThumbhash\CLI\Commands\GenerateCommand
 */
final class GenerateCommandTest extends WPTestCase
{
    private int $attachmentID;

    /**
     * Setting up
     */
    public function set_up()
    {
        parent::set_up();

        $this->attachmentID = $this->factory()->attachment->create_upload_object(
            WPThumbhash::getAssetPath(FIXTURES_ORIGINAL_IMAGE)
        );

        $this->assertIsInt($this->attachmentID);
    }

    /**
     * @covers ::synopsis
     */
    public function test_synopsis()
    {
        $synopsis = GenerateCommand::synopsis();
        $this->assertTrue($synopsis->hasRepeatingPositionalArgument());

        [$ids, $force] = $synopsis->toArray();

        $this->assertEquals('ids', $ids['name']);
        $this->assertTrue($ids['repeating']);
        $this->assertTrue($ids['optional']);

        $this->assertEquals('force', $force['name']);
        $this->assertTrue($force['optional']);
    }

    /**
     * @covers ::execute
     */
    public function test_execute()
    {
        $tester = new CommandTester(new GenerateCommand());

        $tester->run([], ['force' => true]);

        $tester->assertCommandIsSuccessful();
        $tester->assertStatusCode(0);

        $tester->seeInStderr('Generating Thumbhashes (force: true)');
        $tester->seeInStderr('[OK]');
    }
}
