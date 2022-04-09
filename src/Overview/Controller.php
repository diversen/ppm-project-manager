<?php

declare(strict_types=1);

namespace App\Overview;

use App\AppMain;
use App\Utils\AppCal;
use App\Time\TimeModel;
use Pebble\URL;

use App\Settings\SettingsModel;
use Diversen\Lang;

class Controller
{
    public function __construct()
    {
        $this->app_main = new AppMain();
        $this->auth_id = $this->app_main->getAuth()->getAuthId();
    }

    /**
     * @route /overview
     * @verbs GET
     */
    public function index()
    {
        $this->app_main->getAppACL()->isAuthenticatedOrThrow();

        $cal = new AppCal();
        $week_delta_current = (int) URL::getQueryPart('week_delta');

        // Only display current day state
        $current_day_state = (new SettingsModel())->getUserSetting($this->auth_id, 'overview_current_day_state') ?? null;

        $week_state = [
            'week_number_delta' =>      $cal->getWeekNumberFromDelta($week_delta_current),
            'week_number_delta_next' => $cal->getWeekNumberFromDelta($week_delta_current + 1),
            'week_number_delta_prev' => $cal->getWeekNumberFromDelta($week_delta_current -1),
            'week_number' =>            $cal->getWeekNumberFromDelta(0),
            'current' =>                $week_delta_current,
            'next' =>                   $week_delta_current + 1,
            'prev' =>                   $week_delta_current - 1,
            'current_day_state' =>      $current_day_state,
        ];

        $timeModel = new TimeModel();

        $week_ts = $cal->getCurrentWeekDays($week_delta_current);
        $week_data = $timeModel->getWeekData($week_ts);
        $week_time = $timeModel->getWeekTimes($week_ts);

        $data = [
            'week_data' =>              $week_data,
            'week_state' =>             $week_state,
            'week_user_day_times' =>    $week_time['week_user_day_times'],
            'week_user_total' =>        $week_time['week_user_total'],
            'title' => Lang::translate('Overview'),
            'description' => Lang::translate('Overview by week'),
        ];

        \Pebble\Template::render('Overview/overview.tpl.php', $data);
    }
}
