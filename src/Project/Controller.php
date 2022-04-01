<?php

declare(strict_types=1);

namespace App\Project;

use App\Project\ProjectModel;
use Diversen\Lang;
use Exception;
use Pebble\Template;
use Pebble\JSON;
use Pebble\ExceptionTrace;
use App\AppMain;
use Pebble\Pager;
use JasonGrimes\Paginator;


class Controller
{
    private $app_acl;
    private $log;
    private $project_model;
    public function __construct()
    {
        $app_main = new AppMain();
        $this->app_acl = $app_main->getAppACL();
        $this->log = $app_main->getLog();
        $this->project_model = new ProjectModel();
    }

    /**
     * @route /project/inactive
     * @verbs GET
     */
    public function inactive()
    {
        $this->app_acl->isAuthenticatedOrThrow();
        
        $where = [
            'auth_id' => $this->app_acl->getAuthId(), 
            'status' => ProjectModel::PROJECT_CLOSED
        ];

        $project_count = $this->project_model->getNumProjects($where);
        $pager = new Pager($project_count, 10, 'page', false);

        $urlPattern = '/project?page=(:num)';
        
        $template_data = $this->project_model->getIndexData( $where, ['updated' => 'DESC'], [$pager->offset, $pager->limit]);
        $paginator = new Paginator($project_count, $pager->limit, $pager->page, $urlPattern);
        $template_data['paginator'] = $paginator;
        $template_data['title'] = Lang::translate('All projects');
        $template_data['total_time_human'] = 0;

        Template::render(
            'Project/views/project_index.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project
     * @verbs GET
     */
    public function active()
    {
        $this->app_acl->isAuthenticatedOrThrow();
        
        $where = [
            'auth_id' => $this->app_acl->getAuthId(), 
            'status' => ProjectModel::PROJECT_OPEN
        ];

        $project_count = $this->project_model->getNumProjects($where);
        $pager = new Pager($project_count, 10, 'page', false);

        $urlPattern = '/project?page=(:num)';
        
        $template_data = $this->project_model->getIndexData( $where, ['updated' => 'DESC'], [$pager->offset, $pager->limit]);

        $paginator = new Paginator($project_count, $pager->limit, $pager->page, $urlPattern);

        $template_data['paginator'] = $paginator;
        $template_data['title'] = Lang::translate('All projects');
        $template_data['inactive_link'] = 1;

        Template::render(
            'Project/views/project_index.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project/view/:project_id
     * @verbs GET
     */
    public function view(array $params)
    {
        $this->app_acl->authUserIsProjectOwner($params['project_id']);

        $template_data = $this->project_model->getViewData($params);
        $template_data['title'] = Lang::translate('View project');

        Template::render(
            'Project/views/project_view.tpl.php',
            $template_data
        );
    }

    /**
     * @route /project/add
     * @verbs GET
     */
    public function add()
    {
        $this->app_acl->isAuthenticatedOrThrow();

        $form_vars = [
            'title' => Lang::translate('Add project'),
        ];

        Template::render(
            'Project/views/project_add.tpl.php',
            $form_vars
        );
    }

    /**
     * @route /project/edit/:project_id
     * @verbs GET
     */
    public function edit($params)
    {
        $this->app_acl->authUserIsProjectOwner($params['project_id']);
        $project = $this->project_model->getOne($params['project_id']);

        $form_vars = [
            'title' => Lang::translate('Edit project'),
            'project' => $project,
        ];

        Template::render(
            'Project/views/project_edit.tpl.php',
            $form_vars
        );
    }

    /**
     * @route /project/post
     * @verbs POST
     */
    public function post()
    {
        $response['error'] = false;

        try {
            $this->app_acl->isAuthenticatedOrThrow();
            $_POST['auth_id'] = $this->app_acl->getAuthId();

            $this->project_model->create($_POST);
            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $this->log->error('Project.post.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        echo JSON::response($response);
    }

    /**
     * @route /project/put/:project_id
     * @verbs POST
     */
    public function put($params)
    {
        $response['error'] = false;

        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);
            $this->project_model->update($_POST, $params['project_id']);

            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $this->log->error('Project.put.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        echo JSON::response($response);
    }

    /**
     * @route /project/delete/:project_id
     * @verbs POST
     */
    public function delete($params)
    {
        $response['error'] = false;

        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);
            $this->project_model->delete($params['project_id']);
            $response['project_redirect'] = "/project";
        } catch (Exception $e) {
            $this->log->error('Project.delete.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        echo JSON::response($response);
    }

    /**
     * @route /project/tasks/:project_id
     * @verbs GET
     */
    public function tasks(array $params)
    {
        $data = ['error' => false];
        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);
            $data = $this->project_model->getTasksData($params);
        } catch (Exception $e) {
            $this->log->error('Project.tasks.exception', ['exception' => ExceptionTrace::get($e)]);
            $data['error'] = $e->getMessage();
        }

        Template::render(
            'Project/views/project_task_list.tpl.php',
            $data
        );
    }
}
