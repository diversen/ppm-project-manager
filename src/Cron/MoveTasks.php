<?php

declare(strict_types=1);

namespace App\Cron;

use DateTime;
use DateTimeZone;
use App\AppUtils;
use App\Utils\DateUtils;
use App\Task\TaskModel;
use Exception;
use Pebble\ExceptionTrace;

class MoveTasks extends AppUtils
{
    private $date_utils;
    public function __construct()
    {
        parent::__construct();
        $this->date_utils = new DateUtils();
    }

    public function run()
    {
        $this->log->info("MoveTasks. Cron started");
        $users = $this->db->getAll('auth', ['verified' => 1, 'locked' => 0]);

        foreach ($users as $user) {
            $timezone = $this->date_utils->getUserTimezone($user['id']);

            if ($this->isMidnight($timezone)) {
                try {
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_TODAY);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_ONE_WEEK);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_FOUR_WEEKS);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH);
                    $this->moveTasks($user['id'], $timezone, TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH);
                } catch (Exception $e) {
                    $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);
                }
            }
        }
    }

    public function moveTasks($user_id, $timezone, $auto_move_constant)
    {
        $date_str = null;
        $day_name = $this->date_utils->getDateFormat('now - 1 day', $timezone, 'l');

        if ($auto_move_constant == TaskModel::AUTO_MOVE_TODAY) {
            $date_str = 'now';
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_ONE_WEEK) {
            $date_str = 'now + 7 day';
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FOUR_WEEKS) {
            $date_str = 'now + 24 days';
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FIRST_DAY_OF_NEXT_MONTH) {
            $date_str = 'first day of next month';
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_LAST_DAY_OF_THIS_MONTH) {
            $date_str = 'last day of this month';
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_FIRST_SAME_DAY_NEXT_MONTH) {
            $date_str = "first {$day_name} of next month";
        }
        if ($auto_move_constant == TaskModel::AUTO_MOVE_LAST_SAME_DAY_NEXT_MONTH) {
            $date_str = "last {$day_name} of this month";
        }

        $date_to_move = $this->date_utils->getDateFormat('now - 1 day', $timezone);

        $where = [
            'auth_id' => $user_id,
            'auto_move' => $auto_move_constant,
            'status' => TaskModel::TASK_OPEN,
            'end_date' => $date_to_move
        ];

        $tasks_to_move = $this->db->getAll('task', $where);
        foreach ($tasks_to_move as $task) {
            $days_diff = $this->getDaysDiff($task['begin_date'], $task['end_date']);
            $date_new_begin_date = $this->date_utils->getDateFormat($date_str, $timezone);
            $date_new_end_date = $this->date_utils->getDateFormat($date_new_begin_date . " + $days_diff days", $timezone);

            $update_values = [
                'begin_date' => $date_new_begin_date,
                'end_date' => $date_new_end_date
            ];

            $this->db->update('task', $update_values, ['id' => $task['id']]);
        }
    }


    /**
     * Method that checks if the hour is 00 (after midnight) from a timezone, e.g 'Europe/London'
     * Indicates that the day has changed
     */
    public function isMidnight($timezone)
    {
        $hour = $this->date_utils->getDateFormat('now', $timezone, 'H');
        if ($hour === '00') {
            return true;
        }
        return false;
    }

    /**
     * Method that takes date string and returns the difference in days
     * e.g. 2021-01-01 and 2021-01-03 returns 2
     */
    public function getDaysDiff($date1, $date2)
    {
        $date1 = new DateTime($date1);
        $date2 = new DateTime($date2);
        $diff = $date1->diff($date2);
        return $diff->days;
    }


    public function test()
    {
        $timezones = DateTimeZone::listIdentifiers();

        // Find a midnight timezone which is at midnight to test on
        print("Just past midnight in the follwing timezones:");
        foreach ($timezones as $timezone) {
            if ($this->isMidnight($timezone)) {
                echo "\n" . $timezone;
            }
        }

        // echo $this->getDaysDiff('2021-01-01 00:00:00', '2022-01-03 00:00:00');

        $this->run();
    }

    public function getCommand()
    {
        return [
            "usage" => "Will auto-move tasks if conditions are met",
            "options" => [
                "test" => "Run the test method"
            ],
        ];
    }

    /**
     * As CLI command
     */
    public function runCommand(\Diversen\ParseArgv $args)
    {
        if ($args->getOption('test')) {
            $this->test();
        } else {
            echo "Running MoveTasks cron\n";
            $this->run();
        }
    }
}
