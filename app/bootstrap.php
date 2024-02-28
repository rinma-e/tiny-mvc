<?php

use App\Core\ErrorHandler;

// Autoload classes with composer
require_once __DIR__ . '/../vendor/autoload.php';

// load configurations
require_once 'config/config.php';

// init error handler
$errorHandler = new ErrorHandler();

// load helpers functions
require_once 'helpers/helper_functions.php';

// load session helper
require_once 'helpers/session_helper.php';
