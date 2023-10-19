<?php

declare(strict_types=1);

namespace App\Account;

use Diversen\Lang;
use App\Google\GoogleUtils;
use App\Account\Controller;
use Exception;
use Pebble\Attributes\Route;

/**
 * Extends the normal controler to include google auth
 */
class ControllerExt extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route(path: '/account/signin')]
    public function signin(): void
    {
        if ($this->config->get('Account.google')) {
            $google_auth_url = $this->getGoogleAuthUrl();
        } else {
            $google_auth_url = null;
        }


        $context = ['title' => Lang::translate('Sign out')];
        if ($this->auth->isAuthenticated()) {
            echo $this->twig->render('account/signout.twig', $this->getContext($context));
        } else {
            $context = [
                'google_auth_url' => $google_auth_url,
                'title' => Lang::translate('Sign in'),
            ];
            echo $this->twig->render('account/signin_ext.twig', $this->getContext($context));
            
        }
    }

    private function getGoogleAuthUrl(): string
    {
        if (!$this->config->get('Account.google')) {
            return throw new Exception("Configurations file 'google.php' is not enabled", 500);
        }

        $google_helpers = new GoogleUtils();
        $google_auth_url = $google_helpers->getAuthUrl();
        return $google_auth_url;
    }
}
