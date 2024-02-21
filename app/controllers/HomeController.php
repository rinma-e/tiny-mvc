<?php

namespace App\Controllers;

use App\Core\MyPDO;
use App\Core\Controller;
use App\Core\DatabaseManager;

class HomeController extends Controller
{
    protected $dbManager;

    public function __construct()
    {
        // check is user logged in and redirect to login page if not
        if (!isset($_SESSION['user_id'])) {
            redirect('user/login');
        }
    }

    public function index()
    {
        $data = [
            'page_title' => 'Home',
        ];

        $this->loadView('pages/home', $data);
    }
}
