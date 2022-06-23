<?php

declare(strict_types=1);

namespace App;

use Pebble\Router;
use Pebble\Random;
use Pebble\App\AppBase;

use Aidantwoods\SecureHeaders\SecureHeaders;

use App\AppACL;
use App\Settings\SettingsModel;

use Diversen\Lang;

/**
 * AppMain contains the application logic.
 * It initializes the application logic and runs it.
 */
class AppMain extends AppBase
{
    public const VERSION = "1.2.109";
    public static $nonce;
    public static $appAcl = null;

    /**
     * @return \App\AppACL
     */
    public function getAppACL()
    {
        if (!self::$appAcl) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            self::$appAcl = new AppAcl($this->getDB(), $auth_cookie_settings);
        }

        return self::$appAcl;
    }

    /**
     * Load user language and timezone if set else load default language
     * Init translations
     */
    public function setupIntl()
    {
        // Set a default language in case of an early error
        $translations = new Lang();
        $translations->setSingleDir("../src");
        $translations->loadLanguage('en');

        $settings = new SettingsModel();

        $auth_id = $this->getAuth()->getAuthId();
        $user_settings = $settings->getUserSetting($auth_id, 'profile');

        $timezone = $user_settings['timezone'] ?? $this->getConfig()->get('App.timezone');
        date_default_timezone_set($timezone);

        $language = $user_settings['language'] ?? $this->getRequestLanguage();
        $translations->loadLanguage($language);
    }

    public function sendHeaders()
    {
        $config = $this->getConfig();
        $this->sendSSLHeaders();

        $env = $this->getConfig()->get("App.env");
        if ($env === 'dev') {
            return;
        }

        self::$nonce = $nonce = Random::generateRandomString(16);

        $headers = new SecureHeaders();
        $headers->strictMode(false);
        $headers->errorReporting(true);
        $headers->hsts();
        $headers->csp('default', 'self');
        $headers->csp('base-uri', $config->get('App.server_url'));
        $headers->csp('img-src', 'data:');
        $headers->csp('img-src', $config->get('App.server_url'));
        $headers->csp('script-src', "'nonce-$nonce'");
        $headers->csp('style-src', 'self');
        $headers->csp('style-src', 'https://cdnjs.cloudflare.com');
        $headers->csp('font-src', 'https://cdnjs.cloudflare.com');

        $headers->csp('worker-src', $config->get('App.server_url'));
        $headers->apply();
    }

    public static function getNonce()
    {
        return self::$nonce;
    }

    public function run()
    {

        // Add src/ to include path (template include)
        $this->addBaseToIncudePath();
        $this->addSrcToIncludePath();

        $this->setErrorHandler();
        $this->sendHeaders();
        $this->sessionStart();
        $this->setupIntl();
        $this->setDebug();

        $router = new Router();
        $router->addClass(\App\Test\Controller::class);
        $router->addClass(\App\Notification\Controller::class);
        $router->addClass(\App\Account\ControllerExt::class);
        $router->addClass(\App\Home\Controller::class);
        $router->addClass(\App\Google\Controller::class);
        $router->addClass(\App\Overview\Controller::class);
        $router->addClass(\App\Project\Controller::class);
        $router->addClass(\App\Settings\Controller::class);
        $router->addClass(\App\Task\Controller::class);
        $router->addClass(\App\Time\Controller::class);
        $router->addClass(\App\Error\Controller::class);
        $router->addClass(\App\TwoFactor\Controller::class);
        $router->run();
    }
}
