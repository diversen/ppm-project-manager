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
    /**
     * A route for logging e.g. JS errors using a POST request
     * @route /error/log
     * @verbs POST
     */
    public function ajaxError()
    {

        $error = $_POST['error'] ?? '';
        (new AppMain())->getLog()->error($error);
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

    private function getEnv() {
        $env = (new AppMain())->getConfig()->get('App.env');
        return $env;
    }

    public function templateException(Exception $e) {
        (new AppMain())->getLog()->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);

        // Template errors may come in the middle of some content. So we do not display a complete new page.
        $error_message = "<pre>" . ExceptionTrace::get($e) . "</pre>"; 
        
        if ($this->getEnv() !== 'dev') {
            $error_message . "<pre>" . Lang::translate('A sever error happened. The incidence has been logged.') . "</pre>";
        }

        echo $error_message;
    }

    private function getErrorCode($e) {
        $error_code = $e->getCode();
        if (!$error_code) { 
            $error_code = 500;
        }
        return $error_code;
    }

    private function getErrorMessage($e) {
        $error_message = ExceptionTrace::get($e);
        if ($this->getEnv() !== 'dev') {
            $error_message = '';
        }
        return $error_message;
    }

    public function render(Throwable $e) {

        $error_code = $this->getErrorCode($e);
        http_response_code($error_code);

        if ($error_code === 404) {
            $this->notFoundException($e);
        }
        else if ($error_code === 403) {
            $this->forbiddenException($e);
        }
        else if ($error_code === 510) {
            $this->templateException($e);
        }

        // 500. And anything else
        else {
            $this->internalException($e);
        }
    }

    private function notFoundException(Exception $e) {
        (new AppMain())->getLog()->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(404);
        $this->baseError(Lang::translate('404 Page not found'), $this->getErrorMessage($e));
    }

    private function forbiddenException(Exception $e) {
        (new AppMain())->getLog()->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        $this->baseError(Lang::translate('403 Forbidden'), $this->getErrorMessage($e));
    }

    private function internalException(Throwable $e) {
        (new AppMain())->getLog()->error('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);
        $this->baseError(Lang::translate('500 Internal Server Error'), $this->getErrorMessage($e));
    }
}
