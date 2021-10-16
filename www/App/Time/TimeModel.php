<?php

declare(strict_types=1);

namespace App\Time;

use App\AppCommon;
use Pebble\DBInstance;
use App\Task\TaskModel;

class TimeModel extends AppCommon
{

	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * Get total minutes from hh::mm
	 */
	private function totalMinutes($time)
	{
		$aux = explode(":", $time);
		if (count($aux) == 2) {
			return ($aux[0] * 60) + $aux[1];
		} else {
			return 0;
		}
	}

	/**
	 * Validate a post
	 */
	private function validate($post)
	{
		if ($this->totalMinutes($post['minutes']) == 0) {
			throw new \Exception('Not a valid time input');
		}
	}

	/**
	 * Sanitize a post before inserting into db
	 */
	private function sanitize($post)
	{
		$post['minutes'] = $this->totalMinutes($post['minutes']);
		return $post;
	}

	/**
	 * Convert minutes to hh:mm
	 */
	public function minutesToHoursMinutes($time, $format = '%02d:%02d')
	{
		if ($time < 1) {
			return sprintf($format, 0, 0);
		}
		$hours = floor($time / 60);
		$minutes = ($time % 60);
		return sprintf($format, $hours, $minutes);
	}

	/**
	 * Get total task time in minutes
	 */
	public function sumTime($where)
	{

		$sql = "SELECT SUM(minutes) as total_time FROM time ";
		$sql .= $this->db->getWhereSql($where);
		$row = $this->db->prepareFetch($sql, $where);

		$total = $row['total_time'];

		if ($total == NULL) {
			$total = '0';
		}

		return $total;
	}


	/**
	 * Get all `time` rows and attach minutes_hours (hh:mm) and 'note'
	 */
	public function getAll($where)
	{

		$time_rows = DBInstance::get()->getAll('time', $where);

		foreach ($time_rows as $key => $time) {
			$time_rows[$key]['minutes_hours'] = $this->minutesToHoursMinutes($time['minutes']);
			if (empty($time_rows[$key]['note'])) {
				$time_rows[$key]['note'] = 'No note';
			}
		}
		return $time_rows;
	}

	public function getOne($where)
	{
		return DBInstance::get()->getOne('time', $where);
	}

	/**
	 * Create a `time` row
	 */
	public function create(array $post)
	{

		$this->validate($post);
		$post['auth_id'] = $this->app_acl->getAuthId();
		$post = $this->sanitize($post);

		$this->db->beginTransaction();

		if (isset($post['close'])) {
			$task = new TaskModel();
			$task->close($post['task_id']);
			unset($post['close']);
		}

		$this->db->insert('time', $post);
		return $this->db->commit();
	}

	/**
	 * Delete a `time` row
	 */
	public function delete($post): bool
	{
		return $this->db->delete('time', $post);
	}


	/**
	 * Get a full week from an array of week days
	 */
	public function getWeekData(array $week_ts): array
	{

		$week_data = [];

		// iterate all dates in the week
		foreach ($week_ts as $day_ts => $date) {

			$sql = "

                SELECT t.*, p.status as project_status, p.title as project_title FROM task t 
                LEFT JOIN project p ON t.project_id = p.id 
                WHERE t.auth_id = :auth_id AND (:begin_date BETWEEN t.begin_date AND  t.end_date) AND t.status != 2 ORDER BY t.status DESC, t.priority DESC";

			$params = ['auth_id' => $this->app_acl->getAuthId(), 'begin_date' => $date];
			$week_data[$day_ts] = $this->db->prepareFetchAll($sql, $params);
		}

		foreach ($week_data as $day_ts => $day_data) {
			foreach ($day_data as $id => $task) {

				// All time added to task
				$task_time_total = $this->sumTime(['task_id' => $task['id']]);
				$week_data[$day_ts][$id]['task_time_total'] = $this->minutesToHoursMinutes($task_time_total);

				// All time added on day
				$task_time_on_day = $this->sumTime(['task_id' => $task['id'], 'begin_date' => date('Y-m-d H:i:s', $day_ts)]);
				$week_data[$day_ts][$id]['task_time_on_day'] = $this->minutesToHoursMinutes($task_time_on_day);

				// All time added to project
				$project_time = $this->sumTime(['project_id' => $task['project_id']]);
				$week_data[$day_ts][$id]['project_time_total'] = $this->minutesToHoursMinutes($project_time);
			}
		}

		return $week_data;
	}

	public function getWeekTimes(array $week_ts): array
	{

		$week_user_total = 0;
		foreach ($week_ts as $day_ts => $date) {
			$task_time_user_on_day = $this->sumTime(['begin_date' => $date, 'auth_id' => $this->app_acl->getAuthId()]);
			$week_user_total += $task_time_user_on_day;
			$week_user_day_times[$day_ts] = $this->minutesToHoursMinutes($task_time_user_on_day);
		}

		return [
			'week_user_day_times' => $week_user_day_times,
			'week_user_total' => $this->minutesToHoursMinutes($week_user_total),
		];
	}
}
