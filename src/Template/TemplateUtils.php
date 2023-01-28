<?php

declare(strict_types=1);

namespace App\Template;

use Pebble\Path;
use App\AppUtils;
use App\AppMain;
use App\Settings\SettingsModel;
use App\Template\MetaData;
use Pebble\Service\Container;

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
                <img src="/assets/logo.png?version=<?= AppMain::VERSION ?>" alt="<?= $this->config->get('App.site_name') ?>" title="<?= $this->config->get('App.site_name') ?>" width="70" height="70">
            </a>
        </div>
    <?php
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
            <?php endforeach; ?>
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


    /**
     * Just a container for the app's meta data
     * May contain anything
     * @return \App\Template\MetaData
     */
    public function getMetaData() {
        $container = new Container();
        if (!$container->has('meta_data')) {
            $meta_data = new MetaData();
            $container->set('meta_data', $meta_data);
        }
        return $container->get('meta_data');
    }

    public function getTemplatePath(): string
    {
        return Path::getBasePath() . '/src/Template';
    }
}
