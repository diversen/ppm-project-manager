<?php declare(strict_types=1);

namespace App\Task;

use Pebble\DBInstance;
use App\Time\TimeModel;
use App\Cal;
use Diversen\Lang;
use Exception;
use DateTime;

class TaskModel
{

    const TASK_CLOSED = 0;
    const TASK_OPEN = 1;
    const TASK_DELETED = 2;

    /**
     * Sanitize a POST. If begin date is not set then begin_date will be set to current day
     * All date in database should be in UTC
     */
    private function sanitize($post)  {

        $cal = new Cal();
        if (!isset($post['begin_date'])) $post['begin_date'] = $cal->userDateToUTC();
        if (!isset($post['end_date'])) $post['end_date'] = $cal->userDateToUTC();
        if (!isset($post['status'])) $post['status'] = 1;

        if (new DateTime($post['end_date']) < new DateTime($post['begin_date']) ) {
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
            throw new Exception(Lang::translate('Title is required'));
        }

        if (new DateTime($post['end_date']) < new DateTime($post['begin_date']) ) {
            throw new Exception(Lang::translate('The end date can not be before the begin date'));
        }

        
    }

    public function getAll($where) {

        $timeModel = new TimeModel();

        $db = DBInstance::get();

        $sql = "SELECT * FROM task ";
        $sql.= $db->getWhereSql($where);
        $sql.= 'ORDER by begin_date DESC, priority DESC';

        $tasks = $db->prepareFetchAll($sql, $where);
        foreach ($tasks as $key => $task) {
            $total_task_time = $timeModel->sumTime(['task_id' => $task['id']]);
            $tasks[$key]['time_used'] = $timeModel->minutesToHoursMinutes($total_task_time); 
        }
        return $tasks;
    }

    public function getOne($id) {
        $time_model = new TimeModel();
        $task = DBInstance::get()->getOne('task', ['id' => $id]);
        
        $task_time_total = $time_model->sumTime(['task_id' => $task['id']]);
        $task['task_time_total'] = $time_model->minutesToHoursMinutes($task_time_total); 
        return $task;
    }


    public function delete($id) {
        return DBInstance::get()->delete('task', ['id' => $id]);
    }

    public function setExceededUserTasksToday(int $auth_id) {
        $today = (new Cal())->userDate('now', 'Y-m-d 00:00:00');
        $db = DBInstance::get();

        // If both begin_date AND end_date has been exceeded then we can move tasks to today
        $query = "SELECT * FROM `task` WHERE auth_id = :auth_id AND begin_date < :today AND end_date < :today AND status = 1 ";
        $rows = $db->prepareFetchAll($query, ['auth_id' => $auth_id, 'today' => $today, 'end_date' => $today]);

        foreach($rows as $row) {
            $db->update('task', ['begin_date' => $today, 'end_date' => $today], ['id' => $row['id']]);
        }
    }

    public function create($post)
    {
        $post = $this->sanitize($post);
        $this->validate($post);
        $db = DBInstance::get();
        $db->insert('task', $post);
        return $db->lastInsertId();

    }

    public function update($post, $where)
    {
        $post = $this->sanitize($post);
        $this->validate($post);
        $db = DBInstance::get();
        return $db->update('task', $post, $where);
    }
}
