<?php

declare(strict_types=1);

namespace App\Account;

use Pebble\Router\Request;
use Pebble\Exception\ForbiddenException;
use Pebble\URL;
use Diversen\Lang;

/**
 * Middleware class to disable registration
 */
class DisableRegistration
{
    public function check(Request $request): void
    {
        $base_path = URL::getUrlPath(0);
        if ($base_path !== 'account') {
            return;
        }

        $terms_path = URL::getUrlPath(1);
        if ($terms_path === 'terms') {
            return;
        }

        $route = $request->getCurrentRoute();
        $allow_routes = ['/account/signin', '/account/post_signin', '/account/logout'];
        if (!in_array($route, $allow_routes)) {
            throw new ForbiddenException(Lang::translate('Route is not allowed'));
        }
    }
}
