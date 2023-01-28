<?php

declare(strict_types=1);

namespace App;

use Pebble\App\StdUtils;
use Pebble\Service\Container;
use App\AppACL;
use Diversen\Lang;

/**
 * App spcific utils that extends StdUtils
 * Extend this class and you can use the methods in your controllers or models
 */
class AppUtils extends StdUtils
{
    use \App\Template\Trait\Render;
    /**
     * @var \App\AppACL
     */
    protected $app_acl;

    public function __construct()
    {
        parent::__construct();
        $this->csrf->setErrorMessage(Lang::translate('Invalid Request. We will look in to this'));
        $this->app_acl = $this->getAppACL();
    }

    /**
     * @return \App\AppACL
     */
    public function getAppACL()
    {
        $container = new Container();
        if (!$container->has('app_acl')) {
            $auth_cookie_settings = $this->getConfig()->getSection('Auth');
            $app_acl = new AppAcl($this->getDB(), $auth_cookie_settings);
            $container->set('app_acl', $app_acl);
        }
        return $container->get('app_acl');
    }
}
