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

// path to app folder (absolute path)
define('APP_ROOT', dirname(dirname(__FILE__)));

// path to upload root folder (absolute path)
define('UPLOAD_ROOT', str_replace("\\", "/", dirname(dirname(__DIR__))) . "/public/");

if (isset($_SERVER['SERVER_NAME'])) {
    // URL path very if we use virtual host or not
    if (isset($_SERVER['IS_VIRTUAL_HOST_ALIAS'])) {
        $path = PROTOCOL . "://" . $_SERVER['HTTP_HOST'] . '/';
    } else {
        $projectBasePath = explode("\\", dirname(dirname(__DIR__)));
        $projectBaseName = end($projectBasePath);
        $path = PROTOCOL . "://" . $_SERVER['SERVER_NAME'] . "/" . $projectBaseName  . "/";
    }

    // Define the URL root path
    define('URL_ROOT', $path);

    // Assets URL root path
    define('ASSETS', $path . "assets/");
}
