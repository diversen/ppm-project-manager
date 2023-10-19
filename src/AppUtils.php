<?php

declare(strict_types=1);

namespace App;

use Pebble\App\StdUtils;
use App\Settings\SettingsModel;
use Pebble\Service\Container;
use App\AppACL;
use App\Template\TemplateUtils;
use Diversen\Lang;
use Twig\Loader\FilesystemLoader;
use Twig\Environment;
use Pebble\Path;
use Twig\TwigFunction;
use Pebble\Service\AuthService;
use Pebble\Service\ACLRoleService;
use App\AppMain;

function get_config($setting)
{

    $config = (new AppMain())->getConfig();
    return $config->get($setting);
}

function get_nonce()
{
    return (new AppUtils())->getCSP()->getNonce();
}

function use_dark_mode()
{
    $auth = (new AuthService())->getAuth();
    $settings = new SettingsModel();
    $is_authenticated = $auth->isAuthenticated();
    if (!$is_authenticated && isset($_COOKIE['theme_dark_mode'])) {
        $use_dark_mode = $_COOKIE['theme_dark_mode'];
    } else {
        $profile = $settings->getUserSetting($auth->getAuthId(), 'profile');
        $use_dark_mode = $profile['theme_dark_mode'] ?? null;
    }
    return $use_dark_mode;
}


function has_role($role)
{
    $acl_role = (new ACLRoleService())->getACLRole();
    $role = $acl_role->inSessionHasRole($role);
    return $role;
}

function get_version()
{
    return "v1.0.1";
    // return AppMain::VERSION;
}


/**
 * App spcific utils that extends StdUtils
 * Add some App specific methods to AppUtils
 */
class AppUtils extends StdUtils
{
    public const VERSION = "v2.2.3";


    /**
     * @var \App\AppACL
     */
    protected $app_acl;

    /**
     * @var \App\Template\TemplateUtils
     */
    protected $template_utils;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    public function __construct()
    {
        parent::__construct();
        $this->csrf->setErrorMessage(Lang::translate('Invalid Request. We will look in to this'));
        $this->app_acl = $this->getAppACL();
        $this->template_utils = new TemplateUtils();
        $this->twig = $this->getTwig();
    }

    private function getAppACL(): \App\AppACL
    {
        $container = new Container();
        if (!$container->has('app_acl')) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            $app_acl = new AppAcl($this->getDB(), $auth_cookie_settings);
            $container->set('app_acl', $app_acl);
        }
        return $container->get('app_acl');
    }

    private function getTwig(): \Twig\Environment
    {
        $container = new Container();
        if (!$container->has('twig')) {
            $base_path = Path::getBasePath();
            $loader = new FilesystemLoader($base_path . '/templates');
            $twig = new Environment($loader, [
                'cache' => false , // Be careful not to place the cache in a public directory
                'auto_reload' => true,
                'debug' => true,
            ]);

            $twig->addFunction(new TwigFunction('translate', function ($sentence, $substitute = array(), $options = array()) {
                return Lang::translate($sentence, $substitute, $options);
            }));

            $twig->addFunction(new TwigFunction('get_config', function ($config) {
                return get_config($config);
            }));

            $twig->addFunction(new TwigFunction('get_nonce', function () {
                return ($this->getCSP())->getNonce();
            }));

            $twig->addFunction(new TwigFunction('is_authenticated', function () {
                return $this->auth->isAuthenticated();
            }));

            $twig->addFunction(new TwigFunction('get_version', function () {
                return AppUtils::VERSION;
            }));

            $twig->addFunction(new TwigFunction('has_role', function ($role) {
                return $this->acl_role->inSessionHasRole($role);
            }));

            $twig->addFunction(new TwigFunction('csrf_field', function () {
                return $this->csrf->getCSRFFormField();
            }));

            $container->set('twig', $twig);
        }
        return $container->get('twig');
    }

    /**
     * Get home url. Used in getContext
     */
    private function getHomeURL() {

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
     * Use dark mode. Used in getContext
     */
    private function useDarkMode()
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

    public function getContext($variables = [])
    {
        // Create twig context
        $context = [
            'flash_messages' => $this->flash->getMessages(),
            'dark_mode' => $this->useDarkMode(),
            'home_url' => $this->getHomeURL(),
        ];

        // merge context with variables
        $context = array_merge($context, $variables);

        return $context;
    }
}
