<?php

use Pebble\DBCache;
use PHPUnit\Framework\TestCase;

final class DBCacheTest extends TestCase
{

    public function test_set() {

        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $res = $cache->set(10, $to_cache);

        $this->assertEquals(true, $res);

    }

    public function test_get() {
        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $cache->set(10, $to_cache);

        // Try to get a result that has expired
        $from_cache = $cache->get(10, -1);
        $this->assertEquals(null, $from_cache);

        // No expire
        $from_cache = $cache->get(10);
        $this->assertEquals($to_cache, $from_cache);


    }

    public function test_delete() {
        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $cache->set(10, $to_cache);

        $cache->delete(10);
        $from_cache = $cache->get(10);
        $this->assertEquals(null, $from_cache);
    }
}