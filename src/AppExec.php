<?php

namespace App;

use Pebble\ExceptionTrace;
use Pebble\Exception\ForbiddenException;
use Pebble\Exception\NotFoundException;
use Pebble\Exception\TemplateException;
use App\AppMain;

use App\Error\Controller as ErrorController;
use Diversen\Lang;

use Throwable;
use Exception;

/**
 * Class that executes the App
 * It catches 'ForbiddenException' 'NotFoundException', 'Throwable'
 * The App being executed is found in AppMain
 */

class AppExec
{

    public function run()
    {

        $error = new ErrorController();
        try {
            $app_main = new AppMain();
            $app_main->run();
        } catch (TemplateException $e) {
            $exception_str = ExceptionTrace::get($e);
            $app_main->getLog()->error('App.index.exception', ['exception' => $exception_str]);

            if ($app_main->getConfig()->get('App.env') !== 'dev') {
                $exception_str = Lang::translate('A sever error happened. The incidence has been logged.');
            }

            echo "<pre>" . $exception_str . "</pre>";
        } catch (NotFoundException $e) {
            $app_main->getLog()->notice("App.index.not_found ", ['url' => $_SERVER['REQUEST_URI']]);
            $error->notFound($e->getMessage());
        } catch (ForbiddenException $e) {

            // These exceptions are logged in controllers
            $app_main->getLog()->notice("App.index.forbidden", ['url' => $_SERVER['REQUEST_URI']]);
            $error->forbidden($e->getMessage());
        } catch (Throwable $e) {
            $exception_str = ExceptionTrace::get($e);

            try {
                $app_main->getLog()->error('App.index.exception', ['exception' => $exception_str]);
            } catch (Exception $e) {
                $error->error($e->getMessage());
                return;
            }

            if ($app_main->getConfig()->get('App.env') !== 'dev') {
                $exception_str = Lang::translate('A server error happened. The incidence has been logged.');
            }

            $error->error($exception_str);
        }
    }
}
