<?php

namespace App\Controllers;

class HomeController extends Controller
{

    public function Index($request, $response)
    {
    	$response->send("Here we go");
        #view('home.index');
    }
}
