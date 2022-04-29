<?php

declare(strict_types=1);

namespace App\Settings;

use Pebble\DBCache;
use Pebble\Cookie;
use App\AppMain;

class SettingsModel
{
    private $cache = null;
    private $config = null;

    private $allowed = ['company', 'name', 'bio', 'timezone', 'language', 'theme_dark_mode'];
    private $set_cookies = ['language', 'timezone', 'theme_dark_mode'];
    public function __construct()
    {
        $db = (new AppMain())->getDB();
        $this->config = (new AppMain())->getConfig();
        $this->cache = new DBCache($db);
    }

    /**
     * Get a user setting
     */
    public function getUserSetting($auth_id, $setting)
    {
        $key = $auth_id . '_settings_' . $setting;
        return $this->cache->get($key);
    }

    /**
     * set a user setting
     */

    public function setUserSetting($auth_id, $setting, $value)
    {
        $key = $auth_id . '_settings_' . $setting;
        return $this->cache->set($key, $value);
    }

    /**
     * Get allowed values from POST
     */
    private function getAllowed($post)
    {
        $profile_values = array_intersect_key($post, array_flip($this->allowed));
        return $profile_values;
    }

    /**
     * Set profile form values in cache
     */
    public function setProfileSetting($auth_id, $setting, $values)
    {
        $cookie_lifetime = $this->config->get('Cookie.cookie_seconds');
        $cookie = new Cookie($this->config->getSection('Cookie'));
        $values = $this->getAllowed($values);

        foreach ($values as $key => $value) {
            if (in_array($key, $this->set_cookies)) {
                $cookie->setCookie($key, $value, $cookie_lifetime);
            }
        }

        $key = $auth_id . '_settings_' . $setting;
        return $this->cache->set($key, $values);
    }

    /**
     * Get a single setting from the 'profile' settings
     */
    public function getSingleProfileSetting($auth_id, $profile_setting, $default)
    {
        $profile = $this->getUserSetting($auth_id, 'profile');

        if (isset($profile[$profile_setting])) {
            return $profile[$profile_setting];
        }
    }
}
