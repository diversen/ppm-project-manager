<?php declare(strict_types=1);

namespace App;

use Pebble\ACL;
use Pebble\Auth;
use App\Task\TaskModel;
use App\Time\TimeModel;
use Diversen\Lang;
use Pebble\Exception\NotFoundException;

class AppACL
{

    public function __construct()
    {
        $this->auth_id = Auth::getInstance()->getAuthId();
    }

    /**
     * Checks if a current authenticated user is the owner of a project
     */
    public function authUserIsProjectOwner($project_id)
    {

        $access_ary = [
            'entity' => 'project',
            'entity_id' => $project_id,
            'right' => 'owner',
            'auth_id' => $this->auth_id,
        ];

        (new ACL())->hasAccessRightsOrThrow($access_ary, Lang::translate('You are not the owner of this project'));
    }

    /**
     * Get a task entry and checks if task entry exists
     */
    public function getTask($task_id)
    {
        
        // Check if there is a task
        $task = (new TaskModel())->getOne($task_id);
        if (empty($task)) {
            throw new NotFoundException(Lang::translate('There is no such task ID'));
        }

        return $task;
    }

    /**
     * Get a time entry and checks if if time entry exists
     */
    public function getTime($time_id)
    {
        $time = (new TimeModel())->getOne(['id' => $time_id]);
        if (empty($time)) {
            throw new \Exception(Lang::translate('There is no such time ID'));
        }
        return $time;
    }
}
