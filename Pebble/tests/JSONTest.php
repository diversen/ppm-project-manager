<?php

use Pebble\JSON;
use PHPUnit\Framework\TestCase;

final class JSONTest extends TestCase
{
    public function test_response() {

        $json = JSON::response(['hello world'], 0, 512, false);
        $this->assertEquals('["hello world"]', $json);

    }
}
