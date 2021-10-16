<?php

namespace App;

use App\AppACL;
use Pebble\DBInstance;

class AppCommon
{

    public $app_acl;
    public $db;
    public function __construct()
    {
        $this->app_acl = new AppAcl();
        $this->db = DBInstance::get();
    }
}
