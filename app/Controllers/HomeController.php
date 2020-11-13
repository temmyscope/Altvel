<?php

namespace App\Controllers;

class HomeController extends Controller
{

    public function Index($request, $response)
    {
    	$response->send("Welcome Home". $request->input('var'));
      #view('home.index');
    }

    public function all($request, $response){

    	$response->send("Your request got here");
    }
}
