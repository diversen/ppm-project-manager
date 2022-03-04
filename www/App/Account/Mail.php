<?php declare (strict_types = 1);

namespace App\Account;

use Pebble\App;
use Pebble\Template;
use Pebble\SMTP;
use Pebble\Config;
use Diversen\Lang;
use App\AppMain;

class Mail {

    private $config = null;
    public function __construct() {
        $this->config = (new AppMain())->getConfig();
    }
    
    /**
     * Send signup mail
     * @param array $row
     * @return boolean $ret
     */
    public function sendSignupMail(array $row)
    {

        $vars = $this->config->getSection('App');
        $activation_url = (new App())->getSchemeAndHost() . '/account/verify?key=' . $row['random'];
        $vars['activation_url'] = $activation_url;

        $text = Template::getOutput('App/Account/views/signup_email.php', $vars);
        $smtp = new SMTP($this->config->getSection('SMTP'));
        $smtp->sendMarkdown($row['email'], Lang::translate('Activation link'), $text);

    }

    /**
     * Send password recover mail
     * @param array $row
     * @return boolean $ret
     */
    public function sendRecoverMail(array $row)
    {

        $vars = $this->config->getSection('App');
        $activation_url = (new App())->getSchemeAndHost() . '/account/newpassword?key=' . $row['random'];
        $vars['activation_url'] = $activation_url;

        $text = Template::getOutput('App/Account/views/recover_email.php', $vars);
        $smtp = new SMTP($this->config->getSection('SMTP'));
        $smtp->sendMarkdown($row['email'], Lang::translate('Recover your account'), $text);
    }
}
