<?php

namespace App\Time;

class Routes
{
    public static function setRoutes(\Pebble\Router$router)
    {

        $router->add('GET', '/time/add/:task_id', \App\Time\Controller::class, 'add');
        $router->add('POST', '/time/post', \App\Time\Controller::class, 'post');
        $router->add('POST', '/time/delete/:id', \App\Time\Controller::class, 'delete');

    }
}
