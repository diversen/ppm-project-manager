<?php

declare(strict_types=1);

namespace App\Home;

use App\AppUtils;

class Controller extends AppUtils
{
    private $auth_id;
    public function __construct()
    {
        parent::__construct();
        $this->auth_id = $this->auth->getAuthId();
    }

    /**
     * @route /
     * @verbs GET
     */
    public function index()
    {
        if ($this->auth_id) {
            header('Location: /overview');
        }

        $data = ['title' => 'PPM'];
        $this->renderPage('Home/views/home.tpl.php', $data);
    }
}
