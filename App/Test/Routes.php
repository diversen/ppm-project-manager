<?php

namespace App\Test;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {

        $router->add('GET', '/test/redirect_to_https', \App\Test\Controller::class, 'redirect_to_https');


    }
}
