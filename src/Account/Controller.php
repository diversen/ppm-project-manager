<?php

declare(strict_types=1);

namespace App\Account;

use Diversen\Lang;
use Pebble\Captcha;
use Pebble\ExceptionTrace;
use Pebble\Exception\NotFoundException;
use Pebble\File;
use App\AppUtils;
use App\Account\Mail;
use App\Account\Validate;
use App\TwoFactor\TwoFactorModel;
use Exception;
use Pebble\Exception\JSONException;
use Pebble\Attributes\Route;
use Pebble\Router\Request;

class Controller extends AppUtils
{
    const RECOVERY_TIME_OUT = 3600;

    public function __construct()
    {
        parent::__construct();
    }

    #[Route(path: '/account/signin')]
    public function signin(): void
    {
        $context = [];
        if ($this->auth->isAuthenticated()) {
            $context['title'] = Lang::translate('Sign out');
            echo $this->twig->render('account/signout.twig', $this->getContext($context));
        } else {
            $context['title'] = Lang::translate('Sign in');
            echo $this->twig->render('account/signin.twig', $this->getContext($context));
        }
    }

    #[Route(path: '/account/post_signin', verbs: ['POST'])]
    public function post_signin(): void
    {
        usleep(100000);

        $this->csrf->validateTokenJSON();

        $row = $this->auth->authenticate($_POST['email'], $_POST['password']);

        if (!empty($row)) {
            if ($this->twoFactor($row['id'])) {
                return;
            }

            if (isset($_POST['keep_login'])) {
                $this->auth->setCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
            } else {
                $this->auth->setCookie($row, 0);
            }

            $this->log->info('Account.post_login.success', ['auth_id' => $row['id']]);
            $this->flash->setMessage(Lang::translate('You are logged in'), 'success', ['flash_remove' => true]);
            $this->json->renderSuccess(['redirect' => $this->config->get('App.login_redirect')]);
        } else {
            throw new JSONException(Lang::translate('Wrong email or password. Or your account has not been activated.'));
        }
    }

    #[Route(path: '/account/logout')]
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

    #[Route(path: '/account/signout')]
    public function signout(): void
    {
        $context['title'] = Lang::translate('Sign out');
        echo $this->twig->render('account/signout.twig', $this->getContext($context));
    }

    /**
     * Checks if the user has two factor enabled and if so,
     * redirect to the two factor page
     * @return bool $res True if the user has two factor enabled
     */
    private function twoFactor(int $auth_id): bool
    {
        if ($this->config->get('TwoFactor.enabled')) {
            $two_factor = new TwoFactorModel();
            if ($two_factor->shouldRedirect($auth_id)) {
                $this->json->renderSuccess(['redirect' => '/twofactor/verify']);
                return true;
            }
        }
        return false;
    }

    #[Route(path: '/account/signup')]
    public function signup(): void
    {
        $context = [
            'title' => Lang::translate('Email sign up'),
        ];

        echo $this->twig->render('account/signup.twig', $this->getContext($context));
    }

    #[Route(path: '/account/post_signup', verbs: ['POST'])]
    public function post_signup(): void
    {
        usleep(100000);

        $this->csrf->validateTokenJSON();

        // Validate or throw JSONException
        $validate = new Validate();
        $validate->postSignup();

        try {
            $this->createAndSendVerificationKey();
        } catch (Exception $e) {
            throw new JSONException($e->getMessage());
        }
    }

    /**
     * Create a new user and send a verification key
     */
    private function createAndSendVerificationKey(): void
    {

        // Auto verification without email
        if ($this->config->get('Account.no_email_verify')) {
            $this->auth->createAndVerify($_POST['email'], $_POST['password']);
            $row = $this->auth->getByWhere(['email' => $_POST['email']]);
            $this->log->info('Account.post_signup.commit', ['auth_id' => $row['id']]);
            $this->flash->setMessage(Lang::translate('Account has been created. You may log in'), 'success');
            $this->json->renderSuccess(['redirect' => '/account/signin']);
            return;
        }

        // Verification using email
        try {
            $this->db->beginTransaction();

            $this->auth->create($_POST['email'], $_POST['password']);
            $this->log->info('Account.post_signup.success', ['email' => $_POST['email']]);
            $row = $this->auth->getByWhere(['email' => $_POST['email']]);

            $mail = new Mail();
            $mail->sendSignupMail($row);
            $message = Lang::translate('User created. An activation link has been sent to your email. Press the link and your account will be activated');

            $this->db->commit();
            $this->log->info('Account.post_signup.commit', ['auth_id' => $row['id']]);
            $this->flash->setMessage($message, 'success');
            $this->json->renderSuccess(['redirect' => '/account/signin']);
        } catch (Exception $e) {
            $this->log->error('Account.post_signup.exception', ['exception' => ExceptionTrace::get($e)]);
            $this->db->rollback();
            $this->log->info('Account.post_signup.rollback');
            throw new Exception(Lang::translate('The system could not create an account. Please try again another time'));
        }
    }

