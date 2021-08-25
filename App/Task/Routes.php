<?php declare(strict_types=1);

namespace App\Task;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {

        $router->add('GET', '/task/view/:task_id', \App\Task\TaskController::class, 'view');
        $router->add('GET', '/task/edit/:task_id', \App\Task\TaskController::class, 'edit');
        $router->add('GET', '/task/add/:project_id', \App\Task\TaskController::class, 'add');

        $router->add('POST', '/task/post', \App\Task\TaskController::class, 'post');
        $router->add('POST', '/task/delete/:task_id', \App\Task\TaskController::class, 'delete');
        $router->add('POST', '/task/put/:task_id', \App\Task\TaskController::class, 'put');
        $router->add('POST', '/task/put/exceeded/today', \App\Task\TaskController::class, 'move_exceeded_today');

    }
}
