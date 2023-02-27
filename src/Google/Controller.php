<?php

declare(strict_types=1);

namespace App\Google;

use App\AppUtils;
use Diversen\Lang;
use App\Google\GoogleUtils;
use App\TwoFactor\TwoFactorModel;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    private $login_redirect;
    private $logout_redirect;

    public function __construct()
    {
        parent::__construct();
        $this->login_redirect = $this->config->get('App.login_redirect');
        $this->logout_redirect = $this->config->get('App.logout_redirect');
    }


    #[Route(path: '/google/signout')]
    public function signout(): void
    {
        $this->auth->unlinkCurrentCookie();
        $location = "Location: " . $this->logout_redirect;
        header($location);
    }

    #[Route(path: '/google')]
    public function index(): void
    {
        if ($this->auth->isAuthenticated()) {
            $vars = [];
            $this->renderPage(
                'Google/sign_out.tpl.php',
                $vars
            );

            return;
        }

        $google_helpers = new GoogleUtils();
        $client = $google_helpers->getClient();

        // Code is set. Login or create a user based on verified email
        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if (isset($token['id_token'])) {
                $payload = $client->verifyIdToken($token['id_token']);
                if ($payload) {
                    $this->verifyPayload($payload);
                } else {
                    $this->flash->setMessage(Lang::translate('Error trying to signin using Google'), 'error');
                    header("Location: " . $this->login_redirect);
                    return;
                }
            } else {
                $this->flash->setMessage(Lang::translate('No ID token. Try again later'), 'error');
                header("Location: " . $this->login_redirect);
                return;
            }
        } else {
            $authUrl = $client->createAuthUrl();
            $vars = ['auth_url' => $authUrl];
            $this->renderPage(
                'Google/sign_in.tpl.php',
                $vars,
                ['raw' => true]
            );
        }
    }

    private function verifyPayload(array $payload): void
    {
        if (isset($payload['email_verified']) && isset($payload['email'])) {
            $row = $this->auth->getByWhere(['email' => $payload['email']]);
            if (empty($row)) {
                $this->createUser($payload);
            } else {
                $this->loginUser($row);
            }
            return;
        }

        $this->flash->setMessage(
            Lang::translate(
                'Error trying to signin using Google. You will need to give this application access to your email and the email needs to be verified'
            ),
            'error'
        );

        header("Location: " . $this->login_redirect);
    }

    private function createUser($payload): void
    {
        $password = bin2hex(random_bytes(32));
        $this->auth->create($payload['email'], $password);
        $this->log->info('Google.create_user.success', ['email' => $payload['email']]);

        // Verify
        $row = $this->auth->getByWhere(['email' => $payload['email']]);
        $this->auth->verifyKey($row['random']);
        $this->log->info('Google.create_user.verify', ['auth_id' => $row['id']]);

        // Signin and redirect
        $this->auth->setCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
        $this->log->info('Google.create_user.login', ['auth_id' => $row['id']]);

        $this->flash->setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);
        header("Location: " . $this->login_redirect);
    }

    private function loginUser($row): void
    {
        // Verify using two factor
        if ($this->config->get('TwoFactor.enabled')) {
            $two_factor = new TwoFactorModel();
            if ($two_factor->shouldRedirect($row['id'])) {
                header("Location: " . '/twofactor/verify');
                return;
            }
        }

        $this->auth->setCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
        $this->log->info('Google.login_user.success', ['auth_id' => $row['id']]);

        $this->flash->setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);
        header("Location: " . $this->login_redirect);
    }
}
