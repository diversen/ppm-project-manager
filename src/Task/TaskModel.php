<?php

declare(strict_types=1);

namespace App\Task;

use Pebble\App\AppBase;
use Pebble\Exception\NotFoundException;

use App\Time\TimeModel;
use App\Exception\FormException;
use App\Utils\DateUtils;
use Diversen\Lang;
use DateTime;

class TaskModel
{
    public const TASK_CLOSED = 0;
    public const TASK_OPEN = 1;
    public const TASK_DELETED = 2;

    public const AUTO_MOVE_NONE = 0;
    public const AUTO_MOVE_ONE_WEEK = 1;
    public const AUTO_MOVE_FOUR_WEEKS = 2;
    public const AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH = 3;
    public const AUTO_MOVE_LAST_DAY_OF_THIS_MONTH = 4;
    public const AUTO_MOVE_TODAY = 5;

    public const AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH = 6;
    public const AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH = 7;

    public const PRIORITY_URGENT = 4;
    public const PRIORITY_HIGH = 3;
    public const PRIORITY_NORMAL = 2;
    public const PRIORITY_MINOR = 1;
    public const PRIORITY_LOW = 0;
    

    private $db;
    private $time_model;
    private $date_utils;

    public function __construct()
    {
        $app_base = new AppBase();
        $this->db = $app_base->getDB();
        $this->time_model = new TimeModel();
        $this->date_utils = new DateUtils();
    }

    /**
     * Sanitize a POST. If begin date is not set then begin_date will be set to current day
     * All date in database should be in UTC
     */
    private function sanitize($post)
    {

        if (!isset($post['begin_date'])) {
            $post['begin_date'] = $this->date_utils->getUserDateFromUTC('now', 'Y-m-d 00:00:00');
        }
        if (!isset($post['end_date'])) {
            $post['end_date'] = $this->date_utils->getUserDateFromUTC('now', 'Y-m-d 00:00:00');
        }
        if (!isset($post['status'])) {
            $post['status'] = 1;
        }

        if (new DateTime($post['end_date']) < new DateTime($post['begin_date'])) {
            $post['end_date'] = $post['begin_date'];
        }

        // This is not pretty.Find better solution
        unset($post['task_time_total']);

        return $post;
    }

    /**
     * Sanitize. Only needed field is title
     */
    private function validate($post)
    {
        if (!mb_strlen($post['title'])) {
            throw new FormException(Lang::translate('Title is required'));
        }

        if (new DateTime($post['end_date']) < new DateTime($post['begin_date'])) {
            throw new FormException(Lang::translate('The end date can not be before the begin date'));
        }
    }

    public function getNumRows(array $where)
    {
        $num_rows = $this->db->getTableNumRows('task', 'id', $where);
        return $num_rows;
    }

    public function getAll(array $where, array $limit = [])
    {
        $order_by = ['updated' => 'DESC', 'priority' => 'ASC'];
        $tasks = $this->db->getAllQuery('SELECT * FROM task', $where, $order_by, $limit);
        foreach ($tasks as $key => $task) {
            $total_task_time = $this->time_model->sumTime(['task_id' => $task['id']]);
            $tasks[$key]['time_used'] = $this->time_model->minutesToHoursMinutes($total_task_time);
        }
        return $tasks;
    }

    public function getOne($where)
    {
        $task = $this->db->getOne('task', $where);

        if (empty($task)) {
            throw new NotFoundException(Lang::translate('There is no such task'));
        }

        $task_time_total = $this->time_model->sumTime(['task_id' => $task['id']]);
        $task['task_time_total'] = $this->time_model->minutesToHoursMinutes($task_time_total);
        return $task;
    }


    public function delete($id)
    {
        return $this->db->delete('task', ['id' => $id]);
    }

    public function setExceededUserTasksToday(string $auth_id)
    {
        $today = $this->date_utils->getUserDateFromUTC('now', 'Y-m-d 00:00:00');

        // If both begin_date AND end_date has been exceeded then we can move tasks to today
        $query = "SELECT * FROM `task` WHERE auth_id = :auth_id AND begin_date < :today AND end_date < :today AND status = 1 ";
        $rows = $this->db->prepareFetchAll($query, ['auth_id' => $auth_id, 'today' => $today, 'end_date' => $today]);

        foreach ($rows as $row) {
            $this->db->update('task', ['begin_date' => $today, 'end_date' => $today], ['id' => $row['id']]);
        }
    }

    public function create($post)
    {
        $post = $this->sanitize($post);
        $this->validate($post);

        $this->db->inTransactionExec(function () use ($post) {
            $this->db->insert('task', $post);
        });
    }

    public function update(array $post, array $where)
    {
        $post = $this->sanitize($post);
        $this->validate($post);

        $this->db->inTransactionExec(function () use ($post, $where) {
            $task = $this->getOne($where);
            
            
            $this->db->update('time', ['project_id' => $post['project_id']], ['task_id' => $task['id']]);
            $this->db->update('task', $post, $where);
        });
    }

    public function close(string $task_id)
    {
        return $this->db->update('task', ['status' => self::TASK_CLOSED], ['id' => $task_id]);
    }
}
