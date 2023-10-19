<?php

declare(strict_types=1);

namespace App\Account;

use Pebble\Server;
use Pebble\SMTP;
use Diversen\Lang;
use App\AppUtils;

class Mail extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Send signup mail
     * @param array $row
     * @return boolean $ret
     */
    public function sendSignupMail(array $row): void
    {
        $context = $this->config->getSection('App');
        $activation_url = (new Server())->getSchemeAndHost() . '/account/verify?key=' . $row['random'];
        $context['activation_url'] = $activation_url;

        $text = $this->twig->render('account/mails/signup.twig', $this->getContext($context));
        $smtp = new SMTP($this->config->getSection('SMTP'));
        $smtp->sendMarkdown($row['email'], Lang::translate('Activation link'), $text);
    }

    /**
     * Send password recover mail
     * @param array $row
     * @return boolean $ret
     */
    public function sendRecoverMail(array $row): void
    {
        $context = $this->config->getSection('App');
        $activation_url = (new Server())->getSchemeAndHost() . '/account/newpassword?key=' . $row['random'];
        $context['activation_url'] = $activation_url;

        $text = $this->twig->render('account/mails/recover_password.twig', $this->getContext($context));
        $smtp = new SMTP($this->config->getSection('SMTP'));
        $smtp->sendMarkdown($row['email'], Lang::translate('Recover your account'), $text);
    }
}
