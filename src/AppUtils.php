<?php

/**
 * Setup all utils which can then be used in the App
 * 
 * Add app specific utils here: 
 * - App specific ACL
 * - App specific Twig functions
 * - App specific default Twig Context 
 */

declare(strict_types=1);

namespace App;

use Pebble\App\StdUtils;
use Pebble\Service\Container;
use App\AppACL;
use Diversen\Lang;
use App\AppTwig;

class AppUtils extends StdUtils
{

    /**
     * @var \App\AppACL
     */
    protected $app_acl;

    /**
     * @var \Twig\Environment
     */
    protected $twig;

    /**
     * @var \App\AppTwig
     */
    private $app_twig;

    public function __construct()
    {
        parent::__construct();
        $this->csrf->setErrorMessage(Lang::translate('Invalid Request. We will look in to this'));
        $this->app_acl = $this->getAppACL();
        $this->app_twig = new AppTwig();
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

            $twig = $this->app_twig->getTwig();
            $container->set('twig', $twig);
        }
        return $container->get('twig');
    }

    /**
     * Get a default context to use in Twig
     */
    public function getContext($variables = [])
    {
        return $this->app_twig->getContext($variables);
    }
}
