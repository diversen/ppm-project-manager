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
     * Get a UTC date in format Y-m-d H:i:s from a supplied datetime string, e.g. 'now'
     */
    public function getUTCDate($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {

        $date = new DateTime($datetime_str);
        $date->setTimezone(new DateTimeZone('UTC'));
        return $date->format($format);
    }

    public function getUserDateFromUTC($datetime_str = 'now', $format = 'Y-m-d 00:00:00')
    {
        $date = new DateTime('now');
        $date->modify($datetime_str);
        $date->setTimezone(new DateTimeZone($this->user_timezone));
        return $date->format($format);
    }

    /**
     * function that get a DateTime object from a unix timestamp
     */
    public function getUserDateTimeFromUnixTs($timestamp): DateTime
    {
        $date = new DateTime('now', new DateTimeZone('UTC'));
        $date->setTimestamp($timestamp);
        $date->setTimezone(new DateTimeZone($this->user_timezone));

        return $date;
    }
    
}
