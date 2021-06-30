<?php

namespace App\Settings;

use \Pebble\Auth;
use \Pebble\ACL;
use \App\Settings\SettingsModel;
use \Diversen\Lang;
use \Pebble\Flash;

class Controller
{

    public function __construct()
    {
        $auth = new Auth();
        $this->auth_id = $auth->getAuthId();
    }



    public function index() {

        (new ACL())->isAuthenticatedOrThrow();

        $settings = new SettingsModel();

        $user_settings = $settings->getAllUserSettings();

        \Pebble\Template::render('App/Settings/settings.tpl.php', $user_settings);
    }

    public function put()
    {

        (new ACL())->isAuthenticatedOrThrow();

        $settings = new SettingsModel();
        $post = $_POST;

        $response['error'] = false;
        
        try {

            $settings->setUserSettings($post);

            // Do not display message on 'overview' page
            if (!isset($post['overview_current_day_state'])) {
                Flash::setMessage(Lang::translate('Settings have been updated'), 'success', ['flash_remove' => true]);
            }
        } catch (\Exception $e) {
            $response['error'] = $e->getMessage();
        }

        header('Content-Type: application/json');
        echo json_encode($response);

    }
}
