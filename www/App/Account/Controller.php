<?php declare (strict_types = 1);

namespace App\Account;

use App\Account\Mail;
use App\Account\Validate;
use App\Google\GoogleUtils;
use Diversen\Lang;
use Pebble\Auth;
use Pebble\Captcha;
use Pebble\DBInstance;
use Pebble\Flash;
use Pebble\Config;
use Pebble\CSRF;
use Pebble\JSON;

class Controller
{

    /**
     * @route /account
     * @verbs GET
     */
    public function index()
    {

        $google_auth_url = $this->getGoogleAuthUrl();
        
        $auth = Auth::getInstance();
        if ($auth->isAuthenticated()) {
            $form_vars = ['title' => 'Signin'];
            \Pebble\Template::render('App/Account/views/signout.php',
                $form_vars
            );
        } else {

            $form_vars = [
                'google_auth_url' => $google_auth_url,
                'title' => 'Signin',
                'csrf_token' => (new CSRF())->getToken(),
            ];

            \Pebble\Template::render('App/Account/views/signin.php',
                $form_vars
            );
        }
    }

    private function getGoogleAuthUrl() {
        if (!Config::get('Account.google')) {
            return false;
        }

        $google_helpers = new GoogleUtils();
        $google_auth_url = $google_helpers->getAuthUrl();
        return $google_auth_url;
        
    }

    /**
     * @route /account/logout
     * @verbs GET
     */
    public function logout()
    {
        $auth = Auth::getInstance();
        if (isset($_GET['all_devices'])) {
            $auth_id = $auth->getAuthId();
            $auth->unlinkAllCookies($auth_id);
        } else {
            $auth->unlinkCurrentCookie();
        }


        $redirect = \Pebble\Config::get('App.logout_redirect');
        header("Location: $redirect");
        return;
    }

    /**
     * @route /account/signout
     * @verbs GET
     */
    public function signout()
    {
        \Pebble\Template::render('App/Account/views/signout.php',
            []
        );
        return;
    }

    /**
     * @route /account/post_login
     * @verbs POST
     */
    public function post_login()
    {

        usleep(100000);

        $response = ['error' => true, 'request' => $_POST];

        $csrf = new CSRF();
        if (!$csrf->validateToken()) {
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            echo JSON::response($response);
            return;
        }

        $auth = Auth::getInstance();
        $row = $auth->authenticate($_POST['email'], $_POST['password']);

        if (!empty($row)) {
            
            $response['error'] = false;
            $response['redirect'] = \Pebble\Config::get('App.login_redirect');
            Flash::setMessage('You are logged in', 'success');

            if (isset($_POST['keep_login'])) {
                // Set a cookie that will last for days
                $auth->setPermanentCookie($row);
            } else {
                // Set a cookie that is only valid until window is closed
                $auth->setSessionCookie($row);
            }

        } else {
            $response['message'] = Lang::translate('Wrong email or password. Or your account has not been activated. ');
        }

        echo JSON::response($response);

    }

    /**
     * @route /account/signup
     * @verbs GET
     */
    public function signup()
    {
        $form_vars = [
            'title' => Lang::translate('Signup'),
            'token' => (new CSRF())->getToken(),
        ];

        \Pebble\Template::render('App/Account/views/signup.php',
            $form_vars
        );
    }

    /**
     * @route /account/verify
     * @verbs GET
     */
    public function verify()
    {

        $key = $_GET['key'] ?? '';

        $auth = Auth::getInstance();
        $res = $auth->verifyKey($key);

        if ($res) {
            Flash::setMessage('Your account has been verified. You may log in', 'success');

        } else {
            Flash::setMessage('The key supplied has already been used', 'error');
        }

        header('Location: /account');

    }

    /**
     * @route /account/captcha
     * @verbs GET
     */
    public function captcha()
    {
        $captcha = new Captcha();
        $captcha->outputImage();
    }

