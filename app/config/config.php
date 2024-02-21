<?php
// choose default core
// 'true' - will show error page if controller/method is not found
// 'false' - will use default controller/method if controller/method is not found
define('CORE_SHOW_ERROR', true);

// set default controller and method
define('DEFAULT_CONTROLLER', 'HomeController');
define('DEFAULT_METHOD', 'index');

// set your website title
define('APP_NAME', 'TinyMVC');

// connection settings
define('DB_DRIVER', 'mysql');
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_NAME', '');
define('DB_CHARSET', 'utf8mb4');

// set time zone
date_default_timezone_set('Europe/Belgrade');

// set app mode (important for error reporting).
// if set to true then in development mode, if set to false in production mode
define('DEVELOPMENT', true);

// protocol type http or https
define('PROTOCOL', 'https');

//APP root folder
define('APP_ROOT', dirname(dirname(__FILE__)));

// upload root folder
define('UPLOAD_ROOT', str_replace("\\", "/", dirname(dirname(__DIR__))) . "/public/");

if (isset($_SERVER['SERVER_NAME'])) {
    // root and asset paths
    $path = str_replace("\\", "/", PROTOCOL . "://" . $_SERVER['SERVER_NAME'] . dirname(dirname(__DIR__))  . "/");
    $path = str_replace($_SERVER['DOCUMENT_ROOT'], "", $path);

    //URL root path
    define('URL_ROOT', $path);

    // assets URL root path
    define('ASSETS', $path . "public/assets/");
}
