<?php

namespace Hirasso\WPThumbhash\Tests\Unit;

use Yoast\WPTestUtils\WPIntegration\TestCase;

/**
 * TestCase base class.
 */
abstract class WPTestCase extends TestCase
{
    /**
     * Asserts that an action is attached to an existing function/method
     */
    protected function assertHasAction(
        string $action,
        string|array $callable,
    ) {

        $this->assertTrue(
            is_callable($callable),
            "Action callable not found: " . json_encode($callable)
        );

        $this->assertNotFalse(
            has_action($action, $callable),
            "Does not have the expected $action action"
        );
    }
}
