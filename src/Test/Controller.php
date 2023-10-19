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

    #[Route(path: '/test/worker')]
    public function worker()
    {
        echo $this->twig->render('test/worker.twig', $this->getContext());

    }

    #[Route(path: '/test/translate')]
    public function translate()
    {
        echo $this->twig->render('test/translate.twig', $this->getContext());
    }


    #[Route(path: '/test')]
    public function test()
    {
        $move_tasks = new MoveTasks();
        $users = $move_tasks->test();
        // var_dump($users);
    }
}
