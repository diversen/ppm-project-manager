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

    protected function setUp(): void {
        
        $this->auth = (new AuthService())->getAuth();
        $this->db = (new DBService())->getDB();

        $this->db->delete('auth', ['email' => $this->user_email]);
        $this->db->delete('auth_cookie', ['expires' => 0]);

        $this->auth->create($this->user_email, $this->user_password);
        $user = $this->db->getOne('auth', ['email' => $this->user_email]);
        $this->auth->verifyKey($user['random']);
        $user = $this->db->getOne('auth', ['email' => $this->user_email]);
        
    }

    public function test_setCookie(): void
    {

        // Does signin page exists
        $ch = CurlTest::curl('/account/signin');
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $httpcode);

        // Can over go to overview when no signed in
        $ch = CurlTest::curl('/overview');
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(403, $httpcode);

        // Can user login
        $ch = CurlTest::curl('/account/post_login', [
            'email' => $this->user_email,
            'password' => $this->user_password,
        ]);
        curl_exec($ch);

        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $httpcode);

        // Can user go to overview when signed in
        $ch = CurlTest::curl('/overview');
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $httpcode);

        // Go to add project
        $ch = CurlTest::curl('/project/add');
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $httpcode);


        // Add project
        $ch = CurlTest::curl('/project/post', ['title' => 'Test project', 'note' => 'Test project description']);
        curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $this->assertEquals(200, $httpcode);
    }

    
    public static function tearDownAfterClass(): void
    {
    }

}
