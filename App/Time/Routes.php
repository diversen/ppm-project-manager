<?php declare(strict_types=1);

namespace App\Time;

class Routes
{
    public static function setRoutes(\Pebble\Router$router)
    {

        $router->add('GET', '/time/add/:task_id', \App\Time\TimeController::class, 'add');
        $router->add('POST', '/time/post', \App\Time\TimeController::class, 'post');
        $router->add('POST', '/time/delete/:id', \App\Time\TimeController::class, 'delete');

    }
}
