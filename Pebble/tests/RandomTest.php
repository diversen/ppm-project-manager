<?php declare (strict_types = 1);

use Pebble\Random;
use PHPUnit\Framework\TestCase;

final class RandomTest extends TestCase
{
    public function test_generateRandomString() {

        $str = Random::generateRandomString(16);
        $this->assertIsString($str);
        $this->assertEquals(mb_strlen($str), 16 * 2);

    }
}
