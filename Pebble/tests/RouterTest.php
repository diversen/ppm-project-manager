<?php declare (strict_types = 1);

use Pebble\Router;
use Pebble\Test;
use Pebble\Exception\NotFoundException;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{

    public function test_noRoutes() {


        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world';

        $router = new Router();
        $router->add('POST', '/tester/:param1', Test::class, 'index');

        $this->expectException(NotFoundException::class);
        $router->getValidRoutes();
    }

    public function test_missingMethod() {


        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world';

        $router = new Router();
        $router->add('POST', '/tester/:param1', Test::class, 'doesNotExist');

        $this->expectException(NotFoundException::class);
        $router->getValidRoutes();
    }
    
    public function test_getValidRoutes() {

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world/';

        $router = new Router();

        // Correct match with param
        $router->add('POST', '/test/:param1', Test::class, 'index');
        
        // No match
        $router->add('POST', '/test/:param1/doh', Test::class, 'index');

        // Exact match
        $router->add('POST', '/test/hello_world', Test::class, 'helloWorld');

        $routes = $router->getValidRoutes();

        // 2 correct matches
        $this->assertEquals(2, count($routes));

        $this->assertEquals($routes[0]['route'],'/test/:param1');
        $this->assertEquals($routes[0]['class'],'Pebble\Test');
        $this->assertEquals($routes[0]['method'],'index');
        $this->assertEquals($routes[0]['params']['param1'], 'hello_world');

        $this->assertEquals($routes[1]['route'],'/test/hello_world');
        $this->assertEquals($routes[1]['class'],'Pebble\Test');
        $this->assertEquals($routes[1]['method'], 'helloWorld');


    }

    public function test_run() {

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['REQUEST_URI'] = '/test/hello_world/';

        $router = new Router();

        // Correct match with param
        $router->add('POST', '/test/:param1', Test::class, 'index');
        
        // No match
        $router->add('POST', '/test/:param1/no_match', Test::class, 'index');

        // Exact match
        $router->add('POST', '/test/hello_world', Test::class, 'helloWorld');
        
        $router->run();

        $this->expectOutputString('Hello world');

    }
}
