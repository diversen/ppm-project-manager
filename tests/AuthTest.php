<?php

declare(strict_types=1);

use Pebble\Service\Container;
use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;


final class AuthTest extends TestCase
{

    private $user_email = 'test';
    private $user_email_2 = 'test_2';
    private $user_password = 'password';
    private $user_password_2 = 'password_2'; 
    private $db;
    private $auth;
    private $cookie_file = '/tmp/cookie.txt';

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

        
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookie_file); 
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookie_file);
        return $ch;
    }

    private function curlAssert($url, $post_params, $return_code){
        $ch = $this->curl($url, $post_params);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals($return_code, $httpcode);
        return $result;
    }

    protected function setUp(): void {
        
        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();

        $this->db->delete('auth', ['email' => $this->user_email]);
        $this->db->delete('auth', ['email' => $this->user_email_2]);
        $this->db->delete('auth_cookie', ['expires' => 0]);

        // User 1
        $this->auth->create($this->user_email, $this->user_password);
        $user = $this->db->getOne('auth', ['email' => $this->user_email]);
        $this->auth->verifyKey($user['random']);

        // User 2
        $this->auth->create($this->user_email_2, $this->user_password_2);
        $user = $this->db->getOne('auth', ['email' => $this->user_email_2]);
        $this->auth->verifyKey($user['random']);

        $this->db->delete('project', ['title' => 'Test project']);
        
    }

    public function getTestProjectID() {
        $row = $this->db->getOne('project', ['title' => 'Test project']);
        $project_id = $row['id'];
        return $project_id;
    }

    public function test_project_routes(): void
    {
        $post_data = ['title' => 'Test project', 'note' => 'Test'];
        

        // User 1
        $this->curlAssert('/account/signin', [], 200);
        $this->curlAssert('/overview', [], 403);
        $this->curlAssert('/account/post_login', ['email' => $this->user_email, 'password' => $this->user_password,], 200);
        $this->curlAssert('/overview', [], 200);
        $this->curlAssert('/project/add', [], 200);
        $this->curlAssert('/project/post', $post_data, 200);

        $project_id = $this->getTestProjectID();
        $this->curlAssert("/project/edit/$project_id", [], 200);
        
        $put_data = ['title' => 'Updated',  'note' => 'Updated test',  'id' => $project_id,'status' => '1'];

        $this->curlAssert("/project/put/$project_id", $put_data, 200);
        $this->curlAssert("/project/delete/$project_id", ['test' => 1], 200);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 200);

        $project_id = $this->getTestProjectID();

        // Anon user. Login and check if user 1 project gives 403
        $this->curlAssert('/account/logout', [], 200);
        unlink($this->cookie_file);

        $this->curlAssert('/project/add', [], 403);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 403);
        $this->curlAssert("/project/edit/$project_id", [], 403);
        $this->curlAssert('/project/put/' . $project_id, $put_data, 403);
        $this->curlAssert("/project/delete/$project_id", ['test' => 1], 403);

        // User 2. Login and check if user 1 project gives 403
        $this->curlAssert('/account/post_login', ['email' => $this->user_email_2, 'password' => $this->user_password_2,], 200);
        $this->curlAssert('/project/add', [], 200);
        $this->curlAssert("/project/edit/$project_id", [], 403);
        $this->curlAssert('/project/put/' . $project_id, $put_data, 403);
        $this->curlAssert("/project/delete/$project_id", ['test' => 1], 403);


    }
    
    protected function tearDown(): void
    {
    }

}
