<?php declare (strict_types = 1);

namespace App\Settings;

use Diversen\Lang;
use Pebble\ACL;
use App\Settings\SettingsModel;
use Pebble\JSON;
use Pebble\Exception\NotFoundException;
use Pebble\Flash;
use App\AppMain;


class Controller
{

    public function __construct() {
        $this->acl = (new AppMain())->getAppACL();

    }
    /**
     * @route /settings
     * @verbs GET
     */
    public function index()
    {

        $this->acl->isAuthenticatedOrThrow();

        $settings = new SettingsModel();
        $user_settings = $settings->getUserSetting($this->acl->getAuthId(), 'profile');

        $vars['user_settings'] = $user_settings;

        \Pebble\Template::render('App/Settings/views/settings.tpl.php', $vars);
    }

    /**
     * Public route
     * @route /user/:auth_id
     * @verbs GET
     */
    public function user($params)
    {

        if (!filter_var($params['auth_id'], FILTER_VALIDATE_INT)) {
            throw new NotFoundException;
        }

        $settings = new SettingsModel();
        $user = $settings->getUserSetting($params['auth_id'], 'profile');

        \Pebble\Template::render('App/Settings/views/user.tpl.php', ['user' => $user]);
    }

    /**
     * @route /settings/put
     * @verbs POST
     */
    public function put()
    {

        $this->acl->isAuthenticatedOrThrow();

        $settings = new SettingsModel();
        $post = $_POST;
        $auth_id = $this->acl->getAuthId();

        $response['error'] = false;

        try {

            // Do not display message on 'overview' page
            if (isset($post['overview_current_day_state'])) {
                $settings->setUserSetting($auth_id, 'overview_current_day_state', $post['overview_current_day_state']);
            } else {
                $settings->setUserSetting($auth_id, 'profile', $post);
                Flash::setMessage(Lang::translate('Settings have been updated'), 'success', ['flash_remove' => true]);
            }

            // $settings->setUserSetting($auth_id, 'profile', $post);
            // Flash::setMessage(Lang::translate('Settings have been updated'), 'success', ['flash_remove' => true]);
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo JSON::responseAddRequest($response);
    }
}
