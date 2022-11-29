<?php

declare(strict_types=1);

namespace App\Project;

use Diversen\Lang;
use Pebble\ExceptionTrace;
use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use App\Utils\AppPaginationUtils;
use App\AppUtils;
use App\Exception\FormException;
use App\Project\ProjectModel;
use Exception;

class Controller extends AppUtils
{
    private $project_model;
    private $pagination_utils;

    public function __construct()
    {
        parent::__construct();
        $this->project_model = new ProjectModel();
        $this->pagination_utils = new PaginationUtils(['p.updated' => 'DESC', 'p.title' => 'DESC'], 'project');
    }

    private function getProjectData(array $where, array $order_by)
    {
        $project_count = $this->project_model->getNumProjects($where);
        $pager = new Pager($project_count, $this->config->get('App.pager_limit'));

        $projects = $this->project_model->getIndexData($where, $order_by, [$pager->offset, $pager->limit]);

        $template_data['projects'] = $projects; 
        $template_data['title'] = Lang::translate('All projects');
        $template_data['total_time_human'] = 0;

        if ($where['status'] === ProjectModel::PROJECT_OPEN) {
            $template_data['inactive_link'] = 1;
            $url = '/project';
        } else {
            $url = '/project/inactive';
        }

        $pagination_utils = new AppPaginationUtils();
        $paginator = $pagination_utils->getPaginator(
            total_items: $project_count,
            items_per_page: $this->config->get('App.pager_limit'),
            current_page: $pager->page,
            url: $url,
            default_order: ['p.updated' => 'DESC', 'p.title' => 'DESC'],
            session_key : 'project',
            max_pages: 10,
            
        );

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

        $this->renderPage(
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
        

        $this->pagination_utils = new PaginationUtils(['p.updated' => 'DESC', 'p.title' => 'DESC'], 'project');
        
        $order_by = $this->pagination_utils->getOrderByFromRequest('project');
        $template_data = $this->getProjectData($where, $order_by);

        $this->renderPage(
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

        $this->renderPage(
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

        $this->renderPage(
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

        $this->renderPage(
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
        $response['error'] = true;

        try {
            $this->app_acl->isAuthenticatedOrThrow();
            $_POST['auth_id'] = $this->app_acl->getAuthId();

            $this->project_model->create($_POST);
            $response['redirect'] = "/project";
            $response['error'] = false;
        } catch (FormException $e) {
            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Project.post.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['message'] = $e->getMessage();
        }

        $this->json->render($response);
    }

    /**
     * @route /project/put/:project_id
     * @verbs POST
     */
    public function put($params)
    {
        $response['error'] = true;

        try {
            if (!isset($_POST['status'])) {
                $_POST['status'] = ProjectModel::PROJECT_CLOSED;
            }
            $this->app_acl->authUserIsProjectOwner($params['project_id']);
            $this->project_model->update($_POST, $params['project_id']);
            $response['error'] = false;
            $response['redirect'] = "/project";
        } catch (FormException $e) {
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Project.put.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = true;
            $response['message'] = $e->getMessage();
        }

        $this->json->render($response);
    }

    /**
     * @route /project/delete/:project_id
     * @verbs POST
     */
    public function delete($params)
    {
        $response['error'] = true;

        try {
            $this->app_acl->authUserIsProjectOwner($params['project_id']);
            $this->project_model->delete($params['project_id']);
            $response['error'] = false;
            $response['redirect'] = "/project";
        } catch (Exception $e) {
            $this->log->error('Project.delete.exception', ['exception' => ExceptionTrace::get($e)]);
            $response['message'] = $e->getMessage();
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
