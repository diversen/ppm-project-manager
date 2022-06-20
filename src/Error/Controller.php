<?php

namespace App\Error;

use Diversen\Lang;
use Pebble\ExceptionTrace;
use Pebble\App\AppBase;
use Exception;
use Throwable;

class Controller
{

    /**
     * @var \Pebble\Log
     */
    private $log;

    /**
     * @var \Pebble\Config
     */
    private $config;

    /**
     * @var \Pebble\Template
     */
    private $template;

    /**
     * Notice this does not extend StdUtils because
     * we only want as few requirements as possible here. 
     * In order to almost always show an appropriate error message
     */
    public function __construct()
    {
        try {

            $this->app_base = new AppBase();
            $this->template = $this->app_base->getTemplate();
            $this->json = $this->app_base->getJSON();
            $this->log = $this->app_base->getLog();
            $this->config = $this->app_base->getConfig();
            
        } catch (Exception $e) {

            // This os most likely a config dir that can not be read
            // This will throw an exception
            echo $e->getMessage();
            error_log(ExceptionTrace::get($e));
            exit();
        }
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
        $this->json->render(['logged' => true]);
    }

    private function baseError(string $title, string $error_message)
    {

        $error_vars = [
            'title' => $title,
            'message' => $error_message,
        ];

        $this->template->render(
            'Error/error.tpl.php',
            $error_vars
        );
    }

    private function getEnv()
    {
        $env = $this->config->get('App.env');
        return $env;
    }

    public function templateException(Exception $e)
    {
        $this->log->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);

        // Template errors may come in the middle of some content. So we do not display a complete new page.
        $error_message = "<pre>" . ExceptionTrace::get($e) . "</pre>";

        if ($this->getEnv() !== 'dev') {
            $error_message . "<pre>" . Lang::translate('A sever error happened. The incidence has been logged.') . "</pre>";
        }

        echo $error_message;
    }

    private function getErrorCode(Throwable $e)
    {
        $error_code = $e->getCode();
        if (!$error_code) {
            $error_code = 500;
        }
        return $error_code;
    }

    private function getErrorMessage(Throwable $e)
    {
        $error_message = ExceptionTrace::get($e);
        if ($this->getEnv() !== 'dev') {
            $error_message = '';
        }
        return $error_message;
    }

    public function render(Throwable $e)
    {

        $error_code = $this->getErrorCode($e);
        http_response_code($error_code);

        if ($error_code === 404) {
            $this->notFoundException($e);
        } else if ($error_code === 403) {
            $this->forbiddenException($e);
        } else if ($error_code === 510) {
            $this->templateException($e);
        }

        // 500. And anything else
        else {
            $this->internalException($e);
        }
    }

    private function notFoundException(Exception $e)
    {
        $this->log->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(404);
        $this->baseError(Lang::translate('404 Page not found'), $this->getErrorMessage($e));
    }

    private function forbiddenException(Exception $e)
    {
        $this->log->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        http_response_code(403);
        $this->baseError(Lang::translate('403 Forbidden'), $this->getErrorMessage($e));
    }

    private function internalException(Throwable $e)
    {
        $this->log->error('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        http_response_code(500);
        $this->baseError(Lang::translate('500 Internal Server Error'), $this->getErrorMessage($e));
    }
}
