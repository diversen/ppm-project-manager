<?php declare (strict_types = 1);

use Pebble\DBCache;
use PHPUnit\Framework\TestCase;

final class DBCacheTest extends TestCase
{

    public function test_set()
    {

        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $res = $cache->set('some_key', $to_cache);

        $this->assertEquals(true, $res);

    }

    public function test_get()
    {
        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $cache->set('some_key', $to_cache);

        // Try to get a result that has expired
        $from_cache = $cache->get('some_key', -1);
        $this->assertEquals(null, $from_cache);

        // No expire
        $from_cache = $cache->get('some_key');
        $this->assertEquals($to_cache, $from_cache);

    }

    public function test_delete()
    {
        $cache = new DBCache();

        $to_cache = ['this is a test'];
        $cache->set('some_key', $to_cache);

        $cache->delete('some_key');
        $from_cache = $cache->get('some_key');
        $this->assertEquals(null, $from_cache);
    }
}
