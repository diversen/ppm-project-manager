<?php

declare(strict_types=1);

namespace App\Settings;

use Diversen\Lang;

use Pebble\Exception\NotFoundException;
use Pebble\Exception\JSONException;
use Pebble\ExceptionTrace;
use Pebble\Attributes\Route;
use App\AppUtils;
use App\Settings\SettingsModel;
use Exception;

class Controller extends AppUtils
{
    public function __construct()
    {
        parent::__construct();
    }

    #[Route(path: '/settings')]
    public function index(): void
    {
        $this->acl->isAuthenticatedOrThrow();

        $settings = new SettingsModel();
        $user_settings = $settings->getUserSetting($this->acl->getAuthId(), 'profile');

        $vars['user_settings'] = $user_settings;

        $this->renderPage('Settings/views/settings.tpl.php', $vars);
    }

    #[Route(path: '/user/:auth_id')]
    public function user(array $params): void
    {
        if (!filter_var($params['auth_id'], FILTER_VALIDATE_INT)) {
            throw new NotFoundException();
        }

        $settings = new SettingsModel();
        $user = $settings->getUserSetting((int)$params['auth_id'], 'profile');

        $this->renderPage('Settings/views/user.tpl.php', ['user' => $user]);
    }

    #[Route(path: '/settings/put', verbs: ['POST'])]
    public function put(): void
    {
        $settings = new SettingsModel();
        $post = $_POST;
        try {
            $this->acl->isAuthenticatedOrThrow();
            $auth_id = $this->acl->getAuthId();
            $settings->setProfileSetting($auth_id, 'profile', $post);
            $this->flash->setMessage(Lang::translate('Settings have been updated'), 'success', ['flash_remove' => true]);
            $this->json->renderSuccess();
        } catch (Exception $e) {
            $this->log->error($e->getMessage(), ['exception' => ExceptionTrace::get($e)]);
            throw new JSONException(Lang::translate('Your settings could not be saved. Check if you are logged in'));
        }
    }
}
