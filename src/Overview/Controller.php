<?php

declare(strict_types=1);

namespace App\Overview;

use Pebble\URL;
use Pebble\ExceptionTrace;
use App\AppUtils;
use App\Utils\AppCal;
use App\Time\TimeModel;
use App\Settings\SettingsModel;
use App\Project\ProjectModel;
use Diversen\Lang;
use Exception;
use Pebble\Exception\JSONException;
use Pebble\Attributes\Route;

class Controller extends AppUtils
{
    private $project_model;
    private $auth_id;
    public function __construct()
    {
        parent::__construct();
        $this->auth_id = $this->auth->getAuthId();
        $this->project_model = new ProjectModel();
    }

    #[Route(path: '/overview')]
    public function index()
    {
        $this->acl->isAuthenticatedOrThrow();

        $cal = new AppCal();
        $week_delta_current = (int) URL::getQueryPart('week_delta');

        // Only display current day state
        $current_day_state = (new SettingsModel())->getUserSetting($this->auth_id, 'overview_current_day_state') ?? null;

        $week_state = [
            'week_number_delta' =>      $cal->getWeekNumberFromDelta($week_delta_current),
            'week_number_delta_next' => $cal->getWeekNumberFromDelta($week_delta_current + 1),
            'week_number_delta_prev' => $cal->getWeekNumberFromDelta($week_delta_current - 1),
            'week_number' =>            $cal->getWeekNumberFromDelta(0),
            'current' =>                $week_delta_current,
            'next' =>                   $week_delta_current + 1,
            'prev' =>                   $week_delta_current - 1,
            'current_day_state' =>      $current_day_state,
        ];

        $time_model = new TimeModel();

        $week_ts = $cal->getCurrentWeekDays($week_delta_current);
        $week_data = $time_model->getWeekData($week_ts);
        $week_time = $time_model->getWeekTimes($week_ts);

        $template_data = [
            'week_data' =>              $week_data,
            'week_state' =>             $week_state,
            'week_user_day_times' =>    $week_time['week_user_day_times'],
            'week_user_total' =>        $week_time['week_user_total'],
            'title' => Lang::translate('Overview'),
            'description' => Lang::translate('Overview by week'),
            'has_projects' => $this->project_model->userHasProjects($this->auth_id),
        ];

        $this->template_utils->renderPage('Overview/overview.tpl.php', $template_data);
    }

    #[Route(path: '/overview/settings/put', verbs: ['POST'])]
    public function setSettings()
    {
        $settings = new SettingsModel();
        $post = $_POST;

        try {
            $this->acl->isAuthenticatedOrThrow();
            $auth_id = $this->acl->getAuthId();

            if (isset($post['overview_current_day_state'])) {
                $settings->setUserSetting($auth_id, 'overview_current_day_state', $post['overview_current_day_state']);
            }
            $this->json->renderSuccess();
        } catch (Exception $e) {
            $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException(Lang::translate('Your settings could not be saved. Check if you are logged in'));
        }
    }
}
