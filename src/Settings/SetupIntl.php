<?php

declare(strict_types=1);

namespace App\Settings;

use Diversen\Lang;
use App\AppUtils;
use App\Settings\SettingsModel;
use Pebble\HTTP\AcceptLanguage;

/**
 * Middleware that loads language and timezone based on user settings
 * Fallback to using browser language
 */
class SetupIntl extends AppUtils
{

    public function getRequestLanguage(): ?string
    {
        $default = $this->getConfig()->get('Language.default');
        $supported = $this->getConfig()->get('Language.enabled');

        return AcceptLanguage::getLanguage($supported, $default);
    }
    
    /**
     * Load user language and timezone if set else load default language
     * Init translations
     */
    public function setupIntl(): void
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
}



