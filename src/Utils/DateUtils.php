<?php

declare(strict_types=1);

namespace App\Utils;

use App\Settings\SettingsModel;
use DateTime;
use DateTimeZone;
use Pebble\App\StdUtils;

class DateUtils extends StdUtils
{
    private $user_timezone;

    public function __construct()
    {
        parent::__construct();
        $this->user_timezone = $this->getUserTimezone();
    }

    public function getUserTimeZone(string $auth_id = null)
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
     * Get a datetime formatted using a DateTime constructor string, the format and implicitly the user's timezone
     */
    public function getUserDateFormatFromUTC($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {
        return $this->getDateFormat($datetime_str, $this->user_timezone, $format);
    }

    /**
     * Get a DateTime object from a unix timestamp and implicit the user's timezone
     */
    public function getUserDateTimeFromUnixTs($timestamp): DateTime
    {
        $date_time = new DateTime('now', new DateTimeZone('UTC'));
        $date_time->setTimestamp($timestamp);
        $date_time->setTimezone(new DateTimeZone($this->user_timezone));
        return $date_time;
    }
}
