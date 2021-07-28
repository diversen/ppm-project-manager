<?php declare (strict_types = 1);

use Pebble\ACLRole;
use Pebble\Config;
use Pebble\DBInstance;
use Pebble\Auth;
use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLRoleTest extends TestCase
{

    private function dbConnect()
    {
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    }

    private static function cleanup()
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = new ACLRole();
        $acl->removeAccessRights(['entity' => 'test_entity']);
        
    }



    private function create() {

        $auth = Auth::getInstance();
        $res = $auth->create('some_email@test.dk', 'some_password');
        return $res;
    }

    private function verify() {
        $auth = Auth::getInstance();
        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);

        return $auth->verifyKey($row['random']);


    }


    public function createVerifyLoginUser() {
        $this->dbConnect();
        $this->cleanup();
        $this->create();
        $this->verify();

        $auth = new Auth();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);
        return $row;
    }


    public function test_setRole_removeRole() {

        $row = $this->createVerifyLoginUser();

        $acl = new ACLRole();
        
        // ['entity', 'entity_id', 'right', 'auth_id']
        $role = [
            'right' => 'admin',
            'auth_id' => $row['id']
        ];

        $res = $acl->setRole($role);
        $this->assertEquals(true, $res);

        $res = $acl->removeRole(['auth_id' => $row['id']]);
        $this->assertEquals(true, $res);

        
    }

    public function test_hasRoleOrThrow() {
        $row = $this->createVerifyLoginUser();

        $acl = new ACLRole();

        $role = [
            'right' => 'admin',
            'auth_id' => $row['id']
        ];

        $acl->setRole($role);

        $role = [
            'right' => 'admin', // This is still admin, so ok.
            'auth_id' => $row['id']
        ];

        // $this->expectException(ForbiddenException::class);
        $res = $acl->hasRoleOrThrow($role);
        $this->assertEquals(true, $res);

    }


    public function test_hasRoleOrThrow_throw() {
        $row = $this->createVerifyLoginUser();

        $acl = new ACLRole();

        $role = [
            'right' => 'admin', // This is 'admin'
            'auth_id' => $row['id']
        ];

        $acl->setRole($role);

        $role = [
            'right' => 'super', // This is 'super' now
            'auth_id' => $row['id']
        ];

        $this->expectException(ForbiddenException::class);
        $acl->hasRoleOrThrow($role);

    }

    public static function tearDownAfterClass(): void
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = new ACLRole();
        $acl->removeAccessRights(['entity' => 'NONE']);
    }
}
