<?php

namespace App\Controllers;

class HomeController extends Controller
{

    public function Index()
    {

        view('home.index', compact(['home' => 'welcome here']));
    }
}
