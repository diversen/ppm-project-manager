<?php

namespace App\Error;

use Diversen\Lang;
use Pebble\JSON;
use Pebble\ExceptionTrace;
use App\AppMain;
use Exception;
use Throwable;

class Controller
{
    public $app_main = null;
    public $log;
    public function __construct()
    {
        $this->app_main = new AppMain();
        $this->log = $this->app_main->getLog();
    }
    /**
     * A route for logging e.g. JS errors using a POST request
     * @route /error/log
     * @verbs POST
     */
    public function ajaxError()
    {

        $error = $_POST['error'] ?? '';
        $this->log->error($error);
        echo JSON::response(['logged' => true]);
    }

    private function baseError(string $title, string $error_message)
    {

        $error_vars = [
            'title' => $title,
            'message' => $error_message,
        ];

        \Pebble\Template::render(
            'Error/error.tpl.php',
            $error_vars
        );
    }

    public function templateException(Exception $e) {
        $this->log->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);

        // Template errors may come in the middle of some content. So we do not display a complete new page.
        $error_message = "<pre>" . ExceptionTrace::get($e) . "</pre>"; 
        if ($this->app_main->getConfig()->get('App.env') !== 'dev') {
            $error_message . "<pre>" . Lang::translate('A sever error happened. The incidence has been logged.') . "</pre>";
        }

        echo $error_message;
    }

    private function getErrorMessage($e) {
        $error_message = ExceptionTrace::get($e);
        if ($this->app_main->getConfig()->get('App.env') !== 'dev') {
            $error_message = '';
        }
        return $error_message;
    }

    public function notFoundException(Exception $e) {
        $this->log->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(404);
        $this->baseError(Lang::translate('404 Page not found'), $this->getErrorMessage($e));
    }

    public function forbiddenException(Exception $e) {
        $this->log->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        $this->baseError(Lang::translate('403 Forbidden'), $this->getErrorMessage($e));
    }

    public function internalException(Throwable $e) {
        $this->log->error('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);
        $this->baseError(Lang::translate('500 Internal Server Error'), $this->getErrorMessage($e));
    }
}
