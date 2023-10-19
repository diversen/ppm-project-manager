<?php

declare(strict_types=1);

namespace App\Home;

use App\AppUtils;
use Pebble\Attributes\Route;
use Parsedown;

class Controller extends AppUtils
{
    private $auth_id;
    public function __construct()
    {
        parent::__construct();
        $this->auth_id = $this->auth->getAuthId();
    }

    #[Route(path: '/')]
    public function index()
    {
        if ($this->auth_id) {
            header('Location: /overview');
        }

        $context = ['title' => 'PPM'];

        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false);
        $markdown = $this->twig->render('home/home.md', $this->getContext($context));

        $context['markdown'] = $parsedown->parse($markdown);

        echo $this->twig->render('home/home.twig', $this->getContext($context));
    }
}
