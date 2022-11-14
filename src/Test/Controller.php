<?php

declare(strict_types=1);

namespace App\Test;

use Pebble\App\StdUtils;
use App\Cron\MoveTasks;

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
    
    /**
     * @route /test
     * @verbs GET
     */
    public function test(){
        $move_tasks = new MoveTasks();
        $users = $move_tasks->test();
        // var_dump($users);
    }
}
