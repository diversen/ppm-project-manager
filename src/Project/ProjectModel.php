<?php

declare(strict_types=1);

namespace App\Project;

use Pebble\URL;
use Pebble\Pager;
use Pebble\App\StdUtils;
use Diversen\Lang;
use App\Time\TimeModel;
use App\Task\TaskModel;
use App\Exception\FormException;
use App\AppMain;

/**
 * Project related model
 */
class ProjectModel extends StdUtils
{
    public const PROJECT_CLOSED = 0;
    public const PROJECT_OPEN = 1;
    public const PROJECT_DELETED = 2;

    private $app_acl;
    private $task_model;

    public function __construct()
    {
        parent::__contruct();
        $app_main = new AppMain();
        $this->app_acl = $app_main->getAppACL();
        $this->task_model = new TaskModel();
        $this->time_model = new TimeModel();
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
        return $this->db->getAllQuery('SELECT * FROM project', $params, $order, $limit);
    }

    /**
     * Get single project from ID
     */
    public function getOne($id)
    {
        return $this->db->getOne('project', ['id' => $id]);
    }

    /**
     * Delete a project from an ID
     */
    public function delete($id)
    {
        $this->db->beginTransaction();
        $this->db->delete('project', ['id' => $id]);
        $this->app_acl->removeProjectRights($id);
        return $this->db->commit();
    }

    /**
     * Create a project from a POST and return the project ID
     */
    public function create($post)
    {
        $this->validate($post);

        $this->db->beginTransaction();
        $this->db->insert('project', $post);
        $project_id = $this->db->lastInsertId();

        $this->app_acl->setProjectRights($project_id);
        $this->db->commit();

        return $project_id;
    }

    /**
     * Update a project from a POST and a ID
     */
    public function update($post, $project_id)
    {
        $this->validate($post);

        // Forcde update even when noting has been updated
        $post['updated'] = date('Y-m-d H:i:s');

        return $this->db->update('project', $post, ['id' => $project_id]);
    }

    public function getNumProjects(array $where): int
    {
        return $this->db->getTableNumRows('project', 'id', $where);
    }

    /**
     * Get project index data from an $auth_id
     */
    public function getIndexData(array $where, array $order = [], array $limit = [])
    {
        $projects = $this->getAll($where, $order, $limit);
        foreach ($projects as $key => $project) {
            $project_time = $this->time_model->sumTime(['project_id' => $project['id']]);
            $projects[$key]['project_time_total'] = $project_time;
            $projects[$key]['project_time_total_human'] = $this->time_model->minutesToHoursMinutes($project_time);
        }

        $data = [
            'projects' => $projects,
        ];

        return $data;
    }

    /**
     * Get project 'view' data from controller params
     */
    public function getViewData($params)
    {
        $project = $this->getOne($params['project_id']);
        $tasks = $this->task_model->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_OPEN]);
        $tasks_count = $this->task_model->getNumRows(['project_id' => $project['id'], 'status' => TaskModel::TASK_OPEN]);
        $tasks_completed = $this->task_model->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_CLOSED]);
        $tasks_completed_count = $this->task_model->getNumRows(['project_id' => $project['id'], 'status' => TaskModel::TASK_CLOSED]);

        $timeModel = new TimeModel();
        $total = $timeModel->sumTime(['project_id' => $params['project_id']]);
        $total_time = $timeModel->minutesToHoursMinutes($total);

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
     * Get tasks data ['tasks' => $tasks, 'more' => '/links/ot/more/tasks]
     */
    public function getTasksData(array $params): array
    {

        // Where
        $project_id = $params['project_id'];
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
}
