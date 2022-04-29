<?php

declare(strict_types=1);

namespace App\Settings;

use Diversen\Lang;
use App\Settings\SettingsModel;
use Pebble\JSON;
use Pebble\Exception\NotFoundException;
use Pebble\ExceptionTrace;
use Pebble\Flash;
use App\AppMain;
use Exception;

class Controller
{
    private $acl;
    private $log;
    private $flash;
    public function __construct()
    {
        $this->acl = (new AppMain())->getAppACL();
        $this->log = (new AppMain())->getLog();
        $this->flash = new Flash();
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

        \Pebble\Template::render('Settings/views/settings.tpl.php', $vars);
    }

    /**
     * Public route
     * @route /user/:auth_id
     * @verbs GET
     */
    public function user($params)
    {
        if (!filter_var($params['auth_id'], FILTER_VALIDATE_INT)) {
            throw new NotFoundException();
        }

        $settings = new SettingsModel();
        $user = $settings->getUserSetting($params['auth_id'], 'profile');

        \Pebble\Template::render('Settings/views/user.tpl.php', ['user' => $user]);
    }

    /**
     * @route /settings/put
     * @verbs POST
     */
    public function put()
    {
        $settings = new SettingsModel();
        $post = $_POST;

        $response['error'] = false;

        try {
            $this->acl->isAuthenticatedOrThrow();
            $auth_id = $this->acl->getAuthId();

            $settings->setProfileSetting($auth_id, 'profile', $post);
            $this->flash->setMessage(Lang::translate('Settings have been updated'), 'success', ['flash_remove' => true]);
        } catch (Exception $e) {
            $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);
            $response['error'] = Lang::translate('Your settings could not be saved. Check if you are logged in');
        }

        header('Content-Type: application/json');
        echo JSON::response($response);
    }
}
