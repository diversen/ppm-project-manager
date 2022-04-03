<?php

namespace App\Time;

use Exception;

use App\AppMain;
use App\Project\ProjectModel;
use App\Time\TimeModel;
use App\AppPaginationUtils;

use Pebble\Pager;
use Pebble\JSON;
use Pebble\ExceptionTrace;

use JasonGrimes\Paginator;

class Controller
{
    private $app_acl;
    private $log;
    private $pagination_utils;
    private $time_model;
    private $config;
    public function __construct()
    {
        $app_main = new AppMain();
        $this->app_acl = $app_main->getAppACL();
        $this->log = $app_main->getLog();
        $this->pagination_utils = new AppPaginationUtils(['begin_date' => 'DESC']);
        $this->time_model = new TimeModel();
        $this->config = $app_main->getConfig();
    }

    /**
     * @route /time/add/:task_id
     * @verbs GET
     */
    public function add($params)
    {
        $task = $this->app_acl->getTask($params['task_id']);
        $this->app_acl->authUserIsProjectOwner($task['project_id']);

        $project = (new ProjectModel())->getOne($task['project_id']);

        $where = ['task_id' => $task['id']];
        $total = $this->time_model->getNumTime($where);

        $this->config->get('App.pager_limit');
        $pager = new Pager($total, $this->config->get('App.pager_limit'));
        $order_by = $this->pagination_utils->getOrderByFromQuery();

        $url_pattern = $this->pagination_utils->getPaginationURLPattern('/time/add/' . $task['id']);
        $paginator = new Paginator($total, $pager->limit, $pager->page, $url_pattern);

        $time_rows = $this->time_model->getAll($where, $order_by, [$pager->offset, $pager->limit]);

        $time_vars = [
            'task' => $task,
            'project' => $project,
            'time_rows' => $time_rows,
            'paginator' => $paginator,
        ];

        \Pebble\Template::render(
            'Time/views/time_add.tpl.php',
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
            (new TimeModel())->create($post);
        } catch (\Exception $e) {
            $this->log->error('Time.post.error', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        $response['project_redirect'] = '/project/view/' . $task['project_id'];

        echo JSON::response($response);
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
        } catch (\Exception $e) {
            $this->log->error('Time.post.delete', ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = $e->getMessage();
        }

        echo JSON::response($response);
    }
}
