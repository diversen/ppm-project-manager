<?php declare (strict_types = 1);

use Pebble\SMTP;
use PHPUnit\Framework\TestCase;

final class SMTPTest extends TestCase
{

    public function test_sendWithException() {

        $this->expectException(PHPMailer\PHPMailer\Exception::class);
        $smtp = new SMTP();
        $file = __DIR__ . '/file_test_files/a_file.txt';
        $smtp->sendWithException('test@test.dk', 'test mail', 'Hello world', '<p>Hello world</p>', [$file]);

    }
}
