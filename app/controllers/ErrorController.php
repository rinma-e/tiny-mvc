<?php

namespace App\Controllers;

use App\Core\Controller;

class ErrorController extends Controller
{
    public function error404()
    {
        $this->loadView('errors/404');
    }

    public function error500()
    {
        $this->loadView('errors/500');
    }
}
