<?php declare (strict_types = 1);

use Pebble\Log;
use PHPUnit\Framework\TestCase;

final class LogTest extends TestCase
{
    public function test_construct_failure()
    {

        $this->expectException(Exception::class);
        $log = new Log();

    }

    public function test_construct_success()
    {

        $log_dir = __DIR__ . '/logs';
        $log = new Log(['log_dir' => $log_dir]);

        $this->assertInstanceOf(Pebble\Log::class, $log);

    }

    public function test_message()
    {

        $log_dir = __DIR__ . '/logs';
        $log = new Log(['log_dir' => $log_dir]);

        $log->message('Hello world', 'info');

        // 2021-07-19 09:29:43 INFO Hello world
        $this->assertStringContainsString('INFO Hello world', file_get_contents($log_dir . '/main.log'));

    }

    public function test_on()
    {

        $log_dir = __DIR__ . '/logs';
        $log = new Log(['log_dir' => $log_dir]);

        
        $log->on(['info'], function ($message) use ($log) {
            $log->message('events working', 'debug');
        });

        $log->message('Hello world', 'info');

        // 2021-07-19 09:29:43 INFO Hello world
        $this->assertStringContainsString('INFO Hello world', file_get_contents($log_dir . '/main.log'));
        $this->assertStringContainsString('DEBUG events working', file_get_contents($log_dir . '/main.log'));

    }
    public static function tearDownAfterClass(): void
    {
        $log_dir = __DIR__ . '/logs';
        exec("rm -r $log_dir");
    }
}
