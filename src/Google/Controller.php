<?php

declare(strict_types=1);

namespace App\Google;

use Pebble\Flash;
use Pebble\SessionTimed;
use Diversen\Lang;
use App\Google\GoogleUtils;
use App\TwoFactor\TwoFactorModel;
use App\AppMain;

class Controller
{
    private $auth;
    private $config;
    public function __construct()
    {
        $app_main = new AppMain();
        $this->auth = $app_main->getAuth();
        $this->config = $app_main->getConfig();
        $this->login_redirect = $this->config->get('App.login_redirect');
        $this->logout_redirect = $this->config->get('App.logout_redirect');
        $this->flash = new Flash();
    }

    /**
     * @route /google/signout
     * @verbs GET
     */
    public function signout()
    {
        $this->auth->unlinkCurrentCookie();
        $location = "Location: " . $this->logout_redirect;
        header($location);
    }

    /**
     * @route /google
     * @verbs GET
     */
    public function index()
    {
        if ($this->auth->isAuthenticated()) {
            $vars = [];
            \Pebble\Template::render(
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
            \Pebble\Template::render(
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

        $this->flash->setMessage(Lang::translate('Error trying to signin using Google. You will need to give this application access to your email and the email needs to be verified'), 'error');
        header("Location: " . $this->login_redirect);
    }

    private function createUser($payload)
    {
        $password = bin2hex(random_bytes(32));
        $this->auth->create($payload['email'], $password);

        // Verify
        $row = $this->auth->getByWhere(['email' => $payload['email']]);
        $this->auth->verifyKey($row['random']);

        // Signin and redirect
        $this->auth->setPermanentCookie($row);
        $this->flash->setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);
        header("Location: " . $this->login_redirect);
    }

    private function loginUser($row)
    {

        // Verify using two factor
        if ($this->config->get('TwoFactor.enabled')) {
            $two_factor = new TwoFactorModel();
            if ($two_factor->isTwoFactorEnabled($row['id'])) {
                $session_timed = new SessionTimed();
                $session_timed->setValue('auth_id_to_login', $row['id'], $this->config->get('TwoFactor.time_to_verify'));
                $session_timed->setValue('keep_login', true, $this->config->get('TwoFactor.time_to_verify'));
                $this->flash->setMessage(Lang::translate('Verify your login.'), 'success', ['flash_remove' => true]);
                header("Location: " . '/2fa/verify');
                return;
            }
        }

        $this->auth->setPermanentCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
        $this->flash->setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);
        header("Location: " . $this->login_redirect);
        return;
    }
}
