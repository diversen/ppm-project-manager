<?php declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Pebble\Config;
use Pebble\DBInstance;

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

final class DBInstanceTest extends TestCase
{

    /*
    private function getDB()
    {
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
        return DBInstance::get();
    }*/

    public function test_connect_invalid()
    {

        $this->expectException(Exception::class);
        $db_config = Config::getSection('DB');
        DBInstance::close();
        DBInstance::connect($db_config['url'] . 'wrong_url', $db_config['username'], $db_config['password']);

    }

    public function test_connect_valid()
    {

        $db_config = Config::getSection('DB');
        DBInstance::close();
        $res = DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);

        $this->assertEquals(
            $res,
            null
        );
    }

}
