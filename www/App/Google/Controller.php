<?php declare (strict_types = 1);

namespace App\Google;

use \Pebble\Config;
use \Pebble\Auth;
use \Pebble\Flash;
use \Pebble\Random;
use \Diversen\Lang;
use \App\Google\GoogleUtils;

class Controller
{

    public function __construct () {
        $this->login_redirect = Config::get('App.login_redirect');
        $this->logout_redirect = Config::get('App.logout_redirect');
    }

    /**
     * @route /google/signout
     * @verbs GET
     */
    public function signout () {

        $auth = Auth::getInstance();
        $auth->unlinkCurrentCookie();
        $location = "Location: " . $this->logout_redirect;
        header($location);

    }

    /**
     * @route /google
     * @verbs GET
     */
    public function index()
    {

        $auth = Auth::getInstance();
        
        if ($auth->isAuthenticated()) {

            $twig_vars = [];
            \Pebble\Template::render('App/Google/sign_out.tpl.php',
                $twig_vars
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
                    Flash::setMessage(Lang::translate('Error trying to signin using Google'), 'error');
                    header("Location: " . $this->login_redirect);
                    return;
                }
            } else {
                Flash::setMessage(Lang::translate('No auth token. Try again later'), 'error');
                header("Location: " . $this->login_redirect);
                return;
            }
        } else {
            $authUrl = $client->createAuthUrl();
            $twig_vars = ['auth_url' => $authUrl];
            \Pebble\Template::render('App/Google/sign_in.tpl.php',
                $twig_vars, ['raw' => true] 
            );
        }
    }


    public function verifyPayload ($payload) {
        
        $auth = Auth::getInstance();
        if (isset($payload['email_verified']) && isset($payload['email'])) {
            $row = $auth->getByWhere(['email' => $payload['email']]);

            if (empty($row)) {

                // Create user with random password
                $password = bin2hex(random_bytes(32));
                $auth->create($payload['email'], $password);
                
                // Get user and verify user
                $row = $auth->getByWhere(['email' => $payload['email']]);
                $auth->verifyKey($row['random']);

                // Signin and set flash message
                $auth->setPermanentCookie($row);
                // Flash::setMessage('New user has been created from your google account. You are signed in.', 'success');

            } else {
                $auth->setPermanentCookie($row);
                Flash::setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true] );

            }


        } else {
            Flash::setMessage(Lang::translate('Error trying to signin using Google. You will need to give this application access to your email'), 'error');
        }

        header("Location: " . $this->login_redirect);
    }
}
