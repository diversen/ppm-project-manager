<?php declare (strict_types = 1);

namespace App\Task;

use App\Project\ProjectModel;
use App\Task\TaskModel;
use Pebble\Auth;
use Pebble\ACL;
use Pebble\JSON;

class Controller
{

    public function __construct()
    {

        $auth = Auth::getInstance();
        $this->auth_id = $auth->getAuthId();

    }


    public function add($params)
    {

        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $params['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);

        $project = (new ProjectModel())->getOne($params['project_id']);
        $task = ['begin_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')];
        $vars = [
            'project' => $project,
            'task' => $task,
        ];

        \Pebble\Template::render('App/Task/views/task_add.tpl.php',
            $vars
        );

    }

    public function edit($params)
    {

        $task = (new TaskModel())->getOne($params['task_id']);
        $project = (new ProjectModel())->getOne($task['project_id']);

        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $task['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);


        $vars = [
            'task' => $task,
            'project' => $project];

        \Pebble\Template::render('App/Task/views/task_edit.tpl.php',
            $vars
        );

    }

    public function view($params)
    {
        
        $task = (new TaskModel())->getOne($params['task_id']);
        $project = (new ProjectModel())->getOne($task['project_id']);

        $access_ary = [
            'entity' => 'project', 
            'entity_id' => $task['project_id'], 
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary);

        $vars = [
            'task' => $task,
            'project' => $project];

        \Pebble\Template::render('App/Task/views/task_view.tpl.php',
            $vars
        );

    }

    public function post()
    {

        $task_model = new TaskModel();
        $response['error'] = false;

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $_POST['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];
    
            (new ACL())->hasAccessRightsOrThrow($access_ary);

            $_POST['auth_id'] = $this->auth_id;
            $task_model->create($_POST);
            $response['project_redirect'] = "/project/view/" . $_POST['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;

        }

        echo JSON::response($response);
    }

    public function put($params)
    {

        $task = (new TaskModel())->getOne($params['task_id']);

        // 'now' updates a tasks begin_date to 'today'
        // Used on overview page
        // It unsets the 'begin_date' because if there is no date then 'today' date will be used. 
        // If 'end_date' < 'begin_date' then 'end_date' will be updated to the same as 'begin_date' 
        if (isset($_POST['now'])) {
            unset($task['begin_date']);    
            $_POST = $task;
        }

        $response['error'] = false;
        $response['post'] = $_POST;

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $task['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];
    
            (new ACL())->hasAccessRightsOrThrow($access_ary);

            (new TaskModel())->update($_POST, ['id' => $params['task_id']]);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            
        }

        echo JSON::response($response);
    }
    
    public function move_exceeded_today () {

        (new ACL())->isAuthenticatedOrThrow();

        $response['error'] = false;

        try {

            (new TaskModel())->setExceededUserTasksToday($this->auth_id);
            $response['project_redirect'] = '/overview';

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            
        }

        echo JSON::response($response);

    }

    public function delete($params)
    {

        $task = (new TaskModel())->getOne($params['task_id']);
        $response['error'] = false;

        try {

            $access_ary = [
                'entity' => 'project', 
                'entity_id' => $task['project_id'], 
                'right' => 'owner',
                'auth_id' => $this->auth_id,
            ];
    
            (new ACL())->hasAccessRightsOrThrow($access_ary);

            (new TaskModel())->delete($params['task_id']);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            
        }

        echo JSON::response($response);
    }
}
