<?php

namespace App\Project;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {
        
        // GET
        $router->add('GET', '/project', \App\Project\ProjectController::class, 'index');
        $router->add('GET', '/project/inactive', \App\Project\ProjectController::class, 'inactive');
        $router->add('GET', '/project/view/:project_id', \App\Project\ProjectController::class, 'view');
        $router->add('GET', '/project/add', \App\Project\ProjectController::class, 'add');
        $router->add('GET', '/project/edit/:project_id', \App\Project\ProjectController::class, 'edit');
        $router->add('GET', '/project/mail_test', \App\Project\ProjectController::class, 'test');
        $router->add('GET', '/project/users/:project_id', \App\Project\ProjectController::class, 'users');

        // POST
        $router->add('POST','/project/post', \App\Project\ProjectController::class, 'post');
        $router->add('POST','/project/put/:project_id', \App\Project\ProjectController::class, 'put');
        $router->add('POST','/project/delete/:project_id', \App\Project\ProjectController::class, 'delete');
        $router->add('POST','/project/user/put/:project_id', \App\Project\ProjectController::class, 'put_user');

    }
}


