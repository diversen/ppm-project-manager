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
    }

    public function test_create()
    {

        $this->dbConnect();
        $this->cleanup();

        $auth = new Auth();
        $res = $auth->create('some_email@test.dk', 'some_password');

        $this->assertEquals($res, true);

    }

    public function test_create_unique_email()
    {

        $this->expectException(PDOException::class);

        $this->dbConnect();
        $this->cleanup();

        $auth = new Auth();
        $auth->create('some_email@test.dk', 'some_password');
        $auth->create('some_email@test.dk', 'some_password');

    }

    public function test_verify()
    {

        $this->dbConnect();
        $this->cleanup();

        $auth = new Auth();

        $email = 'some_email@test.dk';

        $res = $auth->create($email, 'some_password');

        $row = $auth->getByEmail($email, 'some_email@test.dk');
        $this->assertEquals("0", $row['verified']);

        $res = $auth->isVerified($row['email']);
        $this->assertEquals(false, $res);

        $res = $auth->verifyKey($row['random']);
        $this->assertEquals(true, $res);

        $row = $auth->getByEmail($email);
        $this->assertEquals("1", $row['verified']);

    }
}
