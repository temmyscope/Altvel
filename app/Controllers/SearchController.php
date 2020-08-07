<?php
namespace App\Controllers;

use App\Providers\Request;

class SearchController extends Controller{
	

	public function IndexEndPoint(){
		view('search.index');
	}
	
}
