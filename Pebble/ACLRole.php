<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\ACL;
use Pebble\Exception\ForbiddenException;

class ACLRole extends ACL
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Sets a user role ['right => 'admin', 'auth_id' => '1234']
	 * `$aclr->setRole(['right' => 'admin', 'auth_id' => '1234'])`
     */
    public function setRole(array $role)
    {

        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        return $this->setAccessRights($role);
    }

    /**
     * Remove a role
     * `$aclr->removeRole(['right => 'admin', 'auth_id' => '1234'])`
     */
    public function removeRole(array $role)
    {

        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        return $this->removeAccessRights($role);
    }

    /**
     * Checks if a user has a role, e.g. ['right => 'admin', 'auth_id' => '1234']
	 * `$aclr->hasRoleOrThrow(['right => 'admin', 'auth_id' => '1234'])`
     */
    public function hasRoleOrThrow(array $role)
    {

        $role['entity'] = 'ROLE';
        $role['entity_id'] = '0';

        $has_role = $this->hasAccessRights($role);
        if (!$has_role) {
            throw new ForbiddenException('You can not access this page.');
        }
        return true;
    }
}
