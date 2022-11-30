<?php

declare(strict_types=1);

namespace App;

use Pebble\App\StdUtils;
use Pebble\Service\Container;
use App\AppACL;

/**
 * App spcific utils that extends StdUtils
 */
class AppUtils extends StdUtils
{
    /**
     * @var \App\AppACL
     */
    protected $app_acl;

    public function __construct()
    {
        parent::__construct();
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


    /**
     * Render a template including header and footer
     */
    public function renderPage(string $template, array $data = [], $options = [])
    {
        $this->template->render('templates/header.tpl.php', $data, $options);
        $this->template->render($template, $data, $options);
        $this->template->render('templates/footer.tpl.php', $data, $options);
    }
}
