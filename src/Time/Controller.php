<?php

namespace App\Time;

use Exception;

use App\Project\ProjectModel;
use App\Time\TimeModel;
use App\Exception\FormException;
use App\AppUtils;
use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use Pebble\ExceptionTrace;
use Pebble\Exception\JSONException;
use Diversen\Lang;
use Pebble\Attributes\Route;
use Pebble\Router\Request;

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

    #[Route(path: '/time/add/:task_id')]
    public function add(Request $request)
    {
        $task = $this->app_acl->isProjectOwnerGetTask($request->param('task_id'));
        $project = $this->project_model->getOne(['id' => $task['project_id']]);

        $where = ['task_id' => $task['id']];
        $total = $this->time_model->getNumTime($where);

        $pager = new Pager($total, $this->config->get('App.pager_limit'));
        $order_by = $this->pagination_utils->getOrderByFromQuery();
        $time_rows = $this->time_model->getAll($where, $order_by, [$pager->offset, $pager->limit]);

        $paginator = PaginationUtils::getPaginator(
            total_items: $total,
            items_per_page: $this->config->get('App.pager_limit'),
            current_page: $pager->page,
            url: '/time/add/' . $task['id'],
            default_order: ['begin_date' => 'DESC'],
            session_key : 'time',
        );

        $context = [
            'task' => $task,
            'project' => $project,
            'time_rows' => $time_rows,
            'paginator' => $paginator,
        ];

        $pagination_utils = new PaginationUtils(['begin_date' => 'DESC'], 'time'); 
        $sorting = $pagination_utils->getSortingURLPaths(['begin_date']);        
        $context['sorting'] = $sorting;

        $context = $this->getContext($context);
        echo $this->twig->render('time/add.twig', $context);
    }


    #[Route(path: '/time/post', verbs: ['POST'])]
    public function post()
    {
        try {
            $task = $this->app_acl->isProjectOwnerGetTask($_POST['task_id']);

            $post = $_POST;
            $post['project_id'] = $task['project_id'];

            $this->time_model->create($post);
            $this->flash->setMessage(Lang::translate('Time added'), 'success', ['flash_remove' => true]);

            $response['redirect'] = '/project/view/' . $task['project_id'];
            $this->json->renderSuccess($response);
        } catch (FormException $e) {
            throw new JSONException($e->getMessage());
        } catch (Exception $e) {
            $this->log->error('Time.post.error', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }

    #[Route(path: '/time/delete/:id', verbs: ['POST'])]
    public function delete(Request $request)
    {
        try {
            $this->app_acl->isProjectOwnerGetTime($request->param('id'));
            $this->time_model->delete(['id' => $request->param('id')]);
            $this->flash->setMessage(Lang::translate('Time deleted'), 'success', ['flash_remove' => true]);
            $this->json->renderSuccess();
        } catch (Exception $e) {
            $this->log->error('Time.post.delete', ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException($e->getMessage());
        }
    }
}
