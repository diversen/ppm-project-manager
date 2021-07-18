<?php

use Pebble\Captcha;
use PHPUnit\Framework\TestCase;

final class CaptchaTest extends TestCase
{

    public function test_outputImage() {

        $captcha = new Captcha();

        $captcha->getBuilder();

        $this->assertIsString($_SESSION['captcha_phrase']);

    }

    public function test_validate () {

        $captcha = new Captcha();

        $captcha->getBuilder();

        $phrase = $_SESSION['captcha_phrase'];

        $res = $captcha->validate('not correct');

        $this->assertEquals($res, false);
  
        $res = $captcha->validate($phrase);

        $this->assertEquals($res, true);

    }
}