<?php

declare(strict_types=1);

namespace App\Task;

use Pebble\ExceptionTrace;
use App\Project\ProjectModel;
use App\Task\TaskModel;
use App\AppUtils;
use App\Exception\FormException;
use Diversen\Lang;
use Pebble\Exception\JSONException;
use Pebble\Attributes\Route;
use Pebble\Router\Request;
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

    #[Route(path: '/task/add/:project_id')]
    public function add(Request $request)
    {
        $task = ['begin_date' => date('Y-m-d'), 'end_date' => date('Y-m-d')];
        $context = ['task' => $task];

        if ($request->param('project_id') === 'project-unknown') {
            $this->app_acl->isAuthenticatedOrThrow();
            $context['projects'] = $this->project_model->getAll(['auth_id' => $this->auth->getAuthId()], ['title' => 'ASC']);
            $context['project'] = null;
        } else {
            $this->app_acl->isProjectOwner($request->param('project_id'));
            $project = $this->project_model->getOne(['id' => $request->param('project_id')]);
            $context['project'] = $project;
        }

        $context = $this->getContext($context);
        echo $this->twig->render('task/add.twig', $context);

    }

    #[Route(path: '/task/edit/:task_id')]
    public function edit(Request $request)
    {
        $task = $this->app_acl->isProjectOwnerGetTask($request->param('task_id'));
        $project = $this->project_model->getOne(['id' => $task['project_id']]);
        $projects = $this->project_model->getAll(['auth_id' => $this->app_acl->getAuthId()], ['title' => 'ASC']);

        $context = [
            'task' => $task,
            'project' => $project,
            'all_projects' => $projects,
        ];

        $context = $this->getContext($context);
        echo $this->twig->render('task/edit.twig', $context);
    }

    #[Route(path: '/task/view/:task_id')]
    public function view(Request $request)
    {
        $task = $this->app_acl->isProjectOwnerGetTask($request->param('task_id'));
        $project = $this->project_model->getOne(['id' => $task['project_id']]);

        $context = [
            'task' => $task,
            'project' => $project
        ];

        $context = $this->getContext($context);
        echo $this->twig->render('task/view.twig', $context);
    }

    #[Route(path: '/task/post', verbs: ['POST'])]
    public function post()
    {
        try {
            if ($_POST['project_id'] === '0') {
                throw new FormException(Lang::translate('Please choose a project'));
            }

            $this->app_acl->isProjectOwner($_POST['project_id']);
            $_POST['auth_id'] = $this->app_acl->getAuthId();

            $task_model = new TaskModel();
            $task_model->create($_POST);

            if (isset($_POST['session_flash'])) {
                $this->flash->setMessage(Lang::translate('Task created'), 'success', ['flash_remove' => true]);
            }

            $response['redirect'] = "/project/view/" . $_POST['project_id'];
            $response['message'] = Lang::translate('Task created');

            $this->json->renderSuccess($response);
        } catch (FormException $e) {
            throw new JSONException($e->getMessage());
        } catch (Exception $e) {
            $this->log->error('Task.post.error', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }

    #[Route(path: '/task/put/:task_id', verbs: ['POST'])]
    public function put(Request $request)
    {
        try {
            $task = $this->app_acl->isProjectOwnerGetTask($request->param('task_id'));

            // 'now' updates a tasks begin_date to 'today'
            // Used on overview page
            // It unsets the 'begin_date' because if there is no date then 'today' date will be used.
            // If 'end_date' < 'begin_date' then 'end_date' will be updated to the same as 'begin_date'
            if (isset($_POST['now'])) {
                unset($task['begin_date']);
                $_POST = $task;
            }

            // Is a new project chosen for the task
            $this->app_acl->isProjectOwner($_POST['project_id']);
            $this->task_model->update($_POST, ['id' => $request->param('task_id')]);
            $this->flash->setMessage(Lang::translate('Task updated'), 'success', ['flash_remove' => true]);

            $response['redirect'] = "/project/view/" . $task['project_id'];
            $this->json->renderSuccess($response);
        } catch (FormException $e) {
            throw new JSONException($e->getMessage());
        } catch (Exception $e) {
            $this->log->error('Task.put.error', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }

    #[Route(path: '/task/put/exceeded/today', verbs: ['POST'])]
    public function move_exceeded_today()
    {
        try {
            $this->app_acl->isAuthenticatedOrThrow();
            $this->task_model->setExceededUserTasksToday($this->app_acl->getAuthId());
            $this->flash->setMessage(Lang::translate('Tasks moved to today'), 'success', ['flash_remove' => true]);
            $response['redirect'] = '/overview';
            $this->json->renderSuccess($response);
        } catch (Exception $e) {
            $this->log->error('Task.post.error', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }

    #[Route(path: '/task/delete/:task_id', verbs: ['POST'])]
    public function delete(Request $request)
    {
        try {
            $task = $this->app_acl->isProjectOwnerGetTask($request->param('task_id'));
            $this->task_model->delete($request->param('task_id'));
            $this->flash->setMessage(Lang::translate('Task deleted'), 'success', ['flash_remove' => true]);
            $response['redirect'] = "/project/view/" . $task['project_id'];
            $this->json->renderSuccess($response);
        } catch (Exception $e) {
            $this->log->error('Task.post.delete', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }
}
