<?php declare(strict_types=1);

namespace Pebble;

use Pebble\DBInstance;
use Pebble\Auth;
use Pebble\Exception\ForbiddenException;
use InvalidArgumentException;

class ACL {

    /**
     * Just set the auth id of the current user
     */
    public function __construct()
    {
        $this->auth_id = Auth::getInstance()->getAuthId();
    }

    /**
     * Check if there is a valid auth id
     */
    private function isAuthenticated() {

        if (!$this->auth_id) {
            return false;
        }
        return true;
    }

    /**
     * Check if user is authenticated or throw a ForbiddenException
     */
    public function isAuthenticatedOrThrow() {
        if (!$this->isAuthenticated()) {
            throw new ForbiddenException('You can not access this page');
        }
    }

    /**
     * Check if a user can access a page and output json error if not
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
     * create access rights ['entity', 'entity_id', 'right', 'auth_id'] row in `acl` table
     */
    public function setAccessRights(array $access_rights) {
        $this->validateAccessAry($access_rights);
        $db = DBInstance::get();
        return $db->insert('acl', $access_rights);
        
    }

    /**
     * Remove access rights ['entity', 'entity_id', 'right', 'auth_id'] from `acl` table
     */
    public function removeAccessRights($where_access_rights) {

        $db = DBInstance::get();
        return $db->delete('acl', $where_access_rights);
    }

    /**
     * Check for valid access rights ['entity', 'entity_id', 'right', 'auth_id'] in `acl` table
     */
    private function hasRights(array $where_access_rights): bool {
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
    private function getRightsArray(string $rights_str): array {
        $rights_array = explode(',', $rights_str);
        $ret_ary = [];
        foreach($rights_array as $right) {
            $ret_ary[] = trim($right);
        }
        return $ret_ary;
    }

    /**
     * Check for a valid access rights ary
     */
    private function validateAccessAry(array $ary) {
        if (!isset($ary['entity'], $ary['entity_id'], $ary['right'], $ary['auth_id'])) {
            throw new InvalidArgumentException('Invalid data for ACL::validateAccessAry');
        }
    }

    public function hasAccessRights(array $ary) {
        $this->validateAccessAry($ary);

        $rights_ary = $this->getRightsArray($ary['right']);
        foreach($rights_ary as $right) {

            $ary['right'] = $right;

            if ($this->hasRights($ary)) {
                return true;
            }
        }
        return false;
    }


    public function hasAccessRightsOrThrow(array $ary) {

        if (!$this->hasAccessRights($ary)) {
            throw new ForbiddenException('You can not access this page');
        }
    }
}
