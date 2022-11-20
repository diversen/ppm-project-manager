<?php

declare(strict_types=1);

use Pebble\Service\AuthService;
use Pebble\Service\DBService;
use PHPUnit\Framework\TestCase;

/**
 * Authorize test case.
 * Server needs to be running for this test to work.
 * ./serv
 */
final class AuthTest extends TestCase
{

    private $user_email = 'test';
    private $user_email_2 = 'test_2';
    private $user_password = 'password';
    private $user_password_2 = 'password_2';
    private $db;
    private $auth;
    private $cookie_file = '/tmp/cookie.txt';
    private $project_id;
    private $task_id;
    private $time_id;

    private function curl($url, $post_params = [], $headers = []): CurlHandle
    {
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

    private function curlAssert($url, $post_params, $return_code)
    {
        $ch = $this->curl($url, $post_params);
        $result = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals($return_code, $httpcode);
        return $result;
    }

    protected function setUp(): void
    {

        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();

        // User 1
        $this->db->delete('auth', ['email' => $this->user_email]);
        $this->auth->createAndVerify($this->user_email, $this->user_password);

        // User 2
        $this->db->delete('auth', ['email' => $this->user_email_2]);
        $this->auth->createAndVerify($this->user_email_2, $this->user_password_2);

        $this->db->delete('project', ['title' => 'Test project']);
    }

    private function setProjectID()
    {
        $row = $this->db->getOne('project', ['title' => 'Test project']);
        $this->project_id = $row['id'];
    }

    private function setTaskID()
    {
        $row = $this->db->getOne('task', ['title' => 'Test task']);
        $this->task_id = $row['id'];
    }

    private function setTimeID()
    {
        $row = $this->db->getOne('time', ['note' => 'Time note']);
        $this->time_id = $row['id'];
    }

    public function test_project_routes(): void
    {
        $post_data = ['title' => 'Test project', 'note' => 'Test'];

        // User 1

        $this->curlAssert('/account/signin', [], 200);
        $this->curlAssert('/overview', [], 403);
        $this->curlAssert('/account/post_login', ['email' => $this->user_email, 'password' => $this->user_password,], 200);
        $this->curlAssert('/settings', [], 200);
        $this->curlAssert('/settings/put', ['timezone' => 'Africa/Accra', 'language' => 'de', 'theme_dark_mode' => 1], 200);
        $this->curlAssert('/overview', [], 200);
        $this->curlAssert('/project/add', [], 200);
        $this->curlAssert('/project/post', $post_data, 200);
        $this->setProjectID();
        $this->curlAssert("/project/view/$this->project_id", [], 200);
        $this->curlAssert("/project/edit/$this->project_id", [], 200);

        $put_data = ['title' => 'Updated',  'note' => 'Updated test',  'id' => $this->project_id, 'status' => '1'];
        $this->curlAssert("/project/put/$this->project_id", $put_data, 200);
        $this->curlAssert("/project/delete/$this->project_id", ['test' => 1], 200);
        $this->curlAssert("/project/edit/$this->project_id", [], 403);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 200);

        $this->setProjectID();
        $put_data = ['title' => 'Updated',  'note' => 'Updated test',  'id' => $this->project_id, 'status' => '1'];

        // Anon user. Login and check if user 1 project gives 403
        $this->curlAssert('/account/logout', [], 200);
        $this->curlAssert('/project/add', [], 403);
        $this->curlAssert('/settings', [], 403);
        $this->curlAssert('/settings/put', ['timezone' => 'Africa/Accra', 'language' => 'de', 'theme_dark_mode' => 1], 403);
        $this->curlAssert("/project/view/$this->project_id", [], 403);
        $this->curlAssert('/project/post', ['title' => 'Test project', 'note' => 'Test'], 403);
        $this->curlAssert("/project/edit/$this->project_id", [], 403);
        $this->curlAssert("/project/put/$this->project_id", $put_data, 403);
        $this->curlAssert("/project/delete/$this->project_id", ['test' => 1], 403);

        // User 2. Login and check if user 1 project gives 403
        $this->curlAssert('/account/post_login', ['email' => $this->user_email_2, 'password' => $this->user_password_2,], 200);
        $this->curlAssert('/project/add', [], 200);
        $this->curlAssert("/project/view/$this->project_id", [], 403);
        $this->curlAssert("/project/edit/$this->project_id", [], 403);
        $this->curlAssert("/project/put/$this->project_id", $put_data, 403);
        $this->curlAssert("/project/delete/$this->project_id", ['test' => 1], 403);
        $this->curlAssert('/account/logout', [], 200);

        // TASK and time routes

        // User 1.
        $this->curlAssert('/account/post_login', ['email' => $this->user_email, 'password' => $this->user_password,], 200);
        $this->curlAssert("/task/add/$this->project_id", [], 200);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 200);
        $this->setTaskID();
        $this->curlAssert("/task/view/$this->task_id", [], 200);
        $this->curlAssert("/task/edit/$this->task_id", [], 200);
        $this->curlAssert("/task/put/$this->task_id", ['title' => 'Test task', 'note' => 'Test'], 200);
        $this->curlAssert("/task/delete/$this->task_id", ['post' => 1], 200);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 200);
        $this->setTaskID();

        $this->curlAssert("/time/add/$this->task_id", [], 200);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 200);
        $this->setTimeID();
        $this->curlAssert("/time/delete/$this->time_id", ['post' => 1], 200);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 200);
        $this->setTimeID();

        $this->curlAssert('/account/logout', [], 200);

        // Anon user
        $this->curlAssert("/task/add/$this->project_id", [], 403);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 403);
        $this->curlAssert("/task/view/$this->task_id", [], 403);
        $this->curlAssert("/task/edit/$this->task_id", [], 403);
        $this->curlAssert("/task/put/$this->task_id", ['title' => 'Test task', 'note' => 'Test'], 403);
        $this->curlAssert("/task/delete/$this->task_id", ['post' => 1], 403);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 403);
        $this->curlAssert("/time/add/$this->task_id", [], 403);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 403);
        $this->curlAssert("/time/delete/$this->time_id", ['post' => 1], 403);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 403);
        $this->curlAssert('/account/logout', [], 200);

        // User 2
        $this->curlAssert('/account/post_login', ['email' => $this->user_email_2, 'password' => $this->user_password_2,], 200);
        $this->curlAssert("/task/add/$this->project_id", [], 403);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 403);
        $this->curlAssert("/task/view/$this->task_id", [], 403);
        $this->curlAssert("/task/edit/$this->task_id", [], 403);
        $this->curlAssert("/task/put/$this->task_id", ['title' => 'Test task', 'note' => 'Test'], 403);
        $this->curlAssert("/task/delete/$this->task_id", ['post' => 1], 403);
        $this->curlAssert("/task/post", ['title' => 'Test task', 'note' => 'Test', 'project_id' => $this->project_id], 403);
        $this->curlAssert("/time/add/$this->task_id", [], 403);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 403);
        $this->curlAssert("/time/delete/$this->time_id", ['post' => 1], 403);
        $this->curlAssert("/time/post", ['note' => 'Time note', 'minutes' => '3:00', 'task_id' => $this->task_id], 403);
        $this->curlAssert('/account/logout', [], 200);

        unlink($this->cookie_file);
    }


    protected function tearDown(): void
    {
    }
}
