<?php declare (strict_types = 1);

use Pebble\URL;
use PHPUnit\Framework\TestCase;

final class URLTest extends TestCase
{

    public function test_returnTo()
    {
        $_SERVER['REQUEST_URI'] = '/here/is/a/path?param=test';

        $str = URL::returnTo('/some/url');
        $this->assertEquals('/some/url?return_to=%2Fhere%2Fis%2Fa%2Fpath%3Fparam%3Dtest', $str);

    }

    public function test_getQueryPart()
    {
        $_GET['param1'] = 'test1';
        $param1 = URL::getQueryPart('param1');
        $this->assertEquals('test1', $param1);

    }

}
