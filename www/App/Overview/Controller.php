<?php declare (strict_types = 1);

namespace App\Overview;

use App\Cal;
use App\Time\TimeModel;
use Pebble\URL;
use Pebble\ACL;
use Pebble\Auth;
use App\Settings\SettingsModel;
use Diversen\Lang;

class Controller
{

    public function __construct()
    {
        $this->auth_id = Auth::getInstance()->getAuthId();
    }

    /**
     * @route /overview
     * @verbs GET
     */
    public function index()
    {

        (new ACL())->isAuthenticatedOrThrow();

        $cal = new Cal();
        $week_delta_current = (int) URL::getQueryPart('week_delta');

        // Only display current day state
        $current_day_state = (new SettingsModel())->getUserSetting($this->auth_id, 'overview_current_day_state') ?? null;

        $week_state = [
            'week_number_delta' =>      $cal->getWeekNumberFromDelta($week_delta_current),
            'week_number_delta_next' => $cal->getWeekNumberFromDelta($week_delta_current + 1),
            'week_number_delta_prev' => $cal->getWeekNumberFromDelta($week_delta_current -1 ),
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

        \Pebble\Template::render('App/Overview/overview.tpl.php', $data);

    }
}
