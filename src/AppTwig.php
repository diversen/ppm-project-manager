<?php

/**
 * This class exposes two methods: 
 * - a method to get a Twig instance
 * - a method to get a default context for Twig
 */

declare(strict_types=1);

namespace App;

use Pebble\App\StdUtils;
use App\Settings\SettingsModel;
use Diversen\Lang;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Twig\TwigFunction;
use Pebble\Path;
use App\AppMain;
use Pebble\URL;
use App\Utils\DateUtils;
use App\Utils\AppCal;
use Parsedown;

class AppTwig extends StdUtils
{

    /**
     * Get twig with loaded functions
     */
    public function getTwig(): \Twig\Environment
    {
        $base_path = Path::getBasePath();
        $loader = new FilesystemLoader([$base_path . '/src/templates']);
        $twig_config = $this->config->getSection('Twig');
        $twig = new Environment($loader, $twig_config);

        $twig->addFunction(new TwigFunction('translate', function ($sentence, $substitute = array(), $options = array()) {
            return Lang::translate($sentence, $substitute, $options);
        }));

        $twig->addFunction(new TwigFunction('get_config', function ($config) {
            return $this->config->get($config);
        }));

        $twig->addFunction(new TwigFunction('get_nonce', function () {
            return ($this->getCSP())->getNonce();
        }));

        $twig->addFunction(new TwigFunction('is_authenticated', function () {
            return $this->auth->isAuthenticated();
        }));

        $twig->addFunction(new TwigFunction('get_version', function () {
            return AppMain::VERSION;
        }));

        $twig->addFunction(new TwigFunction('has_role', function ($role) {
            return $this->acl_role->inSessionHasRole($role);
        }));

        $twig->addFunction(new TwigFunction('csrf_field', function () {
            return $this->csrf->getCSRFFormField();
        }));

        $twig->addFunction(new TwigFunction('get_flash_messages', function () {
            return $this->flash->getMessages();
        }));

        $twig->addFunction(new TwigFunction('return_to_url', function ($link, $return_to = null) {
            return URL::returnTo($link, $return_to);
        }));

        $twig->addFunction(new TwigFunction('render_markdown', function ($content, $safe = true) {
            $parsedown = new Parsedown();
            $parsedown->setSafeMode($safe);
            return $parsedown->text($content);
        }));

        $twig->addFunction(new TwigFunction('user_date_format', function ($ts, $format) {
            $date_utils = new DateUtils();
            return $date_utils->getUserDateTimeFormatted($ts, $format);
        }));

        $twig->addFunction(new TwigFunction('is_today', function ($ts) {
            return (new AppCal())->isToday($ts);
        }));

        return $twig;
    }

    /**
     * Get a default context for Twig
     */
    public function getContext(array $variables = []): array
    {
        // Create a context array that will contain some global variables
        // for the Twig templates
        $context = [
            'flash_messages' => $this->flash->getMessages(),
            'dark_mode' => $this->useDarkMode(),
            'home_url' => $this->getHomeURL(),
        ];

        // merge context with variables
        $context = array_merge($context, $variables);

        return $context;
    }

    /**
     * Get home url. Used in getContext
     */
    private function getHomeURL(): string
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

    /**
     * Is dark mode enabled. Used in getContext
     */
    private function useDarkMode(): ?string
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
