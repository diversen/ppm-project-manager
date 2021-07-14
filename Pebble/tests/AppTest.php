<?php declare (strict_types = 1);

use PHPUnit\Framework\TestCase;
use Pebble\Config;
use Pebble\App;

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

final class AppTest extends TestCase
{

    public function test_getSchemeAndHost()
    {

        $scheme_and_host = (new App())->getSchemeAndHost();
        $this->assertEquals('http://localhost:8000', $scheme_and_host);
    }
}
