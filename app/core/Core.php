<?php
/*
    *  App Core class
    *  Creates URL & loads core controller
    *  URL FORMAT - controller/method/parameters
    *  NOTE:    CORE_SHOW_ERROR = true => if controller/method is not found it will show 404 page.
    *           CORE_SHOW_ERROR = false => if controller/method is not found it will use DEFAULT_CONTROLLER and DEFAULT_METHOD.
    *           - CORE_SHOW_ERROR, DEFAULT_CONTROLLER and DEFAULT_METHOD are set in config.php
*/

namespace App\Core;

class Core
{
    protected $currentController = DEFAULT_CONTROLLER;
    protected $currentMethod = DEFAULT_METHOD;
    protected $params = [];

    public function __construct()
    {
        //get url
        $url = $this->getURL();

        $page_found = false;

        //if url exist, check is first value is valid controller
        if (!empty($url)) {
            //check if controller class exists
            if (file_exists(APP_ROOT . '\\controllers\\' . ucfirst($url['0']) . 'Controller.php')) {
                //if exist set as current controller
                $current_controller = ucfirst($url['0']) . 'Controller';

                //check is there second element in $url array
                if (!empty($url[1])) {
                    //check is this valid method in controller
                    if (is_callable($current_controller, $url[1])) {
                        $page_found = true;
                        //if method exist set controller and method as current
                        $this->currentController = $current_controller;
                        $this->currentMethod = $url[1];

                        //unset array
                        unset($url['0']);
                        unset($url['1']);

                        //rest of $url elements are our current parameters. If no more elements $params is empty array
                        $this->params = $url ? array_values($url) : [];
                    }
                }
            }
        } else {
            // if no url setting $page_found to true will use default controller
            $page_found = true;
        }

        // if controller/method not found but CORE_SHOW_ERROR = false, it will set $page_found to true and use default controller/method
        if (!$page_found && CORE_SHOW_ERROR === false) {
            $page_found = true;
        }

        if ($page_found) {
            //include file and set as current controller
            require_once APP_ROOT . '\\controllers\\' . $this->currentController . '.php';

            //instantiate current controller class
            $controller = 'App\Controllers\\' . $this->currentController;
            $this->currentController = new $controller;

            //call current controller with current method and parameters
            call_user_func_array([$this->currentController, $this->currentMethod], $this->params);
        } else {
            redirect('error/error404');
        }
    }

    /**
     * Retrieve and sanitize the URL from the query parameters.
     *
     * @return array The sanitized URL segments or [] if 'url' parameter is not set.
     */
    protected function getURL(): array
    {
        if (isset($_GET['url'])) {
            $url = trim($_GET['url'], '/');
            $url = filter_var($url, FILTER_SANITIZE_URL);
            $url = explode('/', $url);
            return $url;
        }
        return [];
    }
}
