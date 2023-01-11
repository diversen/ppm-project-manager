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


    /**
     * Render a template as HTML including a header and footer
     */
    public function renderPage(string $template, array $data = [], $options = [])
    {
        $title = $data['title'] ?? null;
        if (!$title) {
            $data['title'] = $this->config->get('App.site_name');
        }

        $description = $data['description'] ?? null;
        if (!$description) {
            $data['description'] = $title;
        }

        $this->template->render('Template/header.tpl.php', $data, $options);
        $this->template->render($template, $data, $options);
        $this->template->render('Template/footer.tpl.php', $data, $options);
    }
}
