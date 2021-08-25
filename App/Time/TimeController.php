<?php

namespace App\Time;

use App\AppACL;
use App\Project\ProjectModel;
use App\Time\TimeModel;
use Pebble\JSON;

class TimeController
{

    public function add($params) {

        $app_acl = new AppACL();
        $task = $app_acl->getTask($params['task_id']);            
        $app_acl->authUserIsProjectOwner($task['project_id']); 

        $project = (new ProjectModel())->getOne($task['project_id']);
        $time_rows = (new TimeModel())->getAll(['task_id' => $task['id']]);

        $time_vars = [
            'task' => $task,
            'project' => $project,
            'time_rows' => $time_rows,
        ];

        \Pebble\Template::render('App/Time/views/time_add.tpl.php',
            $time_vars
        );
    }

    public function post() {

        $response['error'] = false;
        
        try {

            $app_acl = new AppACL();
            $task = $app_acl->getTask($_POST['task_id']);            
            $app_acl->authUserIsProjectOwner($task['project_id']);    

            // POST time
            $post = $_POST;
            $post['project_id'] = $task['project_id'];
            (new TimeModel())->create($post);

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        $response['project_redirect'] = '/project/view/' . $task['project_id'];

        echo JSON::response($response);

    }

    public function delete($params) {

        $response['error'] = false;
        $response['post'] = $_POST;

        try {

            $app_acl = new AppACL();
            $time = $app_acl->getTime($params['id']);
            $app_acl->authUserIsProjectOwner($time['project_id']); 
    
            (new TimeModel())->delete(['id' => $params['id']]);

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
        
    }
}
