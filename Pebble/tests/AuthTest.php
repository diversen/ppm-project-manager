<?php declare (strict_types = 1);

use Pebble\Auth;
use Pebble\Config;
use Pebble\DBInstance;
use PHPUnit\Framework\TestCase;

$config_dir = dirname(__FILE__) . '/../../config';
Config::readConfig($config_dir);

final class AuthTest extends TestCase
{

    private function dbConnect()
    {
        $db_config = Config::getSection('DB');
        DBInstance::connect($db_config['url'], $db_config['username'], $db_config['password']);
    }

    private function cleanup()
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");
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

    public function test_authenticate()
    {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();

        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $rows[] = $row;

        $this->assertEquals(1, count($rows));

    }

    public function test_verify()
    {

        $this->dbConnect();
        $this->cleanup();
        $this->create();
        

        $auth = Auth::getInstance();


        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("0", $row['verified']);

        $res = $auth->isVerified($row['email']);
        $this->assertEquals(false, $res);

        $res = $this->verify();
        $this->assertEquals(true, $res);

        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);
        $this->assertEquals("1", $row['verified']);

    }

    public function test_create()
    {

        $this->dbConnect();
        $this->cleanup();

        $this->assertEquals($this->create(), true);

    }

    public function test_create_unique_email()
    {

        $this->expectException(PDOException::class);

        $this->dbConnect();
        $this->cleanup();

        $auth = Auth::getInstance();
        $auth->create('some_email@test.dk', 'some_password');
        $auth->create('some_email@test.dk', 'some_password');

    }



    public function test_getByWhere()
    {
        $this->dbConnect();
        $this->cleanup();
        $this->create();

        $auth = Auth::getInstance();

        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);

        $rows[] = $row;
        $this->assertEquals(1, count($rows));

    }

    public function test_updatePassword()
    {

        $this->dbConnect();
        $this->cleanup();
        $this->create();

        $auth = Auth::getInstance();
        

        $row = $auth->getByWhere(['email' => 'some_email@test.dk']);

        $auth->updatePassword($row['id'], 'new secure password');

        $row = $auth->authenticate('some_email@test.dk', 'some_password');

        $this->assertEquals([], $row);

        $row = $auth->authenticate('some_email@test.dk', 'new secure password');
        $rows[] = $row;
        $this->assertEquals(1, count($rows));

    }

    public function test_isAuthenticated() {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);


        $res = $auth->isAuthenticated();
        $this->assertEquals(true, $res);

    }

    public function test_getAuthId() {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);

        $res = $auth->getAuthId();
        $this->assertGreaterThan(0, (int)$res);
        

    }

    public function test_unlinkCurrentCookie() {

        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);

        $res = $auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $auth->unlinkCurrentCookie();
        $res = $auth->isAuthenticated();
        $this->assertEquals(false, $res);

    }

    public function test_unlinkAllCookies() {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setPermanentCookie($row);

        $res = $auth->isAuthenticated();
        $this->assertEquals(true, $res);

        $auth->unlinkAllCookies($row['id']);
        $res = $auth->isAuthenticated();
        $this->assertEquals(false, $res);
    }

    public function test_setSessionCookie() {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setSessionCookie($row);

        $res = $auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    public function test_setPermanentCookie() {
        $this->dbConnect();
        $this->cleanup();

        $this->create();
        $this->verify();

        $auth = Auth::getInstance();
        $row = $auth->authenticate('some_email@test.dk', 'some_password');
        $auth->setSessionCookie($row);

        $res = $auth->isAuthenticated();
        $this->assertEquals(true, $res);
    }

    public static function tearDownAfterClass(): void
    {
        $db = DBInstance::get();
        $db->prepareExecute("DELETE FROM `auth` WHERE `email` = :email", ['email' => 'some_email@test.dk']);
        $db->prepareExecute("DELETE FROM `auth_cookie`");
    }
}
