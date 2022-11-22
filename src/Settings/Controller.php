<?php

declare(strict_types=1);

namespace App\Settings;

use Diversen\Lang;

use Pebble\Exception\NotFoundException;
use Pebble\ExceptionTrace;
use App\AppUtils;
use App\Settings\SettingsModel;
use Exception;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
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

        $this->renderPage('Settings/views/settings.tpl.php', $vars);
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

        $this->renderPage('Settings/views/user.tpl.php', ['user' => $user]);
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
        $this->json->render($response);
    }
}
