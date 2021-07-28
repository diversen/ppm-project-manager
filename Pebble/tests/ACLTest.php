<?php declare (strict_types = 1);

use Pebble\ACL;
use Pebble\Config;
use Pebble\DBInstance;
use Pebble\Auth;
use Pebble\Exception\ForbiddenException;
use PHPUnit\Framework\TestCase;

final class ACLTest extends TestCase
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

        $acl = new ACL();
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

    public function test_isAuthenticatedOrThrow_throw() {

        $this->expectException(ForbiddenException::class);
        $acl = new ACL();
        $acl->isAuthenticatedOrThrow();

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

    public function test_isAuthenticatedOrThrow() {

        $this->createVerifyLoginUser();

        $acl = new ACL();
        
        $res = $acl->isAuthenticatedOrThrow();

        $this->assertEquals(null, $res);

    }


    public function test_isAuthenticatedOrJSONError_throw() {
        
        $this->cleanup();
        
        $acl = new ACL();
        $res = $acl->isAuthenticatedOrJSONError();
        $this->assertEquals(false, $res);
        $this->expectOutputString('{"error":"You can not access this page"}');

    }

    public function test_isAuthenticatedOrJSONError() {

        $this->createVerifyLoginUser();

        $acl = new ACL();
        $res = $acl->isAuthenticatedOrJSONError();
        $this->assertEquals(true, $res);

    }

    public function test_setAccessRights_removeAccessRights() {

        $row = $this->createVerifyLoginUser();

        $acl = new ACL();
        
        // ['entity', 'entity_id', 'right', 'auth_id']
        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read',
            'auth_id' => $row['id']
        ];

        $res = $acl->setAccessRights($rights);
        $this->assertEquals(true, $res);

        $res = $acl->removeAccessRights(['auth_id' => $row['id']]);
        $this->assertEquals(true, $res);

    }

    public function test_hasAccessRightsOrThrow() {
        $row = $this->createVerifyLoginUser();

        $acl = new ACL();

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read',
            'auth_id' => $row['id']
        ];

        $acl->setAccessRights($rights);

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read,write', // It has read so it is ok.
            'auth_id' => $row['id']
        ];

        $res = $acl->hasAccessRightsOrThrow($rights);
        $this->assertEquals(true, $res);


    }


    public function test_hasAccessRightsOrThrow_throw() {
        $row = $this->createVerifyLoginUser();

        $acl = new ACL();

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'read',
            'auth_id' => $row['id']
        ];

        $acl->setAccessRights($rights);

        $rights = [
            'entity' => 'test_entity',
            'entity_id' => 42,
            'right' => 'write', // THIS IS WRITE NOW
            'auth_id' => $row['id']
        ];

        $this->expectException(ForbiddenException::class);
        $acl->hasAccessRightsOrThrow($rights);

    }

    public static function tearDownAfterClass(): void
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");

        $acl = new ACL();
        $acl->removeAccessRights(['entity' => 'test_entity']);
    }
}
