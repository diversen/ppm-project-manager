<?php

declare(strict_types=1);

namespace App\Utils;

use App\AppUtils;
use App\AppMain;
use App\Settings\SettingsModel;
use Diversen\Lang;

/**
 * Template utils
 */
class TemplateUtils extends AppUtils
{
    public function getHomeURL(): string
    {
        $is_authenticated = $this->auth->isAuthenticated();
        if ($is_authenticated) {
            $home_url = $this->config->get('App.home_url_authenticated');
        } else {
            $home_url = $this->config->get('App.home_url');
        }

        if (!$home_url) {
            $home_url = '/';
        }

        return $home_url;
    }

    public function renderLogo(): void
    { ?>
        <div class="logo">
            <a title="<?= $this->config->get('App.site_name') ?>" href="<?= $this->getHomeURL() ?>">
                <img src="/assets/logo.png?version=<?= AppMain::VERSION ?>" alt="<?= $this->config->get('App.site_name') ?>" title="<?= $this->config->get('App.site_name') ?>" width="70" height="70" />
            </a>
        </div>
    <?php
    }

    public function renderMainMenu(): void
    {
        $is_authenticated = $this->auth->isAuthenticated(); ?>
        <div class="app-menu">
            <?php if (!$is_authenticated) : ?>
                <a href="/account/signin" data-path="/account/signin"><?= Lang::translate('Sign in') ?></a>
                <a href="/account/signup" data-path="/account/signup"><?= Lang::translate('Email sign up') ?></a>
                <a href="/account/recover" data-path="/account/recover"><?= Lang::translate('Lost password') ?></a>
            <?php else : ?>
                <a href="/overview" data-path="/overview"><?= Lang::translate('Home') ?></a>
                <a href="/project" data-path="/project"><?= Lang::translate('Projects') ?></a>
                <a href="/settings" data-path="/settings"><?= Lang::translate('Settings') ?></a>
                <a href="/account/signout" data-path="/account/signout"><?= Lang::translate('Sign out') ?></a>
                <a id="timer_toggle" title="<?= Lang::translate('Toggle timer') ?>">&#128337; <?= Lang::translate('Timer') ?></a>
            <?php endif; ?>
        </div>

        <?php if ($this->acl_role->inSessionHasRole('admin')) : ?>
            <div class="app-menu app-menu-admin">
                <a href="/admin" data-path="/admin"><?= Lang::translate('Admin') ?></a>
            </div>
        <?php endif;


        if ($this->auth->isAuthenticated()): ?>
            <div id="timer" class="timer" >
                <span id="timer_display"></span>
                <button id="timer_start" class="button-timer button-small">Start</button>
                <button id="timer_pause" class="button-timer button-small">Pause</button>
                <button id="timer_reset" class="button-timer button-small">Reset</button>
            </div>
        <?php endif;
    }

    public function renderFlashMessages(): void
    {
        $flash_messages = $this->flash->getMessages(); ?>
        <div class="flash-messages">
            <?php

            foreach ($flash_messages as $message) :
                $remove_class = '';
        if (isset($message['options']['flash_remove'])) {
            $remove_class = ' flash-remove ';
        } ?>
                <div class="flash flash-<?= $message['type'] ?> <?= $remove_class ?>"><?= $message['message'] ?></div>
            <?php

            endforeach; ?>
        </div>
        <?php
    }

    public function useDarkMode()
    {
        $settings = new SettingsModel();
        $is_authenticated = $this->auth->isAuthenticated();
        if (!$is_authenticated && isset($_COOKIE['theme_dark_mode'])) {
            $use_dark_mode = $_COOKIE['theme_dark_mode'];
        } else {
            $profile = $settings->getUserSetting($this->auth->getAuthId(), 'profile');
            $use_dark_mode = $profile['theme_dark_mode'] ?? null;
        }
        return $use_dark_mode;
    }
}
