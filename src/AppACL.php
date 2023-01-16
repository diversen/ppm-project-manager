<?php

declare(strict_types=1);

namespace App;

use Pebble\ACL;
use App\Task\TaskModel;
use App\Time\TimeModel;
use Diversen\Lang;
use Pebble\Exception\NotFoundException;
use Pebble\DB;
use Pebble\Exception\ForbiddenException;

/**
 * App spcific ACL
 */
class AppACL extends ACL
{
    public function __construct(DB $db, array $auth_cookie_settings)
    {
        parent::__construct($db, $auth_cookie_settings);
    }

    /**
     * Checks if current authenticated user is the owner of a project
     * @throws ForbiddenException
     */
    public function isProjectOwner($project_id)
    {
        $this->isAuthenticatedOrThrow(Lang::translate('You are not logged in. Please log in.'));

        $access_ary = [
            'entity' => 'project',
            'entity_id' => (int)$project_id,
            'right' => 'owner',
            'auth_id' => $this->getAuthId(),
        ];

        $this->hasAccessRightsOrThrow($access_ary, Lang::translate('You are not the owner of this project.'));
    }


    public function setProjectRights($project_id)
    {
        $access_rights = [
            'entity' => 'project',
            'entity_id' => (int)$project_id,
            'right' => 'owner',
            'auth_id' => $this->getAuthId(),
        ];

        return $this->setAccessRights($access_rights);
    }

    public function removeProjectRights($project_id)
    {
        $access_rights = ['entity' => 'project', 'entity_id' => $project_id];
        return $this->removeAccessRights($access_rights);
    }

    /**
     * Get a task entry and checks if task entry exists
     * @throws ForbiddenException
     */
    public function isProjectOwnerGetTask($task_id): array
    {
  
        $this->isAuthenticatedOrThrow(Lang::translate('You are not logged in. Please log in.'));
        
        // Check if there is a task
        $task = (new TaskModel())->getOne(['id' => (int)$task_id]);
        if (empty($task)) {
            throw new ForbiddenException(Lang::translate('You do not have access to this task ID'));
        }

        $this->isProjectOwner($task['project_id']);

        return $task;
    }

    /**
     * Get a time entry and checks if if time entry exists
     * @throws NotFoundException
     */
    public function isProjectOwnerGetTime($time_id): array
    {
        $time = (new TimeModel())->getOne(['id' => (int)$time_id]);
        if (empty($time)) {
            throw new ForbiddenException(Lang::translate('There is no such time ID'));
        }

        $this->isProjectOwner($time['project_id']);

        return $time;
    }
}
