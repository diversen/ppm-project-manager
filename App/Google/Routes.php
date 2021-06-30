<?php

namespace App\Google;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {
        $router->add('GET', '/google', \App\Google\Controller::class, 'index');
        $router->add('GET', '/google/signout', \App\Google\Controller::class, 'signout');
    }
}
