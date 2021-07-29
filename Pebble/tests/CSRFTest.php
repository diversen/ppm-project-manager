<?php declare (strict_types = 1);

use Pebble\CSRF;
use PHPUnit\Framework\TestCase;

final class CSRFTest extends TestCase
{

    public function test_getToken()
    {
        $token = (new CSRF())->getToken();
        $this->assertEquals($token, $_SESSION['csrf_token']);
    }

    public function test_validateToken()
    {
        $csrf = new CSRF();
        $token = $csrf->getToken();
        $_POST['csrf_token'] = $token;
        $this->assertEquals(true, $csrf->validateToken());

    }
}
