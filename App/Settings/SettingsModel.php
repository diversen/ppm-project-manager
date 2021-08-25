<?php declare(strict_types=1);

namespace App\Settings;

use Pebble\DBCache;

class SettingsModel {


    /**
     * Get a user setting
     */
    public function getUserSetting($auth_id, $setting) {
        $cache = new DBCache();
        $key = $auth_id . '_settings_' . $setting;
        return $cache->get($key);
    }

    /**
     * set a user setting
     */
    
    public function setUserSetting($auth_id, $setting, $value) {
        $key = $auth_id . '_settings_' . $setting;
        $cache = new DBCache();
        return $cache->set($key, $value);
    }

    /**
     * Get a single setting from the 'profile' settings
     */
    public function getSingleProfileSetting($auth_id, $profile_setting, $default) {
        $profile = $this->getUserSetting($auth_id, 'profile');

        if (isset($profile[$profile_setting])) {
            return $profile[$profile_setting];
        }
    }
}
