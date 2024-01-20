<?php

use \App\Utils\AppCal;

// require_once "vendor/autoload.php';
require_once "vendor/autoload.php";

$app_cal = new AppCal();

// $app_cal->isToday();

// Set timezone to tokyo

/**
 * Get todays date as yyyy-mm-dd hh:mm:ss
 */
function getTodayDate($timezone)
{
    date_default_timezone_set($timezone);
    $today_ts = strtotime('now');
    $today_date = date('Y-m-d H:i:s', $today_ts);
    return $today_date;

}

// Tokyo
echo "Current date time in tokyo " . getTodayDate('Asia/Tokyo') . "\n";
$week_days_tokyo = $app_cal->getCurrentWeekDays(0);
print_r($week_days_tokyo); # Get weekdays for this week
$last_sunday_tokyo = strtotime('last sunday 23:00'); # Get timestamp for last sunday 23:00
echo "Last sunday 23:00 tokyo " . $last_sunday_tokyo . "\n";

$week_number_tokyo = date('W', $last_sunday_tokyo);
echo "Week number tokyo " . $week_number_tokyo . "\n";

// Copenhagen
echo "Current date time in copenhagen " . getTodayDate('Europe/Copenhagen') . "\n";
$week_days_copenhagen = $app_cal->getCurrentWeekDays(0);
print_r($week_days_copenhagen);
$last_suday_copenhagen = strtotime('last sunday 23:00');
echo "Last sunday 23:00 copenhagen " . $last_suday_copenhagen . "\n";

$week_number_copenhagen = date('W', $last_suday_copenhagen);
echo "Week number copenhagen " . $week_number_copenhagen . "\n";

// Shift datetime to tokyo
date_default_timezone_set('Asia/Tokyo');

// When it is 23:00 sunday in copenhagen what is the week number in tokyo
$week_number_tokyo = date('W', $last_suday_copenhagen);

// This will alway be +1 week in tokyo compared to copenhagen
echo "When the time in copenhagen is sunday 23:00. Week number in tokyo is: " . $week_number_tokyo . "\n";
 