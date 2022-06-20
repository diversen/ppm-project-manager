<?php

declare(strict_types=1);

namespace App\Test;

use Pebble\App\StdUtils;

class Controller extends StdUtils
{
    /**
     * @route /worker
     * @verbs GET
     */
    public function worker()
    {
        $this->template->render('Test/worker.tpl.php');
    }

    /**
     * @route /translate
     * @verbs GET
     */
    public function translate()
    {
        $this->template->render('Test/translate.tpl.php');
    }
}