    #[Route(path: '/account/verify')]
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

    #[Route(path: '/account/captcha')]
    public function captcha(): void
    {
        $captcha = new Captcha();
        $captcha->outputImage();
    }

    #[Route(path: '/account/recover')]
    public function recover(): void
    {
        $context = [
            'title' => Lang::translate('Forgotten password'),
        ];

        echo $this->twig->render('account/recover.twig', $this->getContext($context));
    }

    private function shouldSendRecoveryEmail(string $email)
    {
        $exists = $this->db_cache->get($email . '_recovery', self::RECOVERY_TIME_OUT);
        if (!$exists) {
            return true;
        }
        return false;
    }

    private function setRecoveryEmailSent(string $email)
    {
        return $this->db_cache->set($email . '_recovery', true);
    }


    #[Route(path: '/account/post_recover', verbs: ['POST'])]
    public function post_recover(): void
    {
        $email = $_POST['email'];

        $captcha = new Captcha();

        $this->csrf->validateTokenJSON();

        $row = $this->auth->getByWhere(['email' => $email]);
        if (empty($row)) {
            throw new JSONException(Lang::translate('No such email in our system'));
        }

        if (!$captcha->validate($_POST['captcha'])) {
            throw new JSONException(Lang::translate('The image text does not match your submission'));
        }

        if (!$this->shouldSendRecoveryEmail($email)) {
            throw new JSONException(
                Lang::translate('You have already requested a recovery email. Please wait one hour before requesting a new one. Please check your spam folder.')
            );
        }

        $mail = new mail();
        try {
            $mail->sendRecoverMail($row);
            $mail_success = true;
        } catch (Exception $e) {
            $this->log->error('Account.post_recover.exception', ['exception' => ExceptionTrace::get($e)]);
            $mail_success = false;
        }

        if ($mail_success) {
            $this->setRecoveryEmailSent($email);
            $this->log->info('Account.post_recover.success', ['auth_id' => $row['id']]);
            $this->flash->setMessage(
                Lang::translate('A notification email has been sent with instructions to create a new password'),
                'success',
                ['flash_remove' => true]
            );
            $this->json->renderSuccess(['redirect' => '/account/signin']);
        } else {
            throw new JSONException(Lang::translate('E-mail could not be sent. Try again later.'));
        }
    }

    #[Route(path: '/account/newpassword')]
    public function newpassword(): void
    {
        $context = [
            'title' => Lang::translate('New password'),
            'key' => $_GET['key'] ?? null,
        ];

        echo $this->twig->render('account/new_password.twig', $this->getContext($context));
    }

    #[Route(path: '/account/post_newpassword', verbs: ['POST'])]
    public function post_newpassword(): void
    {
        $this->csrf->validateTokenJSON();

        $row = $this->auth->getByWhere(['random' => $_POST['key']]);

        if (empty($row)) {
            throw new JSONException(Lang::translate('No such account connected to supplied key'), 404);
        }

        // Validate or throw exception
        $validate = new Validate();
        $validate->passwords();

        // OK. Update password
        $this->auth->unlinkAllCookies($row['id']);
        $this->auth->updatePassword($row['id'], $_POST['password']);

        $this->log->info('Account.newpassword.success', ['auth_id' => $row['id']]);
        $this->flash->setMessage(Lang::translate('Your password has been updated'), 'success', ['flash_remove' => true]);
        $this->json->renderSuccess(['redirect' => '/account/signin']);
    }

    #[Route(path: '/account/terms/:document', verbs: ['GET', 'POST'])]
    public function terms(Request $request): void
    {
        $terms_dir = '../templates/account/terms/';
        $allowed_files = File::dirToArray($terms_dir);

        if (!in_array($request->param('document') . '.twig', $allowed_files)) {
            throw new NotFoundException(Lang::translate('Page not found'));
        }

        $context['server_url'] = $this->config->get('App.server_url');
        $context['site_name'] = $this->config->get('App.site_name');
        $context['title'] = Lang::translate('Terms of service');
        $context['contact_email'] = $this->config->get('App.contact_email');
        $context['company_name'] = $this->config->get('App.company_name');

        $twig_template = '../templates/account/terms/' . $request->param('document') . '.twig';
        $markdown_document = file_get_contents($twig_template);
        $context['markdown_document'] = $markdown_document;

        $context = $this->getContext($context);
        echo $this->twig->render('account/terms.twig', $context);
    }
}
