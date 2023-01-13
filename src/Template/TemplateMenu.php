<?php

declare(strict_types=1);

namespace App\Template;

use App\Template\TemplateUtils;
use Diversen\Lang;

/**
 * Render Menu
 */
class TemplateMenu extends TemplateUtils
{
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


        if ($this->auth->isAuthenticated()) : ?>
            <div id="timer" class="timer">
                <span id="timer_display"></span>
                <button id="timer_start" class="button-timer button-small">Start</button>
                <button id="timer_pause" class="button-timer button-small">Pause</button>
                <button id="timer_reset" class="button-timer button-small">Reset</button>
            </div>
        <?php endif;
    }
}
