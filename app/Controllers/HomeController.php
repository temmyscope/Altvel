<?php

namespace App\Controllers;

class HomeController extends Controller
{



    public function IndexEndPoint()
    {
        view('home.index');
    }
}
