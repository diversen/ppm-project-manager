<?php

namespace App\Overview;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {
        
        $router->add('GET', '/overview', \App\Overview\Controller::class, 'index');

    }
}


