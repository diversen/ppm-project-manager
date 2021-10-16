<?php

declare(strict_types=1);

namespace App\Project;

use Pebble\DBInstance;
use Diversen\Lang;
use App\Time\TimeModel;
use App\Task\TaskModel;
use Exception;
use App\AppCommon;

/**
 * Project related model
 */
class ProjectModel extends AppCommon
{

    const PROJECT_CLOSED = 0;
    const PROJECT_OPEN = 1;
    const PROJECT_DELETED = 2;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Validate project submission
     */
    private function validate(array $post)
    {
        if (!isset($post['title']) || !mb_strlen($post['title'])) {
            throw new Exception(Lang::translate('Title is required'));
        }
    }

    /**
     * Get all project from an where array
     */
    public function getAll(array $where)
    {
        $sql = 'SELECT * FROM project';
        $sql .= $this->db->getWhereSql($where);
        $sql .= 'ORDER by `updated` DESC';

        return $this->db->prepareFetchAll($sql, $where);
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
        $db = DBInstance::get();

        // Forcde update even when noting has been updated
        $post['updated'] = date('Y-m-d H:i:s');

        return $db->update('project', $post, ['id' => $project_id]);
    }


    /**
     * Get project index data from an $auth_id
     */
    public function getIndexData($auth_id)
    {

        $projects = $this->getAll(['auth_id' => $auth_id, 'status' => ProjectModel::PROJECT_OPEN]);
        $time_model = new TimeModel();

        $total_time = 0;

        // Active
        foreach ($projects as $key => $project) {
            $project_time = $time_model->sumTime(['project_id' => $project['id']]);
            $total_time += $project_time;
            $projects[$key]['project_time_total'] = $project_time;
            $projects[$key]['project_time_total_human'] = $time_model->minutesToHoursMinutes($project_time);
        }

        // Inactive
        $inactive = $this->getAll(['auth_id' => $auth_id, 'status' => ProjectModel::PROJECT_CLOSED]);
        foreach ($inactive as $key => $project) {
            $project_time = $time_model->sumTime(['project_id' => $project['id']]);
            $projects[$key]['project_time_total'] = $project_time;
            $inactive[$key]['project_time_total_human'] = $time_model->minutesToHoursMinutes($project_time);
            $total_time += $project_time;
        }

        $data = [
            'projects' => $projects,
            'inactive' => $inactive,
            'total_time' => $total_time,
            'total_time_human' => $time_model->minutesToHoursMinutes($total_time),
        ];

        return $data;
    }

    /**
     * Get project 'view' data from controller params
     */
    public function getViewData($params)
    {

        $project = $this->getOne($params['project_id']);
        $tasks = (new TaskModel())->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_OPEN]);
        $tasks_completed = (new TaskModel())->getAll(['project_id' => $project['id'], 'status' => TaskModel::TASK_CLOSED]);

        $timeModel = new TimeModel();
        $total = $timeModel->sumTime(['project_id' => $params['project_id']]);
        $total_time = $timeModel->minutesToHoursMinutes($total);

        $data = [
            'project' => $project,
            'tasks' => $tasks,
            'tasks_completed' => $tasks_completed,
            'project_time' => $total_time,
        ];

        return $data;
    }
}