    /**
     * @route /account/post_signup
     * @verbs POST
     */
    public function post_signup()
    {

        usleep(100000);

        $validate = new Validate();

        $response = $validate->signup();
        if ($response['error'] === true) {
            echo JSON::response($response);
            return;
        }

        $auth = Auth::getInstance();

        $db = DBInstance::get();
        $db->beginTransaction();

        $res = $auth->create($_POST['email'], $_POST['password']);
        if ($res) {

            // Create account without verification using mail
            if (Config::get('Account.no_email_verify')) {
                $db->update('auth', ['verified' => 1], ['email' => $_POST['email']]);
                $message = Lang::translate('Account has been created. You may log in');
                $mail_res = true;
            } else {
                $row = $validate->getByEmail($_POST['email']);
                $mail = new Mail();
                $mail_res = $mail->sendSignupMail($row);
                $message = Lang::translate('User created. An activation link has been sent to your email. Press the link and your account will be activated');
            }

            if (!$mail_res) {
                $db->rollback();
                $response['error'] = true;
                $response['message'] =  Lang::translate('Could not create account. Please try again another time');
            } else {
                $db->commit();

                Flash::setMessage(
                    $message,
                    'success'
                );
                $response['error'] = false;
                $response['redirect'] = '/account';
            }
        }

        echo JSON::response($response);
    }

    /**
     * @route /account/recover
     * @verbs GET
     */
    public function recover()
    {

        $token = (new CSRF())->getToken();

        $form_vars = [
            'title' => Lang::translate('Recover account'),
            'token' => $token,
        ];

        \Pebble\Template::render('App/Account/views/recover.php',
            $form_vars
        );

    }

    /**
     * @route /account/post_recover
     * @verbs POST
     */
    public function post_recover()
    {

        $captcha = new Captcha();
        $validate = new Validate();

        $response = ['error' => true];

        $row = $validate->getByEmail($_POST['email']);

        $csrf = new CSRF();
        if (!$csrf->validateToken()) {
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            echo JSON::response($response);
            return;
        }

        if (empty($row)) {
            $response['message'] = Lang::translate('No such email in our system');
            echo JSON::response($response);
            return;
        }

        if (!$captcha->validate($_POST['captcha'])) {
            $response['message'] = Lang::translate('Image text does not match');
            echo JSON::response($response);
            return;
        }

        if (!empty($row)) {

            $mail = new mail();
            $res = $mail->sendRecoverMail($row);
            if ($res) {

                Flash::setMessage(
                    Lang::translate('A notification email has been sent with instructions to create a new password'),
                    'success'
                );
                $response['error'] = false;
            } else {
                $response['message'] = Lang::translate('E-mail could not be sent. Try again later.');
            }
        }

        echo JSON::response($response);
        return;
    }

    /**
     * @route /account/newpassword
     * @verbs GET,POST
     */
    public function newpassword()
    {

        $key = $_GET['key'] ?? null;

        $auth = Auth::getInstance();
        $row = $auth->getByWhere(['random' => $key]);

        if (!empty($_POST) && !empty($row)) {

            $validate = new Validate();
            $response = $validate->passwords();

            if ($response['error'] === true) {
                Flash::setMessage($response['message'], 'error');
                header("Location: $_SERVER[REQUEST_URI]");
            } else {

                // Remove all cookie logins
                $auth->unlinkAllCookies($row['id']);

                $auth->updatePassword($row['id'], $_POST['password']);
                Flash::setMessage('Your password has been updated', 'success');
                header("Location: /account");
            }

            return;
        }

        $vars['title'] = Lang::translate('Create new password');
        if (!empty($row)) {
            $vars['error'] = 0;
        } else {
            Flash::setMessage(Lang::translate('No such account connected to supplied key') , 'error');
            $vars['error'] = 1;
        }

        $vars['token'] = (new CSRF())->getToken();
        
        \Pebble\Template::render('App/Account/views/newpassword.php',
            $vars
        );
    }
}
