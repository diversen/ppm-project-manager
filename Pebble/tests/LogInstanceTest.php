<?php declare (strict_types = 1);

use Pebble\LogInstance;
use Pebble\Log;
use PHPUnit\Framework\TestCase;

final class LogInstanceTest extends TestCase
{
    public function test_construct_init()
    {

        $log_dir = __DIR__ . '/logs';
        $log = new Log(['log_dir' => $log_dir]);
        $res = LogInstance::init($log);
        $this->assertEquals(null, $res);

    }

    public function test_construct_get()
    {

        $log_dir = __DIR__ . '/logs';
        $log = new Log(['log_dir' => $log_dir]);
        LogInstance::init($log);

        $this->assertInstanceOf(Pebble\Log::class, LogInstance::get());

    }
}
