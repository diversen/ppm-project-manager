<?php

declare(strict_types=1);

namespace App\Utils;

use App\Settings\SettingsModel;
use DateTime;
use DateTimeZone;
use App\AppUtils;

class DateUtils extends AppUtils
{
    private $user_timezone;

    public function __construct()
    {
        parent::__construct();
        $this->user_timezone = $this->getUserTimezone();
    }

    public function getUserTimeZone(int $auth_id = null)
    {
        $settings_model = new SettingsModel();
        if (!$auth_id) {
            $auth_id = $this->auth->getAuthId();
        }

        $timezone = $settings_model->getSingleProfileSetting($auth_id, 'timezone');
        if (!$timezone) {
            $timezone = $this->config->get('App.timezone');
        }
        return $timezone;
    }

    /**
     * Get a datetime formatted using a DateTime constructor string, a timezone, and the format
     */
    public function getDateFormat($datetime_str = 'now', $timezone='UTC', $format = 'Y-m-d 00:00:00')
    {
        $date = new DateTime($datetime_str, new DateTimeZone($timezone));
        return $date->format($format);
    }


    /**
     * Convert a UTC timestamp into a user's timezone and return a formatted date
     * Used for data saved as UTC in the database
     */
    public function getUserDateFormatFromUTC($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {
        $date_time = new DateTime($datetime_str, new DateTimeZone('UTC'));
        $date_time->setTimezone(new DateTimeZone($this->user_timezone));
        return $date_time->format($format);
    }

    /**
     * Get a DateTime object from a unix timestamp and implicit the script's timezone
     * (which is the user's timezone)
     */
    public function getUserDateTimeFromUnixTs($timestamp): DateTime
    {
        // User timezone is implicit set by date_default_timezone_set() in AppMain
        $date_time = new DateTime('now');
        $date_time->setTimestamp($timestamp);
        return $date_time;
    }
}
