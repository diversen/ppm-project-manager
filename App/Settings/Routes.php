<?php

namespace App\Settings;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {

        $router->add('GET', '/settings', \App\Settings\Controller::class, 'index');
        $router->add('GET', '/settings/test', \App\Settings\Controller::class, 'test');
        $router->add('POST', '/settings/put', \App\Settings\Controller::class, 'put');

    }
}
