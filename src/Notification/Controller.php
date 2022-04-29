<?php

declare(strict_types=1);

namespace App\Notification;

use Pebble\Template;

class Controller
{
    /**
     * @route /notification
     * @verbs GET
     */
    public function notificaton()
    {
        Template::render('Notification/notification.tpl.php');
    }
}
