<?php

use App\Core\ErrorHandler;

// Autoload core libraries
spl_autoload_register(function ($className) {
     // replace namespace separators with directory separators in the relative
     // class name, append with .php
     $file = realpath(dirname(__DIR__)) . '\\' . strtolower($className) . '.php';

     // if the file exists, require it
     if (file_exists($file)) {
          require_once $file;
     }
});

// load configurations
require_once 'config/config.php';

// init error handler
$errorHandler = new ErrorHandler();

// load helpers functions
require_once 'helpers/helper_functions.php';

// load session helper
require_once 'helpers/session_helper.php';
