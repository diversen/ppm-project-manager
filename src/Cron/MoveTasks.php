<?php

declare(strict_types=1);

namespace App\Cron;

use DateTimeZone;
use Pebble\App\StdUtils;
use App\Utils\DateUtils;
use App\Task\TaskModel;
use Exception;
use Pebble\ExceptionTrace;

class MoveTasks extends StdUtils
{
    private $date_utils;
    public function __construct()
    {

        parent::__construct();
        $this->date_utils = new DateUtils();
    }

    public function run()
    {
        $users = $this->db->getAll('auth', ['verified' => 1, 'locked' => 0]);

        foreach ($users as $user) {

            $default_timezone = date_default_timezone_get();
            $this->log->debug('MoveTasks. Default timezone: ' . $default_timezone);
            $timezone = $this->date_utils->getUserTimezone($user['id']);

            if ($this->isMidnight($timezone)) {

                $date_time = new \DateTime('now', new \DateTimeZone($timezone));
                $date_format = $date_time->format('Y-m-d H:i:s');

                $this->log->debug("MoveTasks. Moving tasks for user {$user['id']} at {$date_format} in timezone {$timezone}");

                try {
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_TODAY);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_ONE_WEEK);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_FOUR_WEEKS);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH);
                    $this->moveTasks($user['id'], TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH);
                } catch (Exception $e) {
                    $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);
                }
            }
        }
    }

    public function moveTasks($user_id, $auto_move_constant)
    {

        $date_str = null;
        $day_name = $this->date_utils->getUTCDate('now - 1 day', 'l');

        if ($auto_move_constant == TaskModel::AUTO_MOVE_TODAY) $date_str = 'now';
        if ($auto_move_constant == TaskModel::AUTO_MOVE_ONE_WEEK) $date_str = 'now + 7 day';
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FOUR_WEEKS) $date_str = 'now + 24 days';
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH) $date_str = 'first day of next month';
        if ($auto_move_constant == TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH) $date_str = 'last day of this month';
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH) $date_str = "first {$day_name} of next month";
        if ($auto_move_constant == TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH) $date_str = "last {$day_name} of this month";

        $date_to_move = $this->date_utils->getUTCDate('now - 1 day');
        $date_new_date = $this->date_utils->getUTCDate($date_str);
        
        $update_values = [
            'begin_date' => $date_new_date,
            'end_date' => $date_new_date
        ];

        $where = [
            'auth_id' => $user_id,
            'auto_move' => $auto_move_constant,
            'status' => TaskModel::TASK_OPEN,
            'begin_date' => $date_to_move
        ];


        $tasks_to_move = $this->db->getAll('task', $where);
        $this->log->debug("MoveTasks.", ['tasks' => $tasks_to_move]);

        $this->db->update(
            'task',
            $update_values,
            $where,
        );
    }


    /**
     * Method that checks if the hour is 00 (after midnight) from a timezone, e.g 'Europe/London'
     * Indicates that the day has changed
     */
    public function isMidnight($timezone)
    {
        $date = new \DateTime('now', new \DateTimeZone($timezone));
        $hour = $date->format('H');

        if ($hour === '00') {
            return true;
        }
        return false;
    }

    public function test()
    {

        $date_str = 'first wednesday of next month';
        $date_new_date = $this->date_utils->getUTCDate($date_str);
        echo $date_new_date . "<br />";

        $date_str = 'last wednesday of next month';
        $date_new_date = $this->date_utils->getUTCDate($date_str);
        echo $date_new_date;
        // die;

        $timezones = DateTimeZone::listIdentifiers();

        // Find a midnight timezone which is at midnight to test on
        print("Output of DateTimeZone::listIdentifiers() function:");
        foreach ($timezones as $timezone) {
            if ($this->isMidnight($timezone)) {
                var_dump($timezone);
            }
        }

        $this->run();
    }
}
