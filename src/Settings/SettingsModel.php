<?php

declare(strict_types=1);

namespace App\Settings;

use Pebble\DBCache;
use App\AppMain;

class SettingsModel
{
    private $cache = null;
    public function __construct()
    {
        $db = (new AppMain())->getDB();
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
