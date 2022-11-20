<?php

namespace App\Admin;

use Pebble\App\StdUtils;

class Controller extends StdUtils
{


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route /admin/phpinfo
     * @verbs GET
     */
    public function phpinfo()
    {

        header("Content-Security-Policy: script-src: 'self' 'unsafe-inline'");
        $this->acl_role->hasRoleOrThrow(['right' => 'admin', 'auth_id' => $this->acl_role->getAuthId()]);

        $template_vars = [];
        $this->template->render(
            'Admin/views/system.php',
            $template_vars
        );
    }
}
