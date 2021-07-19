<?php

use Pebble\ExceptionTrace;
use PHPUnit\Framework\TestCase;

final class ExceptionTraceTest extends TestCase
{

    public function test_get() {

        try {
            throw new Exception('An error');
        } catch(Exception $e) {
            $trace = ExceptionTrace::get($e);
            $this->assertStringContainsString('Message: An error', $trace);
            $this->assertStringContainsString('Pebble/tests/ExceptionTraceTest.php (12)', $trace);
            $this->assertStringContainsString('Trace: #0', $trace);
        }
    }
}
