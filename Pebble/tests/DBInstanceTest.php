<?php declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Pebble\Config;
use Pebble\DBInstance;

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

final class DBInstanceTest extends TestCase
{


    public function test_get()
    {
        DBInstance::close();
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
        $db = DBInstance::get();
        $this->assertInstanceOf(Pebble\DB::class, $db);
        DBInstance::close();
    }

    public function test_connect_invalid()
    {

        DBInstance::close();
        $this->expectException(Exception::class);
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'] . 'wrong_url', $db_config['username'], $db_config['password']);

    }

    public function test_connect_valid()
    {

        DBInstance::close();
        $db_config = Config::getSection('DB');
        
        $res = DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);

        $this->assertEquals(
            $res,
            null
        );

        DBInstance::close();
    }

}
