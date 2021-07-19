<?php

use Pebble\File;
use PHPUnit\Framework\TestCase;

final class FileTest extends TestCase
{

    public function test_dirToArray() {

        $files = File::dirToArray(__DIR__ . '/file_test_files');
        $expect = ['a_file.txt'];
        $this->assertEquals($files, $expect);
    }
}
