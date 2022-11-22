<?php

declare(strict_types=1);

namespace App\Task;

use Pebble\ExceptionTrace;
use App\Project\ProjectModel;
use App\Task\TaskModel;
use App\AppUtils;
use App\Exception\FormException;

use Exception;

class Controller extends AppUtils
{
    private $project_model;
    private $task_model;

    public function __construct()
    {
        parent::__construct();
        $this->project_model = new ProjectModel();
        $this->task_model = new TaskModel();
    }

    /**
     * @route /task/add/:project_id
     * @verbs GET
     */
    public function add($params)
    {
        $this->app_acl->authUserIsProjectOwner($params['project_id']);

        $project = $this->project_model->getOne(['id' => $params['project_id']]);
        $task = ['begin_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')];
        $template_vars = [
            'project' => $project,
            'task' => $task,
        ];

        $this->template->render(
            'Task/views/task_add.tpl.php',
            $template_vars
        );
    }

    /**
     * @route /task/edit/:task_id
     * @verbs GET
     */
    public function edit($params)
    {
        $task = $this->app_acl->getTask($params['task_id']);
        $this->app_acl->authUserIsProjectOwner($task['project_id']);

        $project = $this->project_model->getOne(['id' => $task['project_id']]);
        $projects = $this->project_model->getAll(['auth_id' => $this->app_acl->getAuthId()], ['updated' => 'DESC']);

        $template_vars = [
            'task' => $task,
            'project' => $project,
            'all_projects' => $projects,
        ];

        $this->template->render(
            'Task/views/task_edit.tpl.php',
            $template_vars
        );
    }

    /**
     * @route /task/view/:task_id
     * @verbs GET
     */
    public function view($params)
    {
        $task = $this->app_acl->getTask($params['task_id']);
        $this->app_acl->authUserIsProjectOwner($task['project_id']);
        $project = $this->project_model->getOne(['id' => $task['project_id']]);

        $template_vars = [
            'task' => $task,
            'project' => $project
        ];

        $this->template->render(
            'Task/views/task_view.tpl.php',
            $template_vars
        );
    }

    /**
     * @route /task/post
     * @verbs POST
     */
    public function post()
    {
        $response['error'] = false;
        try {
            $this->app_acl->authUserIsProjectOwner($_POST['project_id']);

            $_POST['auth_id'] = $this->app_acl->getAuthId();

            $task_model = new TaskModel();
            $task_model->create($_POST);
            $response['project_redirect'] = "/project/view/" . $_POST['project_id'];

        } catch (FormException $e) {
            $response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Task.post.error', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
    }

    /**
     * @route /task/put/:task_id
     * @verbs POST
     */
    public function put($params)
    {
        $response['error'] = false;
        $response['post'] = $_POST;

        try {
            $task = $this->app_acl->getTask($params['task_id']);
            $this->app_acl->authUserIsProjectOwner($task['project_id']);

            // 'now' updates a tasks begin_date to 'today'
            // Used on overview page
            // It unsets the 'begin_date' because if there is no date then 'today' date will be used.
            // If 'end_date' < 'begin_date' then 'end_date' will be updated to the same as 'begin_date'
            if (isset($_POST['now'])) {
                unset($task['begin_date']);
                $_POST = $task;
            }

            // Is a new project chosen for the task
            $this->app_acl->authUserIsProjectOwner($_POST['project_id']);

            $this->task_model->update($_POST, ['id' => $params['task_id']]);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];
        } catch (FormException $e) {
            $response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Task.put.error', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
    }

    /**
     * @route /task/put/exceeded/today
     * @verbs POST
     */
    public function move_exceeded_today()
    {
        try {
            $response['error'] = false;
            $this->app_acl->isAuthenticatedOrThrow();
            $this->task_model->setExceededUserTasksToday($this->app_acl->getAuthId());
            $response['project_redirect'] = '/overview';
        } catch (Exception $e) {
            $this->log->error('Task.post.error', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
    }

    /**
     * @route /task/delete/:task_id
     * @verbs POST
     */
    public function delete($params)
    {
        $response['error'] = false;

        try {
            $task = $this->app_acl->getTask($params['task_id']);
            $this->app_acl->authUserIsProjectOwner($task['project_id']);

            $this->task_model->delete($params['task_id']);
            $response['project_redirect'] = "/project/view/" . $task['project_id'];
        } catch (Exception $e) {
            $this->log->error('Task.post.delete', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
    }
}
