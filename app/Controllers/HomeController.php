<?php

namespace App\Controllers;

class HomeController extends Controller
{

    public function Index()
    {
    		echo "Here we go";
        view('home.index');
    }
}
