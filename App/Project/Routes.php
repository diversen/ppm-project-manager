<?php

namespace App\Project;

class Routes
{
    public static function setRoutes(\Pebble\Router $router)
    {
        
        // GET
        $router->add('GET', '/project', \App\Project\Controller::class, 'index');
        $router->add('GET', '/project/inactive', \App\Project\Controller::class, 'inactive');
        $router->add('GET', '/project/view/:project_id', \App\Project\Controller::class, 'view');
        $router->add('GET', '/project/add', \App\Project\Controller::class, 'add');
        $router->add('GET', '/project/edit/:project_id', \App\Project\Controller::class, 'edit');
        $router->add('GET', '/project/mail_test', \App\Project\Controller::class, 'test');
        $router->add('GET', '/project/users/:project_id', \App\Project\Controller::class, 'users');

        // POST
        $router->add('POST','/project/post', \App\Project\Controller::class, 'post');
        $router->add('POST','/project/put/:project_id', \App\Project\Controller::class, 'put');
        $router->add('POST','/project/delete/:project_id', \App\Project\Controller::class, 'delete');
        $router->add('POST','/project/user/put/:project_id', \App\Project\Controller::class, 'put_user');

    }
}


