<?php

declare(strict_types=1);

namespace App\Account;

use Diversen\Lang;
use Pebble\Captcha;
use Pebble\CSRF;
use Pebble\SessionTimed;
use Pebble\ExceptionTrace;

use Pebble\Exception\NotFoundException;
use Pebble\File;

use App\AppUtils;
use App\Account\Mail;
use App\Account\Validate;
use App\TwoFactor\TwoFactorModel;


use Exception;
use Parsedown;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @route /account/signin
     * @verbs GET
     */
    public function index(): void
    {
        $template_vars = [];
        if ($this->auth->isAuthenticated()) {
            $template_vars['title'] = Lang::translate('Sign out');
            $this->renderPage(
                'Account/views/signout.php',
                $template_vars
            );
        } else {
            $template_vars['title'] = Lang::translate('Sign in');
            $template_vars['csrf_token'] = (new CSRF())->getToken();

            $this->renderPage(
                'Account/views/signin.php',
                $template_vars
            );
        }
    }

    /**
     * Log the user out and redirect
     * @route /account/logout
     * @verbs GET
     */
    public function logout(): void
    {
        $auth_id = $this->auth->getAuthId();

        if (isset($_GET['all_devices'])) {
            $this->auth->unlinkAllCookies($auth_id);
        } else {
            $this->auth->unlinkCurrentCookie();
        }

        $this->log->info('Account.logout.success', ['auth_id' => $auth_id]);

        $redirect = $this->config->get('App.logout_redirect');
        header("Location: $redirect");
    }

    /**
     * Page with link to logout route
     * @route /account/signout
     * @verbs GET
     */
    public function signout(): void
    {
        $this->renderPage(
            'Account/views/signout.php',
            ['title' => Lang::translate('Sign out')]
        );
    }

    /**
     * @route /account/post_login
     * @verbs POST
     */
    public function post_login(): void
    {
        usleep(100000);

        $validate = new Validate();
        $response = $validate->postLogin();
        if ($response['error'] === true) {
            $this->json->render($response);
            return;
        }

        $this->log->info('Account.post_login.email', ['email' => $_POST['email']]);

        $response['error'] = true;
        $row = $this->auth->authenticate($_POST['email'], $_POST['password']);

        if (!empty($row)) {
            $response['error'] = false;
            if ($this->twoFactor($response, $row)) {
                return;
            }

            $response['redirect'] = $this->config->get('App.login_redirect');
            if (isset($_POST['keep_login'])) {
                $this->auth->setCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
            } else {
                $this->auth->setCookie($row, 0);
            }

            $this->log->info('Account.post_login.success', ['auth_id' => $row['id']]);
            $this->flash->setMessage(Lang::translate('You are logged in'), 'success', ['flash_remove' => true]);
        } else {
            $response['message'] = Lang::translate('Wrong email or password. Or your account has not been activated.');
        }

        $this->json->render($response);
    }

    /**
     * Checks if the user has two factor enabled and if so,
     * redirect to the two factor page
     * @return bool $res True if the user has two factor enabled
     */
    private function twoFactor(array $response, array $row): bool
    {
        if ($this->config->get('TwoFactor.enabled')) {
            $two_factor = new TwoFactorModel();
            if ($two_factor->isTwoFactorEnabled($row['id'])) {
                $session_timed = new SessionTimed();
                $session_timed->setValue('auth_id_to_login', $row['id'], $this->config->get('TwoFactor.time_to_verify'));
                $session_timed->setValue('keep_login', isset($_POST['keep_login']), $this->config->get('TwoFactor.time_to_verify'));
                $this->flash->setMessage(Lang::translate('Verify your login.'), 'success', ['flash_remove' => true]);
                $response['redirect'] = '/twofactor/verify';
                $this->json->render($response);
                return true;
            }
        }
        return false;
    }

    /**
     * @route /account/signup
     * @verbs GET
     */
    public function signup(): void
    {
        $template_vars = [
            'title' => Lang::translate('Email sign up'),
            'token' => (new CSRF())->getToken(),
        ];

        $this->renderPage(
            'Account/views/signup.php',
            $template_vars
        );
    }

    /**
     * @route /account/verify
     * @verbs GET
     */
    public function verify(): void
    {
        $key = $_GET['key'] ?? '';

        $row = $this->auth->getByWhere(['random' => $key]);
        if (empty($row)) {
            $this->flash->setMessage(Lang::translate('No valid verification key could be found'), 'error');
            $this->log->info('Account.verify.failed', ['get' => $_GET]);
            header("Location: /account/signin");
            return;
        }

        $res = $this->auth->verifyKey($key);
        if ($res) {
            $this->flash->setMessage(Lang::translate('Your account has been verified. You may log in'), 'success');
            $this->log->info('Account.verify.success', ['auth_id' => $row['id']]);
        } else {
            $this->flash->setMessage(Lang::translate('The key supplied has already been used'), 'error');
            $this->log->info('Account.verify.failed', ['auth_id' => $row['id']]);
        }

        header("Location: /account/signin");
    }

    /**
     * @route /account/captcha
     * @verbs GET
     */
    public function captcha(): void
    {
        $captcha = new Captcha();
        $captcha->outputImage();
    }

    /**
     * @route /account/post_signup
     * @verbs POST
     */
    public function post_signup(): void
    {
        usleep(100000);

        $validate = new Validate();
        $response = $validate->postSignup();
        if ($response['error'] === true) {
            $this->json->render($response);
            return;
        }

        $this->db->beginTransaction();

        $res = $this->auth->create($_POST['email'], $_POST['password']);
        if ($res) {
            $this->log->info('Account.post_signup.success', ['email' => $_POST['email']]);
            $row = $this->auth->getByWhere(['email' => $_POST['email']]);

            if ($this->config->get('Account.no_email_verify')) {
                $this->db->update('auth', ['verified' => 1], ['email' => $_POST['email']]);
                $message = Lang::translate('Account has been created. You may log in');
                $mail_success = true;
            } else {
                $mail = new Mail();

                try {
                    $mail_success = true;
                    $mail->sendSignupMail($row);
                } catch (Exception $e) {
                    $this->log->error('Account.post_signup.exception', ['exception' => ExceptionTrace::get($e)]);
                    $mail_success = false;
                }

                $message = Lang::translate('User created. An activation link has been sent to your email. Press the link and your account will be activated');
            }

            if (!$mail_success) {
                $this->db->rollback();
                $this->log->info('Account.post_signup.rollback');
                $response['error'] = true;
                $response['message'] = Lang::translate('The system could not create an account. Please try again another time');
            } else {
                $this->db->commit();
                $this->log->info('Account.post_signup.commit', ['auth_id' => $row['id']]);
                $this->flash->setMessage($message, 'success');
                $response['error'] = false;
                $response['redirect'] = '/account/signin';
            }
        }

        $this->json->render($response);
    }

    /**
     * @route /account/recover
     * @verbs GET
     */
    public function recover(): void
    {
        $template_vars = [
            'token' => (new CSRF())->getToken(),
            'title' => Lang::translate('Forgotten password'),
        ];

        $this->renderPage(
            'Account/views/recover.php',
            $template_vars
        );
    }

    /**
     * @route /account/post_recover
     * @verbs POST
     */
    public function post_recover(): void
    {
        $captcha = new Captcha();

        $response = ['error' => true];

        $csrf = new CSRF();
        if (!$csrf->validateToken()) {
            $response['message'] = Lang::translate('Invalid Request. We will look in to this');
            $this->json->render($response);
            return;
        }

        $row = $this->auth->getByWhere(['email' => $_POST['email']]);

        if (empty($row)) {
            $response['message'] = Lang::translate('No such email in our system');
            $this->json->render($response);
            return;
        }

        if (!$captcha->validate($_POST['captcha'])) {
            $response['message'] = Lang::translate('The image text does not match your submission');
            $this->json->render($response);
            return;
        }

        if (!empty($row)) {
            $mail = new mail();
            try {
                $mail->sendRecoverMail($row);
                $mail_success = true;
            } catch (Exception $e) {
                $this->log->error('Account.post_recover.exception', ['exception' => ExceptionTrace::get($e)]);
                $mail_success = false;
            }

            if ($mail_success) {
                $this->log->info('Account.post_recover.success', ['auth_id' => $row['id']]);
                $this->flash->setMessage(
                    Lang::translate('A notification email has been sent with instructions to create a new password'),
                    'success'
                );
                $response['error'] = false;
                $response['redirect'] = '/account/signin';
            } else {
                $response['message'] = Lang::translate('E-mail could not be sent. Try again later.');
            }
        }

        $this->json->render($response);
    }

    /**
     * @route /account/newpassword
     * @verbs GET
     */
    public function newpassword(): void
    {
        $key = $_GET['key'] ?? null;
        $row = $this->auth->getByWhere(['random' => $key]);

        $template_vars = ['title' => Lang::translate('New password')];
        if (empty($row)) {
            $template_vars['error'] = true;
            $this->flash->setMessage(Lang::translate('No such account connected to supplied key'), 'error');
        }

        $template_vars['token'] = (new CSRF())->getToken();

        $this->renderPage(
            'Account/views/newpassword.php',
            $template_vars
        );
    }

    /**
     * @route /account/newpassword
     * @verbs POST
     */
    public function post_newpassword(): void
    {
        $key = $_GET['key'] ?? null;
        $row = $this->auth->getByWhere(['random' => $key]);

        if (!empty($_POST) && !empty($row)) {
            $validate = new Validate();
            $response = $validate->passwords();

            if ($response['error'] === true) {
                $this->flash->setMessage($response['message'], 'error');
                header("Location: $_SERVER[REQUEST_URI]");
            } else {
                $this->auth->unlinkAllCookies($row['id']);
                $this->auth->updatePassword($row['id'], $_POST['password']);

                $this->log->info('Account.newpassword.success', ['auth_id' => $row['id']]);
                $this->flash->setMessage(Lang::translate('Your password has been updated'), 'success');

                header("Location: /account/signin");
            }
        }
    }


    /**
     * @route /account/terms/:document
     * @verbs GET,POST
     */
    public function terms($params): void
    {
        $terms_dir = '../src/Account/views/terms/';
        $allowed_files = File::dirToArray($terms_dir);

        if (!in_array($params['document'] . '.php', $allowed_files)) {
            throw new NotFoundException(Lang::translate('Page not found'));
        }

        $markdown_file = '../src/Account/views/terms/' . $params['document'] . '.php';
        $markdown_text = file_get_contents($markdown_file);

        $data['server_url'] = $this->config->get('App.server_url');
        $data['site_name'] = $this->config->get('App.site_name');
        $data['title'] = Lang::translate('Terms of service');
        $data['contact_email'] = $this->config->get('App.contact_email');
        $data['company_name'] = $this->config->get('App.company_name');

        // Add veriables into markdown text
        $markdown_text = $this->template->getOutput($markdown_file, $data);

        $parsedown = new Parsedown();
        $parsedown->setSafeMode(false);
        $data['note_markdown'] = $parsedown->text($markdown_text);

        $this->renderPage('Account/views/terms.tpl.php', $data, ['raw' => true]);
    }
}
