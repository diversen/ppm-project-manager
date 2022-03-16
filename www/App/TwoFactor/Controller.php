<?php

namespace App\TwoFactor;

use OTPHP\TOTP;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use Pebble\Config;
use Diversen\Lang;


use Pebble\ACL;
use Pebble\Flash;
use Pebble\SessionTimed;

use App\TwoFactor\TwoFactorModel;
use App\AppMain;

class Controller
{
    private $twoFactorModel;
    private $acl;
    private $log;

    public function __construct() {
        $app_main = new AppMain();
        $this->twoFactorModel = new TwoFactorModel();
        $this->acl = $app_main->getAppACL();
        $this->config = $app_main->getConfig();
        $this->log = $app_main->getLog();
    }

    private function getOtpAuthUrl(string $label, string $key): string
    {
        $label = rawurlencode($label);
        $url = "otpauth://totp/$label?secret=$key";
        return $url;
    }


    private function getQRCode(string $totp_auth_url): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new ImagickImageBackEnd()
        );
        $writer = new Writer($renderer);
        $png_str = $writer->writeString($totp_auth_url);
        $base64_png = 'data:image/png;base64,' . base64_encode($png_str);
        return $base64_png;
    }

    /**
     * @route /2fa/recreate
     * @verbs GET
     */
    public function recreate () {
        $this->acl->isAuthenticatedOrThrow();
        if ($this->twoFactorModel->isTwoFactorEnabled($this->acl->getAuthId())) {
            $this->twoFactorModel->delete($this->acl->getAuthId());
            Flash::setMessage(Lang::translate('New QR code has been created'), 'success', ['flash_remove' => true]);
        }

        header('Location: /2fa/enable', true);

    }

    /**
     * @route /2fa/enable
     * @verbs GET
     */
    public function enable()
    {

        $this->acl->isAuthenticatedOrThrow();

        // A random secret will be generated from this.
        // You should store the secret with the user for verification.

        if (!$this->twoFactorModel->isTwoFactorEnabled($this->acl->getAuthId())) {
            $otp = TOTP::create();
            $secret = $otp->getSecret();
            $otp = TOTP::create($secret);

            $label = $this->config->get('TwoFactor.totp_label');

            $otp_auth_url = $this->getOtpAuthUrl($label, $secret);
            $qr_image = $this->getQRCode($otp_auth_url);

            $this->twoFactorModel->create($this->acl->getAuthId(), $secret);

            $vars = ['qr_image' => $qr_image, 'enabled' => false];

            \Pebble\Template::render(
                'App/TwoFactor/views/enable.tpl.php',
                $vars
            );
        } else {
            $vars = ['enabled' => true];
            \Pebble\Template::render(
                'App/TwoFactor/views/is_enabled.tpl.php',
                $vars
            );
        }
    }

    /**
     * @route /2fa/put
     * @verbs POST
     */
    public function put() {

        $auth_id = $this->acl->getAuthId();
        $secret = $this->twoFactorModel->getUserSecret($auth_id);
        $input = $_POST['code'];
        $otp = TOTP::create($secret);
        $res['error'] = false;
        if(!$otp->verify($input)) {
            $message = Lang::translate('The code could not be verified. Try again.');
            $res['error'] = $message;
            
        } else {
            $this->twoFactorModel->verify($auth_id);
            $res['message'] = Lang::translate('The code is verified. Two factor is enabled.');
        }

        echo json_encode($res);
   
    }

    /**
     * @route /2fa/verify/post
     * @verbs POST
     */    
    public function verify_post () {
        $session_timed = new SessionTimed();
        $auth_id = $session_timed->getValue('auth_id_to_login');
        $keep_login = $session_timed->getValue('keep_login');

        if (!$auth_id) {
            $message = Lang::translate('You were to slow to enter two factor code. You will need to login again.');
            $res['error'] = $message;
            echo json_encode($res);
            return;
        }

        $this->log->info('TwoFactor.verify_post', ['auth_id' => $auth_id]);

        $secret = $this->twoFactorModel->getUserSecret($auth_id);
        $input = $_POST['code'];
        $otp = TOTP::create($secret);
        $res['error'] = false;
        if(!$otp->verify($input)) {
            $message = Lang::translate('The code could not be verified. Try again.');
            $res['error'] = $message;
            
        } else {
            
            $login_redirect = $this->config->get('App.login_redirect');
            $res['message'] = Lang::translate('The code is verified. You are logged in.');
            $res['redirect'] = $login_redirect;

            $row = $this->acl->getByWhere(['id' => $auth_id]);

            $response['redirect'] = $this->config->get('App.login_redirect');
            if ($keep_login) {
                $this->acl->setPermanentCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
            } else {
                $this->acl->setSessionCookie($row, $this->config->get('Auth.cookie_seconds'));
            }

            $this->log->info('TwoFactor.verify_post.success', ['auth_id' => $auth_id]);
            
            Flash::setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);
        }

        echo json_encode($res);
    }

    /**
     * @route /2fa/verify
     * @verbs GET
     */
    public function verify() {
        
        $vars = [];
        \Pebble\Template::render(
            'App/TwoFactor/views/verify.tpl.php',
            $vars
        );
    }
}
