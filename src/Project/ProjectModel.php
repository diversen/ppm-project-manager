<?php

declare(strict_types=1);

namespace App\Project;

use Pebble\URL;
use Pebble\Pager;
use Pebble\Pagination\PaginationUtils;
use Diversen\Lang;
use App\Time\TimeModel;
use App\Task\TaskModel;
use App\Exception\FormException;
use App\AppUtils;
use App\Utils\DateUtils;

/**
 * Project related model
 */
class ProjectModel extends AppUtils
{
    public const PROJECT_CLOSED = 0;
    public const PROJECT_OPEN = 1;
    public const PROJECT_DELETED = 2;

    private $task_model;
    private $date_utils;
    private $time_model;
    private $default_order_by = ['p.updated' => 'DESC', 'p.title' => 'DESC', 'project_time_total' => 'DESC'];
    private $pagination_utils;

    public function __construct()
    {
        parent::__construct();
        $this->task_model = new TaskModel();
        $this->time_model = new TimeModel();
        $this->date_utils = new DateUtils();
        $this->pagination_utils = new PaginationUtils(order_by_default: $this->default_order_by, session_key: 'project');
    }

    /**
     * Validate project submission
     */
    private function validate(array $post)
    {
        if (!isset($post['title']) || !mb_strlen($post['title'])) {
            throw new FormException(Lang::translate('Title is required'));
        }
    }

    /**
     * Get all project from an where array
     */
    public function getAll(array $params, array $order = [], array $limit = [])
    {
        $projects = $this->db->getAll('project', $params, $order, $limit);
        return $projects;
    }

    /**
     * Get single project from ID
     */
    public function getOne(array $where): array
    {
        return $this->db->getOne('project', $where);
    }

    /**
     * Delete a project from an ID
     */
    public function delete($id)
    {
        $this->db->inTransactionExec(function () use ($id) {
            $this->db->delete('project', ['id' => $id]);
            $this->app_acl->removeProjectRights($id);
        });
    }

    /**
     * Create a project from a POST and return the project ID
     */
    public function create($post)
    {
        $this->validate($post);

        // Store in UTC
        $post['updated'] = $this->date_utils->getDateFormat('now', 'UTC', 'Y-m-d H:i:s');
        $post['created'] = $this->date_utils->getDateFormat('now', 'UTC', 'Y-m-d H:i:s');

        $this->db->inTransactionExec(function () use ($post) {
            $this->db->insert('project', $post);
            $project_id = $this->db->lastInsertId();
            $this->app_acl->setProjectRights($project_id);
        });
    }

    /**
     * Update a project from a POST and a ID
     */
    public function update(array $post, string $project_id)
    {
        $this->validate($post);

        // Force an update
        $post['updated'] = $this->date_utils->getDateFormat('now', 'UTC', 'Y-m-d H:i:s');

        $this->db->inTransactionExec(function () use ($post, $project_id) {
            $this->db->update('project', $post, ['id' => $project_id]);
        });
    }

    public function getNumProjects(array $where): int
    {
        $num_projects = $this->db->getTableNumRows('project', 'id', $where);
        return $num_projects;
    }

    public function userHasProjects(int $user_id): bool
    {
        $where = [
            'auth_id' => $user_id
        ];
        $num = $this->getNumProjects($where);
        return $num > 0;
    }

    /**
     * Get project index data from an $auth_id
     */
    public function getIndexData(array $where, array $order_by = [], array $limit = [])
    {
        $sql = "
            SELECT p.*, SUM(t.minutes) AS project_time_total 
            FROM project p 
            LEFT JOIN time t ON p.id = t.project_id 
            WHERE p.auth_id = :auth_id AND p.status= :status GROUP by p.id ";

        $sql .= $this->db->getOrderBySql($order_by);
        $sql .= $this->db->getLimitSql($limit);

        $projects = $this->db->getStmt($sql, $where)->fetchAll();

        foreach ($projects as &$project) {
            if (!$project['project_time_total']) {
                $project['project_time_total'] = 0;
            }
            $project_time = (int)$project['project_time_total'];
            $project['project_time_total_human'] = $this->time_model->minutesToHoursMinutes($project_time);
            $updated = $this->date_utils->getUserDateFormatFromUTC($project['updated'], 'd/m/Y');
            $project['updated_human'] = $updated;
        }

        return $projects;
    }

    /**
     * Get project data for '/project/view/:id'
     */
    public function getViewData($project_id)
    {
        $project = $this->getOne(['id' => $project_id]);
        $tasks = $this->task_model->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_OPEN]);
        $tasks_count = $this->task_model->getNumRows(['project_id' => $project['id'], 'status' => TaskModel::TASK_OPEN]);
        $tasks_completed = $this->task_model->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_CLOSED]);
        $tasks_completed_count = $this->task_model->getNumRows(['project_id' => $project['id'], 'status' => TaskModel::TASK_CLOSED]);

        $time_model = new TimeModel();
        $total = $time_model->sumTime(['project_id' => $project_id]);
        $total_time = $time_model->minutesToHoursMinutes($total);

        $data = [
            'project' => $project,
            'tasks' => $tasks,
            'tasks_completed' => $tasks_completed,
            'project_time' => $total_time,
            'tasks_count' => $tasks_count,
            'tasks_completed_count' => $tasks_completed_count,
        ];

        return $data;
    }

    /**
     * Get tasks data ['tasks' => $tasks, 'more' => '/links/to/more/tasks]
     */
    public function getTasksData(string $project_id): array
    {
        $status = URL::getQueryPart('status');
        $where['project_id'] = $project_id;
        if (isset($status)) {
            $where['status'] = $status;
        }

        $total = $this->task_model->getNumRows($where);
        $pager = new Pager($total, $this->config->get('App.pager_limit'));

        $data['tasks'] = $this->task_model->getAll($where, [$pager->offset, $pager->limit]);
        if ($pager->has_next) {
            $data['next'] = "/project/tasks/$project_id?status=$status&page=$pager->next";
        }

        return $data;
    }

    public function getProjectData(array $where)
    {
        $context = [];
        
        // ORDER BY
        $order_by = $this->pagination_utils->getOrderBy();

        // Project count
        $project_count = $this->getNumProjects($where);
        
        // Get Pager
        $pager = $this->pagination_utils->getPager($project_count, $this->config->get('App.pager_limit'));

        // Get projects
        $projects = $this->getIndexData($where, $order_by, [$pager->offset, $pager->limit]);
        $context['projects'] = $projects;

        if ($where['status'] === self::PROJECT_OPEN) {
            $url = '/project';
        } else {
            $url = '/project/inactive';
        }

        // Paginator
        $paginator = $this->pagination_utils->getPaginator(
            total_items: $project_count,
            items_per_page: $this->config->get('App.pager_limit'),
            current_page: $pager->page,
            url: $url,
            max_pages: 10,
        );
        $context['paginator'] = $paginator;

        // Sorting
        $sorting = $this->pagination_utils->getSortingURLPaths();   
        $context['sorting'] = $sorting;

        return $context;
    }

    public function getNumProjectsOpen()
    {
        return $this->getNumProjects([
            'auth_id' => $this->app_acl->getAuthId(),
            'status' => ProjectModel::PROJECT_OPEN,
        ]);
    }

    public function getNumProjectsClosed()
    {
        return $this->getNumProjects([
            'auth_id' => $this->app_acl->getAuthId(),
            'status' => ProjectModel::PROJECT_CLOSED,
        ]);
    }
}
