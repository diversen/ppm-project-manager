<?php declare (strict_types = 1);

namespace Pebble;

use InvalidArgumentException;
use Pebble\Auth;
use Pebble\DBInstance;
use Pebble\Exception\ForbiddenException;

class ACL
{

    /**
     * Just set the auth id of the current user
     */
    public function __construct()
    {
        $this->auth_id = Auth::getInstance()->getAuthId();
    }

    /**
     * Gets the auth id
     */
    public function getAuthId(): string {
        return $this->auth_id;
    }

    /**
     * Check if there is a valid auth id
     */
    private function isAuthenticated()
    {

        if (!$this->auth_id) {
            return false;
        }
        return true;
    }

    /**
     * Check if user is authenticated or throw a ForbiddenException
     */
    public function isAuthenticatedOrThrow()
    {
        if (!$this->isAuthenticated()) {
            throw new ForbiddenException('You can not access this page');
        }
    }

    /**
     * Check if a user can access a page and output JSON error if not
     */
    public function isAuthenticatedOrJSONError(): bool
    {

        $response = [];

        try {
            $this->isAuthenticatedOrThrow();
        } catch (ForbiddenException $e) {
            $response['error'] = $e->getMessage();
            echo json_encode($response);
            return false;
        }

        return true;
    }

    /**
     * Create access right ['entity', 'entity_id', 'right', 'auth_id'] row in `acl` table
     */
    public function setAccessRights(array $access_rights)
    {
        $this->validateAccessAry($access_rights);
        $db = DBInstance::get();
        return $db->insert('acl', $access_rights);

    }

    /**
     * Remove access right ['entity', 'entity_id', 'right', 'auth_id'] from `acl` table
     * But it could also just be ['entity' => 'blog']
     */
    public function removeAccessRights(array $where_access_rights)
    {
        $db = DBInstance::get();
        return $db->delete('acl', $where_access_rights);
    }

    /**
     * Check for a valid access rights ary
     */
    protected function validateAccessAry(array $ary)
    {
        if (!isset($ary['entity'], $ary['entity_id'], $ary['right'], $ary['auth_id'])) {
            throw new InvalidArgumentException('Invalid data for ACL::validateAccessAry');
        }
    }

    /**
     * Check for valid access right ['entity', 'entity_id', 'right', 'auth_id'] in `acl` table
     */
    private function hasRights(array $where_access_rights): bool
    {
        $db = DBInstance::get();
        $row = $db->getOne('acl', $where_access_rights);
        if (empty($row)) {
            return false;
        }
        return true;
    }

    /**
     * Get rights as an array from a list, e.g. the string 'owner, user' returns ['owner', 'user']
     */
    private function getRightsArray(string $rights_str): array
    {
        $rights_array = explode(',', $rights_str);
        $ret_ary = [];
        foreach ($rights_array as $right) {
            $ret_ary[] = trim($right);
        }
        return $ret_ary;
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' in a list of rights.
     */
    protected function hasAccessRights(array $ary)
    {
        $this->validateAccessAry($ary);

        $rights_ary = $this->getRightsArray($ary['right']);
        foreach ($rights_ary as $right) {

            $ary['right'] = $right;

            if ($this->hasRights($ary)) {
                return true;
            }
        }
        return false;
    }

    /**
     * If a user has the right 'owner', then if we test for 'owner,admin', using e.g. hasAccessRightsOrThrow,
     * then he will be allowed. He just needs one 'right' in a list of rights.
     * 
     */
    public function hasAccessRightsOrThrow(array $ary)
    {

        $has_access_rights = $this->hasAccessRights($ary);
        if (!$has_access_rights) {
            throw new ForbiddenException('You can not access this page');
        }
        return true;
    }
}
