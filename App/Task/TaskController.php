<?php declare (strict_types = 1);

namespace App\Task;

use App\Project\ProjectModel;
use App\Task\TaskModel;
use Pebble\Auth;
use Pebble\ACL;
use Pebble\JSON;
use App\AppACL;

class TaskController
{

    public function __construct()
    {

        $auth = Auth::getInstance();
        $this->auth_id = $auth->getAuthId();

    }

    /**
     * GET
     */
    public function add($params)
    {

        $app_acl = new AppACL();
        $app_acl->authUserIsProjectOwner($params['project_id']);

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

    /**
     * GET
     */
    public function edit($params)
    {

        
        $app_acl = new AppACL();
        $task = $app_acl->getTask($params['task_id']);     
        $app_acl->authUserIsProjectOwner($task['project_id']);

        $project = (new ProjectModel())->getOne($task['project_id']);

        $vars = [
            'task' => $task,
            'project' => $project];

        \Pebble\Template::render('App/Task/views/task_edit.tpl.php',
            $vars
        );

    }

    /**
     * GET
     */
    public function view($params)
    {
        
        $app_acl = new AppACL();
        $task = $app_acl->getTask($params['task_id']);
        $app_acl->authUserIsProjectOwner($task['project_id']);

        $project = (new ProjectModel())->getOne($task['project_id']);

        $vars = [
            'task' => $task,
            'project' => $project];

        \Pebble\Template::render('App/Task/views/task_view.tpl.php',
            $vars
        );

    }
    
    /**
     * POST
     */
    public function post()
    {

        
        $response['error'] = false;

        try {

            $app_acl = new AppACL();
            $app_acl->authUserIsProjectOwner($_POST['project_id']);

            $_POST['auth_id'] = $this->auth_id;

            $task_model = new TaskModel();
            $task_model->create($_POST);
            $response['project_redirect'] = "/project/view/" . $_POST['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            $response['post'] = $_POST;

        }

        echo JSON::response($response);
    }

    /**
     * POST
     */
    public function put($params)
    {

        $response['error'] = false;
        $response['post'] = $_POST;

        try {

            $app_acl = new AppACL();
            $task = $app_acl->getTask($params['task_id']);
            $app_acl->authUserIsProjectOwner($task['project_id']);

            // 'now' updates a tasks begin_date to 'today'
            // Used on overview page
            // It unsets the 'begin_date' because if there is no date then 'today' date will be used. 
            // If 'end_date' < 'begin_date' then 'end_date' will be updated to the same as 'begin_date' 
            if (isset($_POST['now'])) {
                unset($task['begin_date']);    
                $_POST = $task;
            }

            (new TaskModel())->update($_POST, ['id' => $params['task_id']]);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            
        }

        echo JSON::response($response);
    }

    /**
     * POST
     */
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

    /**
     * POST
     */
    public function delete($params)
    {

        $response['error'] = false;

        try {

            $app_acl = new AppACL();
            $task = $app_acl->getTask($params['task_id']);
            $app_acl->authUserIsProjectOwner($task['project_id']);

            (new TaskModel())->delete($params['task_id']);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];

        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
            
        }

        echo JSON::response($response);
    }
}
