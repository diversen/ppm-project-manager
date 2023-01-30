<?php

declare(strict_types=1);

namespace App\Settings;

use Pebble\Cookie;
use App\AppUtils;

class SettingsModel extends AppUtils
{
    private $allowed = ['company', 'name', 'bio', 'timezone', 'language', 'theme_dark_mode'];
    private $set_cookies = ['language', 'timezone', 'theme_dark_mode'];
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get a user setting
     */
    public function getUserSetting(int $auth_id, string $setting): mixed
    {
        $key = $auth_id . '_settings_' . $setting;
        return $this->db_cache->get($key);
    }

    /**
     * set a user setting
     */
    public function setUserSetting(int $auth_id, string $setting, string $value): mixed
    {
        $key = (string)$auth_id . '_settings_' . $setting;
        return $this->db_cache->set($key, $value);
    }

    /**
     * Get allowed values from POST
     */
    private function getAllowed($post): array
    {
        $profile_values = array_intersect_key($post, array_flip($this->allowed));
        return $profile_values;
    }

    /**
     * Set profile form values in cache
     */
    public function setProfileSetting(int $auth_id, string $setting, array $values)
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
        $this->db_cache->set($key, $values);
    }

    /**
     * Get a single setting from the 'profile' settings
     */
    public function getSingleProfileSetting(int $auth_id, string $profile_setting): ?string
    {
        $profile = $this->getUserSetting($auth_id, 'profile');

        if (isset($profile[$profile_setting])) {
            return $profile[$profile_setting];
        }
        return null;
    }
}
