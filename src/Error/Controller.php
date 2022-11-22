<?php

namespace App\Error;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Pebble\Path;
use Diversen\Lang;
use Pebble\ExceptionTrace;
use Exception;
use Pebble\App\StdUtils;
use Throwable;

class Controller extends StdUtils
{
    /**
     * @var \Pebble\Log
     */
    protected $log;

    /**
     * @var \Pebble\Config
     */
    protected $config;

    /**
     * @var \Pebble\Template
     */
    protected $template;

    /**
     * Notice that there is no parent::__construct() call here.
     * This is because we want to initialize as few services as possible.
     */
    public function __construct()
    {
        try {

            $this->template = $this->getTemplate();
            $this->json = $this->getJSON();
            $this->log = $this->getLog();
            $this->config = $this->getConfig();
        } catch (Exception $e) {

            // This is most likely a config dir that can not be read
            // This will throw an exception
            // But can not be logged using the normal LogService
            // Because the normal log service uses Config
            echo $e->getMessage();

            $base_path = Path::getBasePath();
            $log = new Logger('base');
            $log->pushHandler(new StreamHandler($base_path . '/logs/emergency.log', Logger::DEBUG));
            $log->emergency($e->getMessage(), ['trace' => ExceptionTrace::get($e)]);

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

        if ($error_code === 404) {
            $this->notFoundException($e);
        } elseif ($error_code === 403) {
            $this->forbiddenException($e);
        } elseif ($error_code === 510) {
            $this->templateException($e);
        }

        // Anything else
        else {
            if (is_int($error_code)) {
                http_response_code($error_code);
            } else {
                http_response_code(500);
            }
            $this->internalException($e);
        }
    }

    private function notFoundException(Exception $e)
    {
        $this->log->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
        $this->baseError(Lang::translate('404 Page not found'), $this->getErrorMessage($e));
    }

    private function forbiddenException(Exception $e)
    {
        $this->log->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
        $this->baseError(Lang::translate('403 Forbidden'), $this->getErrorMessage($e));
    }

    private function templateException(Throwable $e)
    {
        $this->log->error('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        $this->baseError(Lang::translate('510 Template error'), $this->getErrorMessage($e));
    }

    private function internalException(Throwable $e)
    {
        $this->log->error('App.index.exception', ['exception' => ExceptionTrace::get($e)]);
        $this->baseError(Lang::translate('500 Internal Server Error'), $this->getErrorMessage($e));
    }

}
