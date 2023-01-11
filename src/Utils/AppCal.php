<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * A class for getting calendar information
 * about weeks and days
 */
class AppCal
{
    /**
     * Get an array containing the weekdays where the key is a unix timestamp
     * and the value is a date string formatted according to $format
     */
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

    /**
     * Get a week delta string, e.g. +1 week or -10 week
     * @return string $week_delta_str a string representing that week that can be read by strtotime
     */
    public function getWeekDeltaStr(int $week_delta)
    {
        $week_delta_str = '';
        if ($week_delta < 0) {
            $week_delta_str = '-' . abs($week_delta) . " week";
        }
        if ($week_delta > 0) {
            $week_delta_str = '+' . $week_delta . " week";
        }
        return $week_delta_str;
    }

    /**
     * Get a week number as string from a week delta. e.g. -2 or +4
     * indicating the week number calculated from the current week)
     */
    public function getWeekNumberFromDelta(int $week_delta)
    {
        $week_delta_str = $this->getWeekDeltaStr($week_delta);
        return date('W', strtotime("today $week_delta_str"));
    }
}
