<?php

namespace App\Error;

use Diversen\Lang;

class Controller
{

    /**
     * Not found errror
     */
    public function notFound(string $error_message = '')
    {

        header('HTTP/1.0 404 Not Found');

        if (empty($error_message)) {
            $error_message = Lang::translate('Page not found. If this page has existed then it has been deleted');
        }

        $error_vars = [
            'title' => Lang::translate('404 Page not found'),
            'message' => $error_message,
        ];

        \Pebble\Template::render('App/Error/error.tpl.php',
            $error_vars
        );

    }

    /**
     * Forbidden error
     */
    public function forbidden(string $error_message = '')
    {

        header('HTTP/1.0 403 Forbidden');

        if (empty($error_message)) {
            $error_message = Lang::translate('Access denied. If you are not signed-in, then please sign in. If you are signed in, then you do not have the rights to view this page.');
        }

        $error_vars = [
            'title' => '403 Forbidden',
            'message' => $error_message,
        ];

        \Pebble\Template::render('App/Error/error.tpl.php',
            $error_vars
        );

    }

    /**
     * Server error. Any other error than forbidden and not found.
     */
    public function error(string $error_message = '')
    {

        header('HTTP/1.0 500 Internal Server Error');

        if (empty($error_message)) {
            $error_message = Lang::translate('Something went wrong. We will look into it.');
        }

        $error_vars = [
            'title' => '500 Internal Server Error',
            'message' => $error_message,
        ];

        \Pebble\Template::render('App/Error/error.tpl.php',
            $error_vars
        );
    }
}
