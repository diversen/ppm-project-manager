<?php

declare(strict_types=1);

namespace App\Project;

use Diversen\Lang;
use Pebble\ExceptionTrace;
use Pebble\Pager;
use App\Utils\AppPaginationUtils;
use App\AppUtils;
use App\Exception\FormException;
use App\Project\ProjectModel;
use JasonGrimes\Paginator;
use Exception;

class Controller extends AppUtils 
{

    private $project_model;
    private $pagination_utils;

    public function __construct()
    {
        parent::__construct();
        $this->project_model = new ProjectModel();
        $this->pagination_utils = new AppPaginationUtils(['updated' => 'DESC', 'title' => 'DESC']);
    }

    private function getProjectData(array $where, array $order_by)
    {
        $project_count = $this->project_model->getNumProjects($where);
        $pager = new Pager($project_count, $this->config->get('App.pager_limit'));

        $template_data = $this->project_model->getIndexData($where, $order_by, [$pager->offset, $pager->limit]);
        $template_data['title'] = Lang::translate('All projects');
        $template_data['total_time_human'] = 0;

        if ($where['status'] === ProjectModel::PROJECT_OPEN) {
            $template_data['inactive_link'] = 1;
            $url_pattern = $this->pagination_utils->getPaginationURLPattern('/project');
        } else {
            $url_pattern = $this->pagination_utils->getPaginationURLPattern('/project/inactive');
        }

        $paginator = new Paginator($project_count, $pager->limit, $pager->page, $url_pattern);
        $paginator->setMaxPagesToShow(5);
        $template_data['paginator'] = $paginator;

        return $template_data;
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
            'status' => ProjectModel::PROJECT_CLOSED,
        ];

        $order_by = $this->pagination_utils->getOrderByFromRequest('project');
        $template_data = $this->getProjectData($where, $order_by);

        $this->template->render(
            'Project/views/index.tpl.php',
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
            'status' => ProjectModel::PROJECT_OPEN,
        ];

        $order_by = $this->pagination_utils->getOrderByFromRequest('project');
        $template_data = $this->getProjectData($where, $order_by);

        $this->template->render(
            'Project/views/index.tpl.php',
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

        $this->template->render(
            'Project/views/view.tpl.php',
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

        $this->template->render(
            'Project/views/add.tpl.php',
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
        $project = $this->project_model->getOne(['id' => $params['project_id']]);

        $form_vars = [
            'title' => Lang::translate('Edit project'),
            'project' => $project,
        ];

        $this->template->render(
            'Project/views/edit.tpl.php',
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
        } catch (FormException $e) {
            $response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Project.post.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
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
        } catch (FormException $e) {
            $response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Project.put.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
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

        $this->json->render($response);
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

        $this->template->render(
            'Project/views/task_list.tpl.php',
            $data
        );
    }
}
