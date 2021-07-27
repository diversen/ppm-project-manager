<?php declare (strict_types = 1);

namespace App;

use DateTime;
use DateTimeZone;
use Pebble\Auth;
use Pebble\Config;

use App\Settings\SettingsModel;

class Cal
{

    private function getUserTimeZone() {

        $settings_model = new SettingsModel;
        $auth_id = Auth::getInstance()->getAuthId();
        $timezone = $settings_model->getSingleProfileSetting($auth_id, 'timezone', null);
        if (!$timezone) {
            $timezone = Config::get('App.timezone');
        }
        return $timezone;

    }
    /**
     * Get a UTC date in format Y-m-d H:i:s from a supplied datetime string
     */
    public function userDateToUTC($datetime_str = 'now') {

        $user_time_zone = $this->getUserTimeZone();
        $date = new DateTime($datetime_str, new DateTimeZone($user_time_zone));

        $date->setTimezone(new DateTimeZone('UTC'));
        return $date->format('Y-m-d 00:00:00');
    }

    
    public function userDate($datetime_str = 'now', $format = 'Y-m-d 00:00:00') {

        $user_time_zone = $this->getUserTimeZone();

        $date = new DateTime($datetime_str, new DateTimeZone($user_time_zone) );
        return $date->format($format);

    }

    public function userDateFromUnixTs($unix_ts) {
        $date_time = new DateTime();
        $date_time->setTimestamp($unix_ts);

        $user_time_zone = $this->getUserTimeZone();

        $date_time->setTimezone(new DateTimeZone($user_time_zone));
        return $date_time->format('Y-m-d 00:00:00');
        
    }

     
    public function getCurrentWeekDays(int $week_delta, string $format = 'Y-m-d H:i:s')
    {

        
        $week_delta_str = $this->getWeekDeltaStr($week_delta);

        $week = [];

        $ts_mon = strtotime("Monday this week $week_delta_str");
        $week[$ts_mon] = date($format, $ts_mon);
        
        $ts_tue = strtotime("Tuesday this week $week_delta_str");
        $week[$ts_tue] = date($format, $ts_tue);

        $ts_wed = strtotime("Wednesday this week $week_delta_str");
        $week[$ts_wed] = date($format, $ts_wed);

        $ts_thu = strtotime("Thursday this week $week_delta_str");
        $week[$ts_thu] = date($format, $ts_thu);

        $ts_fri = strtotime("Friday this week $week_delta_str");
        $week[$ts_fri] = date($format, $ts_fri);

        $ts_sat = strtotime("Saturday this week $week_delta_str");
        $week[$ts_sat] = date($format, $ts_sat);

        $ts_sun = strtotime("Sunday this week $week_delta_str");
        $week[$ts_sun] = date($format, $ts_sun);

        return $week;

    }

    public function getWeekDeltaStr(int $week_delta) { 
        $week_delta_str = '';
        if ($week_delta < 0) {
            $week_delta_str = '-' . abs($week_delta) . " week";

        }
        if ($week_delta > 0) {
            $week_delta_str = '+' . $week_delta . " week";
        }
        return $week_delta_str;
    }

    public function getWeekNumberFromDelta(int $week_delta) {
        
        $week_delta_str = $this->getWeekDeltaStr($week_delta);
        return date('W', strtotime("today $week_delta_str"));
    }

    
}
