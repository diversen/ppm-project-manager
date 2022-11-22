<?php

declare(strict_types=1);

namespace App\Notification;

use App\AppUtils;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
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
