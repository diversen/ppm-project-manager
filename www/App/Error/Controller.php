<?php

namespace App\Error;

use Diversen\Lang;

class Controller
{

    private function baseError(string $title, string $error_message) {

        if (empty($error_message)) {
            $error_message = Lang::translate('Page not found. If this page has existed then it has been deleted');
        }

        $error_vars = [
            'title' => $title,
            'message' => $error_message,
        ];

        \Pebble\Template::render('App/Error/error.tpl.php',
            $error_vars
        );
    }

    /**
     * Not found errror
     */
    public function notFound(string $error_message = '')
    {

        header('HTTP/1.0 404 Not Found');
        $this->baseError(Lang::translate('404 Page not found'), $error_message);
    }

    /**
     * Forbidden error
     */
    public function forbidden(string $error_message = '')
    {

        header('HTTP/1.0 403 Forbidden');
        $this->baseError(Lang::translate('403 Forbidden'), $error_message);

    }

    /**
     * Server error. Any other error than forbidden and not found.
     */
    public function error(string $error_message = '')
    {
        Lang::translate('Hello world');
        header('HTTP/1.0 500 Internal Server Error');
        $this->baseError(Lang::translate('500 Internal Server Error'), $error_message);
    }
}