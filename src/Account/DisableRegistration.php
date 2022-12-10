<?php

namespace App\Account;

use Pebble\Router;
use Pebble\Exception\ForbiddenException;
use Pebble\URL;
use Diversen\Lang;

/**
 * Middleware class to disable registration
 */
class DisableRegistration
{
    public function check()
    {
        $base_path = URL::getUrlPath(0);
        if ($base_path !== 'account') {
            return;
        }

        $terms_path = URL::getUrlPath(1);
        if ($terms_path === 'terms') {
            return;
        }

        $route = Router::getCurrentRoute();
        $allow_routes = ['/account/signin', '/account/post_login', '/account/logout'];
        if (!in_array($route, $allow_routes)) {
            throw new ForbiddenException(Lang::translate('Route is not allowed'));
        }
    }
}
