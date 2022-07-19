<?php

declare(strict_types=1);

namespace App\Account;

use Diversen\Lang;
use Pebble\CSRF;

use App\Google\GoogleUtils;
use App\Account\Controller;
use stdClass;

/**
 * Extends the normal controler to include google auth
 */
class ControllerExt extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route /account/signin
     * @verbs GET
     */
    public function index(array $params, stdClass $obj)
    {
        $google_auth_url = $this->getGoogleAuthUrl();
        $template_vars = [];
        if ($this->auth->isAuthenticated()) {
            $this->template->render(
                'Account/views/signout.php'
            );
        } else {
            $template_vars = [
                'google_auth_url' => $google_auth_url,
                'title' => Lang::translate('Sign in'),
                'csrf_token' => (new CSRF())->getToken(),
            ];

            $this->template->render(
                'Account/views/signin_ext.php',
                $template_vars
            );
        }
    }

    private function getGoogleAuthUrl()
    {
        if (!$this->config->get('Account.google')) {
            return false;
        }

        $google_helpers = new GoogleUtils();
        $google_auth_url = $google_helpers->getAuthUrl();
        return $google_auth_url;
    }
}
