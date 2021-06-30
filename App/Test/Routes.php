<?php

namespace App\Test;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {

        $router->add('GET', '/test/index/', \App\Test\Controller::class, 'index');


    }
}
