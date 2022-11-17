<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;
use Pebble\App\AppBase;

class CurlTest {
    public static function curl($url, $post_params = [], $headers = []): CurlHandle {
        $ch = curl_init(); 

        if (!empty($post_params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        }

        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000' . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 3);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $cookies = '/tmp/cookie.txt';
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
        return $ch;
    }
}

final class AuthTest extends TestCase
{

    private $user_email = 'test';
    private $user_password = 'password';
    private $db;
    private $auth;
    private $project_id;

    private function curl($url, $post_params = [], $headers = []): CurlHandle {
        $ch = curl_init(); 

        if (!empty($post_params)) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
        }

        curl_setopt($ch, CURLOPT_URL, 'http://localhost:8000' . $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 3);
        curl_setopt($ch, CURLOPT_HEADER, true);

        $cookies = '/tmp/cookie.txt';
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookies); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies);
        return $ch;
    }

    private function curlAssert($url, $post_params, $return_code){
        $ch = CurlTest::curl($url, $post_params);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals($return_code, $httpcode);
    }

    protected function setUp(): void {
        
        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();

        $this->db->delete('auth', ['email' => $this->user_email]);
        $this->db->delete('auth_cookie', ['expires' => 0]);

        $this->auth->create($this->user_email, $this->user_password);
        $user = $this->db->getOne('auth', ['email' => $this->user_email]);
        $this->auth->verifyKey($user['random']);

        $this->db->delete('project', ['title' => 'Test project']);
        
    }

    public function getTestProjectID() {
        $row = $this->db->getOne('project', ['title' => 'Test project']);
        $project_id = $row['id'];
        return $project_id;
    }

    public function test_user_routes(): void
    {

        // Verified user
        $this->curlAssert('/account/signin', [], 200);
        $this->curlAssert('/overview', [], 403);
        $this->curlAssert('/account/post_login', ['email' => $this->user_email, 'password' => $this->user_password,], 200);
        $this->curlAssert('/overview', [], 200);
        $this->curlAssert('/project/add', [], 200);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 200);

        $project_id = $this->getTestProjectID();
        $this->curlAssert("/project/edit/$project_id", [], 200);
    
        $put_data = ['title' => 'Updated',  'note' => 'Updated test',  'id' => $project_id,'status' => '1'];
        $this->curlAssert('/project/put/' . $project_id, $put_data, 200);

        // Anon user
        $this->curlAssert('/account/logout', [], 200);
        $this->curlAssert('/project/add', [], 403);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 403);
        $this->curlAssert("/project/edit/$project_id", [], 403);
        $this->curlAssert('/project/put/' . $project_id, $put_data, 403);

    }
    
    protected function tearDown(): void
    {
    }

}
