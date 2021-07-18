<?php

use Pebble\Config;
use PHPUnit\Framework\TestCase;

final class ConfigTest extends TestCase
{

    public function test_readConfig() {
        $config_dir = dirname(__FILE__) . '/../../config';
        Config::readConfig($config_dir);
        $test_config = Config::getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);

    }

    public function test_getSection() {
        $config_dir = dirname(__FILE__) . '/../../config';
        Config::readConfig($config_dir);
        $test_config = Config::getSection('Test');
        $this->assertEquals('Test username', $test_config['username']);
    } 

    public function test_get() {
        $config_dir = dirname(__FILE__) . '/../../config';
        Config::readConfig($config_dir);
        $test_config = Config::get('Test.username');
        $this->assertEquals('Test username', $test_config);
    } 
}