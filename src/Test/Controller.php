<?php

declare(strict_types=1);

namespace App\Test;

use Pebble\Template;

class Controller
{

    /**
     * @route /test
     * @verbs GET
     */
    public function test() {
        Template::render('Test/test.tpl.php');
    }
}
