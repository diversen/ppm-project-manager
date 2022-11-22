<?php

declare(strict_types=1);

namespace App\Test;

use App\AppUtils;
use Pebble\Exception\NotFoundException;
use App\Cron\MoveTasks;


class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
        if ($this->config->get('App.env') !== 'dev') {
            throw new NotFoundException();
        }
    }

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
     * @route /test/template/exception
     * @verbs GET
     */
    public function templateException() {
        $this->template->render('Test/template_exception.tpl.php');
    }
    
    /**
     * @route /test
     * @verbs GET
     */
    public function test()
    {
        $move_tasks = new MoveTasks();
        $users = $move_tasks->test();
        // var_dump($users);
    }
}
