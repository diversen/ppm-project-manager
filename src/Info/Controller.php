<?php

declare(strict_types=1);

namespace App\Info;

use App\AppUtils;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route /info/phpinfo
     * @verbs GET
     */
    #[Route(path: '/info/phpinfo', verbs: ['GET'])]
    public function phpinfo()
    {
        header("Content-Security-Policy: script-src: 'self' 'unsafe-inline'");
        $this->acl_role->hasRoleOrThrow(['right' => 'admin', 'auth_id' => $this->acl_role->getAuthId()]);

        $template_vars = [];
        $this->template->render(
            'Info/views/system.php',
            $template_vars
        );
    }
}
