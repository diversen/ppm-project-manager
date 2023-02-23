<?php

declare(strict_types=1);

namespace App\Notification;

use App\AppUtils;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route(path: '/notification', verbs: ['GET'])]
    public function notificaton()
    {
        $this->renderPage('Notification/notification.tpl.php');
    }
}
