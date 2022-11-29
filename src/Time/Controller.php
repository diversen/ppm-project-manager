<?php

namespace App\Time;

use Exception;

use App\Project\ProjectModel;
use App\Time\TimeModel;
use App\Utils\AppPaginationUtils;
use App\Exception\FormException;
use App\AppUtils;
use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use Pebble\ExceptionTrace;

class Controller extends AppUtils
{
    private $pagination_utils;
    private $time_model;
    private $project_model;

    public function __construct()
    {
        parent::__construct();

        $this->pagination_utils = new PaginationUtils(['begin_date' => 'DESC'], 'time');
        $this->time_model = new TimeModel();
        $this->project_model = new ProjectModel();
    }

    /**
     * @route /time/add/:task_id
     * @verbs GET
     */
    public function add($params)
    {
        $task = $this->app_acl->getTask($params['task_id']);
        $this->app_acl->authUserIsProjectOwner($task['project_id']);

        $project = $this->project_model->getOne(['id' => $task['project_id']]);

        $where = ['task_id' => $task['id']];
        $total = $this->time_model->getNumTime($where);

        $pager = new Pager($total, $this->config->get('App.pager_limit'));
        $order_by = $this->pagination_utils->getOrderByFromQuery();
        $time_rows = $this->time_model->getAll($where, $order_by, [$pager->offset, $pager->limit]);
        
        $pagination_utils = new AppPaginationUtils();
        $paginator = $pagination_utils->getPaginator(
            total_items: $total,
            items_per_page: $this->config->get('App.pager_limit'),
            current_page: $pager->page,
            url: '/time/add/' . $task['id'],
            default_order: ['begin_date' => 'DESC'],
            session_key : 'time',

        );

        $time_vars = [
            'task' => $task,
            'project' => $project,
            'time_rows' => $time_rows,
            'paginator' => $paginator,
        ];

        $this->renderPage(
            'Time/views/add.tpl.php',
            $time_vars
        );
    }

    /**
     * @route /time/post
     * @verbs POST
     */
    public function post()
    {
        $response['error'] = false;

        try {
            $task = $this->app_acl->getTask($_POST['task_id']);
            $this->app_acl->authUserIsProjectOwner($task['project_id']);

            // POST time
            $post = $_POST;
            $post['project_id'] = $task['project_id'];
            $this->time_model->create($post);
        } catch (FormException $e) {
            $response['error'] = $e->getMessage();
        } catch (Exception $e) {
            $this->log->error('Time.post.error', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $response['project_redirect'] = '/project/view/' . $task['project_id'];

        $this->json->render($response);
    }

    /**
     * @route /time/delete/:id
     * @verbs POST
     */
    public function delete($params)
    {
        $response['error'] = false;

        try {
            $time = $this->app_acl->getTime($params['id']);
            $this->app_acl->authUserIsProjectOwner($time['project_id']);

            $this->time_model->delete(['id' => $params['id']]);
        } catch (Exception $e) {
            $this->log->error('Time.post.delete', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $this->json->render($response);
    }
}
