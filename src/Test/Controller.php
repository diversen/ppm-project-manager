<?php

declare(strict_types=1);

namespace App\Test;

use App\AppUtils;
use Pebble\Exception\NotFoundException;
use App\Cron\MoveTasks;
use PDO;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
        if ($this->config->get('App.env') !== 'dev') {
            throw new NotFoundException();
        }
    }

    #[Route(path: '/worker')]
    public function worker()
    {
        $this->template_utils->renderPage('Test/worker.tpl.php');
    }

    #[Route(path: '/translate')]
    public function translate()
    {
        $this->template->render('Test/translate.tpl.php');
    }

    #[Route(path: '/test/template/exception')]
    public function templateException()
    {
        $this->template->render('Test/template_exception.tpl.php');
    }

    #[Route(path: '/test')]
    public function test()
    {
        $move_tasks = new MoveTasks();
        $users = $move_tasks->test();
        // var_dump($users);
    }
}
