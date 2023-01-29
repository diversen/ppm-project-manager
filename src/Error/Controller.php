<?php

namespace App\Error;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Pebble\Path;
use Diversen\Lang;
use Pebble\ExceptionTrace;
use Pebble\JSON;
use Exception;
use App\AppUtils;
use Throwable;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\TemplateException;
use Pebble\Exception\JSONException;

class Controller extends AppUtils
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
        $this->json->renderSuccess(['logged' => true]);
    }

    private function baseError(string $title, string $error_message)
    {
        $meta_container = $this->getDataContainer();
        $data = [
            'title' => $title,
            'description' => $title,
            'message' => $error_message,
        ];

        $content = $this->template->getOutput('Error/error.tpl.php', $data);
        $meta_container->setData('content', $content);
        $this->template->render('Template/page_error.tpl.php', $data);
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
        $class = get_class($e);

        if ($class === NotFoundException::class) {
            $this->log->notice("App.not_found.exception", ['url' => $_SERVER['REQUEST_URI']]);
            $this->baseError(Lang::translate('404 Page not found'), $this->getErrorMessage($e));
        } elseif ($class === ForbiddenException::class) {
            $this->log->notice("App.forbidden.exception", ['url' => $_SERVER['REQUEST_URI']]);
            $this->baseError(Lang::translate('403 Forbidden'), $this->getErrorMessage($e));
        } elseif ($class === TemplateException::class) {
            $error_vars = [
                'title' => Lang::translate('510 Template error'),
                'message' => ExceptionTrace::get($e),
            ];

            $this->log->error('App.template.exception', ['exception' => ExceptionTrace::get($e)]);
            $this->template->render(
                'Error/error.tpl.php',
                $error_vars
            );
        } elseif ($class === JSONException::class) {
            // JSONException is not logged. Should be logged in a controller class

            $response['message'] = $e->getMessage();
            $response['code'] = $error_code;

            $json = new JSON();
            $json->renderError($response);
        } else {
            $this->log->error('App.internal.exception', ['exception' => ExceptionTrace::get($e)]);
            $this->baseError(Lang::translate('500 Internal Server Error'), $this->getErrorMessage($e));
        }
    }
}
