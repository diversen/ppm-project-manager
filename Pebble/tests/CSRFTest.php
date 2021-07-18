<?php

use Pebble\CSRF;
use PHPUnit\Framework\TestCase;

final class CSRFTest extends TestCase
{

    function test_getToken() {
        $token = (new CSRF())->getToken();
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    function test_validateToken() {
        $csrf = new CSRF();
        $token = $csrf->getToken();
        $_POST['csrf_token'] = $token;
        $this->assertEquals(true, $csrf->validateToken());
        
    
    }

}