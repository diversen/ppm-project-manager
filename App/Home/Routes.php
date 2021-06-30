<?php

namespace App\Home;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {
        
        $router->add('GET', '/', \App\Home\Controller::class, 'index');
        $router->add('GET', '/terms/:document', \App\Home\Controller::class, 'terms');

    }
}


