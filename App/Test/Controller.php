<?php declare(strict_types=1);

namespace App\Test;

use Pebble\Template;
use Pebble\DBInstance;


class Controller {
    public function index ($params) {
        \Pebble\JSON::response(['test' => 100, 'test2' => 'hello world']);
    }
}