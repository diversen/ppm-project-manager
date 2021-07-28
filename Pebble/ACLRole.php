<?php declare (strict_types = 1);

namespace Pebble;

use Pebble\ACL;
use Pebble\DBInstance;
use Pebble\Exception\ForbiddenException;

class ACLRole extends ACL
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * create access rights ['NONE', '0', 'right', 'auth_id'] row in `acl` table
     * A role does not have a real entity BUT 'NONE' and the 'entity_id' is '0'
     */
    public function setRole(array $role)
    {

        $role['entity'] = 'NONE';
        $role['entity_id'] = '0';

        $this->validateAccessAry($role);
        $db = DBInstance::get();
        return $db->insert('acl', $role);
    }

    /**
     * Remove access rights ['right', 'auth_id'] from `acl` table
     * e.g. ['right => 'admin', 'auth_id' => '1234']
     */
    public function removeRole(array $role)
    {

        $role['entity'] = 'NONE';
        $role['entity_id'] = '0';

        $db = DBInstance::get();
        return $db->insert('acl', $role);
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' of a list of rights.
     */
    public function hasRoleOrThrow(array $role)
    {

        $role['entity'] = 'NONE';
        $role['entity_id'] = '0';

        $has_role = $this->hasAccessRights($role);
        if (!$has_role) {
            throw new ForbiddenException('You can not access this page.');
        }
        return true;
    }
}
