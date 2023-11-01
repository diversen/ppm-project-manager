<?php

declare(strict_types=1);

namespace App\Home;

use App\AppUtils;
use Pebble\Attributes\Route;
use Pebble\Path;

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
        $base_path = Path::getBasePath();
        $markdown_home = file_get_contents($base_path . '/src/templates/home/home.md');
        $context['markdown_home'] = $markdown_home;

        $context = $this->getContext($context);
        echo $this->twig->render('home/home.twig', $context);
    }
}
