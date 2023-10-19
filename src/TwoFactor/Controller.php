<?php

declare(strict_types=1);

namespace App\TwoFactor;

use OTPHP\TOTP;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

use Diversen\Lang;
use Pebble\SessionTimed;
use App\AppUtils;
use App\TwoFactor\TwoFactorModel;
use Pebble\Exception\JSONException;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    private $twoFactorModel;

    public function __construct()
    {
        parent::__construct();
        $this->twoFactorModel = new TwoFactorModel();
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
            new SvgImageBackEnd(),
        );
        $writer = new Writer($renderer);
        $svg_str = $writer->writeString($totp_auth_url);
        return $svg_str;
    }

    #[Route(path: '/twofactor/recreate')]
    public function recreate()
    {
        $this->acl->isAuthenticatedOrThrow();
        if ($this->twoFactorModel->isTwoFactorEnabled($this->acl->getAuthId())) {
            $this->twoFactorModel->delete($this->acl->getAuthId());
            $this->flash->setMessage(Lang::translate('New QR code has been created'), 'success', ['flash_remove' => true]);
        }

        header('Location: /twofactor/enable', true);
    }

    #[Route(path: '/twofactor/enable')]
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

            $context = [
                'qr_image' => $qr_image,
                'enabled' => false,
                'title' => Lang::translate('Enable two factor')
            ];

            echo $this->twig->render('twofactor/enable.twig', $this->getContext($context));
        } else {
            $context = [
                'enabled' => true,
                'title' => Lang::translate('Two factor is enabled'),
            ];
            echo $this->twig->render('twofactor/is_enabled.twig', $this->getContext($context));
        }
    }

    #[Route(path: '/twofactor/put', verbs: ['POST'])]
    public function put()
    {
        $this->acl->isAuthenticatedOrThrow();

        $auth_id = $this->acl->getAuthId();
        $secret = $this->twoFactorModel->getUserSecret($auth_id);
        $input = $_POST['code'];
        $otp = TOTP::create($secret);

        if (!$otp->verify($input)) {
            throw new JSONException(Lang::translate('The code could not be verified. Try again.'));
        } else {
            $this->twoFactorModel->verify($auth_id);
            $response['message'] = Lang::translate('The code is verified. Two factor is enabled.');
            $this->json->renderSuccess($response);
        }
    }

    #[Route(path: '/twofactor/verify/post', verbs: ['POST'])]
    public function verify_post()
    {
        usleep(1000000);

        $session_timed = new SessionTimed();
        $auth_id = $session_timed->getValue('auth_id_to_login');
        $keep_login = $session_timed->getValue('keep_login');

        if (!$auth_id) {
            $message = Lang::translate('You were to slow to enter two factor code. You will need to login again.');
            throw new JSONException($message);
        }

        $this->log->info('TwoFactor.verify_post', ['auth_id' => $auth_id]);

        $secret = $this->twoFactorModel->getUserSecret($auth_id);
        $input = $_POST['code'];
        $otp = TOTP::create($secret);

        if (!$otp->verify($input)) {
            throw new JSONException(Lang::translate('The code could not be verified. Try again.'));
        } else {
            $row = $this->acl->getByWhere(['id' => $auth_id]);
            if ($keep_login) {
                $this->acl->setCookie($row, $this->config->get('Auth.cookie_seconds_permanent'));
            } else {
                $this->acl->setCookie($row, 0);
            }

            $this->log->info('TwoFactor.verify_post.success', ['auth_id' => $auth_id]);
            $this->flash->setMessage(Lang::translate('You are signed in.'), 'success', ['flash_remove' => true]);

            $response['message'] = Lang::translate('The code is verified. You are logged in.');
            $response['redirect'] = $this->config->get('App.login_redirect');

            $this->json->renderSuccess($response);
        }
    }

    #[Route(path: '/twofactor/verify')]
    public function verify()
    {
        $context = ['title' => Lang::translate('Verify two factor')];
        echo $this->twig->render('twofactor/verify.twig', $this->getContext($context));
    }
}
