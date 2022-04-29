<?php

declare(strict_types=1);

namespace App\Test;

use Pebble\Template;

class Controller
{
    /**
     * @route /worker
     * @verbs GET
     */
    public function worker()
    {
        Template::render('Test/worker.tpl.php');
    }

    /**
     * @route /translate
     * @verbs GET
     */
    public function translate()
    {
        Template::render('Test/translate.tpl.php');
    }
}
