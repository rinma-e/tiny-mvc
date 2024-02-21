<?php

namespace App\Core;

use ErrorException;

class ErrorHandler
{
    public function __construct()
    {
        error_reporting(E_ALL);

        if (DEVELOPMENT) {
            ini_set('display_errors', '1');
        } else {
            ini_set('display_errors', '0');
            ini_set('log_errors', '1');
        }

        $this->registerHandlers();
    }

    public function handleException($e)
    {
        error_log(date('[d/m/Y H:i:s]', time()) . "->" . $e . "\r\n", 3, APP_ROOT . "\\logs\\" . date('d.m.Y', time()) . "-errors.log");
        http_response_code(500);
        if (filter_var(ini_get('display_errors'), FILTER_VALIDATE_BOOLEAN)) {
            echo "<pre>" . $e . "</pre>";
        } else {
            redirect('error/error500');
        }
        exit;
    }

    public function registerHandlers()
    {
        set_exception_handler([$this, 'handleException']);

        set_error_handler(function ($level, $message, $file = '', $line = 0) {
            throw new ErrorException($message, 0, $level, $file, $line);
        });

        register_shutdown_function(function () {
            $error = error_get_last();
            if ($error !== null) {
                $e = new ErrorException(
                    $error['message'],
                    0,
                    $error['type'],
                    $error['file'],
                    $error['line']
                );
                $this->handleException($e);
            }
        });
    }
}
