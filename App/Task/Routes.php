<?php

namespace App\Task;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {

        $router->add('GET', '/task/view/:task_id', \App\Task\Controller::class, 'view');
        $router->add('GET', '/task/edit/:task_id', \App\Task\Controller::class, 'edit');
        $router->add('GET', '/task/add/:project_id', \App\Task\Controller::class, 'add');

        $router->add('POST', '/task/post', \App\Task\Controller::class, 'post');
        $router->add('POST', '/task/delete/:task_id', \App\Task\Controller::class, 'delete');
        $router->add('POST', '/task/put/:task_id', \App\Task\Controller::class, 'put');
        $router->add('POST', '/task/put/exceeded/today', \App\Task\Controller::class, 'move_exceeded_today');

    }
}
