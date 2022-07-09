<?php

declare(strict_types=1);

namespace App\Utils;

use App\Settings\SettingsModel;
use DateTime;
use DateTimeZone;
use Pebble\Service\AuthService;

class DateUtils
{
    private $user_timezone;
    private $auth;
    public function __construct()
    {
        $this->auth = (new AuthService())->getAuth();
        $this->user_timezone = $this->getUserTimezone();
    }

    private function getUserTimeZone()
    {
        $settings_model = new SettingsModel();
        $auth_id = $this->auth->getAuthId();
        $timezone = $settings_model->getSingleProfileSetting($auth_id, 'timezone', null);
        if (!$timezone) {
            $timezone = $this->config->get('App.timezone');
        }
        return $timezone;
    }

    /**
     * Get a UTC date in format Y-m-d H:i:s from a supplied datetime string, e.g. 'now'
     */
    public function getUTCDate($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {

        // Generate a date in the default timezone (e.g. user's timezone)
        $date = new DateTime($datetime_str);

        // Convert to UTC
        $date->setTimezone(new DateTimeZone('UTC'));
        return $date->format($format);
    }

    public function getUserDateFromUTC($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {
        $date = new DateTime($datetime_str, new DateTimeZone('UTC'));
        $date->setTimezone(new DateTimeZone($this->user_timezone));
        return $date->format($format);
    }
}
