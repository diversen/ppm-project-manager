<?php

namespace App\Time;

use App\Project\ProjectModel;
use App\Task\TaskModel;
use App\Time\TimeModel;
use Pebble\Auth;
use Pebble\ACL;
use Pebble\JSON;

class Controller
{

    public function __construct()
    {
        $this->auth_id = Auth::getInstance()->getAuthId();
    }


    public function add($params) {

        
        $task = (new TaskModel())->getOne($params['task_id']);

        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $task['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);

        $project = (new ProjectModel())->getOne($task['project_id']);
        $time_rows = (new TimeModel())->getAll(['task_id' => $params['task_id']]);

        $time_vars = [
            'task' => $task,
            'project' => $project,
            'time_rows' => $time_rows,
            'auth_id' => $this->auth_id,
        ];

        \Pebble\Template::render('App/Time/views/time_add.tpl.php',
            $time_vars
        );
    }

    public function post() {

        $response['error'] = false;
        $response['project_redirect'] = '/project/view/' . $_POST['project_id'];

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $_POST['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];
    
            (new ACL())->hasAccessRightsOrThrow($access_ary);

            (new TimeModel())->create($_POST);
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);

    }

    public function delete($params) {

        $response['error'] = false;
        $response['post'] = $_POST;

        $time = (new TimeModel())->getOne(['id' =>$params['id']]);

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $time['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];
    
            (new ACL())->hasAccessRightsOrThrow($access_ary);
            (new TimeModel())->delete(['id' => $params['id']]);

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;
        }

        echo JSON::response($response);
        
    }
}
