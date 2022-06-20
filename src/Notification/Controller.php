<?php

declare(strict_types=1);

namespace App\Notification;

use Pebble\Template;
use Pebble\App\StdUtils;

class Controller extends StdUtils
{

    public function __construct()
    {
        parent::__contruct();
    }
    /**
     * @route /notification
     * @verbs GET
     */
    public function notificaton()
    {
        $this->template->render('Notification/notification.tpl.php');
    }
}
