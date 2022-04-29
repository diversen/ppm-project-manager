<?php

declare(strict_types=1);

namespace App\Notification;

use App\AppMain;

class Model
{
    private $db;
    public function __construct()
    {
        $app_main = new AppMain();
        $this->db = $app_main->getDB();
    }
}
