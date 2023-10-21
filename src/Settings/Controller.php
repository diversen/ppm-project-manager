<?php

declare(strict_types=1);

namespace App\Settings;

use Diversen\Lang;

use Pebble\Exception\NotFoundException;
use Pebble\Exception\JSONException;
use Pebble\ExceptionTrace;
use Pebble\Attributes\Route;
use Pebble\Router\Request;
use App\AppUtils;
use App\Settings\SettingsModel;
use Exception;
use Pebble\HTML\Tag;

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

        $links = [];
        if ($this->config->get('TwoFactor.enabled')) {
            $links[] = Tag::getTag('a', Lang::translate('Two factor authentication'), ['href' => '/twofactor/enable']);
        }
        if ($this->config->get('Notification.enabled')) {
            $links[] = Tag::getTag('a', Lang::translate('Notifications'), ['href' => '/notification']);
        }

        $context['links'] = $links;
        $context['user_settings'] = $user_settings;
        $context['timezones'] = timezone_identifiers_list();
        $context['languages'] = $this->config->get('Language.enabled');
        
        echo $this->twig->render('settings/update.twig', $this->getContext($context));

    }

    #[Route(path: '/settings/profile')]
    public function user(Request $request): void
    {
        $auth_id = $this->auth->getAuthId();
        if (!$auth_id) {
            throw new NotFoundException();
        }
         
        $settings = new SettingsModel();
        $user = $settings->getUserSetting( $auth_id, 'profile');

        $context['name'] = $user['name'] ?? '';
        $context['bio'] = $user['bio'] ?? '';

        echo $this->twig->render('settings/profile.twig', $this->getContext($context));
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
