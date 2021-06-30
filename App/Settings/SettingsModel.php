<?php

namespace App\Settings;

use Pebble\Auth;
use Pebble\DBCache;
use Pebble\Config;

class SettingsModel {

    /**
     * Default mode is to use logged in users settings as part of cache key.
     * You can prevent this by setting a auth_id
     * Then you will be able to get and set settings from an arbitrary auth_id
     */
    public function __construct(int $auth_id = null) {
        if ($auth_id) {
            $this->auth_id = $auth_id;
        } else {
            $this->auth_id = Auth::getInstance()->getAuthId();
        }
    }

    /**
     * Get a setting key with unique auth_id prepended
     */
    private function getKey($setting) {
        $key = $this->auth_id . '_settings_' . $setting;
        return $key;
    }

    /**
     * Get a user setting
     */
    public function getUserSetting($setting) {
        $cache = new DBCache();
        return $cache->get($this->getKey($setting));
    }

    /**
     * Get a user setting
     */
    public function getUserSettingFromAuthId($auth_id, $setting) {
        $cache = new DBCache();
        $key = $auth_id . '_settings_' . $setting;
        return $cache->get($key);
    }

    /**
     * Get users current timezon or return the app's default timezone
     */
    public function getUserDefaultTimeZone() {
        $user_time_zone = $this->getUserSetting('timezone');
        if (!$user_time_zone) {
            $user_time_zone = Config::get('App.timezone');
        }

        return $user_time_zone;
    }

    /**
     * set a user setting
     */
    public function setUserSetting($setting, $value) {

        // Remember theme even if user is logged out
        if ($setting == 'theme_dark_mode') {
            $time = 365 * (60 * 60 * 24); // 365 days
            $this->setCookie($setting, $value, time() + $time);

        }
        $cache = new DBCache();
        return $cache->set($this->getKey($setting), $value);
    }

    /**
     * Set a array of user settings
     */
    public function setUserSettings($post) {
        foreach($post as $key => $val) {
            $this->setUserSetting($key, $val); 
        }

        // Set all user 
        $this->setUserSetting('profile', $post); 
    }

    /**
     * get a user setting where null value returns '0'
     */
    public function getUserSettingDefaultZero($setting) {
        $cache = new DBCache();
        $ret_val = $cache->get($this->getKey($setting));
        if (!$ret_val) {
            return '0';
        }
        return $ret_val;
    }

    /**
     * Get all user setting
     */
    public function getAllUserSettings(): array {
        $theme_dark_mode = $this->getUserSetting('theme_dark_mode');

        $timezone = $this->getUserSetting('timezone');
        if (!$timezone) {
            $timezone = Config::get('App.timezone');
        }

        $language = $this->getUserSetting('language');
        if (!$language) {
            $language = Config::get('Language.default');
        }

        $user_settings = [
            'timezone' => $timezone,
            'theme_dark_mode' => $theme_dark_mode,
            'title' => 'Settings',
            'language' => $language,
            'company' => $this->getUserSetting('company'),
            'name' => $this->getUserSetting('name'),
            'bio' => $this->getUserSetting('bio'),
            'phone' => $this->getUserSetting('phone')
            
        ];
        return $user_settings;
    }


    /**
     * Set a cookie with key value and expire time
     */
    private function setCookie (string $key, string $value, int $time) {
        $auth_settings = Config::getSection('Auth');

        $path = $auth_settings['cookie_path'];
        $domain = $auth_settings['cookie_domain'];
        $secure = $auth_settings['cookie_secure'];
        $http_only = $auth_settings['cookie_http'];

        return setcookie($key, $value, $time, $path, $domain, $secure, $http_only);
    }

    public static $instance = null;
    public static function getInstance() {

        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}
